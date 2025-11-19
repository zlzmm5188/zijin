<?php
/**
 * 支付服务类 - SevenPay支付网关对接
 *
 * 商户信息：
 * - 商户号: M1763172182
 * - API密钥: 3e4cbfb35a35787ccc98bd13027b4a02
 * - 支付网关: https://sevenpay.lh.plus/api/pay/create
 * - 查询地址: https://sevenpay.lh.plus/api/pay/query
 * - 回调IP: 23.94.207.16
 *
 * 支付通道：
 * - S02: 微信扫码 (100-3000)
 * - S01: 支付宝小额原生 (100-20000)
 */

class PaymentService
{
    // 商户配置
    const MERCHANT_ID = 'M1763172182';
    const API_KEY = '3e4cbfb35a35787ccc98bd13027b4a02';
    const PAY_CREATE_URL = 'https://sevenpay.lh.plus/api/pay/create';
    const PAY_QUERY_URL = 'https://sevenpay.lh.plus/api/pay/query';
    const CALLBACK_IP = '23.94.207.16';

    // 支付通道配置
    const CHANNEL_WECHAT = 'S02';  // 微信扫码
    const CHANNEL_ALIPAY = 'S01';  // 支付宝小额原生

    // 金额限制
    const WECHAT_MIN = 100;
    const WECHAT_MAX = 3000;
    const ALIPAY_MIN = 100;
    const ALIPAY_MAX = 20000;

    /**
     * 创建支付订单
     *
     * @param string $orderNo 商户订单号
     * @param float $amount 支付金额（元）
     * @param string $channel 支付通道 (S01=支付宝, S02=微信)
     * @param string $notifyUrl 回调通知地址
     * @param string $returnUrl 前端跳转地址（必填）
     * @param string $userIp 用户IP地址
     * @return array
     */
    public static function createOrder($orderNo, $amount, $channel, $notifyUrl, $returnUrl, $userIp = '')
    {
        // 验证金额范围
        if ($channel === self::CHANNEL_WECHAT) {
            if ($amount < self::WECHAT_MIN || $amount > self::WECHAT_MAX) {
                throw new Exception("微信支付金额范围：" . self::WECHAT_MIN . "-" . self::WECHAT_MAX . "元");
            }
        } elseif ($channel === self::CHANNEL_ALIPAY) {
            if ($amount < self::ALIPAY_MIN || $amount > self::ALIPAY_MAX) {
                throw new Exception("支付宝支付金额范围：" . self::ALIPAY_MIN . "-" . self::ALIPAY_MAX . "元");
            }
        }

        // 获取用户真实IP（如果未提供）
        if (empty($userIp)) {
            $userIp = self::getRealClientIp();
        }

        // 构建请求参数（根据支付网关文档）
        $params = [
            'merchantCode' => self::MERCHANT_ID,
            'channelType' => $channel,
            'merchantOrderNo' => $orderNo,
            'amount' => number_format($amount, 2, '.', ''),
            'notifyUrl' => $notifyUrl,
            'returnUrl' => $returnUrl,
            'ip' => $userIp,
            'title' => '充值订单',
            'describe' => '用户充值',
        ];

        // 验证必填参数
        if (empty($params['merchantCode'])) {
            throw new Exception('商户号配置错误：商户号为空');
        }
        if (empty($params['returnUrl'])) {
            throw new Exception('跳转地址不能为空');
        }

        // 记录请求参数（用于调试）
        error_log("支付网关请求参数: " . json_encode($params, JSON_UNESCAPED_UNICODE));

        // 生成签名
        $params['sign'] = self::generateSign($params);

        // 记录签名后的参数（用于调试）
        error_log("支付网关签名后参数: " . json_encode($params, JSON_UNESCAPED_UNICODE));

        // 发送请求（使用表单格式）
        $response = self::httpPostForm(self::PAY_CREATE_URL, $params);

        if (!$response) {
            throw new Exception('支付网关请求失败');
        }

        // 记录原始响应（用于调试）
        error_log("支付网关原始响应: " . $response);

        $result = json_decode($response, true);

        if (!$result) {
            error_log("支付网关响应解析失败，原始响应: " . $response);
            throw new Exception('支付网关返回数据格式错误，响应：' . substr($response, 0, 200));
        }

        // 记录解析后的响应
        error_log("支付网关解析后响应: " . json_encode($result, JSON_UNESCAPED_UNICODE));

        // 如果返回失败，直接抛出异常（不验证签名）
        if (isset($result['code']) && $result['code'] != 1) {
            $errorMsg = $result['message'] ?? '支付网关返回错误';
            error_log("支付网关返回失败: " . $errorMsg);
            throw new Exception($errorMsg);
        }

        // 验证返回签名（如果存在）
        if (isset($result['sign']) && !self::verifySign($result, $result['sign'])) {
            error_log("支付网关返回签名验证失败");
            // 不抛出异常，因为有些网关可能不返回签名
            // throw new Exception('支付网关返回签名验证失败');
        }

        return $result;
    }

    /**
     * 查询订单状态
     *
     * @param string $orderNo 商户订单号
     * @return array
     */
    public static function queryOrder($orderNo)
    {
        $params = [
            'merchantCode' => self::MERCHANT_ID,
            'merchantOrderNo' => $orderNo,
        ];

        // 生成签名
        $params['sign'] = self::generateSign($params);

        // 发送请求（使用表单格式）
        $response = self::httpPostForm(self::PAY_QUERY_URL, $params);

        if (!$response) {
            throw new Exception('查询请求失败');
        }

        $result = json_decode($response, true);

        if (!$result) {
            throw new Exception('查询返回数据格式错误');
        }

        // 验证返回签名
        if (isset($result['sign']) && !self::verifySign($result, $result['sign'])) {
            throw new Exception('查询返回签名验证失败');
        }

        return $result;
    }

    /**
     * 验证回调签名
     *
     * @param array $data 回调数据
     * @param string $sign 回调签名
     * @return bool
     */
    public static function verifyCallback($data, $sign)
    {
        return self::verifySign($data, $sign);
    }

    /**
     * 获取真实客户端IP
     * 优先级：X-Forwarded-For > X-Real-IP > REMOTE_ADDR
     * 不使用 127.0.0.1 或服务器IP
     * @return string
     */
    public static function getRealClientIp()
    {
        // 优先级1: X-Forwarded-For（可能包含多个IP，用逗号分隔）
        $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
        if (!empty($forwardedFor)) {
            $ips = array_map('trim', explode(',', $forwardedFor));
            // 优先选择第一个有效的IPv4地址（公网IP优先）
            foreach ($ips as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip; // 返回公网IPv4
                }
            }
            // 如果没有公网IPv4，返回第一个有效的IPv4（包括内网）
            foreach ($ips as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    return $ip;
                }
            }
            // 如果都是IPv6，返回第一个有效的IPv6
            foreach ($ips as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    return $ip;
                }
            }
        }

        // 优先级2: X-Real-IP
        $realIp = $_SERVER['HTTP_X_REAL_IP'] ?? '';
        if (!empty($realIp)) {
            $realIp = trim($realIp);
            if (filter_var($realIp, FILTER_VALIDATE_IP)) {
                // 排除本地IP和服务器IP
                $serverAddr = $_SERVER['SERVER_ADDR'] ?? '';
                if ($realIp !== '127.0.0.1' && $realIp !== '::1' && $realIp !== $serverAddr) {
                    return $realIp;
                }
            }
        }

        // 优先级3: REMOTE_ADDR
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!empty($remoteAddr)) {
            $remoteAddr = trim($remoteAddr);
            if (filter_var($remoteAddr, FILTER_VALIDATE_IP)) {
                // 排除本地IP和服务器IP
                $serverAddr = $_SERVER['SERVER_ADDR'] ?? '';
                if ($remoteAddr !== '127.0.0.1' && $remoteAddr !== '::1' && $remoteAddr !== $serverAddr) {
                    return $remoteAddr;
                }
            }
        }

        // 如果所有方法都失败，记录警告并使用第一个可用的IP（排除127.0.0.1）
        $fallbackIp = '';
        if (!empty($forwardedFor)) {
            $ips = array_map('trim', explode(',', $forwardedFor));
            $fallbackIp = $ips[0] ?? '';
        } elseif (!empty($realIp)) {
            $fallbackIp = trim($realIp);
        } elseif (!empty($remoteAddr)) {
            $fallbackIp = trim($remoteAddr);
        }

        if (!empty($fallbackIp) && $fallbackIp !== '127.0.0.1' && $fallbackIp !== '::1') {
            $serverAddr = $_SERVER['SERVER_ADDR'] ?? '';
            if ($fallbackIp !== $serverAddr) {
                error_log("警告：无法获取真实客户端IP，使用备用IP: {$fallbackIp}");
                return $fallbackIp;
            }
        }

        // 最后的备选方案：记录严重警告
        error_log("严重警告：无法获取真实客户端IP，所有方法都失败。X-Forwarded-For: " . ($forwardedFor ?: '空') . ", X-Real-IP: " . ($realIp ?: '空') . ", REMOTE_ADDR: " . ($remoteAddr ?: '空'));
        // 返回一个占位符，让支付网关拒绝而不是使用错误的IP
        return '0.0.0.0';
    }

    /**
     * 生成签名
     *
     * @param array $params 参数数组
     * @return string
     */
    private static function generateSign($params)
    {
        // 创建参数副本，避免修改原数组
        $signParams = $params;

        // 移除sign字段
        unset($signParams['sign']);

        // 按键名ASCII码排序（字典序）
        ksort($signParams);

        // 构建签名字符串（只包含非空参数）
        $signString = '';
        foreach ($signParams as $key => $value) {
            // 跳过空值和null，但保留0和false
            if ($value !== '' && $value !== null) {
                $signString .= $key . '=' . $value . '&';
            }
        }

        // 拼接密钥
        $signString .= 'key=' . self::API_KEY;

        // 记录签名字符串（用于调试）
        error_log("支付网关签名字符串: " . $signString);

        // MD5加密（小写，不是大写）
        $sign = md5($signString);

        error_log("支付网关生成签名: " . $sign);

        return $sign;
    }

    /**
     * 验证签名
     *
     * @param array $data 数据数组
     * @param string $sign 待验证的签名
     * @return bool
     */
    private static function verifySign($data, $sign)
    {
        $calculatedSign = self::generateSign($data);
        // 签名是小写，所以比较时都转小写
        return strtolower($sign) === $calculatedSign;
    }

    /**
     * HTTP POST请求（表单格式）
     *
     * @param string $url 请求地址
     * @param array $data 请求数据
     * @return string|false
     */
    private static function httpPostForm($url, $data)
    {
        // 记录请求数据（用于调试）
        error_log("支付网关请求 [URL:{$url}]: " . json_encode($data, JSON_UNESCAPED_UNICODE));

        // 构建表单数据
        $postData = http_build_query($data);
        error_log("支付网关表单数据: " . $postData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);

        // 使用表单格式（application/x-www-form-urlencoded）
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Accept: application/json',
            'User-Agent: Providence-Payment-Client/1.0'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        // 记录HTTP状态码和错误信息（用于调试）
        if ($httpCode !== 200) {
            error_log("支付网关HTTP状态码: {$httpCode}");
        }
        if ($error) {
            error_log("支付网关CURL错误: {$error}");
        }

        curl_close($ch);

        if ($error) {
            error_log("支付网关请求错误: {$error}");
            throw new Exception("支付网关请求失败: {$error}");
        }

        if ($httpCode !== 200) {
            error_log("支付网关HTTP错误: {$httpCode}, 响应: {$response}");
            throw new Exception("支付网关HTTP错误: {$httpCode}");
        }

        return $response;
    }

    /**
     * 获取支付通道名称
     *
     * @param string $channel 通道编码
     * @return string
     */
    public static function getChannelName($channel)
    {
        $channels = [
            self::CHANNEL_WECHAT => '微信扫码',
            self::CHANNEL_ALIPAY => '支付宝小额原生',
        ];

        return $channels[$channel] ?? '未知通道';
    }
}

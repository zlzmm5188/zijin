<?php
/**
 * TronGrid API 服务类
 *
 * 用于查询TRON链上交易，特别是USDT (TRC20) 交易
 *
 * API文档: https://developers.tron.network/reference/background
 * API Key: 0df6bed7-990e-44ee-a67f-291672963894
 */

class TronGridService
{
    // TronGrid API配置
    const API_BASE_URL = 'https://api.trongrid.io';
    const API_KEY = '0df6bed7-990e-44ee-a67f-291672963894';

    // USDT TRC20 合约地址
    const USDT_CONTRACT = 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t';

    /**
     * 获取账户信息（包括TRC20代币余额）
     *
     * @param string $address TRON地址
     * @return array
     */
    public static function getAccountInfo($address)
    {
        $url = self::API_BASE_URL . '/v1/accounts/' . $address;

        $response = self::httpGet($url);

        if (!$response || !isset($response['data']) || empty($response['data'])) {
            return null;
        }

        return $response['data'][0] ?? null;
    }

    /**
     * 获取账户的TRC20代币交易记录
     *
     * @param string $address TRON地址
     * @param string $contractAddress 合约地址（USDT: TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t）
     * @param int $limit 返回数量限制
     * @param string $fingerprint 分页标识
     * @return array
     */
    public static function getTrc20Transactions($address, $contractAddress = self::USDT_CONTRACT, $limit = 200, $fingerprint = '')
    {
        $url = self::API_BASE_URL . '/v1/accounts/' . $address . '/transactions/trc20';

        $params = [
            'limit' => $limit,
            'contract_address' => $contractAddress,
            'only_confirmed' => 'true',
            'only_to' => 'true', // 只获取转入交易
        ];

        if (!empty($fingerprint)) {
            $params['fingerprint'] = $fingerprint;
        }

        $queryString = http_build_query($params);
        $url .= '?' . $queryString;

        $response = self::httpGet($url);

        if (!$response || !isset($response['data'])) {
            return [];
        }

        return $response['data'] ?? [];
    }

    /**
     * 获取账户的USDT余额
     *
     * @param string $address TRON地址
     * @return float USDT余额（已除以10^6，因为USDT精度是6位）
     */
    public static function getUsdtBalance($address)
    {
        $accountInfo = self::getAccountInfo($address);

        if (!$accountInfo || !isset($accountInfo['trc20'])) {
            return 0;
        }

        // 查找USDT余额
        foreach ($accountInfo['trc20'] as $token) {
            if (isset($token[self::USDT_CONTRACT])) {
                // USDT精度是6位，需要除以10^6
                return floatval($token[self::USDT_CONTRACT]) / 1000000;
            }
        }

        return 0;
    }

    /**
     * 检查指定地址是否收到指定金额的USDT
     *
     * @param string $address TRON地址
     * @param float $expectedAmount 期望金额
     * @param int $sinceTimestamp 检查此时间之后的交易（Unix时间戳，秒）
     * @return array|null 返回匹配的交易信息，如果没有则返回null
     */
    public static function checkUsdtPayment($address, $expectedAmount, $sinceTimestamp = 0)
    {
        // 获取最近的TRC20交易
        $transactions = self::getTrc20Transactions($address, self::USDT_CONTRACT, 50);

        if (empty($transactions)) {
            return null;
        }

        // 将期望金额转换为最小单位（USDT精度6位）
        $expectedAmountInSmallestUnit = intval($expectedAmount * 1000000);

        // 检查每笔交易
        foreach ($transactions as $tx) {
            // 检查交易时间
            if (isset($tx['block_timestamp']) && $tx['block_timestamp'] < $sinceTimestamp * 1000) {
                continue; // 交易时间早于订单创建时间
            }

            // 检查是否是转入交易（to == 平台地址）
            if (isset($tx['to']) && strtolower($tx['to']) === strtolower($address)) {
                // 检查金额（允许0.01 USDT的误差）
                $txAmount = intval($tx['value'] ?? 0);
                $amountDiff = abs($txAmount - $expectedAmountInSmallestUnit);

                // 允许1 USDT的误差（考虑到网络费用等）
                if ($amountDiff <= 1000000) {
                    return [
                        'txid' => $tx['transaction_id'] ?? '',
                        'from' => $tx['from'] ?? '',
                        'to' => $tx['to'] ?? '',
                        'amount' => $txAmount / 1000000, // 转换为USDT
                        'block_timestamp' => isset($tx['block_timestamp']) ? intval($tx['block_timestamp'] / 1000) : 0,
                        'confirmed' => isset($tx['confirmed']) ? $tx['confirmed'] : false,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * HTTP GET 请求
     *
     * @param string $url 请求URL
     * @return array|false
     */
    private static function httpGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'TRON-PRO-API-KEY: ' . self::API_KEY,
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("TronGrid API请求错误: {$error}");
            return false;
        }

        if ($httpCode !== 200) {
            error_log("TronGrid API HTTP错误: {$httpCode}, 响应: {$response}");
            return false;
        }

        $result = json_decode($response, true);

        if (!$result) {
            error_log("TronGrid API响应解析失败: {$response}");
            return false;
        }

        return $result;
    }

    /**
     * 将TRON地址转换为Base58格式（如果需要）
     *
     * @param string $address 地址
     * @return string
     */
    public static function normalizeAddress($address)
    {
        // TronGrid API接受Base58格式的地址
        // 如果地址已经是Base58格式，直接返回
        return $address;
    }
}

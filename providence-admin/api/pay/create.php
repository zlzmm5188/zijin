<?php

/**
 * 创建支付订单接口
 * POST /index.php/pay/create
 *
 * 对接SevenPay支付网关，创建微信/支付宝支付订单
 */

require_once __DIR__ . '/../../config/bootstrap.php';

// 加载支付服务
require_once __DIR__ . '/../services/PaymentService.php';

// 验证Token并获取用户信息 - 已禁用：无登录模式
$authUser = Auth::user(); // 始终返回 guest 用户
// if (!$authUser) {
//     error_log("支付订单创建失败：Token验证失败，Token: " . ($token ? substr($token, 0, 20) . '...' : 'empty'));
//     Response::error('未登录或登录已过期，请重新登录', 401);
// }

// 验证用户ID是否存在 - 已禁用
// if (empty($authUser['user_id'])) {
//     error_log("支付订单创建失败：用户ID为空，Auth数据: " . json_encode($authUser, JSON_UNESCAPED_UNICODE));
//     Response::error('用户身份验证失败，请重新登录', 401);
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$data = json_decode(file_get_contents('php://input'), true);
$amount = (float)($data['amount'] ?? 0);
$paymentMethod = trim($data['payment_method'] ?? ''); // wechat 或 alipay
$returnUrl = trim($data['return_url'] ?? ''); // 前端跳转地址（必填）

// 验证参数
if ($amount <= 0) {
    Response::error('支付金额必须大于0');
}

if (!in_array($paymentMethod, ['wechat', 'alipay'])) {
    Response::error('支付方式错误，仅支持微信或支付宝');
}

// 确定支付通道
$channel = $paymentMethod === 'wechat' ? PaymentService::CHANNEL_WECHAT : PaymentService::CHANNEL_ALIPAY;

// 验证金额范围
if ($channel === PaymentService::CHANNEL_WECHAT) {
    if ($amount < PaymentService::WECHAT_MIN || $amount > PaymentService::WECHAT_MAX) {
        Response::error("微信支付金额范围：" . PaymentService::WECHAT_MIN . "-" . PaymentService::WECHAT_MAX . "元");
    }
} elseif ($channel === PaymentService::CHANNEL_ALIPAY) {
    if ($amount < PaymentService::ALIPAY_MIN || $amount > PaymentService::ALIPAY_MAX) {
        Response::error("支付宝支付金额范围：" . PaymentService::ALIPAY_MIN . "-" . PaymentService::ALIPAY_MAX . "元");
    }
}

$userId = $authUser['user_id'];
$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 生成商户订单号（确保唯一性）
    $maxAttempts = 10;
    $orderNo = null;
    for ($i = 0; $i < $maxAttempts; $i++) {
        $orderNo = 'RCH' . date('YmdHis') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

        // 检查订单号是否已存在
        $existing = $db->fetchOne(
            "SELECT id FROM recharge_records WHERE order_no = :order_no LIMIT 1",
            ['order_no' => $orderNo]
        );

        if (!$existing) {
            break; // 订单号唯一，可以使用
        }

        // 如果订单号已存在，等待1毫秒后重试
        usleep(1000);
    }

    if (!$orderNo) {
        throw new Exception('生成订单号失败，请重试');
    }

    // 验证订单号是否成功生成
    if (empty($orderNo) || strlen($orderNo) < 10) {
        throw new Exception('订单号格式错误');
    }

    // 构建回调地址（使用固定域名，确保回调地址正确）
    // 注意：回调地址必须与支付网关后台配置的白名单一致
    $notifyUrl = 'https://apis.copla.top/index.php/pay/notify';

    // 记录回调地址（用于调试）
    error_log("支付订单回调地址: {$notifyUrl}");

    // 获取用户真实IP（使用PaymentService的统一方法）
    $userIp = PaymentService::getRealClientIp();

    // 记录获取到的IP（用于调试）
    error_log("支付订单创建 - 获取到的用户IP: {$userIp}");

    // 验证returnUrl（支付网关要求必填）
    if (empty($returnUrl)) {
        $returnUrl = 'https://copla.top/recharge.html'; // 默认跳转地址
        error_log("支付订单：returnUrl为空，使用默认地址: {$returnUrl}");
    }

    // 记录订单创建信息（用于调试）
    error_log("支付订单创建 [用户ID:{$userId}, 订单号:{$orderNo}, 金额:{$amount}, 通道:{$channel}, 回调:{$notifyUrl}, 跳转:{$returnUrl}]");

    // 调用支付网关创建订单
    try {
        $paymentResult = PaymentService::createOrder(
            $orderNo,
            $amount,
            $channel,
            $notifyUrl,
            $returnUrl,
            $userIp
        );
    } catch (Exception $e) {
        $db->rollBack();
        error_log("支付网关调用失败 [订单:{$orderNo}]: " . $e->getMessage());
        Response::error('创建支付订单失败：' . $e->getMessage());
    }

    // 记录支付网关返回（用于调试）
    error_log("支付网关返回 [订单:{$orderNo}]: " . json_encode($paymentResult, JSON_UNESCAPED_UNICODE));

    // 检查支付网关返回（根据文档：code=1表示成功，code=0表示失败）
    if (!isset($paymentResult['code']) || $paymentResult['code'] != 1) {
        $errorMsg = $paymentResult['message'] ?? $paymentResult['msg'] ?? '支付网关返回错误';
        $db->rollBack();
        error_log("支付网关返回错误 [订单:{$orderNo}]: " . $errorMsg . "，完整响应: " . json_encode($paymentResult, JSON_UNESCAPED_UNICODE));
        Response::error('创建支付订单失败：' . $errorMsg);
    }

    // 保存充值记录
    $metaData = [
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'channel' => $channel,
        'channel_name' => PaymentService::getChannelName($channel),
        'payment_gateway' => 'sevenpay',
        'gateway_order_no' => $paymentResult['data']['orderNo'] ?? '',
        'gateway_response' => $paymentResult,
        'pay_url' => $paymentResult['data']['payUrl'] ?? '',
        'qr_code' => $paymentResult['data']['qrCode'] ?? $paymentResult['data']['qr_code'] ?? '',
        'expire_time' => isset($paymentResult['data']['expireTime']) ? $paymentResult['data']['expireTime'] : (time() + 30 * 60), // 30分钟有效期
        'timestamp' => time()
    ];

    $insertData = [
        'order_no' => $orderNo,
        'user_id' => $userId,
        'amount' => $amount,
        'currency' => 'CNY',
        'payment_method' => $paymentMethod,
        'status' => 0, // 待支付
        'meta' => json_encode($metaData, JSON_UNESCAPED_UNICODE),
        'created_at' => date('Y-m-d H:i:s')
    ];

    // 记录插入前的数据（用于调试）
    error_log("准备插入充值记录 [用户:{$userId}, 订单:{$orderNo}]: " . json_encode($insertData, JSON_UNESCAPED_UNICODE));

    $rechargeId = $db->insert('recharge_records', $insertData);

    if (!$rechargeId) {
        // 记录详细的错误信息
        error_log("保存充值记录失败 [用户:{$userId}, 订单:{$orderNo}]: " . json_encode($insertData, JSON_UNESCAPED_UNICODE));

        // 检查数据库连接
        try {
            $testQuery = $db->fetchOne("SELECT 1 as test");
            if (!$testQuery) {
                error_log("数据库连接测试失败");
                throw new Exception('数据库连接失败，请重试');
            }
        } catch (Exception $dbError) {
            error_log("数据库错误: " . $dbError->getMessage());
            throw new Exception('数据库错误：' . $dbError->getMessage());
        }

        throw new Exception('保存充值记录失败，请重试');
    }

    // 记录插入成功
    error_log("充值记录插入成功 [ID:{$rechargeId}, 用户:{$userId}, 订单:{$orderNo}]");

    $db->commit();

    // 计算订单过期时间（30分钟）
    $expireTime = time() + 30 * 60;

    // 返回支付信息（根据文档：data.payUrl是支付链接）
    Response::success([
        'order_no' => $orderNo,
        'amount' => $amount,
        'payment_method' => $paymentMethod,
        'channel' => $channel,
        'channel_name' => PaymentService::getChannelName($channel),
        'pay_url' => $paymentResult['data']['payUrl'] ?? '', // 支付链接
        'qr_code' => $paymentResult['data']['qrCode'] ?? $paymentResult['data']['qr_code'] ?? '', // 二维码
        'expire_time' => date('Y-m-d H:i:s', $expireTime), // 订单过期时间
        'expire_timestamp' => $expireTime, // 订单过期时间戳
        'remaining_time' => 30 * 60, // 剩余时间（秒）
        'gateway_order_no' => $paymentResult['data']['orderNo'] ?? '', // 网关订单号
    ], '支付订单创建成功');
} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log("创建支付订单错误 [用户:{$userId}]: " . $e->getMessage());
    Response::error('创建支付订单失败：' . $e->getMessage());
}

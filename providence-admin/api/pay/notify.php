<?php
/**
 * 支付回调通知接口
 * POST /index.php/pay/notify
 *
 * 接收SevenPay支付网关的异步回调通知
 */

require_once __DIR__ . '/../../config/bootstrap.php';

// 加载支付服务
require_once __DIR__ . '/../services/PaymentService.php';

// 记录回调日志
$rawInput = file_get_contents('php://input');
$logData = [
    'time' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'unknown',
    'raw_data' => $rawInput,
    'raw_data_length' => strlen($rawInput),
    'get' => $_GET,
    'post' => $_POST,
    'headers' => getallheaders()
];
error_log('支付回调通知: ' . json_encode($logData, JSON_UNESCAPED_UNICODE));

// 处理GET请求（可能是支付网关测试回调地址）
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // GET请求可能是支付网关测试回调地址，返回成功
    error_log('支付回调：收到GET请求，可能是测试回调地址');
    echo 'success';
    exit;
}

// 获取回调数据（支付网关使用POST表单格式）
$callbackData = $_POST;

// 如果POST为空，尝试从原始输入获取
if (empty($callbackData)) {
    $rawData = $rawInput;

    // 尝试解析JSON格式
    if (!empty($rawData)) {
        $callbackData = json_decode($rawData, true);

        // 如果不是JSON，尝试解析表单格式
        if (!$callbackData) {
            parse_str($rawData, $callbackData);
        }
    }

    // 如果还是为空，尝试从GET获取（某些网关可能使用GET）
    if (empty($callbackData) && !empty($_GET)) {
        $callbackData = $_GET;
    }
}

if (empty($callbackData)) {
    error_log('支付回调：回调数据为空，请求方法: ' . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
    http_response_code(400);
    echo json_encode(['code' => -1, 'msg' => '回调数据为空'], JSON_UNESCAPED_UNICODE);
    exit;
}

// 验证签名
$sign = $callbackData['sign'] ?? '';
if (empty($sign) || !PaymentService::verifyCallback($callbackData, $sign)) {
    error_log('支付回调签名验证失败: ' . json_encode($callbackData, JSON_UNESCAPED_UNICODE));
    http_response_code(400);
    echo json_encode(['code' => -1, 'msg' => '签名验证失败']);
    exit;
}

// 获取订单信息（根据文档）
$merchantOrderNo = $callbackData['merchant_order_no'] ?? ''; // 商户订单号
$gatewayOrderNo = $callbackData['order_no'] ?? ''; // 系统订单号
$amount = floatval($callbackData['amount'] ?? 0);
$payAmount = floatval($callbackData['pay_amount'] ?? 0);
$status = intval($callbackData['status'] ?? 0); // 支付状态：1=支付成功, 2=支付失败
$payTime = isset($callbackData['payment_time']) ? intval($callbackData['payment_time']) : 0; // 10位时间戳

if (empty($merchantOrderNo)) {
    http_response_code(400);
    echo json_encode(['code' => -1, 'msg' => '订单号为空']);
    exit;
}

$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 查询充值记录
    $recharge = $db->fetchOne(
        "SELECT id, user_id, amount, status, meta FROM recharge_records WHERE order_no = :order_no LIMIT 1",
        ['order_no' => $merchantOrderNo]
    );

    if (!$recharge) {
        error_log("支付回调：订单不存在 - {$merchantOrderNo}");
        $db->rollBack();
        http_response_code(404);
        echo json_encode(['code' => -1, 'msg' => '订单不存在']);
        exit;
    }

    // 如果订单已处理，直接返回成功
    if ($recharge['status'] == 1) {
        $db->rollBack();
        echo json_encode(['code' => 200, 'msg' => '订单已处理']);
        exit;
    }

    // 验证金额
    if (abs($recharge['amount'] - $amount) > 0.01) {
        error_log("支付回调：金额不匹配 - 订单:{$recharge['amount']}, 回调:{$amount}");
        $db->rollBack();
        http_response_code(400);
        echo json_encode(['code' => -1, 'msg' => '金额不匹配']);
        exit;
    }

    // 处理支付成功（根据文档：status=1表示支付成功）
    if ($status === 1) {
        $userId = $recharge['user_id'];

        // 更新充值记录状态
        $meta = json_decode($recharge['meta'], true) ?? [];
        $meta['callback_data'] = $callbackData;
            $meta['callback_time'] = date('Y-m-d H:i:s');
            $meta['pay_time'] = $payTime > 0 ? date('Y-m-d H:i:s', $payTime) : '';
            $meta['gateway_order_no'] = $gatewayOrderNo;
            $meta['pay_amount'] = $payAmount;

        $db->update('recharge_records', [
            'status' => 1, // 已支付
            'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $recharge['id']]);

        // 增加用户CNY余额（微信/支付宝充值）
        // 获取或创建CNY钱包
        $cnyWallet = $db->fetchOne(
            "SELECT balance FROM wallets WHERE user_id = :user_id AND currency = 'CNY' LIMIT 1",
            ['user_id' => $userId]
        );

        if (!$cnyWallet) {
            // 创建CNY钱包
            $db->insert('wallets', [
                'user_id' => $userId,
                'currency' => 'CNY',
                'balance' => 0,
                'frozen' => 0,
                'ribao_balance' => 0,
                'points' => 0,
                'total_income' => 0
            ]);
            $balanceBefore = 0;
        } else {
            $balanceBefore = floatval($cnyWallet['balance'] ?? 0);
        }

        // 计算赠送金额（2%赠送，1万送200）
        $bonusRate = 0.02; // 2%
        $bonusAmount = $amount * $bonusRate;
        $totalAmount = $amount + $bonusAmount;
        $newBalance = $balanceBefore + $totalAmount;

        // 更新CNY钱包余额
        $db->update('wallets', [
            'balance' => $newBalance
        ], ['user_id' => $userId, 'currency' => 'CNY']);

        // 同步更新users表的balance_cny字段
        $db->update('users', [
            'balance_cny' => $newBalance
        ], ['id' => $userId]);

            // 记录充值资金流水
            $db->insert('wallet_logs', [
                'user_id' => $userId,
                'type' => 'recharge',
                'amount' => $amount,
                'currency' => 'CNY',
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceBefore + $amount,
                'order_no' => $merchantOrderNo,
                'remark' => "充值到账（支付网关回调）充值金额：{$amount} 元",
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // 记录赠送资金流水
            if ($bonusAmount > 0) {
                $db->insert('wallet_logs', [
                    'user_id' => $userId,
                    'type' => 'bonus',
                    'amount' => $bonusAmount,
                    'currency' => 'CNY',
                    'balance_before' => $balanceBefore + $amount,
                    'balance_after' => $newBalance,
                    'order_no' => $merchantOrderNo,
                    'remark' => "充值赠送（2%赠送，充值{$amount} 元，赠送{$bonusAmount} 元）",
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // 更新充值记录的meta，记录赠送信息
            $meta['bonus_rate'] = $bonusRate;
            $meta['bonus_amount'] = $bonusAmount;
            $meta['total_amount'] = $totalAmount;
            $db->update('recharge_records', [
                'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE)
            ], ['id' => $recharge['id']]);
        }

        // 记录审核日志
        $db->insert('audit_log', [
            'user_id' => $userId,
            'action' => 'RECHARGE_SUCCESS',
            'amount' => $amount,
            'currency' => 'CNY',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'detail' => "充值订单 {$merchantOrderNo} 支付成功",
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $db->commit();

        // 返回小写 success（根据文档要求）
        echo 'success';
        exit;
    }

    // 支付失败（根据文档：status=2表示支付失败）
    if ($status === 2) {
        $meta = json_decode($recharge['meta'], true) ?? [];
        $meta['callback_data'] = $callbackData;
        $meta['callback_time'] = date('Y-m-d H:i:s');
        $meta['fail_reason'] = $callbackData['fail_reason'] ?? '支付失败';

        $db->update('recharge_records', [
            'status' => -1, // 支付失败
            'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $recharge['id']]);

        $db->commit();

        // 返回小写 success（根据文档要求）
        echo 'success';
        exit;
    }

    $db->rollBack();
    http_response_code(400);
    echo 'fail';

} catch (Exception $e) {
    if (isset($db)) {
        $db->rollBack();
    }
    error_log("支付回调处理错误: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['code' => -1, 'msg' => '处理失败']);
}

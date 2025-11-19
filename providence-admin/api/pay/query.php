<?php
/**
 * 查询支付订单状态接口
 * GET /index.php/pay/query
 *
 * 查询SevenPay支付网关的订单状态
 */

require_once __DIR__ . '/../../config/bootstrap.php';

// 加载支付服务
require_once __DIR__ . '/../services/PaymentService.php';

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

$orderNo = trim($_GET['order_no'] ?? '');

if (empty($orderNo)) {
    Response::error('订单号不能为空');
}

$userId = $authUser['user_id'];
$db = Database::getInstance();

try {
    // 查询本地订单
    $recharge = $db->fetchOne(
        "SELECT id, user_id, order_no, amount, status, meta FROM recharge_records WHERE order_no = :order_no LIMIT 1",
        ['order_no' => $orderNo]
    );

    if (!$recharge) {
        Response::error('订单不存在');
    }

    // 验证订单归属
    if ($recharge['user_id'] != $userId) {
        Response::error('无权访问此订单');
    }

    // 如果订单已成功，直接返回
    if ($recharge['status'] == 1) {
        Response::success([
            'order_no' => $recharge['order_no'],
            'amount' => $recharge['amount'],
            'status' => 1,
            'status_text' => '支付成功',
            'local_status' => true
        ], '订单已支付成功');
    }

    // 查询支付网关订单状态
    $queryResult = PaymentService::queryOrder($orderNo);

    if (!isset($queryResult['code']) || $queryResult['code'] != 1) {
        Response::error('查询订单状态失败：' . ($queryResult['message'] ?? '未知错误'));
    }

    $gatewayStatus = $queryResult['data']['status'] ?? 0;
    $gatewayOrderNo = $queryResult['data']['order_no'] ?? '';

    // 如果网关显示已支付，但本地未更新，则同步状态（根据文档：status=1表示支付成功）
    if ($gatewayStatus === 1 && $recharge['status'] != 1) {
        $db->beginTransaction();

        // 更新本地订单状态
        $meta = json_decode($recharge['meta'], true) ?? [];
        $meta['query_data'] = $queryResult['data'];
        $meta['query_time'] = date('Y-m-d H:i:s');
        $meta['gateway_order_no'] = $gatewayOrderNo;

        $db->update('recharge_records', [
            'status' => 1,
            'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s')
        ], ['id' => $recharge['id']]);

        // 增加用户余额
        $user = $db->fetchOne("SELECT money FROM users WHERE id = :id", ['id' => $userId]);
        if ($user) {
            $newBalance = $user['money'] + $recharge['amount'];
            $db->update('users', ['money' => $newBalance], ['id' => $userId]);

            // 记录资金流水
            $db->insert('wallet_logs', [
                'user_id' => $userId,
                'type' => 'recharge',
                'amount' => $recharge['amount'],
                'currency' => 'CNY',
                'balance_before' => $user['money'],
                'balance_after' => $newBalance,
                'order_no' => $orderNo,
                'remark' => '充值到账（查询同步）',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $db->commit();

        Response::success([
            'order_no' => $recharge['order_no'],
            'amount' => $recharge['amount'],
            'status' => 1,
            'status_text' => '支付成功',
            'gateway_status' => $gatewayStatus,
            'gateway_order_no' => $gatewayOrderNo,
            'synced' => true
        ], '订单支付成功（已同步）');
    }

    // 返回查询结果（根据文档：status=0支付中, 1=支付成功, 2=支付失败）
    $statusTextMap = [
        0 => '支付中',
        1 => '支付成功',
        2 => '支付失败'
    ];

    Response::success([
        'order_no' => $recharge['order_no'],
        'amount' => $recharge['amount'],
        'status' => $gatewayStatus, // 直接使用网关返回的状态码
        'status_text' => $statusTextMap[$gatewayStatus] ?? '未知状态',
        'gateway_status' => $gatewayStatus,
        'gateway_order_no' => $gatewayOrderNo,
        'pay_time' => isset($queryResult['data']['payment_time']) ? date('Y-m-d H:i:s', $queryResult['data']['payment_time']) : '',
        'expire_time' => isset($queryResult['data']['expire_time']) ? date('Y-m-d H:i:s', $queryResult['data']['expire_time']) : ''
    ], '查询成功');

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("查询支付订单错误 [用户:{$userId}, 订单:{$orderNo}]: " . $e->getMessage());
    Response::error('查询订单状态失败：' . $e->getMessage());
}

<?php
/**
 * 获取用户待支付订单接口
 * GET /index.php/pay/pending-order
 *
 * 用于恢复未完成的支付订单（在有效期内）
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 认证检查已禁用：无登录模式
$authUser = Auth::user(); // 始终返回 guest 用户
// if (!$authUser) {
//     Response::error('未登录或登录已过期', 401);
// }

$userId = $authUser['user_id'] ?? 1;
$db = Database::getInstance();

try {
    // 查询用户最近的待支付订单（微信/支付宝）
    // 只查询30分钟内的订单（订单有效期）
    $expireTime = date('Y-m-d H:i:s', time() - 30 * 60); // 30分钟前

    $pendingOrder = $db->fetchOne(
        "SELECT id, order_no, amount, currency, payment_method, status, meta, created_at
         FROM recharge_records
         WHERE user_id = :user_id
           AND payment_method IN ('wechat', 'alipay')
           AND status = 0
           AND created_at >= :expire_time
         ORDER BY created_at DESC
         LIMIT 1",
        [
            'user_id' => $userId,
            'expire_time' => $expireTime
        ]
    );

    if (!$pendingOrder) {
        Response::success([
            'has_pending' => false,
            'order' => null
        ], '没有待支付的订单');
    }

    // 解析订单元数据
    $meta = [];
    if (!empty($pendingOrder['meta'])) {
        $decoded = json_decode($pendingOrder['meta'], true);
        if (is_array($decoded)) {
            $meta = $decoded;
        }
    }

    // 计算剩余时间（秒）
    $createdTime = strtotime($pendingOrder['created_at']);
    $expireTime = $createdTime + 30 * 60; // 30分钟有效期
    $remainingTime = max(0, $expireTime - time());

    // 检查订单是否已过期
    if ($remainingTime <= 0) {
        // 订单已过期，更新状态
        $db->update('recharge_records', [
            'status' => -2, // 已过期
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $pendingOrder['id']]);

        Response::success([
            'has_pending' => false,
            'order' => null
        ], '订单已过期');
    }

    // 返回待支付订单信息
    Response::success([
        'has_pending' => true,
        'order' => [
            'order_no' => $pendingOrder['order_no'],
            'amount' => floatval($pendingOrder['amount']),
            'currency' => $pendingOrder['currency'],
            'payment_method' => $pendingOrder['payment_method'],
            'remaining_time' => $remainingTime, // 剩余时间（秒）
            'expire_time' => date('Y-m-d H:i:s', $expireTime),
            'expire_timestamp' => $expireTime, // 过期时间戳
            'pay_url' => $meta['pay_url'] ?? '',
            'qr_code' => $meta['qr_code'] ?? '',
            'gateway_order_no' => $meta['gateway_order_no'] ?? '',
            'created_at' => $pendingOrder['created_at']
        ]
    ], '发现待支付的订单');

} catch (Exception $e) {
    error_log("获取待支付订单错误 [用户:{$userId}]: " . $e->getMessage());
    Response::error('获取待支付订单失败：' . $e->getMessage());
}

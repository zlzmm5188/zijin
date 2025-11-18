<?php
/**
 * 充值申请
 * POST /user/recharge/add
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$input = json_decode(file_get_contents('php://input'), true);
$amount = (float)($input['amount'] ?? 0);
$payment_method = $input['payment_method'] ?? 'bank';
$certificate = $input['certificate'] ?? '';

if ($amount <= 0) {
    Response::error('充值金额必须大于0');
}

$db = Database::getInstance();
$orderNo = 'RCH' . date('YmdHis') . rand(1000, 9999);

$orderId = $db->insert('recharge_records', [
    'order_no' => $orderNo,
    'user_id' => $authUser['user_id'],
    'amount' => $amount,
    'payment_method' => $payment_method,
    'certificate' => $certificate,
    'status' => 0
]);

if ($orderId) {
    Response::success([
        'order_id' => $orderId,
        'order_no' => $orderNo,
        'amount' => $amount,
        'status' => 0,
        'status_text' => '待审核'
    ], '充值申请提交成功，等待审核');
} else {
    Response::error('充值申请失败');
}

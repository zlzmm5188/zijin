<?php
/**
 * USDT充值申请
 * POST /pay/us/recharge
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

$data = json_decode(file_get_contents('php://input'), true);
$amount = (float)($data['amount'] ?? 0);

if ($amount <= 0) {
    Response::error('充值金额必须大于0');
}

if ($amount < 10) {
    Response::error('最低充值金额为10 USDT');
}

$userId = $authUser['user_id'];
$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 生成订单号
    $orderNo = 'RCH' . date('YmdHis') . rand(1000, 9999);

    // 插入充值记录
    $insertData = [
        'order_no' => $orderNo,
        'user_id' => $userId,
        'amount' => $amount,
        'currency' => 'USDT',
        'payment_method' => 'usdt',
        'status' => 0, // 待审核
        'created_at' => date('Y-m-d H:i:s')
    ];

    $result = $db->insert('recharge_records', $insertData);

    if (!$result) {
        throw new Exception('创建充值订单失败');
    }

    $db->commit();

    Response::success([
        'order_no' => $orderNo,
        'amount' => $amount,
        'currency' => 'USDT',
        'payment_method' => 'usdt',
        'status' => 0
    ], '充值申请已提交，请上传凭证');

} catch (Exception $e) {
    $db->rollBack();
    error_log("USDT充值API错误: " . $e->getMessage());
    Response::error('充值申请失败: ' . $e->getMessage());
}

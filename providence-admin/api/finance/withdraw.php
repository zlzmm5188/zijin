<?php
/**
 * 提现申请
 * POST /user/withdraw/add
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
$bank_id = (int)($input['bank_id'] ?? 0);

if ($amount <= 0) {
    Response::error('提现金额必须大于0');
}

$db = Database::getInstance();

// 获取用户信息
$user = $db->fetchOne("SELECT * FROM " . $db->getPrefix() . "users WHERE id = :id", ['id' => $authUser['user_id']]);

if ($user['balance'] < $amount) {
    Response::error('余额不足');
}

// 获取VIP配置
$vipConfig = $db->fetchOne("SELECT withdraw_fee_rate FROM " . $db->getPrefix() . "vip_levels WHERE id = :level", ['level' => $user['vip_level']]);
$feeRate = $vipConfig['withdraw_fee_rate'] ?? 0.005;
$fee = $amount * $feeRate;
$actualAmount = $amount - $fee;

// 获取银行卡信息
$bankCard = null;
if ($bank_id) {
    $bankCard = $db->fetchOne("SELECT * FROM " . $db->getPrefix() . "bank_cards WHERE id = :id AND user_id = :user_id", 
        ['id' => $bank_id, 'user_id' => $user['id']]);
}

$orderNo = 'WTH' . date('YmdHis') . rand(1000, 9999);

$db->beginTransaction();
try {
    // 创建提现订单
    $orderId = $db->insert('withdraw_records', [
        'order_no' => $orderNo,
        'user_id' => $user['id'],
        'amount' => $amount,
        'fee' => $fee,
        'actual_amount' => $actualAmount,
        'withdraw_method' => $bankCard ? $bankCard['card_type'] : 'bank',
        'bank_info' => $bankCard ? json_encode($bankCard) : null,
        'status' => 0
    ]);
    
    // 冻结金额
    $db->update('users', 
        ['frozen_balance' => $user['frozen_balance'] + $amount],
        'id = :id', 
        ['id' => $user['id']]
    );
    
    $db->commit();
    
    Response::success([
        'order_id' => $orderId,
        'order_no' => $orderNo,
        'amount' => $amount,
        'fee' => $fee,
        'actual_amount' => $actualAmount,
        'status' => 0,
        'status_text' => '待审核'
    ], '提现申请提交成功');
    
} catch (Exception $e) {
    $db->rollBack();
    Response::error('提现申请失败: ' . $e->getMessage());
}

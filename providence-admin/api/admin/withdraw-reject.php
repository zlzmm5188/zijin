<?php

/**
 * 提现审核拒绝API
 * POST /admin/withdraw-reject
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

// 验证管理员登录 - 已禁用：无登录模式
$authUser = Auth::user(); // 始终返回 guest 用户（带管理员权限）
// if (!$authUser || !isset($authUser['is_admin']) || !$authUser['is_admin']) {
//     Response::error('无权限访问', 403);
// }

$input = json_decode(file_get_contents('php://input'), true);
$withdrawId = (int)($input['id'] ?? 0);
$reason = trim($input['reason'] ?? '');

if ($withdrawId <= 0) {
    Response::error('提现记录ID无效');
}

if (empty($reason)) {
    Response::error('拒绝原因不能为空');
}

$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 获取提现记录
    $withdrawal = $db->fetchOne(
        "SELECT * FROM withdraw_records WHERE id = :id FOR UPDATE",
        ['id' => $withdrawId]
    );

    if (!$withdrawal) {
        throw new Exception('提现记录不存在');
    }

    if ($withdrawal['status'] != 0) {
        throw new Exception('该提现申请已经被处理');
    }

    // 更新提现记录状态
    $updateData = [
        'status' => 2, // 已拒绝
        'reviewed_at' => date('Y-m-d H:i:s'),
        'remark' => $reason
    ];

    $success = $db->update(
        'withdraw_records',
        $updateData,
        'id = :id',
        ['id' => $withdrawId]
    );

    if (!$success) {
        throw new Exception('更新提现记录失败');
    }

    // 退回用户余额
    $userId = $withdrawal['user_id'];
    $amount = $withdrawal['amount'];
    $currency = $withdrawal['currency'];

    // 获取用户钱包
    $wallet = $db->fetchOne(
        "SELECT * FROM wallets WHERE user_id = :user_id AND currency = :currency FOR UPDATE",
        ['user_id' => $userId, 'currency' => $currency]
    );

    if (!$wallet) {
        throw new Exception('用户钱包不存在');
    }

    // 退回金额到余额（从冻结转回余额）
    $newBalance = bcadd($wallet['balance'], $amount, 8);
    $newFrozen = bcsub($wallet['frozen'], $amount, 8);
    if (bccomp($newFrozen, 0, 8) < 0) {
        $newFrozen = 0;
    }

    $db->update(
        'wallets',
        [
            'balance' => $newBalance,
            'frozen' => $newFrozen,
            'updated_at' => date('Y-m-d H:i:s')
        ],
        'id = :id',
        ['id' => $wallet['id']]
    );

    // 同步更新 users 表余额
    $balanceField = $currency === 'USDT' ? 'balance_usdt' : 'balance_cny';
    $userBalance = $db->fetchOne(
        "SELECT {$balanceField} FROM users WHERE id = :user_id",
        ['user_id' => $userId]
    );
    $newUserBalance = bcadd($userBalance[$balanceField] ?? 0, $amount, 8);
    $db->update('users', [
        $balanceField => $newUserBalance,
        'updated_at' => date('Y-m-d H:i:s')
    ], "id = {$userId}");

    // 记录钱包流水
    $db->insert('wallet_logs', [
        'user_id' => $userId,
        'wallet_id' => $wallet['id'],
        'currency' => $withdrawal['currency'],
        'change_amount' => $amount,
        'balance_after' => $newBalance,
        'biz_type' => 'UNFREEZE',
        'ref_id' => $withdrawId,
        'meta' => json_encode([
            'description' => '提现拒绝，退回余额',
            'order_no' => $withdrawal['order_no'],
            'reason' => $reason
        ], JSON_UNESCAPED_UNICODE),
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // 记录审计日志
    AuditLog::log(
        'withdrawal',
        '管理员拒绝提现申请并退回余额',
        'ADMIN',
        $authUser['user_id'],
        'withdraw_records',
        $withdrawId,
        ['status' => 0],
        ['status' => 2, 'reason' => $reason, 'refund_amount' => $amount]
    );

    $db->commit();

    Response::success([
        'withdrawal_id' => $withdrawId,
        'refund_amount' => $amount
    ], '已拒绝提现申请并退回余额');
} catch (Exception $e) {
    $db->rollBack();
    error_log("提现审核拒绝API错误: " . $e->getMessage());
    Response::error('拒绝失败: ' . $e->getMessage());
}

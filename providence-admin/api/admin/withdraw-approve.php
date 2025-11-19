<?php

/**
 * 提现审核通过API
 * POST /admin/withdraw-approve
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
$actualAmount = (float)($input['actual_amount'] ?? 0);
$remark = trim($input['remark'] ?? '');

if ($withdrawId <= 0) {
    Response::error('提现记录ID无效');
}

if ($actualAmount <= 0) {
    Response::error('实际打款金额必须大于0');
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
        'status' => 1, // 已通过
        'actual_amount' => $actualAmount,
        'reviewed_at' => date('Y-m-d H:i:s'),
        'remark' => $remark
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

    // 扣除用户钱包余额（提现通过，资金已打款）
    $userId = $withdrawal['user_id'];
    $amount = $withdrawal['amount'];
    $currency = $withdrawal['currency'];

    // 获取用户钱包（加锁）
    $wallet = $db->fetchOne(
        "SELECT * FROM wallets WHERE user_id = :user_id AND currency = :currency FOR UPDATE",
        ['user_id' => $userId, 'currency' => $currency]
    );

    if (!$wallet) {
        throw new Exception('用户钱包不存在');
    }

    // 检查冻结金额是否足够
    if (bccomp($wallet['frozen'], $amount, 8) < 0) {
        throw new Exception('冻结金额不足，无法完成提现');
    }

    // 扣除冻结金额（提现已打款，从冻结中扣除）
    $newFrozen = bcsub($wallet['frozen'], $amount, 8);
    $db->update('wallets', [
        'frozen' => $newFrozen,
        'updated_at' => date('Y-m-d H:i:s')
    ], "id = :id", ['id' => $wallet['id']]);

    // 同步更新 users 表余额（冻结金额减少）
    $balanceField = $currency === 'USDT' ? 'balance_usdt' : 'balance_cny';
    $userBalance = $db->fetchOne(
        "SELECT {$balanceField} FROM users WHERE id = :user_id",
        ['user_id' => $userId]
    );
    // 注意：users 表不存储 frozen，只同步 balance，这里不需要更新 users 表
    // 因为冻结金额已经在 wallets 表中管理

    // 记录钱包流水
    $db->insert('wallet_logs', [
        'user_id' => $userId,
        'wallet_id' => $wallet['id'],
        'currency' => $currency,
        'change_amount' => -$amount,
        'balance_after' => $wallet['balance'],
        'biz_type' => 'WITHDRAW',
        'ref_id' => $withdrawId,
        'meta' => json_encode([
            'description' => '提现审核通过，扣除冻结金额',
            'order_no' => $withdrawal['order_no'],
            'actual_amount' => $actualAmount,
            'remark' => $remark
        ], JSON_UNESCAPED_UNICODE),
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // 记录审计日志
    AuditLog::log(
        'withdrawal',
        '管理员审核通过提现申请',
        'ADMIN',
        $authUser['user_id'],
        'withdraw_records',
        $withdrawId,
        ['status' => 0],
        ['status' => 1, 'actual_amount' => $actualAmount, 'remark' => $remark]
    );

    $db->commit();

    Response::success([
        'withdrawal_id' => $withdrawId,
        'actual_amount' => $actualAmount
    ], '审核通过成功');
} catch (Exception $e) {
    $db->rollBack();
    error_log("提现审核通过API错误: " . $e->getMessage());
    Response::error('审核失败: ' . $e->getMessage());
}

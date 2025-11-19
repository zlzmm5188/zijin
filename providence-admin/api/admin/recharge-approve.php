<?php

/**
 * 充值审核通过API
 * POST /admin/recharge-approve
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员登录 - 已禁用：无登录模式
$authUser = Auth::user(); // 始终返回 guest 用户（带管理员权限）
// if (!$authUser || !isset($authUser['is_admin']) || !$authUser['is_admin']) {
//     Response::error('无权限访问', 403);
// }

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);
$rechargeId = isset($input['id']) ? (int)$input['id'] : 0;
$remark = isset($input['remark']) ? trim($input['remark']) : '';

if ($rechargeId <= 0) {
    Response::error('充值记录ID无效');
}

$db = Database::getInstance();

try {
    // 开启事务
    $db->beginTransaction();

    // 1. 获取充值记录（加锁）
    $recharge = $db->fetchOne(
        "SELECT * FROM recharge_records WHERE id = :id FOR UPDATE",
        ['id' => $rechargeId]
    );

    if (!$recharge) {
        $db->rollback();
        Response::error('充值记录不存在');
    }

    // 2. 检查状态
    if ($recharge['status'] != 0) {
        $db->rollback();
        $statusMap = [0 => '待审核', 1 => '已通过', 2 => '已拒绝'];
        Response::error('该充值记录已处理，当前状态：' . ($statusMap[$recharge['status']] ?? '未知'));
    }

    // 3. 验证金额
    $amount = $recharge['amount'];
    if ($amount <= 0) {
        $db->rollback();
        Response::error('充值金额无效');
    }

    // 4. 获取用户钱包（加锁）
    $wallet = $db->fetchOne(
        "SELECT * FROM wallets WHERE user_id = :user_id AND currency = :currency FOR UPDATE",
        [
            'user_id' => $recharge['user_id'],
            'currency' => $recharge['currency']
        ]
    );

    if (!$wallet) {
        $db->rollback();
        Response::error('用户钱包不存在');
    }

    // 5. 更新钱包余额
    $newBalance = bcadd($wallet['balance'], $amount, 8);
    $db->update('wallets', [
        'balance' => $newBalance,
        'updated_at' => date('Y-m-d H:i:s')
    ], "user_id = {$recharge['user_id']} AND currency = '{$recharge['currency']}'");

    // 5.1. 同步更新 users 表余额
    $balanceField = $recharge['currency'] === 'USDT' ? 'balance_usdt' : 'balance_cny';
    $userBalance = $db->fetchOne(
        "SELECT {$balanceField} FROM users WHERE id = :user_id",
        ['user_id' => $recharge['user_id']]
    );
    $newUserBalance = bcadd($userBalance[$balanceField] ?? 0, $amount, 8);
    $db->update('users', [
        $balanceField => $newUserBalance,
        'updated_at' => date('Y-m-d H:i:s')
    ], "id = {$recharge['user_id']}");

    // 6. 更新充值记录
    $db->update('recharge_records', [
        'status' => 1,
        'reviewed_at' => date('Y-m-d H:i:s'),
        'reviewed_by' => $authUser['user_id'],
        'remark' => $remark,
        'updated_at' => date('Y-m-d H:i:s')
    ], "id = {$rechargeId}");

    // 7. 写入钱包流水
    $db->insert('wallet_logs', [
        'user_id' => $recharge['user_id'],
        'wallet_id' => $wallet['id'],
        'currency' => $recharge['currency'],
        'change_amount' => $amount,
        'balance_after' => $newBalance,
        'biz_type' => 'RECHARGE',
        'ref_id' => $rechargeId,
        'meta' => json_encode([
            'description' => '充值到账',
            'order_no' => $recharge['order_no']
        ], JSON_UNESCAPED_UNICODE),
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // 8. 记录审计日志
    AuditLog::log([
        'user_id' => $authUser['user_id'],
        'action' => 'recharge_approve',
        'target_type' => 'recharge',
        'target_id' => $rechargeId,
        'details' => json_encode([
            'recharge_user_id' => $recharge['user_id'],
            'amount' => $amount,
            'currency' => $recharge['currency'],
            'order_no' => $recharge['order_no'],
            'old_balance' => $wallet['balance'],
            'new_balance' => $newBalance,
            'remark' => $remark
        ], JSON_UNESCAPED_UNICODE),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    ]);

    // 提交事务
    $db->commit();

    Response::success([
        'recharge_id' => $rechargeId,
        'user_id' => $recharge['user_id'],
        'amount' => number_format($amount, 8, '.', ''),
        'new_balance' => number_format($newBalance, 8, '.', '')
    ], '充值审核通过');
} catch (Exception $e) {
    $db->rollback();
    error_log("充值审核通过失败: " . $e->getMessage());
    Response::error('审核失败: ' . $e->getMessage());
}

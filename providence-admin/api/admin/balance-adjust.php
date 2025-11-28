<?php
/**
 * 用户资产调账API
 * POST /api/admin/balance-adjust
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方式错误', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$userId = (int)($data['user_id'] ?? 0);
$currency = trim($data['currency'] ?? 'CNY');
$type = trim($data['type'] ?? '');
$target = trim($data['target'] ?? 'balance');
$amount = (float)($data['amount'] ?? 0);
$reason = trim($data['reason'] ?? '');
$remark = trim($data['remark'] ?? '');

// 验证
if ($userId <= 0) {
    Response::error('请选择用户');
}

if (!in_array($type, ['ADD', 'SUBTRACT'])) {
    Response::error('操作类型错误');
}

if (!in_array($target, ['balance', 'frozen', 'points'])) {
    Response::error('调账目标错误');
}

if ($amount <= 0) {
    Response::error('金额必须大于0');
}

if (empty($reason)) {
    Response::error('请填写调账原因');
}

$db = Database::getInstance();

// 获取用户信息
$userSql = "SELECT * FROM " . $db->getPrefix() . "users WHERE id = :id";
$user = $db->fetchOne($userSql, ['id' => $userId]);
if (!$user) {
    Response::error('用户不存在');
}

// 获取钱包信息
$walletSql = "SELECT * FROM " . $db->getPrefix() . "wallets WHERE user_id = :user_id AND currency = :currency";
$wallet = $db->fetchOne($walletSql, ['user_id' => $userId, 'currency' => $currency]);

$beforeAmount = 0;
$afterAmount = 0;

if ($target == 'balance') {
    $beforeAmount = $wallet ? (float)$wallet['balance'] : 0;
} elseif ($target == 'frozen') {
    $beforeAmount = $wallet ? (float)$wallet['frozen'] : 0;
} elseif ($target == 'points') {
    $beforeAmount = (float)($user['points'] ?? 0);
}

if ($type == 'ADD') {
    $afterAmount = $beforeAmount + $amount;
} else {
    $afterAmount = $beforeAmount - $amount;
    if ($afterAmount < 0) {
        Response::error('扣减后余额不能为负数');
    }
}

$adminId = 1; // 简化处理
$adminName = 'admin';

// TODO: 生产环境应从Session或Token获取真实管理员信息
// $adminId = $_SESSION['admin_id'] ?? 1;
// $adminName = $_SESSION['admin_name'] ?? 'admin';

try {
    $db->beginTransaction();

    // 生成订单号
    $orderNo = 'ADJ' . date('YmdHis') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // 更新资产
    if ($target == 'points') {
        // 更新积分
        $db->update('users', ['points' => $afterAmount], 'id = :id', ['id' => $userId]);
    } else {
        // 更新钱包
        if ($wallet) {
            $db->update('wallets', [$target => $afterAmount], 'user_id = :user_id AND currency = :currency', 
                ['user_id' => $userId, 'currency' => $currency]);
        } else {
            // 创建钱包记录
            $db->insert('wallets', [
                'user_id' => $userId,
                'currency' => $currency,
                $target => $afterAmount
            ]);
        }
    }

    // 记录调账日志
    $db->insert('balance_adjustments', [
        'order_no' => $orderNo,
        'user_id' => $userId,
        'currency' => $currency,
        'type' => $type,
        'target' => $target,
        'amount' => $amount,
        'before_amount' => $beforeAmount,
        'after_amount' => $afterAmount,
        'reason' => $reason,
        'remark' => $remark,
        'admin_id' => $adminId,
        'admin_name' => $adminName
    ]);

    // 记录钱包流水
    if ($target != 'points') {
        $db->insert('wallet_logs', [
            'user_id' => $userId,
            'wallet_id' => $wallet['id'] ?? 0,
            'currency' => $currency,
            'change_amount' => $type == 'ADD' ? $amount : -$amount,
            'balance_after' => $afterAmount,
            'biz_type' => 'ADJUST',
            'meta' => json_encode(['reason' => $reason, 'order_no' => $orderNo])
        ]);
    }

    $db->commit();

    Response::success([
        'order_no' => $orderNo,
        'before_amount' => $beforeAmount,
        'after_amount' => $afterAmount
    ], '调账成功');

} catch (Exception $e) {
    $db->rollBack();
    error_log("资产调账失败: " . $e->getMessage());
    Response::error('调账失败');
}

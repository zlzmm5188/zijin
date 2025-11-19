<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../bootstrap.php';

try {
    // 验证用户登录 - 已禁用：无登录模式
    $user = Auth::user(); // 始终返回 guest 用户
    // if (!isset($user['id'])) {
    //     Response::error('未登录');
    // }

    $user_id = $user['id'] ?? $user['user_id'] ?? 1;

    // 获取POST数据
    $input = json_decode(file_get_contents('php://input'), true);
    $amount = floatval($input['amount'] ?? 0);
    $pay_password = $input['pay_password'] ?? '';

    // 验证金额
    if ($amount < 100) {
        Response::error('转出金额不能低于100元');
    }

    // 验证支付密码
    if (empty($pay_password)) {
        Response::error('请输入支付密码');
    }

    $db = Database::getInstance();

    // 验证支付密码
    $userInfo = $db->fetchOne("SELECT pay_password FROM users WHERE id = :id", ['id' => $user_id]);
    if (!$userInfo || !password_verify($pay_password, $userInfo['pay_password'])) {
        Response::error('支付密码错误');
    }

    // 开启事务
    $db->beginTransaction();

    try {
        // 检查日利宝余额（在wallets表中，只支持CNY）
        $cnyWallet = $db->fetchOne(
            "SELECT ribao_balance, balance FROM wallets WHERE user_id = :user_id AND currency = 'CNY' FOR UPDATE",
            ['user_id' => $user_id]
        );

        if (!$cnyWallet) {
            throw new Exception('CNY钱包不存在');
        }

        $ribao_balance = floatval($cnyWallet['ribao_balance'] ?? 0);
        $currentBalance = floatval($cnyWallet['balance'] ?? 0);

        if ($ribao_balance < $amount) {
            throw new Exception('日利宝余额不足');
        }

        // 扣除日利宝余额
        $db->query(
            "UPDATE wallets SET ribao_balance = ribao_balance - :amount WHERE user_id = :user_id AND currency = 'CNY'",
            ['amount' => $amount, 'user_id' => $user_id]
        );

        // 增加CNY余额
        $newBalance = $currentBalance + $amount;
        $db->update('wallets', [
            'balance' => $newBalance
        ], ['user_id' => $user_id, 'currency' => 'CNY']);

        // 同步更新users表的balance_cny
        $db->update('users', [
            'balance_cny' => $newBalance
        ], ['id' => $user_id]);

        // 记录日志
        $db->insert('ribao_logs', [
            'user_id' => $user_id,
            'type' => 'transfer_out',
            'amount' => $amount,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $db->commit();

        Response::success('转出成功', [
            'amount' => $amount,
            'time' => date('Y-m-d H:i:s')
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        Response::error($e->getMessage());
    }
} catch (Exception $e) {
    Response::error('系统错误: ' . $e->getMessage());
}

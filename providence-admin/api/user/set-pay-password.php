<?php
/**
 * 设置支付密码
 * POST /user/user/setPayPassword
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

// 验证用户登录
// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     // Response::error(...) [已禁用]);
    // }

$userId = $authUser['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

$payPassword = trim($input['pay_password'] ?? '');
$confirmPassword = trim($input['confirm_password'] ?? '');

if (empty($payPassword)) {
    Response::error('支付密码不能为空');
}

if (strlen($payPassword) !== 6 || !preg_match('/^\d{6}$/', $payPassword)) {
    Response::error('支付密码必须是6位数字');
}

if ($payPassword !== $confirmPassword) {
    Response::error('两次输入的密码不一致');
}

$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 检查是否已设置支付密码
    $user = $db->fetchOne(
        "SELECT trade_password, username FROM users WHERE id = :id",
        ['id' => $userId]
    );

    if (!empty($user['trade_password'])) {
        $db->rollBack();
        Response::error('支付密码已设置，如需修改请联系客服');
    }

    // 加密支付密码
    $hashedPassword = password_hash($payPassword, PASSWORD_DEFAULT);

    // 更新支付密码
    $success = $db->update(
        'users',
        ['trade_password' => $hashedPassword, 'updated_at' => date('Y-m-d H:i:s')],
        'id = :id',
        ['id' => $userId]
    );

    if (!$success) {
        throw new Exception('设置支付密码失败');
    }

    // 审计日志
    AuditLog::log('user', '用户设置支付密码', 'USER', $userId, 'users', $userId, [
        'username' => $user['username']
    ]);

    $db->commit();

    Response::success(null, '支付密码设置成功');

} catch (Exception $e) {
    $db->rollBack();
    error_log("设置支付密码API错误: " . $e->getMessage());
    Response::error('设置支付密码失败: ' . $e->getMessage());
}

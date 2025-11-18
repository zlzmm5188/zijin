<?php
/**
 * 重置用户密码API
 * POST /api/admin/user-reset-password.php
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员权限
// TODO: 实现管理员Token验证

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$userId = (int)($_POST['user_id'] ?? 0);
$newPassword = trim($_POST['new_password'] ?? '');

if ($userId <= 0) {
    Response::error('用户ID无效');
}

if (empty($newPassword)) {
    Response::error('新密码不能为空');
}

// 验证密码强度
$passwordCheck = Validator::validatePasswordStrength($newPassword);
if ($passwordCheck !== true) {
    Response::error($passwordCheck);
}

$db = Database::getInstance();

try {
    // 检查用户是否存在
    $user = $db->fetchOne(
        "SELECT id, username FROM users WHERE id = :id",
        ['id' => $userId]
    );

    if (!$user) {
        Response::error('用户不存在');
    }

    $db->beginTransaction();

    // 生成新密码哈希
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // 更新密码
    $success = $db->update(
        'users',
        [
            'password' => $passwordHash,
            'updated_at' => date('Y-m-d H:i:s')
        ],
        'id = :where_id',
        ['where_id' => $userId]
    );

    if (!$success) {
        throw new Exception('密码更新失败');
    }

    // 记录审计日志
    AuditLog::log(
        'user',
        '管理员重置用户密码',
        'ADMIN',
        1,
        'users',
        $userId,
        ['username' => $user['username']],
        ['password' => '***已重置***']
    );

    $db->commit();

    Response::success([
        'user_id' => $userId,
        'username' => $user['username']
    ], '密码重置成功');

} catch (Exception $e) {
    $db->rollBack();
    error_log("重置密码API错误: " . $e->getMessage());
    Response::error('重置失败: ' . $e->getMessage());
}

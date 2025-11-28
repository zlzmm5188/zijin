<?php
/**
 * 管理员保存API (新增/编辑)
 * POST /api/admin/admin-save
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方式错误', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$id = (int)($data['id'] ?? 0);
$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');
$realName = trim($data['real_name'] ?? '');
$email = trim($data['email'] ?? '');
$phone = trim($data['phone'] ?? '');
$roleId = (int)($data['role_id'] ?? 1);
$status = (int)($data['status'] ?? 1);

// 验证
if (empty($username)) {
    Response::error('用户名不能为空');
}

if ($id === 0 && empty($password)) {
    Response::error('密码不能为空');
}

if (strlen($username) < 3 || strlen($username) > 50) {
    Response::error('用户名长度应为3-50个字符');
}

if (!empty($password) && strlen($password) < 6) {
    Response::error('密码长度不能少于6位');
}

$db = Database::getInstance();

// 检查用户名是否已存在
$existSql = "SELECT id FROM " . $db->getPrefix() . "admins WHERE username = :username AND id != :id";
$exist = $db->fetchOne($existSql, ['username' => $username, 'id' => $id]);
if ($exist) {
    Response::error('用户名已存在');
}

try {
    $db->beginTransaction();

    $saveData = [
        'username' => $username,
        'real_name' => $realName,
        'email' => $email ?: null,
        'phone' => $phone ?: null,
        'role_id' => $roleId,
        'status' => $status
    ];

    if (!empty($password)) {
        $saveData['password'] = password_hash($password, PASSWORD_DEFAULT);
    }

    if ($id > 0) {
        // 更新
        $db->update('admins', $saveData, 'id = :id', ['id' => $id]);
        $message = '更新成功';
    } else {
        // 新增
        $id = $db->insert('admins', $saveData);
        $message = '添加成功';
    }

    $db->commit();

    Response::success(['id' => $id], $message);

} catch (Exception $e) {
    $db->rollBack();
    error_log("管理员保存失败: " . $e->getMessage());
    Response::error('保存失败: ' . $e->getMessage());
}

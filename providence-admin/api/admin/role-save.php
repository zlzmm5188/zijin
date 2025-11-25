<?php
/**
 * 管理员角色保存API
 * POST /api/admin/role-save
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方式错误', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$id = (int)($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$code = trim($data['code'] ?? '');
$description = trim($data['description'] ?? '');
$permissions = $data['permissions'] ?? [];
$status = (int)($data['status'] ?? 1);
$sortOrder = (int)($data['sort_order'] ?? 0);

// 验证
if (empty($name)) {
    Response::error('角色名称不能为空');
}

if (empty($code)) {
    Response::error('角色代码不能为空');
}

$db = Database::getInstance();

// 检查角色代码是否已存在
$existSql = "SELECT id FROM " . $db->getPrefix() . "admin_roles WHERE code = :code AND id != :id";
$exist = $db->fetchOne($existSql, ['code' => $code, 'id' => $id]);
if ($exist) {
    Response::error('角色代码已存在');
}

try {
    $saveData = [
        'name' => $name,
        'code' => $code,
        'description' => $description,
        'permissions' => json_encode($permissions, JSON_UNESCAPED_UNICODE),
        'status' => $status,
        'sort_order' => $sortOrder
    ];

    if ($id > 0) {
        $db->update('admin_roles', $saveData, 'id = :id', ['id' => $id]);
        $message = '更新成功';
    } else {
        $id = $db->insert('admin_roles', $saveData);
        $message = '添加成功';
    }

    Response::success(['id' => $id], $message);

} catch (Exception $e) {
    error_log("角色保存失败: " . $e->getMessage());
    Response::error('保存失败');
}

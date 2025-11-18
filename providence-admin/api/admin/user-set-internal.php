<?php
/**
 * 设置用户为内部人员
 * POST /admin/user-set-internal
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = (int)($input['user_id'] ?? 0);
$isInternal = (int)($input['is_internal'] ?? 0);

if (!$userId) {
    Response::error('用户ID不能为空');
}

$db = Database::getInstance();

// 更新用户
$result = $db->update('users', 
    ['is_internal' => $isInternal],
    'id = :id',
    ['id' => $userId]
);

if ($result) {
    // 记录日志
    $db->insert('admin_logs', [
        'admin_id' => 1, // TODO: 获取当前管理员ID
        'action' => '设置内部人员',
        'module' => '用户管理',
        'content' => "用户ID: {$userId}, 设置为: " . ($isInternal ? '内部人员' : '普通用户'),
        'ip' => $_SERVER['REMOTE_ADDR']
    ]);
    
    Response::success(null, $isInternal ? '已设置为内部人员' : '已取消内部人员');
} else {
    Response::error('操作失败');
}

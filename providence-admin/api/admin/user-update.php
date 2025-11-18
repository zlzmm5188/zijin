<?php
/**
 * 用户编辑API
 * POST /api/admin/user-update.php
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$userId = (int)($_POST['id'] ?? 0);

if ($userId <= 0) {
    Response::error('用户ID无效');
}

$db = Database::getInstance();

try {
    // 检查用户是否存在
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE id = :id",
        ['id' => $userId]
    );

    if (!$user) {
        Response::error('用户不存在');
    }

    $db->beginTransaction();

    $data = [];

    // 可编辑字段
    if (isset($_POST['phone'])) {
        $data['phone'] = trim($_POST['phone']);
    }

    if (isset($_POST['email'])) {
        $data['email'] = trim($_POST['email']);
    }

    if (isset($_POST['vip_level'])) {
        $data['vip_level'] = max(0, min(8, (int)$_POST['vip_level']));
    }

    if (isset($_POST['status'])) {
        $data['status'] = (int)$_POST['status'];
    }

    if (isset($_POST['is_internal'])) {
        $data['is_internal'] = (int)$_POST['is_internal'];
    }

    if (isset($_POST['realname_status'])) {
        $data['realname_status'] = (int)$_POST['realname_status'];
    }

    if (empty($data)) {
        Response::error('没有可更新的数据');
    }

    $data['updated_at'] = date('Y-m-d H:i:s');

    // 记录更新前的日志
    error_log("更新用户 {$userId}, 数据: " . json_encode($data, JSON_UNESCAPED_UNICODE));

    $success = $db->update('users', $data, 'id = :where_id', ['where_id' => $userId]);

    error_log("更新结果: " . ($success ? 'true' : 'false'));

    if ($success === false) {
        throw new Exception('数据库更新失败');
    }

    // 记录审计日志
    AuditLog::log(
        'user',
        '编辑用户信息',
        'ADMIN',
        1,
        'users',
        $userId,
        $user,
        $data
    );

    $db->commit();

    Response::success([
        'id' => $userId,
        'updated' => true
    ], '更新成功');

} catch (Exception $e) {
    $db->rollBack();
    error_log("用户编辑API错误: " . $e->getMessage());
    Response::error('更新失败: ' . $e->getMessage());
}

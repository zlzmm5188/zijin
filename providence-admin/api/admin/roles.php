<?php
/**
 * 管理员角色列表API
 * GET /api/admin/roles
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(10, (int)($_GET['limit'] ?? 50)));
$offset = ($page - 1) * $limit;
$status = $_GET['status'] ?? '';

$where = '1=1';
$params = [];

if ($status !== '') {
    $where .= ' AND status = :status';
    $params['status'] = (int)$status;
}

try {
    $total = $db->count('admin_roles', $where, $params);

    $sql = "SELECT * FROM " . $db->getPrefix() . "admin_roles 
            WHERE {$where} 
            ORDER BY sort_order ASC, id ASC 
            LIMIT {$offset}, {$limit}";

    $list = $db->fetchAll($sql, $params);

    // 格式化数据
    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['status'] = (int)$item['status'];
        $item['sort_order'] = (int)$item['sort_order'];
        $item['permissions'] = json_decode($item['permissions'] ?? '[]', true);
        
        // 统计该角色下的管理员数量
        $item['admin_count'] = $db->count('admins', 'role_id = :role_id', ['role_id' => $item['id']]);
    }

    Response::success([
        'code' => 0,
        'msg' => '',
        'count' => $total,
        'data' => $list
    ]);

} catch (Exception $e) {
    error_log("角色列表API错误: " . $e->getMessage());
    Response::error('数据加载失败');
}

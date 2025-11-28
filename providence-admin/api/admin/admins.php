<?php
/**
 * 管理员列表API
 * GET /api/admin/admins
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(10, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;
$keyword = trim($_GET['keyword'] ?? '');
$status = $_GET['status'] ?? '';
$roleId = $_GET['role_id'] ?? '';

$where = '1=1';
$params = [];

if (!empty($keyword)) {
    $where .= ' AND (a.username LIKE :keyword OR a.real_name LIKE :keyword OR a.phone LIKE :keyword)';
    $params['keyword'] = "%{$keyword}%";
}

if ($status !== '') {
    $where .= ' AND a.status = :status';
    $params['status'] = (int)$status;
}

if (!empty($roleId)) {
    $where .= ' AND a.role_id = :role_id';
    $params['role_id'] = (int)$roleId;
}

try {
    // 统计总数
    $countSql = "SELECT COUNT(*) as count FROM " . $db->getPrefix() . "admins a WHERE {$where}";
    $countResult = $db->fetchOne($countSql, $params);
    $total = $countResult ? (int)$countResult['count'] : 0;

    // 查询列表
    $sql = "SELECT a.id, a.username, a.real_name, a.email, a.phone, a.avatar,
                   a.role_id, r.name as role_name, r.code as role_code,
                   a.status, a.last_login_at, a.last_login_ip, a.login_count,
                   a.created_at, a.updated_at
            FROM " . $db->getPrefix() . "admins a
            LEFT JOIN " . $db->getPrefix() . "admin_roles r ON a.role_id = r.id
            WHERE {$where}
            ORDER BY a.id DESC
            LIMIT {$offset}, {$limit}";

    $list = $db->fetchAll($sql, $params);

    // 格式化数据
    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['role_id'] = (int)$item['role_id'];
        $item['status'] = (int)$item['status'];
        $item['login_count'] = (int)$item['login_count'];
        $item['status_text'] = $item['status'] == 1 ? '正常' : '禁用';
    }

    Response::success([
        'code' => 0,
        'msg' => '',
        'count' => $total,
        'data' => $list
    ]);

} catch (Exception $e) {
    error_log("管理员列表API错误: " . $e->getMessage());
    Response::error('数据加载失败');
}

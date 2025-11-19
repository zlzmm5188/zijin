<?php
/**
 * 用户列表API（修复parsererror）
 * GET /api/admin/users.php
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(10, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;
$keyword = trim($_GET['keyword'] ?? '');

$where = '1=1';
$params = [];

if (!empty($keyword)) {
    $where .= ' AND (username LIKE :keyword OR phone LIKE :keyword OR email LIKE :keyword)';
    $params['keyword'] = "%{$keyword}%";
}

try {
    $total = $db->count('users', $where, $params);

    $sql = "SELECT id, uid, username, email, phone, vip_level, total_invest,
                   parent_id, status, is_internal, realname_status,
                   created_at, updated_at
            FROM " . $db->getPrefix() . "users
            WHERE {$where}
            ORDER BY id DESC
            LIMIT {$offset}, {$limit}";

    $list = $db->fetchAll($sql, $params);

    // 格式化数据
    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['vip_level'] = (int)($item['vip_level'] ?? 0);
        $item['total_invest'] = (float)($item['total_invest'] ?? 0);
        $item['status'] = (int)($item['status'] ?? 1);
        $item['is_internal'] = (int)($item['is_internal'] ?? 0);
        $item['realname_status'] = (int)($item['realname_status'] ?? 0);

        // 获取VIP名称
        $item['vip_name'] = 'VIP' . $item['vip_level'];

        // 实名状态文本
        $realnameTexts = ['未认证', '已认证', '已拒绝'];
        $item['realname_text'] = $realnameTexts[$item['realname_status']] ?? '未认证';

        // 获取下级人数
        $item['children_count'] = $db->count('users', 'parent_id = :id', ['id' => $item['id']]);
    }

    // LayUI表格格式（注意：LayUI特殊需求，这里使用 code:0 表示成功）
    Response::success([
        'code' => 0,  // LayUI表格专用格式
        'msg' => '',
        'count' => $total,
        'data' => $list
    ]);

} catch (Exception $e) {
    error_log("用户列表API错误: " . $e->getMessage());
    Response::error('数据加载失败: ' . $e->getMessage());
}

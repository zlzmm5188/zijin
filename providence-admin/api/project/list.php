<?php
/**
 * 获取项目列表
 * GET /fund/project/all
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$db = Database::getInstance();

// 获取分页参数
$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = min(100, max(10, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $pageSize;

// 获取筛选条件
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? 1;

$where = 'p.status = :status';  // ⭐ 添加表别名 p.
$params = ['status' => $status];

if (!empty($category)) {
    $where .= ' AND p.category = :category';  // ⭐ 添加表别名 p.
    $params['category'] = $category;
}

// 获取总数（修复count查询）
$countSql = "SELECT COUNT(*) as total FROM invest_projects p WHERE {$where}";
$totalResult = $db->fetchOne($countSql, $params);
$total = (int)$totalResult['total'];

// 获取列表
$sql = "SELECT 
    p.id,
    p.project_code,
    p.title as name,
    p.subtitle,
    p.description,
    p.category,
    p.currency,
    p.cover_image,
    p.images,
    p.cycle_days as total_days,
    p.base_rate,
    p.added_rate,
    p.gift_rate,
    p.total_rate as daily_rate,
    p.min_invest,
    p.max_invest,
    p.total_quota,
    p.schedule,
    p.manager_id,
    p.risk_level,
    p.status,
    p.sort as sort_order,
    p.view_count,
    p.invest_count,
    p.total_invested,
    p.sold,
    p.remain,
    pm.name as manager_name,
    pm.title as manager_title,
    pm.avatar as manager_avatar
FROM invest_projects p
LEFT JOIN project_managers pm ON p.manager_id = pm.id
WHERE {$where}
ORDER BY p.sort DESC, p.id DESC
LIMIT {$offset}, {$pageSize}";

$list = $db->fetchAll($sql, $params);

// 格式化数据
foreach ($list as &$item) {
    $item['id'] = (int)$item['id'];
    $item['min_invest'] = (float)$item['min_invest'];
    $item['max_invest'] = (float)($item['max_invest'] ?? 0);
    $item['total_quota'] = (float)($item['total_quota'] ?? 0);
    $item['daily_rate'] = (float)$item['daily_rate'];
    $item['base_rate'] = (float)$item['base_rate'];
    $item['added_rate'] = (float)$item['added_rate'];
    $item['gift_rate'] = (float)$item['gift_rate'];
    $item['total_days'] = (int)$item['total_days'];
    $item['risk_level'] = (int)$item['risk_level'];
    $item['view_count'] = (int)$item['view_count'];
    $item['invest_count'] = (int)$item['invest_count'];
    $item['total_invested'] = (float)$item['total_invested'];
    $item['sold'] = (float)$item['sold'];
    $item['remain'] = (float)$item['remain'];
    $item['schedule'] = (float)($item['schedule'] ?? 0);
    
    // 计算总收益率
    $item['total_return_rate'] = (float)$item['daily_rate'] * (int)$item['total_days'];
}

Response::success([
    'list' => $list,
    'total' => $total,
    'page' => $page,
    'pageSize' => $pageSize,
    'totalPages' => ceil($total / $pageSize)
]);

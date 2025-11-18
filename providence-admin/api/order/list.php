<?php
/**
 * 订单列表
 * GET /user/order/list
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$authUser = Auth::user();
if (!$authUser) {
    Response::error('未登录或登录已过期', 401);
}

$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = min(100, max(10, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $pageSize;

$total = $db->count('user_investments', 'user_id = :user_id', ['user_id' => $authUser['user_id']]);

$sql = "SELECT i.*, p.title as project_title, p.cover_image
        FROM " . $db->getPrefix() . "user_investments i
        LEFT JOIN " . $db->getPrefix() . "projects p ON i.project_id = p.id
        WHERE i.user_id = :user_id
        ORDER BY i.id DESC
        LIMIT {$offset}, {$pageSize}";

$list = $db->fetchAll($sql, ['user_id' => $authUser['user_id']]);

foreach ($list as &$item) {
    $item['id'] = (int)$item['id'];
    $item['invest_amount'] = (float)$item['invest_amount'];
    $item['earned_amount'] = (float)$item['earned_amount'];
    $item['total_return'] = (float)$item['total_return'];
    $item['daily_rate'] = (float)$item['daily_rate'];
}

Response::paginate($list, $total, $page, $pageSize);

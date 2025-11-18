<?php
/**
 * 积分明细
 * GET /user/points/logs
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$pageSize = min(100, max(10, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $pageSize;

$total = $db->count('points_logs', 'user_id = :user_id', ['user_id' => $authUser['user_id']]);

$sql = "SELECT * FROM " . $db->getPrefix() . "points_logs 
        WHERE user_id = :user_id 
        ORDER BY id DESC 
        LIMIT {$offset}, {$pageSize}";

$list = $db->fetchAll($sql, ['user_id' => $authUser['user_id']]);

Response::paginate($list, $total, $page, $pageSize);

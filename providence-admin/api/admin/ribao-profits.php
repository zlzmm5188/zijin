<?php
/**
 * 日利宝收益记录API
 * GET /admin/ribao/profits
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员登录
$authUser = Auth::user();
if (!$authUser || !isset($authUser['is_admin']) || !$authUser['is_admin']) {
    Response::error('无权限访问', 403);
}

$db = Database::getInstance();

// 获取筛选参数
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

$userId = isset($_GET['user_id']) && $_GET['user_id'] !== '' ? (int)$_GET['user_id'] : null;
$startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

try {
    // 构建查询条件
    $where = "wl.type = 'ribao_profit'";
    $params = [];

    if ($userId) {
        $where .= ' AND wl.user_id = :user_id';
        $params['user_id'] = $userId;
    }

    if ($startDate) {
        $where .= ' AND DATE(wl.created_at) >= :start_date';
        $params['start_date'] = $startDate;
    }

    if ($endDate) {
        $where .= ' AND DATE(wl.created_at) <= :end_date';
        $params['end_date'] = $endDate;
    }

    // 获取总数
    $sql = "SELECT COUNT(*) as total
            FROM wallet_logs wl
            WHERE {$where}";
    $totalResult = $db->fetchOne($sql, $params);
    $total = (int)$totalResult['total'];

    // 获取列表
    $sql = "SELECT
                wl.*,
                u.username,
                u.phone
            FROM wallet_logs wl
            LEFT JOIN users u ON wl.user_id = u.id
            WHERE {$where}
            ORDER BY wl.created_at DESC
            LIMIT {$limit} OFFSET {$offset}";

    $list = $db->fetchAll($sql, $params);

    // 格式化金额为decimal string
    foreach ($list as &$item) {
        $item['amount'] = number_format($item['amount'], 8, '.', '');
        $item['balance_before'] = number_format($item['balance_before'], 8, '.', '');
        $item['balance_after'] = number_format($item['balance_after'], 8, '.', '');
    }

    // 获取统计数据
    $statsData = [
        'total_profit' => '0.00000000',
        'count' => $total
    ];

    // 收益统计
    $profitStats = $db->fetchOne(
        "SELECT SUM(amount) as total
         FROM wallet_logs
         WHERE {$where}",
        $params
    );
    $statsData['total_profit'] = number_format($profitStats['total'] ?? 0, 8, '.', '');

    // 返回结果
    Response::success([
        'list' => $list,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total' => $total,
            'limit' => $limit
        ],
        'stats' => $statsData
    ]);

} catch (Exception $e) {
    error_log("日利宝收益记录API错误: " . $e->getMessage());
    Response::error('获取日利宝收益记录失败: ' . $e->getMessage());
}

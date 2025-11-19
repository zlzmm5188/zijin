<?php

/**
 * 提现记录列表API
 * GET /admin/withdrawals
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员登录 - 已禁用：无登录模式
$authUser = Auth::user(); // 始终返回 guest 用户（带管理员权限）
// if (!$authUser || !isset($authUser['is_admin']) || !$authUser['is_admin']) {
//     Response::error('无权限访问', 403);
// }

$db = Database::getInstance();

// 获取筛选参数
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

$status = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null;
$currency = isset($_GET['currency']) ? trim($_GET['currency']) : '';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

try {
    // 构建查询条件
    $where = '1=1';
    $params = [];

    if ($status !== null) {
        $where .= ' AND w.status = :status';
        $params['status'] = $status;
    }

    if ($currency) {
        $where .= ' AND w.currency = :currency';
        $params['currency'] = $currency;
    }

    if ($keyword) {
        $where .= ' AND (u.username LIKE :keyword OR u.phone LIKE :keyword OR w.order_no LIKE :keyword)';
        $params['keyword'] = "%{$keyword}%";
    }

    // 获取总数
    $sql = "SELECT COUNT(*) as total
            FROM withdraw_records w
            LEFT JOIN users u ON w.user_id = u.id
            WHERE {$where}";
    $totalResult = $db->fetchOne($sql, $params);
    $total = (int)$totalResult['total'];

    // 获取列表
    $sql = "SELECT
                w.*,
                u.username,
                u.phone,
                u.email
            FROM withdraw_records w
            LEFT JOIN users u ON w.user_id = u.id
            WHERE {$where}
            ORDER BY w.created_at DESC
            LIMIT {$limit} OFFSET {$offset}";

    $list = $db->fetchAll($sql, $params);

    // 获取统计数据
    $statsData = [
        'pending' => 0,
        'approved_today' => 0,
        'total_amount_today' => 0
    ];

    // 待审核数量
    $pendingResult = $db->fetchOne(
        "SELECT COUNT(*) as count FROM withdraw_records WHERE status = 0",
        []
    );
    $statsData['pending'] = (int)$pendingResult['count'];

    // 今日通过数量和金额
    $todayResult = $db->fetchOne(
        "SELECT
            COUNT(*) as count,
            SUM(amount) as total
        FROM withdraw_records
        WHERE status IN (1, 3)
        AND DATE(reviewed_at) = CURDATE()",
        []
    );
    $statsData['approved_today'] = (int)($todayResult['count'] ?? 0);
    $statsData['total_amount_today'] = number_format($todayResult['total'] ?? 0, 2, '.', '');

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
    error_log("提现列表API错误: " . $e->getMessage());
    Response::error('获取提现列表失败: ' . $e->getMessage());
}

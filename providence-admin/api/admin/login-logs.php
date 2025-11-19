<?php
/**
 * 用户登录历史API
 * GET /admin/login-logs
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
$status = isset($_GET['status']) && $_GET['status'] !== '' ? (int)$_GET['status'] : null;
$startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

try {
    // 构建查询条件
    $where = '1=1';
    $params = [];

    if ($userId) {
        $where .= ' AND ll.user_id = :user_id';
        $params['user_id'] = $userId;
    }

    if ($status !== null) {
        $where .= ' AND ll.status = :status';
        $params['status'] = $status;
    }

    if ($startDate) {
        $where .= ' AND DATE(ll.login_time) >= :start_date';
        $params['start_date'] = $startDate;
    }

    if ($endDate) {
        $where .= ' AND DATE(ll.login_time) <= :end_date';
        $params['end_date'] = $endDate;
    }

    if ($keyword) {
        $where .= ' AND (u.username LIKE :keyword OR u.phone LIKE :keyword OR ll.login_ip LIKE :keyword)';
        $params['keyword'] = "%{$keyword}%";
    }

    // 获取总数
    $sql = "SELECT COUNT(*) as total
            FROM user_login_logs ll
            LEFT JOIN users u ON ll.user_id = u.id
            WHERE {$where}";
    $totalResult = $db->fetchOne($sql, $params);
    $total = (int)$totalResult['total'];

    // 获取列表
    $sql = "SELECT
                ll.*,
                u.username,
                u.phone
            FROM user_login_logs ll
            LEFT JOIN users u ON ll.user_id = u.id
            WHERE {$where}
            ORDER BY ll.login_time DESC
            LIMIT {$limit} OFFSET {$offset}";

    $list = $db->fetchAll($sql, $params);

    // 获取统计数据
    $statsData = [
        'total_logins' => $total,
        'success_today' => 0,
        'failed_today' => 0
    ];

    // 今日成功登录
    $successResult = $db->fetchOne(
        "SELECT COUNT(*) as count
         FROM user_login_logs
         WHERE DATE(login_time) = CURDATE() AND status = 1",
        []
    );
    $statsData['success_today'] = (int)($successResult['count'] ?? 0);

    // 今日失败登录
    $failedResult = $db->fetchOne(
        "SELECT COUNT(*) as count
         FROM user_login_logs
         WHERE DATE(login_time) = CURDATE() AND status = 0",
        []
    );
    $statsData['failed_today'] = (int)($failedResult['count'] ?? 0);

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
    error_log("登录日志API错误: " . $e->getMessage());
    Response::error('获取登录日志失败: ' . $e->getMessage());
}

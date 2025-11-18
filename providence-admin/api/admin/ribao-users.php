<?php
/**
 * 日利宝用户列表API
 * GET /admin/ribao/users
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

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$minBalance = isset($_GET['min_balance']) ? trim($_GET['min_balance']) : '';

try {
    // 构建查询条件
    $where = 'w.currency = :currency';
    $params = ['currency' => 'CNY'];

    if ($keyword) {
        $where .= ' AND (u.username LIKE :keyword OR u.phone LIKE :keyword OR u.id = :user_id)';
        $params['keyword'] = "%{$keyword}%";
        $params['user_id'] = is_numeric($keyword) ? (int)$keyword : 0;
    }

    if ($minBalance !== '') {
        $where .= ' AND w.ribao_balance >= :min_balance';
        $params['min_balance'] = $minBalance;
    }

    // 获取总数
    $sql = "SELECT COUNT(*) as total
            FROM wallets w
            LEFT JOIN users u ON w.user_id = u.id
            WHERE {$where}";
    $totalResult = $db->fetchOne($sql, $params);
    $total = (int)$totalResult['total'];

    // 获取列表
    $sql = "SELECT
                w.user_id,
                w.ribao_balance,
                w.ribao_total_profit,
                w.ribao_yesterday_profit,
                w.updated_at as last_update,
                u.username,
                u.phone,
                u.vip_level
            FROM wallets w
            LEFT JOIN users u ON w.user_id = u.id
            WHERE {$where}
            ORDER BY w.ribao_balance DESC
            LIMIT {$limit} OFFSET {$offset}";

    $list = $db->fetchAll($sql, $params);

    // 格式化金额为decimal string
    foreach ($list as &$item) {
        $item['ribao_balance'] = number_format($item['ribao_balance'], 8, '.', '');
        $item['ribao_total_profit'] = number_format($item['ribao_total_profit'], 8, '.', '');
        $item['ribao_yesterday_profit'] = number_format($item['ribao_yesterday_profit'], 8, '.', '');
    }

    // 获取统计数据
    $statsData = [
        'total_balance' => '0.00000000',
        'total_profit' => '0.00000000',
        'user_count' => $total
    ];

    // 总余额统计
    $balanceStats = $db->fetchOne(
        "SELECT SUM(ribao_balance) as total FROM wallets WHERE currency = 'CNY'",
        []
    );
    $statsData['total_balance'] = number_format($balanceStats['total'] ?? 0, 8, '.', '');

    // 总收益统计
    $profitStats = $db->fetchOne(
        "SELECT SUM(ribao_total_profit) as total FROM wallets WHERE currency = 'CNY'",
        []
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
    error_log("日利宝用户列表API错误: " . $e->getMessage());
    Response::error('获取日利宝用户列表失败: ' . $e->getMessage());
}

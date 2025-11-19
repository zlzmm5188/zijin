<?php
/**
 * 用户钱包流水API
 * GET /admin/wallet-logs
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
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$currency = isset($_GET['currency']) ? trim($_GET['currency']) : '';
$startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

try {
    // 构建查询条件
    $where = '1=1';
    $params = [];

    if ($userId) {
        $where .= ' AND wl.user_id = :user_id';
        $params['user_id'] = $userId;
    }

    if ($type) {
        $where .= ' AND wl.biz_type = :type';
        $params['type'] = $type;
    }

    if ($currency) {
        $where .= ' AND wl.currency = :currency';
        $params['currency'] = $currency;
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
                wl.id,
                wl.user_id,
                wl.wallet_id,
                wl.currency,
                wl.change_amount as amount,
                wl.balance_after,
                wl.biz_type as type,
                wl.ref_id,
                wl.meta,
                wl.created_at,
                u.username,
                u.phone
            FROM wallet_logs wl
            LEFT JOIN users u ON wl.user_id = u.id
            WHERE {$where}
            ORDER BY wl.created_at DESC
            LIMIT {$limit} OFFSET {$offset}";

    $list = $db->fetchAll($sql, $params);

    // 格式化金额为decimal string并计算balance_before
    foreach ($list as &$item) {
        $item['amount'] = number_format($item['amount'], 8, '.', '');
        $item['balance_after'] = number_format($item['balance_after'], 8, '.', '');
        // 计算变动前余额
        $balanceBefore = bcsub($item['balance_after'], $item['amount'], 8);
        $item['balance_before'] = number_format($balanceBefore, 8, '.', '');
        // 生成描述
        $item['description'] = getDescription($item['type'], $item['meta']);
    }

    // 描述生成函数
    function getDescription($type, $meta) {
        $metaData = $meta ? json_decode($meta, true) : [];
        $typeMap = [
            'RECHARGE' => '充值到账',
            'WITHDRAW' => '提现',
            'SUBSCRIBE' => '投资',
            'UNFREEZE' => '解冻',
            'INCOME' => '收益',
            'REWARD' => '奖励'
        ];
        $desc = $typeMap[$type] ?? $type;
        if (isset($metaData['description'])) {
            $desc = $metaData['description'];
        }
        return $desc;
    }

    // 获取统计数据
    $statsData = [
        'total_in' => '0.00000000',
        'total_out' => '0.00000000',
        'count' => $total
    ];

    // 收入统计
    $inStats = $db->fetchOne(
        "SELECT SUM(change_amount) as total
         FROM wallet_logs
         WHERE {$where} AND change_amount > 0",
        $params
    );
    $statsData['total_in'] = number_format($inStats['total'] ?? 0, 8, '.', '');

    // 支出统计
    $outStats = $db->fetchOne(
        "SELECT SUM(ABS(change_amount)) as total
         FROM wallet_logs
         WHERE {$where} AND change_amount < 0",
        $params
    );
    $statsData['total_out'] = number_format($outStats['total'] ?? 0, 8, '.', '');

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
    error_log("钱包流水API错误: " . $e->getMessage());
    Response::error('获取钱包流水失败: ' . $e->getMessage());
}

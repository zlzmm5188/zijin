<?php
/**
 * 订单列表 API
 * GET /api/admin/orders
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('请求方法错误');
}

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(1, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

// 筛选条件
$status = isset($_GET['status']) ? (int)$_GET['status'] : -1; // -1表示全部
$currency = isset($_GET['currency']) ? trim($_GET['currency']) : '';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

$db = Database::getInstance();

// 构建WHERE条件
$where = ['1=1'];
$params = [];

// 状态筛选（排除待审核状态0）
if ($status >= 0) {
    $where[] = 'io.status = :status';
    $params['status'] = $status;
}

// 币种筛选
if ($currency && in_array($currency, ['CNY', 'USDT'])) {
    $where[] = 'io.currency = :currency';
    $params['currency'] = $currency;
}

// 关键词搜索（用户名或项目名）
if ($keyword) {
    $where[] = '(u.username LIKE :keyword OR ip.title LIKE :keyword OR u.uid LIKE :keyword)';
    $params['keyword'] = '%' . $keyword . '%';
}

// 时间筛选
if ($startDate) {
    $where[] = 'DATE(io.created_at) >= :start_date';
    $params['start_date'] = $startDate;
}
if ($endDate) {
    $where[] = 'DATE(io.created_at) <= :end_date';
    $params['end_date'] = $endDate;
}

$whereStr = implode(' AND ', $where);

try {
    // 查询订单列表
    $sql = "SELECT
                io.id,
                io.order_no,
                io.user_id,
                io.project_id,
                io.amount as invest_amount,
                io.cycle_days,
                io.base_rate,
                io.vip_extra_rate,
                io.final_rate as total_rate,
                io.expected_profit,
                io.earned_amount as profit,
                CASE io.status
                    WHEN 'PENDING' THEN 0
                    WHEN 'RUNNING' THEN 1
                    WHEN 'FINISHED' THEN 2
                    WHEN 'REFUND' THEN 3
                    WHEN 'REJECT' THEN 4
                    ELSE 0
                END as status,
                io.currency,
                io.start_at as start_date,
                io.end_at as end_date,
                io.created_at,
                u.username,
                u.uid,
                u.phone,
                u.email,
                ip.title as project_title,
                ip.category as project_category
            FROM invest_orders io
            LEFT JOIN users u ON io.user_id = u.id
            LEFT JOIN invest_projects ip ON io.project_id = ip.id
            WHERE {$whereStr}
            ORDER BY io.id DESC
            LIMIT {$limit} OFFSET {$offset}";

    $list = $db->fetchAll($sql, $params);

    // 查询总数
    $countSql = "SELECT COUNT(*) as total
                 FROM invest_orders io
                 LEFT JOIN users u ON io.user_id = u.id
                 LEFT JOIN invest_projects ip ON io.project_id = ip.id
                 WHERE {$whereStr}";
    $total = (int)$db->fetchOne($countSql, $params)['total'];

    // 格式化数据
    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['user_id'] = (int)$item['user_id'];
        $item['project_id'] = (int)$item['project_id'];
        $item['invest_amount'] = (float)$item['invest_amount'];
        $item['cycle_days'] = (int)$item['cycle_days'];
        $item['base_rate'] = (float)$item['base_rate'];
        $item['vip_extra_rate'] = (float)$item['vip_extra_rate'];
        $item['total_rate'] = (float)$item['total_rate'];
        $item['expected_profit'] = (float)$item['expected_profit'];
        $item['profit'] = (float)($item['profit'] ?? 0);
        $item['status'] = (int)$item['status'];

        // 状态文本（移除待审核状态）
        $statusTexts = [0 => '待审核', 1 => '进行中', 2 => '已完成', 3 => '已取消', 4 => '已拒绝'];
        $item['status_text'] = $statusTexts[$item['status']] ?? '未知';

        // 状态样式
        $statusColors = [0 => 'warning', 1 => 'primary', 2 => 'success', 3 => 'danger', 4 => 'secondary'];
        $item['status_color'] = $statusColors[$item['status']] ?? 'secondary';

        // 计算进度（已收益/预期收益）
        if ($item['expected_profit'] > 0) {
            $item['progress'] = round(($item['profit'] / $item['expected_profit']) * 100, 2);
        } else {
            $item['progress'] = 0;
        }

        // 剩余天数
        if ($item['end_date'] && $item['status'] == 1) {
            $endTime = strtotime($item['end_date']);
            $now = time();
            $item['days_left'] = max(0, ceil(($endTime - $now) / 86400));
        } else {
            $item['days_left'] = 0;
        }
    }

    // 统计数据
    $stats = [
        'total_invest' => (float)$db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) as total FROM invest_orders WHERE {$whereStr}",
            $params
        )['total'],
        'total_profit' => (float)$db->fetchOne(
            "SELECT COALESCE(SUM(earned_amount), 0) as total FROM invest_orders WHERE {$whereStr}",
            $params
        )['total'],
        'running_count' => (int)$db->fetchOne(
            "SELECT COUNT(*) as total FROM invest_orders WHERE status = 'RUNNING' AND {$whereStr}",
            $params
        )['total'],
        'finished_count' => (int)$db->fetchOne(
            "SELECT COUNT(*) as total FROM invest_orders WHERE status = 'FINISHED' AND {$whereStr}",
            $params
        )['total'],
    ];

    Response::success([
        'list' => $list,
        'pagination' => [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ],
        'stats' => $stats
    ]);

} catch (Exception $e) {
    error_log("订单列表API错误: " . $e->getMessage());
    Response::error('查询失败: ' . $e->getMessage());
}

<?php
/**
 * 订单详情 API
 * GET /api/admin/order-detail?id=123
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::error('请求方法错误');
}

$orderId = (int)($_GET['id'] ?? 0);

if ($orderId <= 0) {
    Response::error('订单ID无效');
}

$db = Database::getInstance();

try {
    // 查询订单详情
    $sql = "SELECT
                io.*,
                u.username,
                u.uid,
                u.phone,
                u.email,
                u.vip_level,
                ip.title as project_title,
                ip.subtitle as project_subtitle,
                ip.category as project_category,
                ip.description as project_description
            FROM " . $db->getPrefix() . "invest_orders io
            LEFT JOIN " . $db->getPrefix() . "users u ON io.user_id = u.id
            LEFT JOIN " . $db->getPrefix() . "invest_projects ip ON io.project_id = ip.id
            WHERE io.id = :id";

    $order = $db->fetchOne($sql, ['id' => $orderId]);

    if (!$order) {
        Response::error('订单不存在');
    }

    // 格式化数据
    $order['id'] = (int)$order['id'];
    $order['user_id'] = (int)$order['user_id'];
    $order['project_id'] = (int)$order['project_id'];
    $order['invest_amount'] = (float)$order['invest_amount'];
    $order['cycle_days'] = (int)$order['cycle_days'];
    $order['base_rate'] = (float)$order['base_rate'];
    $order['vip_extra_rate'] = (float)$order['vip_extra_rate'];
    $order['total_rate'] = (float)$order['total_rate'];
    $order['expected_profit'] = (float)$order['expected_profit'];
    $order['profit'] = (float)($order['profit'] ?? 0);
    $order['status'] = (int)$order['status'];
    $order['vip_level'] = (int)($order['vip_level'] ?? 0);

    // 状态文本
    $statusTexts = ['待审核', '进行中', '已完成', '已取消', '已拒绝'];
    $order['status_text'] = $statusTexts[$order['status']] ?? '未知';

    // 计算进度
    if ($order['expected_profit'] > 0) {
        $order['progress'] = round(($order['profit'] / $order['expected_profit']) * 100, 2);
    } else {
        $order['progress'] = 0;
    }

    // 计算剩余天数
    if ($order['end_date'] && $order['status'] == 1) {
        $endTime = strtotime($order['end_date']);
        $now = time();
        $order['days_left'] = max(0, ceil(($endTime - $now) / 86400));
        $order['days_passed'] = max(0, floor((time() - strtotime($order['start_date'])) / 86400));
    } else {
        $order['days_left'] = 0;
        $order['days_passed'] = 0;
    }

    // 查询收益记录
    $profitLogs = $db->fetchAll(
        "SELECT * FROM " . $db->getPrefix() . "order_profit_logs
         WHERE order_id = :order_id
         ORDER BY profit_date DESC
         LIMIT 100",
        ['order_id' => $orderId]
    );

    foreach ($profitLogs as &$log) {
        $log['id'] = (int)$log['id'];
        $log['order_id'] = (int)$log['order_id'];
        $log['profit_amount'] = (float)$log['profit_amount'];
        $log['total_profit'] = (float)$log['total_profit'];
    }

    Response::success([
        'order' => $order,
        'profit_logs' => $profitLogs
    ]);

} catch (Exception $e) {
    error_log("订单详情API错误: " . $e->getMessage());
    Response::error('查询失败: ' . $e->getMessage());
}

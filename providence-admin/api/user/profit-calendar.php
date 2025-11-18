<?php
/**
 * 获取收益日历
 * GET /user/profit/calendar
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

$userId = $authUser['user_id'];
$month = isset($_GET['month']) ? trim($_GET['month']) : date('Y-m');

$db = Database::getInstance();

try {
    // 查询该月的收益记录
    $monthStart = $month . '-01';
    $monthEnd = date('Y-m-t', strtotime($monthStart));

    $sql = "SELECT
                DATE(created_at) as date,
                SUM(CASE WHEN type IN ('PROFIT', 'INVEST_PROFIT') THEN amount ELSE 0 END) as daily_profit
            FROM wallet_logs
            WHERE user_id = :user_id
            AND DATE(created_at) BETWEEN :start AND :end
            GROUP BY DATE(created_at)
            ORDER BY date ASC";

    $list = $db->fetchAll($sql, [
        'user_id' => $userId,
        'start' => $monthStart,
        'end' => $monthEnd
    ]);

    // 格式化为日历数据
    $calendar = [];
    foreach ($list as $item) {
        $calendar[$item['date']] = (float)$item['daily_profit'];
    }

    Response::success([
        'month' => $month,
        'calendar' => $calendar,
        'total_profit' => array_sum($calendar)
    ]);

} catch (Exception $e) {
    error_log("获取收益日历API错误: " . $e->getMessage());
    Response::error('获取收益日历失败: ' . $e->getMessage());
}

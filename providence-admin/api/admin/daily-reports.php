<?php
/**
 * 每日报表API
 * GET /api/admin/daily-reports
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$db = Database::getInstance();

$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

try {
    $sql = "SELECT * FROM " . $db->getPrefix() . "daily_reports 
            WHERE date BETWEEN :start_date AND :end_date 
            ORDER BY date DESC";
    
    $list = $db->fetchAll($sql, [
        'start_date' => $startDate,
        'end_date' => $endDate
    ]);

    // 格式化数据
    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['new_users'] = (int)$item['new_users'];
        $item['active_users'] = (int)$item['active_users'];
        $item['total_users'] = (int)$item['total_users'];
        $item['recharge_count'] = (int)$item['recharge_count'];
        $item['recharge_amount'] = (float)$item['recharge_amount'];
        $item['withdraw_count'] = (int)$item['withdraw_count'];
        $item['withdraw_amount'] = (float)$item['withdraw_amount'];
        $item['invest_count'] = (int)$item['invest_count'];
        $item['invest_amount'] = (float)$item['invest_amount'];
        $item['profit_amount'] = (float)$item['profit_amount'];
        $item['net_income'] = (float)$item['net_income'];
    }

    // 计算汇总
    $summary = [
        'total_new_users' => 0,
        'total_recharge_count' => 0,
        'total_recharge_amount' => 0,
        'total_withdraw_count' => 0,
        'total_withdraw_amount' => 0,
        'total_invest_count' => 0,
        'total_invest_amount' => 0,
        'total_profit_amount' => 0,
        'total_net_income' => 0
    ];

    foreach ($list as $item) {
        $summary['total_new_users'] += $item['new_users'];
        $summary['total_recharge_count'] += $item['recharge_count'];
        $summary['total_recharge_amount'] += $item['recharge_amount'];
        $summary['total_withdraw_count'] += $item['withdraw_count'];
        $summary['total_withdraw_amount'] += $item['withdraw_amount'];
        $summary['total_invest_count'] += $item['invest_count'];
        $summary['total_invest_amount'] += $item['invest_amount'];
        $summary['total_profit_amount'] += $item['profit_amount'];
        $summary['total_net_income'] += $item['net_income'];
    }

    Response::success([
        'code' => 0,
        'msg' => '',
        'count' => count($list),
        'data' => $list,
        'summary' => $summary
    ]);

} catch (Exception $e) {
    error_log("每日报表API错误: " . $e->getMessage());
    Response::error('数据加载失败');
}

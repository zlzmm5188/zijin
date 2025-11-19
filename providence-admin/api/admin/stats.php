<?php
/**
 * 管理后台 - 数据统计API
 * GET /admin/stats
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$db = Database::getInstance();

try {
    // 总注册人数（排除内部人员）
    $totalUsers = $db->count('users', 'is_internal = 0');
    
    // 总充值金额（排除内部人员，已通过）
    $totalRechargeResult = $db->fetchOne(
        "SELECT COALESCE(SUM(r.amount), 0) as total 
         FROM " . $db->getPrefix() . "recharge_records r
         INNER JOIN " . $db->getPrefix() . "users u ON r.user_id = u.id
         WHERE r.status = 1 AND u.is_internal = 0"
    );
    $totalRecharge = $totalRechargeResult['total'] ?? 0;
    
    // 总提现金额（排除内部人员，已打款）
    $totalWithdrawResult = $db->fetchOne(
        "SELECT COALESCE(SUM(w.amount), 0) as total 
         FROM " . $db->getPrefix() . "withdraw_records w
         INNER JOIN " . $db->getPrefix() . "users u ON w.user_id = u.id
         WHERE w.status = 3 AND u.is_internal = 0"
    );
    $totalWithdraw = $totalWithdrawResult['total'] ?? 0;
    
    // 今日注册人数
    $todayUsers = $db->count('users', 
        'is_internal = 0 AND DATE(created_at) = CURDATE()'
    );
    
    // 今日充值金额
    $todayRechargeResult = $db->fetchOne(
        "SELECT COALESCE(SUM(r.amount), 0) as total 
         FROM " . $db->getPrefix() . "recharge_records r
         INNER JOIN " . $db->getPrefix() . "users u ON r.user_id = u.id
         WHERE r.status = 1 AND u.is_internal = 0 AND DATE(r.created_at) = CURDATE()"
    );
    $todayRecharge = $todayRechargeResult['total'] ?? 0;
    
    // 今日提现金额
    $todayWithdrawResult = $db->fetchOne(
        "SELECT COALESCE(SUM(w.amount), 0) as total 
         FROM " . $db->getPrefix() . "withdraw_records w
         INNER JOIN " . $db->getPrefix() . "users u ON w.user_id = u.id
         WHERE w.status = 3 AND u.is_internal = 0 AND DATE(w.created_at) = CURDATE()"
    );
    $todayWithdraw = $todayWithdrawResult['total'] ?? 0;
    
    // 总投资额
    $totalInvestResult = $db->fetchOne(
        "SELECT COALESCE(SUM(i.invest_amount), 0) as total 
         FROM " . $db->getPrefix() . "user_investments i
         INNER JOIN " . $db->getPrefix() . "users u ON i.user_id = u.id
         WHERE u.is_internal = 0"
    );
    $totalInvest = $totalInvestResult['total'] ?? 0;
    
    // 其他统计
    $totalProjects = $db->count('projects', 'status = 1');
    $totalOrders = $db->count('user_investments', '1=1');
    $pendingRecharge = $db->count('recharge_records', 'status = 0');
    $pendingWithdraw = $db->count('withdraw_records', 'status = 0');
    
    Response::success([
        // 总数据（排除内部人员）
        'total_users' => (int)$totalUsers,
        'total_recharge' => (float)$totalRecharge,
        'total_withdraw' => (float)$totalWithdraw,
        'total_invest' => (float)$totalInvest,
        'total_projects' => (int)$totalProjects,
        'total_orders' => (int)$totalOrders,
        
        // 今日数据（排除内部人员）
        'today_users' => (int)$todayUsers,
        'today_recharge' => (float)$todayRecharge,
        'today_withdraw' => (float)$todayWithdraw,
        
        // 待处理
        'pending_recharge' => (int)$pendingRecharge,
        'pending_withdraw' => (int)$pendingWithdraw,
        
        // 净利润
        'net_profit' => (float)($totalRecharge - $totalWithdraw),
    ]);
    
} catch (Exception $e) {
    Response::error('统计数据获取失败: ' . $e->getMessage());
}

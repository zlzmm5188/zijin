<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
$db = Database::getInstance();

try {
    $totalUsers = $db->count('users', 'is_internal = 0');
    $totalRechargeResult = $db->fetchOne("SELECT COALESCE(SUM(r.amount), 0) as total FROM " . $db->getPrefix() . "recharge_records r INNER JOIN " . $db->getPrefix() . "users u ON r.user_id = u.id WHERE r.status = 1 AND u.is_internal = 0");
    $totalRecharge = $totalRechargeResult['total'] ?? 0;
    
    Response::success([
        'total_users' => (int)$totalUsers,
        'total_recharge' => (float)$totalRecharge,
        'total_withdraw' => 0,
        'total_invest' => 0,
        'today_users' => 0,
        'today_recharge' => 0,
        'today_withdraw' => 0,
        'pending_recharge' => 0,
        'pending_withdraw' => 0,
        'net_profit' => (float)$totalRecharge
    ]);
} catch (Exception $e) {
    Response::success([
        'total_users' => 0,
        'total_recharge' => 0,
        'total_withdraw' => 0,
        'net_profit' => 0,
        'today_users' => 0,
        'today_recharge' => 0,
        'today_withdraw' => 0
    ]);
}

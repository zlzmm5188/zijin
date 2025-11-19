<?php
/**
 * 获取签到信息
 * GET /user/sign/info
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证用户登录
// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

$userId = $authUser['user_id'];
$db = Database::getInstance();

try {
    // 获取今天是否已签到
    $today = date('Y-m-d');
    $signToday = $db->fetchOne(
        "SELECT id FROM user_sign_logs WHERE user_id = :user_id AND DATE(signed_at) = :today",
        ['user_id' => $userId, 'today' => $today]
    );

    // 获取连续签到天数
    $continuousDays = 0;
    $checkDate = date('Y-m-d', strtotime('-1 day'));

    while (true) {
        $signed = $db->fetchOne(
            "SELECT id FROM user_sign_logs WHERE user_id = :user_id AND DATE(signed_at) = :date",
            ['user_id' => $userId, 'date' => $checkDate]
        );

        if (!$signed) {
            break;
        }

        $continuousDays++;
        $checkDate = date('Y-m-d', strtotime('-1 day', strtotime($checkDate)));

        // 最多查7天
        if ($continuousDays >= 7) {
            break;
        }
    }

    // 如果今天已签到，连续天数+1
    if ($signToday) {
        $continuousDays++;
    }

    // 获取本月签到天数
    $monthStart = date('Y-m-01');
    $monthSignCount = $db->fetchOne(
        "SELECT COUNT(*) as count FROM user_sign_logs
         WHERE user_id = :user_id AND DATE(signed_at) >= :month_start",
        ['user_id' => $userId, 'month_start' => $monthStart]
    )['count'] ?? 0;

    // 获取累计签到天数
    $totalSignCount = $db->fetchOne(
        "SELECT COUNT(*) as count FROM user_sign_logs WHERE user_id = :user_id",
        ['user_id' => $userId]
    )['count'] ?? 0;

    // 获取用户VIP等级对应的签到积分
    $user = $db->fetchOne("SELECT vip_level FROM users WHERE id = :id", ['id' => $userId]);
    $vipLevel = (int)($user['vip_level'] ?? 0);

    // VIP签到积分规则（从SRS）
    $vipSignPoints = [0 => 6, 1 => 10, 2 => 16, 3 => 20, 4 => 30, 5 => 40, 6 => 50, 7 => 60, 8 => 70];
    $todayPoints = $vipSignPoints[$vipLevel] ?? 6;

    Response::success([
        'is_signed' => (bool)$signToday,
        'continuous_days' => $continuousDays,
        'month_sign_count' => (int)$monthSignCount,
        'total_sign_count' => (int)$totalSignCount,
        'today_points' => $todayPoints,
        'vip_level' => $vipLevel
    ]);

} catch (Exception $e) {
    error_log("获取签到信息API错误: " . $e->getMessage());
    Response::error('获取签到信息失败: ' . $e->getMessage());
}

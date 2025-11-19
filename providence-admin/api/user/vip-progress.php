<?php
/**
 * 获取VIP进度
 * GET /user/vip/progress
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     // Response::error(...) [已禁用]);
    // }

$db = Database::getInstance();

try {
    // 获取用户信息
    $user = $db->fetchOne(
        "SELECT vip_level, total_invest FROM users WHERE id = :id",
        ['id' => $authUser['user_id']]
    );

    if (!$user) {
        Response::error('用户不存在');
    }

    $currentLevel = (int)$user['vip_level'];
    $totalInvest = (float)$user['total_invest'];

    // 获取当前等级信息
    $currentVip = $db->fetchOne(
        "SELECT * FROM vip_level_rules WHERE level = :level",
        ['level' => $currentLevel]
    );

    // 获取下一等级信息
    $nextVip = $db->fetchOne(
        "SELECT * FROM vip_level_rules WHERE level = :level",
        ['level' => $currentLevel + 1]
    );

    // 计算进度
    $progress = 0;
    $needInvest = 0;

    if ($nextVip) {
        $currentMin = (float)($currentVip['min_invest'] ?? 0);
        $nextMin = (float)$nextVip['min_invest'];
        $needInvest = $nextMin - $totalInvest;

        if ($nextMin > $currentMin) {
            $progress = (($totalInvest - $currentMin) / ($nextMin - $currentMin)) * 100;
            $progress = max(0, min(100, $progress));
        }
    } else {
        // 已达到最高等级
        $progress = 100;
    }

    Response::success([
        'current_level' => $currentLevel,
        'current_vip' => $currentVip,
        'next_vip' => $nextVip,
        'total_invest' => $totalInvest,
        'progress' => round($progress, 2),
        'need_invest' => max(0, $needInvest)
    ]);

} catch (Exception $e) {
    error_log("获取VIP进度失败: " . $e->getMessage());
    Response::error('获取VIP进度失败');
}

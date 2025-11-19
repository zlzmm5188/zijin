<?php
/**
 * 获取团队奖励状态
 * GET /user/team/rewards_status
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     // Response::error(...) [已禁用]);
    // }

// 简化实现：返回可领取奖励为0
Response::success([
    'available_rewards' => 0,
    'total_rewards' => 0,
    'claimed_rewards' => 0
]);

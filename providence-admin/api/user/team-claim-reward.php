<?php
/**
 * 领取团队奖励
 * POST /user/team/claim_reward
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     // Response::error(...) [已禁用]);
    // }

// 简化实现：暂无可领取奖励
Response::error('暂无可领取的团队奖励');

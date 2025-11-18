<?php

/**
 * API兼容层 - 匹配前端调用
 * 路径: /user/user/index
 * 转换响应格式: code: 1 → code: 200
 */
require_once dirname(__DIR__, 3) . '/config/bootstrap.php';

try {
    // Token验证 - 已禁用：无登录模式
    // $token = $_SERVER['HTTP_TOKEN'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? $_GET['token'] ?? '';
    //
    // if (empty($token)) {
    //     header('Content-Type: application/json; charset=utf-8');
    //     echo json_encode([
    //         'code' => 401,
    //         'message' => '请先登录',
    //         'data' => null
    //     ], JSON_UNESCAPED_UNICODE);
    //     exit;
    // }
    //
    // // 清理Bearer前缀
    // $token = str_replace('Bearer ', '', $token);
    //
    // // 验证Token
    // $authData = Auth::verifyToken($token);
    // if (!$authData) {
    //     header('Content-Type: application/json; charset=utf-8');
    //     echo json_encode([
    //         'code' => 401,
    //         'message' => '登录已过期，请重新登录',
    //         'data' => null
    //     ], JSON_UNESCAPED_UNICODE);
    //     exit;
    // }
    //
    // $userId = $authData['user_id'];

    // 无登录模式：使用默认用户ID
    $authUser = Auth::user();
    $userId = $authUser['user_id'] ?? 1;
    $db = Database::getInstance();

    // 获取用户基础信息
    $user = $db->fetchOne(
        "SELECT id, username, realname, phone, email, vip_level, total_invest,
                created_at, kyc_status
         FROM users
         WHERE id = ? LIMIT 1",
        [$userId]
    );

    if (!$user) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => 404,
            'message' => '用户不存在',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 获取CNY钱包
    $cnyWallet = $db->fetchOne(
        "SELECT balance, frozen, ribao_balance, ribao_total_profit,
                ribao_yesterday_profit, points, total_income
         FROM wallets
         WHERE user_id = ? AND currency = 'CNY' LIMIT 1",
        [$userId]
    );

    // 获取USDT钱包
    $usdtWallet = $db->fetchOne(
        "SELECT balance, frozen, total_income
         FROM wallets
         WHERE user_id = ? AND currency = 'USDT' LIMIT 1",
        [$userId]
    );

    // 获取投资统计（按币种）
    $investStatsCny = $db->fetchOne(
        "SELECT COUNT(*) as active_count,
                COALESCE(SUM(earned_amount), 0) as total_profit
         FROM invest_orders
         WHERE user_id = ? AND status = 'running' AND currency = 'CNY'",
        [$userId]
    );

    $investStatsUsdt = $db->fetchOne(
        "SELECT COUNT(*) as active_count,
                COALESCE(SUM(earned_amount), 0) as total_profit
         FROM invest_orders
         WHERE user_id = ? AND status = 'running' AND currency = 'USDT'",
        [$userId]
    );

    // 获取团队统计
    $teamStats = $db->fetchOne(
        "SELECT COUNT(*) as team_count,
                COALESCE(SUM(total_invest), 0) as team_performance
         FROM users
         WHERE inviter_id = ?",
        [$userId]
    );

    // 获取VIP信息
    $vipInfo = $db->fetchOne(
        "SELECT extra_rate, level_name FROM vip_interest_rules WHERE vip_level = ?",
        [$user['vip_level']]
    );

    // 返回数据（code: 200格式）
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 200,  // ⭐ 匹配前端期望
        'message' => '获取成功',
        'data' => [
            // 基础信息
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'realname' => $user['realname'] ?? '',
            'phone' => $user['phone'] ?? '',
            'email' => $user['email'] ?? '',

            // VIP
            'vip_level' => (int)$user['vip_level'],
            'vip_rate' => (float)($vipInfo['extra_rate'] ?? 0),
            'vip_benefits' => $vipInfo['level_name'] ?? 'VIP' . $user['vip_level'],

            // 余额
            'balance_cny' => (float)($cnyWallet['balance'] ?? 0),
            'balance_usdt' => (float)($usdtWallet['balance'] ?? 0),
            'frozen_cny' => (float)($cnyWallet['frozen'] ?? 0),
            'frozen_usdt' => (float)($usdtWallet['frozen'] ?? 0),

            // 日利宝
            'ribao_balance' => (float)($cnyWallet['ribao_balance'] ?? 0),
            'ribao_total_profit' => (float)($cnyWallet['ribao_total_profit'] ?? 0),
            'ribao_yesterday_profit' => (float)($cnyWallet['ribao_yesterday_profit'] ?? 0),

            // 积分
            'points' => (float)($cnyWallet['points'] ?? 0),

            // 投资（按币种）
            'total_invest' => (float)($user['total_invest'] ?? 0),
            'active_count' => (int)(($investStatsCny['active_count'] ?? 0) + ($investStatsUsdt['active_count'] ?? 0)),
            'total_profit_cny' => (float)($investStatsCny['total_profit'] ?? 0),
            'total_profit_usdt' => (float)($investStatsUsdt['total_profit'] ?? 0),

            // 团队
            'team_count' => (int)($teamStats['team_count'] ?? 0),
            'team_performance' => (float)($teamStats['team_performance'] ?? 0),

            // 其他
            'kyc_status' => (int)($user['kyc_status'] ?? 0),
            'created_at' => $user['created_at']
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('[User Index API] Error: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 500,
        'message' => '服务器错误: ' . $e->getMessage(),
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}

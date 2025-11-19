<?php
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
try {
    // Token验证 - 已禁用：无登录模式
    // $token = $_SERVER['HTTP_TOKEN'] ?? $_GET['token'] ?? '';
    // if (empty($token)) {
    //     echo json_encode(['code' => 401, 'message' => '请先登录', 'data' => null], JSON_UNESCAPED_UNICODE);
    //     exit;
    // }
    // $authData = Auth::verifyToken($token);
    // if (!$authData) {
    //     echo json_encode(['code' => 401, 'message' => '登录已过期，请重新登录', 'data' => null], JSON_UNESCAPED_UNICODE);
    //     exit;
    // }
    // $userId = $authData['user_id'];

    // 无登录模式：使用默认用户ID
    $authUser = Auth::user();
    $userId = $authUser['user_id'] ?? 1;
    $db = Database::getInstance();
    $user = $db->fetchOne('SELECT id, username, phone, email, vip_level, total_invest, created_at, realname_status FROM users WHERE id = ? LIMIT 1', [$userId]);
    if (!$user) {
        echo json_encode(['code' => 404, 'message' => '用户不存在', 'data' => null], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $cnyWallet = $db->fetchOne('SELECT balance, frozen, ribao_balance, ribao_total_profit, ribao_yesterday_profit, points, total_income FROM wallets WHERE user_id = ? AND currency = ? LIMIT 1', [$userId, 'CNY']);
    $usdtWallet = $db->fetchOne('SELECT balance, frozen, total_income FROM wallets WHERE user_id = ? AND currency = ? LIMIT 1', [$userId, 'USDT']);
    $teamStats = $db->fetchOne('SELECT COUNT(*) as team_count, COALESCE(SUM(total_invest), 0) as team_performance FROM users WHERE invite_code = ?', [$user['id']]);
    // 按币种统计返利和收益
    $referralRewardsCny = $db->fetchOne('SELECT COALESCE(SUM(amount), 0) as total_rewards FROM wallet_logs WHERE user_id = ? AND type = ? AND currency = ?', [$userId, 'referral_reward', 'CNY']);
    $referralRewardsUsdt = $db->fetchOne('SELECT COALESCE(SUM(amount), 0) as total_rewards FROM wallet_logs WHERE user_id = ? AND type = ? AND currency = ?', [$userId, 'referral_reward', 'USDT']);
    $activeInvestmentsCny = $db->fetchOne('SELECT COUNT(*) as active_count, COALESCE(SUM(earned_amount), 0) as total_profit FROM invest_orders WHERE user_id = ? AND status = ? AND currency = ?', [$userId, 'running', 'CNY']);
    $activeInvestmentsUsdt = $db->fetchOne('SELECT COUNT(*) as active_count, COALESCE(SUM(earned_amount), 0) as total_profit FROM invest_orders WHERE user_id = ? AND status = ? AND currency = ?', [$userId, 'running', 'USDT']);
    $vipBenefits = $db->fetchOne('SELECT extra_rate, level_name, min_invest_requirement FROM vip_interest_rules WHERE vip_level = ?', [$user['vip_level']]);

    // 按币种汇总数据
    $totalActiveCount = (int)(($activeInvestmentsCny['active_count'] ?? 0) + ($activeInvestmentsUsdt['active_count'] ?? 0));
    $totalProfitCny = (float)($activeInvestmentsCny['total_profit'] ?? 0);
    $totalProfitUsdt = (float)($activeInvestmentsUsdt['total_profit'] ?? 0);
    $referralRewardsCnyTotal = (float)($referralRewardsCny['total_rewards'] ?? 0);
    $referralRewardsUsdtTotal = (float)($referralRewardsUsdt['total_rewards'] ?? 0);

    echo json_encode([
        'code' => 200,
        'message' => '获取成功',
        'data' => [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'realname' => $user['realname'] ?? '',
            'phone' => $user['phone'] ?? '',
            'email' => $user['email'] ?? '',
            'vip_level' => (int)$user['vip_level'],
            'vip_rate' => (float)($vipBenefits['extra_rate'] ?? 0),
            'vip_benefits' => $vipBenefits['level_name'] ?? 'VIP' . $user['vip_level'],
            // CNY余额
            'balance_cny' => (float)($cnyWallet['balance'] ?? 0),
            'frozen_cny' => (float)($cnyWallet['frozen'] ?? 0),
            'total_income_cny' => (float)($cnyWallet['total_income'] ?? 0),
            // USDT余额
            'balance_usdt' => (float)($usdtWallet['balance'] ?? 0),
            'frozen_usdt' => (float)($usdtWallet['frozen'] ?? 0),
            'total_income_usdt' => (float)($usdtWallet['total_income'] ?? 0),
            // 日利宝（仅CNY）
            'ribao_balance' => (float)($cnyWallet['ribao_balance'] ?? 0),
            'ribao_total_profit' => (float)($cnyWallet['ribao_total_profit'] ?? 0),
            'ribao_yesterday_profit' => (float)($cnyWallet['ribao_yesterday_profit'] ?? 0),
            // 积分（仅CNY）
            'points' => (float)($cnyWallet['points'] ?? 0),
            // 投资统计（按币种）
            'total_invest' => (float)$user['total_invest'],
            'active_count' => $totalActiveCount,
            'total_profit_cny' => $totalProfitCny,
            'total_profit_usdt' => $totalProfitUsdt,
            // 团队统计
            'team_count' => (int)($teamStats['team_count'] ?? 0),
            'team_performance' => (float)($teamStats['team_performance'] ?? 0),
            // 返利统计（按币种）
            'referral_rewards_cny' => $referralRewardsCnyTotal,
            'referral_rewards_usdt' => $referralRewardsUsdtTotal,
            // 其他
            'withdraw_fee' => 2.0,
            'min_withdraw' => 100.0,
            'kyc_status' => (int)$user['kyc_status'],
            'created_at' => $user['created_at']
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('[AI API] Error: ' . $e->getMessage());
    echo json_encode(['code' => -1, 'message' => '服务暂时不可用，请稍后再试', 'data' => null], JSON_UNESCAPED_UNICODE);
}

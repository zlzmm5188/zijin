<?php

/**
 * 数据服务类 - 统一管理用户数据获取，确保数据一致性
 */
class DataService
{
    private $db;
    private $cache = []; // 简单的内存缓存，避免重复查询

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * 获取用户完整数据（确保数据一致性）
     */
    public function getUserData($userId)
    {
        if ($userId <= 0) {
            return null;
        }

        // 检查缓存
        if (isset($this->cache[$userId])) {
            return $this->cache[$userId];
        }

        // 获取用户基本信息
        $user = $this->db->fetchOne(
            "SELECT id, username, vip_level, total_invest, realname_status
             FROM users WHERE id = ?",
            [$userId]
        );

        if (!$user) {
            return null;
        }

        // 获取钱包数据（CNY）
        $cnyWallet = $this->db->fetchOne(
            "SELECT balance, ribao_balance, ribao_total_profit, ribao_yesterday_profit, points
             FROM wallets WHERE user_id = ? AND currency = 'CNY'",
            [$userId]
        );

        // 获取钱包数据（USDT）
        $usdtWallet = $this->db->fetchOne(
            "SELECT balance FROM wallets WHERE user_id = ? AND currency = 'USDT'",
            [$userId]
        );

        // 获取投资统计
        $investStats = $this->db->fetchOne(
            "SELECT COUNT(*) as active_count, COALESCE(SUM(earned_amount), 0) as total_profit
             FROM invest_orders WHERE user_id = ? AND status = 'running'",
            [$userId]
        );

        // 获取团队统计
        $teamStats = $this->db->fetchOne(
            "SELECT COUNT(*) as count, COALESCE(SUM(total_invest), 0) as performance
             FROM users WHERE inviter_id = ?",
            [$userId]
        );

        // 获取VIP信息
        $vipInfo = $this->db->fetchOne(
            "SELECT extra_rate, level_name FROM vip_interest_rules WHERE vip_level = ?",
            [$user['vip_level']]
        );

        // 组装数据（统一格式化）
        $userData = [
            'user_id' => $userId,
            'username' => $user['username'],
            'realname' => '',  // users表没有realname字段
            'vip_level' => (int)$user['vip_level'],
            'vip_rate' => (float)($vipInfo['extra_rate'] ?? 0),
            'vip_benefits' => $vipInfo['level_name'] ?? 'VIP' . $user['vip_level'],
            'balance_cny' => $this->formatAmount($cnyWallet['balance'] ?? 0),
            'balance_usdt' => $this->formatAmount($usdtWallet['balance'] ?? 0),
            'ribao_balance' => $this->formatAmount($cnyWallet['ribao_balance'] ?? 0),
            'ribao_total_profit' => $this->formatAmount($cnyWallet['ribao_total_profit'] ?? 0),
            'ribao_yesterday_profit' => $this->formatAmount($cnyWallet['ribao_yesterday_profit'] ?? 0),
            'points' => (int)($cnyWallet['points'] ?? 0),
            'total_invest' => $this->formatAmount($user['total_invest'] ?? 0),
            'active_count' => (int)($investStats['active_count'] ?? 0),
            'total_profit' => $this->formatAmount($investStats['total_profit'] ?? 0),
            'team_count' => (int)($teamStats['count'] ?? 0),
            'team_performance' => $this->formatAmount($teamStats['performance'] ?? 0),
            'withdraw_fee' => 2.0,
            'min_withdraw' => 100,
            'referral_rewards' => '0.00',
            'kyc_status' => $user['realname_status'] ?? 0
        ];

        // 缓存数据（本次请求有效）
        $this->cache[$userId] = $userData;

        return $userData;
    }

    /**
     * 格式化金额（统一格式，确保一致性）
     */
    private function formatAmount($amount, $decimals = 2)
    {
        $amount = (float)$amount;
        return number_format($amount, $decimals, '.', '');
    }

    /**
     * 清除缓存（当数据更新时调用）
     */
    public function clearCache($userId = null)
    {
        if ($userId) {
            unset($this->cache[$userId]);
        } else {
            $this->cache = [];
        }
    }
}

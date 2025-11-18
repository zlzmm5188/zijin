<?php

/**
 * 邀请返利服务类
 *
 * ⚠️ 事件驱动，不是普通函数
 * ⚠️ 触发时机：订单完成时（order finished）
 */
class ReferralService
{
    /**
     * 处理邀请返利
     * 触发事件：订单完成
     *
     * 有效用户条件：
     * 1. 完成实名认证
     * 2. 至少完成一笔项目认购
     * 3. 该认购项目完全结束（order finished）
     */
    public static function processReward($orderId)
    {
        $db = Database::getInstance();

        // 获取订单信息
        $order = $db->fetchOne(
            "SELECT * FROM " . $db->getPrefix() . "invest_orders WHERE id = :id",
            ['id' => $orderId]
        );

        if (!$order || $order['status'] != OrderStatus::FINISHED) {
            return false;
        }

        // 获取投资用户信息
        $user = $db->fetchOne(
            "SELECT * FROM " . $db->getPrefix() . "users WHERE id = :id",
            ['id' => $order['user_id']]
        );

        if (!$user || !$user['parent_id']) {
            return false; // 没有推荐人
        }

        $db->beginTransaction();
        try {
            // 一级推荐人奖励
            self::giveReward($user['parent_id'], $user['id'], $orderId, 1, $order['invest_amount'], $order['currency'] ?? 'CNY');

            // 二级推荐人奖励
            $parent = $db->fetchOne(
                "SELECT parent_id FROM " . $db->getPrefix() . "users WHERE id = :id",
                ['id' => $user['parent_id']]
            );

            if ($parent && $parent['parent_id']) {
                self::giveReward($parent['parent_id'], $user['id'], $orderId, 2, $order['invest_amount'], $order['currency'] ?? 'CNY');
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            error_log("返利发放失败: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 发放单个推荐奖励
     */
    private static function giveReward($userId, $referredUserId, $investId, $level, $investAmount, $currency)
    {
        $db = Database::getInstance();

        // ⚠️ 检查是否已发放过返利（防止重复发放）
        $existingReward = $db->fetchOne(
            "SELECT id FROM " . $db->getPrefix() . "referral_rewards
             WHERE user_id = :user_id
             AND invest_id = :invest_id
             AND level = :level
             LIMIT 1",
            [
                'user_id' => $userId,
                'invest_id' => $investId,
                'level' => $level
            ]
        );

        if ($existingReward) {
            error_log("返利已发放，跳过重复发放 [用户:{$userId}, 订单:{$investId}, 级别:{$level}]");
            return; // 已发放，直接返回
        }

        // 获取推荐人VIP等级
        $user = $db->fetchOne(
            "SELECT vip_level FROM " . $db->getPrefix() . "users WHERE id = :id",
            ['id' => $userId]
        );

        // 获取返利比例
        $vipRule = $db->fetchOne(
            "SELECT level1_percent, level2_percent FROM " . $db->getPrefix() . "vip_interest_rules WHERE vip_level = :level",
            ['level' => $user['vip_level']]
        );

        $percent = $level == 1 ? $vipRule['level1_percent'] : $vipRule['level2_percent'];

        if ($percent <= 0) {
            return; // 无奖励
        }

        // 计算奖励金额
        $rewardAmount = $investAmount * ($percent / 100);

        // 记录返利（使用 try-catch 防止唯一索引冲突）
        try {
            $db->insert('referral_rewards', [
                'user_id' => $userId,
                'referred_user_id' => $referredUserId,
                'invest_id' => $investId,
                'level' => $level,
                'vip_level' => $user['vip_level'],
                'invest_amount' => $investAmount,
                'reward_percent' => $percent,
                'reward_amount' => $rewardAmount,
                'currency' => $currency,
                'status' => 1
            ]);
        } catch (Exception $e) {
            // 如果是唯一索引冲突，说明已发放过，直接返回
            if (
                strpos($e->getMessage(), 'Duplicate entry') !== false ||
                strpos($e->getMessage(), 'UNIQUE constraint') !== false
            ) {
                error_log("返利记录已存在，跳过重复发放 [用户:{$userId}, 订单:{$investId}, 级别:{$level}]");
                return;
            }
            throw $e; // 其他错误继续抛出
        }

        // 增加用户余额（同币种）- 使用 wallets 表
        // 获取或创建对应币种的钱包
        $wallet = $db->fetchOne(
            "SELECT balance FROM wallets WHERE user_id = :user_id AND currency = :currency LIMIT 1",
            ['user_id' => $userId, 'currency' => $currency]
        );

        if (!$wallet) {
            // 创建钱包
            $db->insert('wallets', [
                'user_id' => $userId,
                'currency' => $currency,
                'balance' => 0,
                'frozen' => 0,
                'total_income' => 0
            ]);
            $balanceBefore = 0;
        } else {
            $balanceBefore = floatval($wallet['balance'] ?? 0);
        }

        $newBalance = $balanceBefore + $rewardAmount;

        // 获取当前 total_income（返利也算收益）
        $currentTotalIncome = floatval($wallet['total_income'] ?? 0);
        $newTotalIncome = $currentTotalIncome + $rewardAmount;

        // 更新钱包余额和累计收益
        $db->update('wallets', [
            'balance' => $newBalance,
            'total_income' => $newTotalIncome
        ], ['user_id' => $userId, 'currency' => $currency]);

        // 同步更新 users 表的余额字段
        if ($currency === 'CNY') {
            $db->update('users', ['balance_cny' => $newBalance], ['id' => $userId]);
        } else {
            $db->update('users', ['balance_usdt' => $newBalance], ['id' => $userId]);
        }

        // 记录钱包流水
        WalletService::addLog($userId, 'referral_reward', $rewardAmount, $currency, $investId, "邀请返利 - {$level}级");

        // 审计日志
        AuditLog::log(
            'promotion',
            '发放邀请返利',
            'system',
            0,
            'referral',
            $userId,
            null,
            ['level' => $level, 'amount' => $rewardAmount, 'currency' => $currency]
        );
    }
}

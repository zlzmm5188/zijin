<?php
/**
 * 钱包服务类
 * 处理多币种钱包操作
 */
class WalletService {
    /**
     * 添加钱包流水（按币种）
     */
    public static function addLog($userId, $type, $amount, $currency, $refId = null, $remark = '') {
        $db = Database::getInstance();

        // 获取当前余额（从 wallets 表）
        $wallet = $db->fetchOne(
            "SELECT balance FROM wallets WHERE user_id = :user_id AND currency = :currency LIMIT 1",
            ['user_id' => $userId, 'currency' => $currency]
        );

        $balanceBefore = floatval($wallet['balance'] ?? 0);
        $balanceAfter = $balanceBefore + $amount;

        return $db->insert('wallet_logs', [
            'user_id' => $userId,
            'type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'currency' => $currency,
            'ref_type' => 'order',
            'ref_id' => $refId,
            'remark' => $remark
        ]);
    }

    /**
     * 检查余额是否足够（按币种）
     */
    public static function hasEnoughBalance($userId, $amount, $currency = 'CNY') {
        $db = Database::getInstance();
        $wallet = $db->fetchOne(
            "SELECT balance FROM wallets WHERE user_id = :user_id AND currency = :currency LIMIT 1",
            ['user_id' => $userId, 'currency' => $currency]
        );

        return floatval($wallet['balance'] ?? 0) >= $amount;
    }

    /**
     * 增加用户余额（按币种）
     */
    public static function addBalance($userId, $amount, $currency = 'CNY', $type = 'profit', $refId = null, $remark = '') {
        $db = Database::getInstance();

        // 获取或创建钱包
        $wallet = $db->fetchOne(
            "SELECT balance, total_income FROM wallets WHERE user_id = :user_id AND currency = :currency FOR UPDATE",
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
            $totalIncomeBefore = 0;
        } else {
            $balanceBefore = floatval($wallet['balance'] ?? 0);
            $totalIncomeBefore = floatval($wallet['total_income'] ?? 0);
        }

        $newBalance = $balanceBefore + $amount;
        $newTotalIncome = $totalIncomeBefore + ($type === 'profit' ? $amount : 0);

        // 更新钱包余额
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
        self::addLog($userId, $type, $amount, $currency, $refId, $remark);

        return $newBalance;
    }
}

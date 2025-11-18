#!/usr/bin/env php
<?php
/**
 * 每日收益发放定时任务 (SRS v1.0规范)
 *
 * ⚠️ 使用EarningsService统一计算
 * ⚠️ 订单完成时触发ReferralService发放返利
 */

require_once __DIR__ . '/../config/bootstrap.php';

echo "[" . date('Y-m-d H:i:s') . "] 开始执行每日收益发放任务...\n";

try {
    $db = Database::getInstance();

    // 获取所有运行中的订单
    $sql = "SELECT * FROM " . $db->getPrefix() . "invest_orders
            WHERE status = :status
            AND start_date <= CURDATE()
            AND end_date >= CURDATE()";

    $investments = $db->fetchAll($sql, ['status' => OrderStatus::RUNNING]);

    echo "找到 " . count($investments) . " 笔运行中的订单\n";

    $successCount = 0;
    $finishedCount = 0;

    foreach ($investments as $order) {
        try {
            // 检查今天是否已发放
            $today = date('Y-m-d');
            $exists = $db->fetchOne(
                "SELECT id FROM " . $db->getPrefix() . "earnings_records
                 WHERE investment_id = :inv_id AND earn_date = :date",
                ['inv_id' => $order['id'], 'date' => $today]
            );

            if ($exists) {
                continue; // 今天已发放
            }

            // 【使用EarningsService计算收益】
            // 注意：invest_orders 表使用 amount 字段，不是 invest_amount
            $investAmount = $order['amount'] ?? $order['invest_amount'] ?? 0;
            $dailyEarning = $investAmount * ($order['final_daily_rate'] ?? 0);

            $db->beginTransaction();

            // 记录收益
            $db->insert('earnings_records', [
                'investment_id' => $order['id'],
                'user_id' => $order['user_id'],
                'amount' => $dailyEarning,
                'earn_date' => $today,
                'status' => 1
            ]);

            // 更新订单累计收益
            $newEarned = $order['earned_amount'] + $dailyEarning;
            $db->update(
                'invest_orders',
                ['earned_amount' => $newEarned],
                'id = :id',
                ['id' => $order['id']]
            );

            // 增加用户余额（根据币种）- 使用 wallets 表
            $currency = $order['currency'] ?? 'CNY';

            // 使用 WalletService 增加余额（自动处理 wallets 表和 users 表同步）
            WalletService::addBalance(
                $order['user_id'],
                $dailyEarning,
                $currency,
                'profit',
                $order['id'],
                "每日收益 - 订单 #{$order['id']}"
            );

            // 检查订单是否完成
            if ($today >= $order['end_date']) {
                // 订单完成
                $db->update(
                    'invest_orders',
                    ['status' => OrderStatus::FINISHED],
                    'id = :id',
                    ['id' => $order['id']]
                );

                // 【触发邀请返利事件】
                ReferralService::processReward($order['id']);

                echo "  ✓ 订单 #{$order['id']} 已完成，触发返利\n";
                $finishedCount++;
            }

            $db->commit();
            $successCount++;

            echo "  ✓ 订单 #{$order['id']} 发放收益: ¥{$dailyEarning}\n";
        } catch (Exception $e) {
            $db->rollBack();
            echo "  ✗ 订单 #{$order['id']} 发放失败: " . $e->getMessage() . "\n";
        }
    }

    echo "\n任务完成！\n";
    echo "发放成功: {$successCount} 笔\n";
    echo "订单完成: {$finishedCount} 笔\n";
    echo "[" . date('Y-m-d H:i:s') . "] 任务结束\n";
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    exit(1);
}

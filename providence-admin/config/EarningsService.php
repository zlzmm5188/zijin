<?php
/**
 * 收益计算服务类 v2.0
 * 
 * ⚠️ 全局唯一收益计算入口
 * ⚠️ 禁止在其他任何地方进行收益计算
 * ⚠️ 前端永远不做数学
 * 
 * 变更：取消年化概念，改为周期收益
 */
class EarningsService {
    /**
     * 计算订单收益（v2.0新算法）
     * 
     * @param float $amount 投资金额
     * @param float $baseRate 基础收益率%（整个周期）
     * @param float $vipExtraRate VIP额外收益率%（整个周期）
     * @param int $cycleDays 周期天数
     * @return array
     * 
     * 公式：
     * profit = amount × (base_rate + vip_extra_rate) / 100
     * total = amount + profit
     * daily_profit = profit / cycle_days
     */
    public static function calculateEarnings($amount, $baseRate, $vipExtraRate, $cycleDays) {
        // 计算最终收益率（周期）
        $finalRate = $baseRate + $vipExtraRate;
        
        // 周期总收益
        $profit = $amount * $finalRate / 100;
        
        // 本金+收益
        $total = $amount + $profit;
        
        // 日均收益
        $dailyProfit = $cycleDays > 0 ? $profit / $cycleDays : 0;
        
        return [
            'final_rate' => round($finalRate, 2),      // 最终收益率%
            'profit' => round($profit, 2),             // 周期总收益
            'total' => round($total, 2),               // 本金+收益
            'daily_profit' => round($dailyProfit, 2)   // 日均收益
        ];
    }
    
    /**
     * 从订单ID计算收益
     */
    public static function calculateOrderEarnings($orderId) {
        $db = Database::getInstance();
        
        $order = $db->fetchOne("SELECT * FROM " . $db->getPrefix() . "invest_orders WHERE id = :id", 
            ['id' => $orderId]);
        
        if (!$order) {
            return false;
        }
        
        return self::calculateEarnings(
            $order['invest_amount'],
            $order['base_rate'],
            $order['vip_extra_rate'],
            $order['cycle_days']
        );
    }
}

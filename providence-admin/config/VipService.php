<?php
/**
 * VIP服务类
 * 
 * ⚠️ 禁止在Controller或前端进行VIP计算
 * ⚠️ 这是唯一的VIP计算入口
 */
class VipService {
    /**
     * 检查并自动升级VIP
     * 触发时机：每次投资成功后
     * 规则：只升不降
     */
    public static function checkAndUpgrade($userId) {
        $db = Database::getInstance();
        
        // 获取用户信息
        $user = $db->fetchOne("SELECT id, username, vip_level FROM " . $db->getPrefix() . "users WHERE id = :id", 
            ['id' => $userId]);
        
        if (!$user) {
            return false;
        }
        
        // 计算累计投资额（已完成的订单）
        $result = $db->fetchOne(
            "SELECT COALESCE(SUM(invest_amount), 0) as total 
             FROM " . $db->getPrefix() . "invest_orders 
             WHERE user_id = :uid AND status IN (1, 2)",
            ['uid' => $userId]
        );
        
        $totalInvest = $result['total'] ?? 0;
        
        // 从规则表获取应有的VIP等级
        $vipRules = $db->fetchAll("SELECT * FROM " . $db->getPrefix() . "vip_interest_rules ORDER BY vip_level DESC");
        
        $newLevel = 0;
        foreach ($vipRules as $rule) {
            if ($totalInvest >= $rule['min_invest']) {
                $newLevel = $rule['vip_level'];
                break;
            }
        }
        
        // 只升不降
        if ($newLevel > $user['vip_level']) {
            $db->beginTransaction();
            try {
                // 更新VIP等级
                $db->update('users', ['vip_level' => $newLevel], 'id = :id', ['id' => $userId]);
                
                // 记录审计日志
                AuditLog::log(
                    'member',
                    'VIP自动升级',
                    'system',
                    0,
                    'vip_upgrade',
                    $userId,
                    ['old_level' => $user['vip_level'], 'total_invest' => $totalInvest],
                    ['new_level' => $newLevel, 'total_invest' => $totalInvest]
                );
                
                $db->commit();
                return ['upgraded' => true, 'old_level' => $user['vip_level'], 'new_level' => $newLevel];
            } catch (Exception $e) {
                $db->rollBack();
                return false;
            }
        }
        
        return ['upgraded' => false, 'level' => $user['vip_level']];
    }
}

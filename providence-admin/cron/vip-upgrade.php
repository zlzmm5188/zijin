#!/usr/bin/env php
<?php
/**
 * VIP自动升级定时任务
 * 根据用户累计投资额自动升级VIP等级
 */

require_once __DIR__ . '/../config/bootstrap.php';

echo "[" . date('Y-m-d H:i:s') . "] 开始执行VIP升级任务...\n";

try {
    $db = Database::getInstance();
    
    // 获取VIP等级配置
    $vipLevels = $db->fetchAll("SELECT * FROM " . $db->getPrefix() . "vip_levels ORDER BY id ASC");
    
    // 获取所有用户
    $users = $db->fetchAll("SELECT id, username, vip_level FROM " . $db->getPrefix() . "users WHERE status = 1");
    
    $upgradeCount = 0;
    
    foreach ($users as $user) {
        // 计算用户累计投资额
        $result = $db->fetchOne(
            "SELECT SUM(invest_amount) as total FROM " . $db->getPrefix() . "user_investments WHERE user_id = :id",
            ['id' => $user['id']]
        );
        
        $totalInvest = $result['total'] ?? 0;
        
        // 根据投资额确定应有的VIP等级
        $newLevel = 1;
        foreach ($vipLevels as $level) {
            if ($totalInvest >= $level['min_invest']) {
                $newLevel = $level['id'];
            }
        }
        
        // 如果等级有变化，升级
        if ($newLevel > $user['vip_level']) {
            $db->update('users', 
                ['vip_level' => $newLevel],
                'id = :id',
                ['id' => $user['id']]
            );
            
            echo "  ✓ 用户 {$user['username']} VIP{$user['vip_level']} -> VIP{$newLevel}\n";
            $upgradeCount++;
        }
    }
    
    echo "\n任务完成！升级 {$upgradeCount} 个用户\n";
    echo "[" . date('Y-m-d H:i:s') . "] 任务结束\n";
    
} catch (Exception $e) {
    echo "错误: " . $e->getMessage() . "\n";
    exit(1);
}

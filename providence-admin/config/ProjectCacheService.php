<?php
/**
 * 项目缓存服务
 * 维护 total_rate、sold、remain 字段级缓存
 * 
 * 原因：
 * 1. 300+项目时实时计算太慢
 * 2. 跨端一致性（web/app/小程序）
 * 3. SRS核心原则：后端统一字段
 */
class ProjectCacheService {
    /**
     * 更新单个项目的缓存
     */
    public static function updateCache($projectId) {
        $db = Database::getInstance();
        
        // 获取项目信息
        $project = $db->fetchOne(
            "SELECT base_rate, added_rate, gift_rate, total_quota, total_invested 
             FROM " . $db->getPrefix() . "invest_projects WHERE id = :id",
            ['id' => $projectId]
        );
        
        if (!$project) {
            return false;
        }
        
        // 计算total_rate（不含VIP，VIP是用户级别的）
        $totalRate = $project['base_rate'] + $project['added_rate'] + $project['gift_rate'];
        
        // 计算sold
        $sold = $project['total_invested'];
        
        // 计算remain
        $totalQuota = $project['total_quota'];
        $remain = $totalQuota > 0 ? max(0, $totalQuota - $sold) : 999999999;
        
        // 计算schedule
        $schedule = $totalQuota > 0 ? ($sold / $totalQuota * 100) : 0;
        
        // 更新缓存
        return $db->update('invest_projects', [
            'total_rate' => $totalRate,
            'sold' => $sold,
            'remain' => $remain,
            'schedule' => $schedule,
            'cache_updated_at' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $projectId]);
    }
    
    /**
     * 批量更新所有项目缓存
     * 定时任务调用
     */
    public static function updateAllCache() {
        $db = Database::getInstance();
        
        $projects = $db->fetchAll("SELECT id FROM " . $db->getPrefix() . "invest_projects WHERE status IN ('ONLINE', 'DRAFT', 'PENDING')");
        
        $count = 0;
        foreach ($projects as $project) {
            if (self::updateCache($project['id'])) {
                $count++;
            }
        }
        
        return $count;
    }
}

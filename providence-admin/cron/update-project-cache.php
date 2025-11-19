#!/usr/bin/env php
<?php
/**
 * 项目缓存刷新定时任务
 * 每小时执行一次
 */

require_once __DIR__ . '/../config/bootstrap.php';

echo "[" . date('Y-m-d H:i:s') . "] 开始更新项目缓存...\n";

$count = ProjectCacheService::updateAllCache();

echo "更新完成！共更新 {$count} 个项目\n";
echo "[" . date('Y-m-d H:i:s') . "] 任务结束\n";

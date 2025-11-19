<?php
/**
 * 投资课堂定时更新任务
 * 建议每天凌晨3点执行
 * 
 * Crontab配置:
 * 0 3 * * * php /www/wwwroot/f.abcmall.one/providence/cron-update-education.php >> /tmp/education-cron.log 2>&1
 */

require_once __DIR__ . '/education-crawler.php';

echo "[" . date('Y-m-d H:i:s') . "] 开始采集投资课堂内容...\n";

$result = crawlEducationContent(true);

if ($result['code'] === 0) {
    echo "[SUCCESS] 采集成功！课程数量: " . $result['count'] . "\n";
    echo "[INFO] 更新时间: " . ($result['updateTime'] ?? 'N/A') . "\n";
} else {
    echo "[ERROR] 采集失败: " . $result['message'] . "\n";
}

echo "[" . date('Y-m-d H:i:s') . "] 完成\n\n";

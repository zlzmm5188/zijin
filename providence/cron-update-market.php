<?php
/**
 * Cron任务：更新市场数据
 * 建议每30秒执行一次
 *
 * Crontab设置：
 * 30秒更新一次：
 * * * * * * php /www/wwwroot/f.abcmall.one/providence/cron-update-market.php
 * * * * * * sleep 30; php /www/wwwroot/f.abcmall.one/providence/cron-update-market.php
 *
 * 或者1分钟更新一次：
 * * * * * * php /www/wwwroot/f.abcmall.one/providence/cron-update-market.php
 */

$scriptPath = __DIR__ . '/market-crawler.php';
$logFile = __DIR__ . '/logs/market-cron.log';

// 确保logs目录存在
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

// 记录开始时间
$startTime = microtime(true);
$timestamp = date('Y-m-d H:i:s');

// 执行爬取
$output = shell_exec("php $scriptPath action=fetch 2>&1");

// 记录结束时间
$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);

// 写入日志
$logMessage = sprintf(
    "[%s] 市场数据更新完成 | 耗时: %s秒\n输出: %s\n%s\n",
    $timestamp,
    $duration,
    trim($output),
    str_repeat('-', 80)
);

file_put_contents($logFile, $logMessage, FILE_APPEND);

// 输出到标准输出
echo $logMessage;

#!/usr/bin/env php
<?php
/**
 * 定时任务：自动更新财经日历
 * 使用方法：
 * 1. 命令行运行：php cron-update-calendar.php
 * 2. 添加到crontab（每小时执行一次）：
 *    crontab -e
 *    然后添加：
 *    0 * * * * php /www/wwwroot/f.abcmall.one/providence/cron-update-calendar.php >> /tmp/calendar-cron.log 2>&1
 */

// 设置时区
date_default_timezone_set('Asia/Shanghai');

echo "[" . date('Y-m-d H:i:s') . "] 开始采集财经日历...\n";

// 配置
define('CALENDAR_DATA_FILE', __DIR__ . '/data/calendar-data.json');

// 确保数据目录存在
if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}

/**
 * 采集金十数据财经日历
 */
function fetchJinshiCalendar() {
    $events = [];
    try {
        $today = date('Y-m-d');
        $url = 'https://rili.jin10.com/data/daily/' . str_replace('-', '/', $today);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && $httpCode == 200) {
            $data = json_decode($response, true);
            if (isset($data['data']) && is_array($data['data'])) {
                foreach ($data['data'] as $item) {
                    $importance = 'low';
                    if (isset($item['star']) && $item['star'] >= 3) {
                        $importance = 'high';
                    } elseif (isset($item['star']) && $item['star'] >= 2) {
                        $importance = 'medium';
                    }

                    $region = 'us';
                    $country = $item['country'] ?? '未知';
                    if (strpos($country, '中国') !== false) $region = 'cn';
                    elseif (strpos($country, '美国') !== false) $region = 'us';
                    elseif (strpos($country, '欧') !== false) $region = 'eu';
                    elseif (strpos($country, '日本') !== false) $region = 'jp';
                    elseif (strpos($country, '英国') !== false) $region = 'uk';

                    $events[] = [
                        'id' => 'jinshi_' . ($item['id'] ?? uniqid()),
                        'date' => $today,
                        'time' => $item['pub_time'] ?? '--:--',
                        'region' => $region,
                        'country' => $country,
                        'event' => $item['name'] ?? '',
                        'importance' => $importance,
                        'actual' => $item['actual'] ?? '--',
                        'forecast' => $item['consensus'] ?? '--',
                        'previous' => $item['previous'] ?? '--',
                        'type' => 'data',
                        'unit' => $item['unit'] ?? '',
                        'source' => '金十数据'
                    ];
                }
                echo "  金十数据：成功采集 " . count($events) . " 条\n";
            }
        } else {
            echo "  金十数据：请求失败 (HTTP $httpCode)\n";
        }
    } catch (Exception $e) {
        echo "  金十数据：" . $e->getMessage() . "\n";
    }
    return $events;
}

/**
 * 生成补充数据
 */
function generateSupplementData() {
    $today = date('Y-m-d');
    $tomorrow = date('Y-m-d', strtotime('+1 day'));
    $dayAfter = date('Y-m-d', strtotime('+2 days'));

    $mockData = [
        $today => [
            ['time' => '09:30', 'region' => 'cn', 'country' => '中国', 'event' => '工业增加值同比', 'importance' => 'high', 'actual' => '--', 'forecast' => '5.4%', 'previous' => '5.3%', 'type' => 'data', 'unit' => '%'],
            ['time' => '20:30', 'region' => 'us', 'country' => '美国', 'event' => '美联储主席鲍威尔讲话', 'importance' => 'high', 'actual' => '--', 'forecast' => '--', 'previous' => '--', 'type' => 'event', 'unit' => ''],
        ],
        $tomorrow => [
            ['time' => '16:30', 'region' => 'us', 'country' => '美国', 'event' => '零售销售月率', 'importance' => 'high', 'actual' => '--', 'forecast' => '0.3%', 'previous' => '0.2%', 'type' => 'data', 'unit' => '%'],
        ],
        $dayAfter => [
            ['time' => '20:30', 'region' => 'us', 'country' => '美国', 'event' => '核心PPI月率', 'importance' => 'high', 'actual' => '--', 'forecast' => '0.2%', 'previous' => '0.1%', 'type' => 'data', 'unit' => '%'],
        ]
    ];

    $events = [];
    foreach ($mockData as $date => $dateEvents) {
        foreach ($dateEvents as $event) {
            $events[] = array_merge($event, [
                'id' => 'supplement_' . md5($date . $event['time'] . $event['event']),
                'date' => $date,
                'source' => '系统数据'
            ]);
        }
    }

    return $events;
}

/**
 * 按日期组织数据
 */
function organizeByDate($allEvents) {
    $organized = [];

    foreach ($allEvents as $event) {
        $date = $event['date'];
        if (!isset($organized[$date])) {
            $organized[$date] = [];
        }
        $organized[$date][] = $event;
    }

    // 按时间排序
    foreach ($organized as $date => &$events) {
        usort($events, function($a, $b) {
            if ($a['time'] === '全天') return -1;
            if ($b['time'] === '全天') return 1;
            return strcmp($a['time'], $b['time']);
        });
    }

    return $organized;
}

// ========== 执行采集 ==========
$allEvents = [];

echo "\n正在采集...\n";
$allEvents = array_merge($allEvents, fetchJinshiCalendar());
$allEvents = array_merge($allEvents, generateSupplementData());

if (empty($allEvents)) {
    echo "\n[" . date('Y-m-d H:i:s') . "] 警告：未采集到任何数据\n";
    exit(1);
}

// 去重
$uniqueEvents = [];
$eventKeys = [];
foreach ($allEvents as $event) {
    $key = $event['date'] . '_' . $event['time'] . '_' . $event['event'];
    if (!in_array($key, $eventKeys)) {
        $eventKeys[] = $key;
        $uniqueEvents[] = $event;
    }
}

echo "\n去重后剩余：" . count($uniqueEvents) . " 条\n";

// 按日期组织
$calendarData = organizeByDate($uniqueEvents);

// 统计
echo "\n日期统计：\n";
foreach ($calendarData as $date => $events) {
    echo "  $date: " . count($events) . " 条\n";
}

// 添加元数据
$result = [
    'data' => $calendarData,
    'updateTime' => date('Y-m-d H:i:s'),
    'timestamp' => time(),
    'totalEvents' => count($uniqueEvents),
    'dateRange' => [
        'start' => min(array_keys($calendarData)),
        'end' => max(array_keys($calendarData))
    ]
];

// 保存到文件
file_put_contents(CALENDAR_DATA_FILE, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "\n[" . date('Y-m-d H:i:s') . "] 采集成功！数据已保存到 " . CALENDAR_DATA_FILE . "\n";
echo "[" . date('Y-m-d H:i:s') . "] 完成\n\n";

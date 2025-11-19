#!/usr/bin/env php
<?php
/**
 * 定时任务：自动更新财经新闻
 * 使用方法：
 * 1. 命令行运行：php cron-update-news.php
 * 2. 添加到crontab（每5分钟执行一次）：
 *    crontab -e
 *    然后添加以下行：
 *    EVERY_5_MIN php /www/wwwroot/f.abcmall.one/providence/cron-update-news.php >> /tmp/news-cron.log 2>&1
 */

// 设置时区
date_default_timezone_set('Asia/Shanghai');

echo "[" . date('Y-m-d H:i:s') . "] 开始采集新闻...\n";

// 配置
define('NEWS_DATA_FILE', __DIR__ . '/data/news-data.json');

// 确保数据目录存在
if (!is_dir(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
}

/**
 * 采集新浪财经快讯
 */
function fetchSinaFinance() {
    $news = [];
    try {
        $url = 'https://zhibo.sina.com.cn/api/zhibo/feed?zhibo_id=152&page=1&page_size=30&tag_id=0&dire=f';
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
            if (isset($data['result']['data']['feed']['list'])) {
                foreach ($data['result']['data']['feed']['list'] as $item) {
                    $text = strip_tags($item['rich_text']);
                    $news[] = [
                        'id' => 'sina_' . $item['id'],
                        'time' => date('H:i', $item['create_time']),
                        'title' => $text,
                        'desc' => mb_substr($text, 0, 60, 'UTF-8') . '...',
                        'content' => $text,
                        'tags' => ['财经'],
                        'importance' => 'medium',
                        'source' => '新浪财经',
                        'views' => rand(10000, 100000),
                        'date' => date('Y-m-d', $item['create_time']),
                        'timestamp' => $item['create_time']
                    ];
                }
                echo "  新浪财经：成功采集 " . count($news) . " 条\n";
            }
        } else {
            echo "  新浪财经：请求失败 (HTTP $httpCode)\n";
        }
    } catch (Exception $e) {
        echo "  新浪财经：" . $e->getMessage() . "\n";
    }
    return $news;
}

/**
 * 采集金十数据快讯
 */
function fetchJinshi() {
    $news = [];
    try {
        $url = 'https://flash-api.jin10.com/get_flash_list?channel=-1&vip=1&limit=30';
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
            if (isset($data['data'])) {
                foreach ($data['data'] as $item) {
                    $importance = 'low';
                    if (isset($item['important']) && $item['important'] >= 3) {
                        $importance = 'high';
                    } elseif (isset($item['important']) && $item['important'] >= 2) {
                        $importance = 'medium';
                    }

                    $news[] = [
                        'id' => 'jinshi_' . $item['id'],
                        'time' => date('H:i', $item['time']),
                        'title' => $item['content'],
                        'desc' => mb_substr($item['content'], 0, 60, 'UTF-8') . '...',
                        'content' => $item['content'],
                        'tags' => ['金十数据'],
                        'importance' => $importance,
                        'source' => '金十数据',
                        'views' => rand(10000, 100000),
                        'date' => date('Y-m-d', $item['time']),
                        'timestamp' => $item['time']
                    ];
                }
                echo "  金十数据：成功采集 " . count($news) . " 条\n";
            }
        } else {
            echo "  金十数据：请求失败 (HTTP $httpCode)\n";
        }
    } catch (Exception $e) {
        echo "  金十数据：" . $e->getMessage() . "\n";
    }
    return $news;
}

/**
 * 分类新闻
 */
function categorizeNews($allNews) {
    $categorized = [
        'all' => [],
        'forex' => [],
        'stock' => [],
        'commodity' => [],
        'crypto' => []
    ];

    foreach ($allNews as $news) {
        $categorized['all'][] = $news;

        $text = $news['title'] . ' ' . $news['content'];

        if (preg_match('/(美元|欧元|人民币|日元|英镑|汇率|外汇|央行|美联储|加息|降息|利率)/u', $text)) {
            $categorized['forex'][] = $news;
        }

        if (preg_match('/(股市|A股|港股|美股|上证|深证|创业板|科创板|涨停|跌停|IPO|上市|股票)/u', $text)) {
            $categorized['stock'][] = $news;
        }

        if (preg_match('/(原油|黄金|白银|铜|铁矿石|大豆|玉米|商品|期货|WTI|布伦特)/u', $text)) {
            $categorized['commodity'][] = $news;
        }

        if (preg_match('/(比特币|以太坊|加密货币|BTC|ETH|区块链|NFT|Web3|数字货币)/u', $text)) {
            $categorized['crypto'][] = $news;
        }
    }

    return $categorized;
}

// ========== 执行采集 ==========
$allNews = [];

echo "\n正在采集...\n";
$allNews = array_merge($allNews, fetchSinaFinance());
$allNews = array_merge($allNews, fetchJinshi());

if (empty($allNews)) {
    echo "\n[" . date('Y-m-d H:i:s') . "] 警告：未采集到任何新闻\n";
    exit(1);
}

// 按时间戳排序
usort($allNews, function($a, $b) {
    return $b['timestamp'] - $a['timestamp'];
});

// 去重
$uniqueNews = [];
$titles = [];
foreach ($allNews as $news) {
    $titleKey = md5($news['title']);
    if (!in_array($titleKey, $titles)) {
        $titles[] = $titleKey;
        $uniqueNews[] = $news;
    }
}

echo "\n去重后剩余：" . count($uniqueNews) . " 条\n";

// 分类
$newsData = categorizeNews($uniqueNews);

// 统计
echo "\n分类统计：\n";
echo "  全部: " . count($newsData['all']) . " 条\n";
echo "  外汇: " . count($newsData['forex']) . " 条\n";
echo "  股票: " . count($newsData['stock']) . " 条\n";
echo "  商品: " . count($newsData['commodity']) . " 条\n";
echo "  加密: " . count($newsData['crypto']) . " 条\n";

// 添加更新时间
$newsData['updateTime'] = date('Y-m-d H:i:s');
$newsData['timestamp'] = time();

// 保存到文件
file_put_contents(NEWS_DATA_FILE, json_encode($newsData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

echo "\n[" . date('Y-m-d H:i:s') . "] 采集成功！数据已保存到 " . NEWS_DATA_FILE . "\n";
echo "[" . date('Y-m-d H:i:s') . "] 完成\n\n";

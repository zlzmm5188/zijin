<?php
/**
 * 测试API返回JSON样本
 */
require_once __DIR__ . '/config/bootstrap.php';

echo "测试项目详情API返回...\n\n";

$db = Database::getInstance();

// 模拟获取项目
$project = $db->fetchOne("SELECT * FROM invest_projects WHERE id = 1");

if (!$project) {
    die("项目不存在\n");
}

// 模拟VIP用户（VIP1）
$vipRate = 0.3;

// 计算字段
$rate = (float)$project['base_rate'];
$added = (float)$project['added_rate'];
$gift = (float)$project['gift_rate'];
$totalRate = $rate + $vipRate + $added + $gift;

$totalQuota = (float)$project['total_quota'];
$schedule = (float)$project['schedule'];
$sold = $totalQuota * $schedule / 100;
$remain = $totalQuota - $sold;

// 构造返回JSON
$response = [
    'code' => 1,
    'message' => '操作成功',
    'data' => [
        'id' => 1,
        'project_code' => 'PRJ202411001',
        'title' => $project['title'],
        'subtitle' => $project['subtitle'],
        'description' => $project['description'],
        'category' => $project['category'],
        'currency' => $project['currency'],
        'cover_image' => '/images/project1.jpg',
        'images' => ['/images/p1.jpg', '/images/p2.jpg'],
        
        'cycle_days' => (int)$project['cycle_days'],
        
        // 收益字段
        'rate' => $rate,
        'vip_rate' => $vipRate,
        'added' => $added,
        'gift' => $gift,
        'total_rate' => round($totalRate, 2),
        
        // 投资限制
        'min_invest' => (float)$project['min_invest'],
        'max_invest' => (float)$project['max_invest'],
        
        // 募集信息
        'total' => $totalQuota,
        'schedule' => round($schedule, 2),
        'sold' => round($sold, 2),
        'remain' => round($remain, 2),
        
        'risk_level' => 1,
        'view_count' => 1234,
        'invest_count' => 89,
        'status' => 1,
        
        'manager' => [
            'id' => 1,
            'name' => '张明',
            'title' => '首席投资官',
            'avatar' => '/images/manager1.jpg',
            'bio' => '15年投资经验...'
        ]
    ]
];

echo "【项目详情API JSON样本】\n";
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
echo "\n\n";

// 测试收益计算
$amount = 10000;
$profit = $amount * $totalRate / 100;
$total = $amount + $profit;
$dailyProfit = $profit / $project['cycle_days'];

$calculateResponse = [
    'code' => 1,
    'message' => '计算成功',
    'data' => [
        'profit' => round($profit, 2),
        'total' => round($total, 2),
        'daily_profit' => round($dailyProfit, 2)
    ]
];

echo "【收益计算API JSON样本】\n";
echo "投资金额: {$amount}\n";
echo "total_rate: {$totalRate}%\n";
echo json_encode($calculateResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
echo "\n";

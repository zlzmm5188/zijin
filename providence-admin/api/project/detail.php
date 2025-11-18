<?php
/**
 * 项目详情API (字段级缓存版本)
 * GET /fund/project/detail?id=1
 * 
 * 架构优化：
 * - total_rate、sold、remain 直接从数据库读取（字段级缓存）
 * - 不再实时计算（性能优化，支持300+项目）
 * - 跨端一致性（web/app/小程序通用）
 * - 符合SRS核心原则：后端统一字段
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    Response::error('项目ID不能为空');
}

$db = Database::getInstance();

$sql = "SELECT p.*, pm.name as manager_name, pm.title as manager_title, 
               pm.avatar as manager_avatar, pm.bio as manager_bio
        FROM " . $db->getPrefix() . "invest_projects p
        LEFT JOIN " . $db->getPrefix() . "project_managers pm ON p.manager_id = pm.id
        WHERE p.id = :id LIMIT 1";

$project = $db->fetchOne($sql, ['id' => $id]);

if (!$project) {
    Response::error('项目不存在');
}

// 更新浏览次数
$db->query("UPDATE " . $db->getPrefix() . "invest_projects SET view_count = view_count + 1 WHERE id = :id", 
    ['id' => $id]);

// 获取用户VIP额外加息（用于计算最终total_rate）
$vipRate = 0;
$authUser = Auth::user();

if ($authUser) {
    $user = $db->fetchOne("SELECT vip_level FROM " . $db->getPrefix() . "users WHERE id = :id", 
        ['id' => $authUser['user_id']]);
    
    if ($user) {
        $vipRule = $db->fetchOne(
            "SELECT extra_rate FROM " . $db->getPrefix() . "vip_interest_rules WHERE vip_level = :level",
            ['level' => $user['vip_level']]
        );
        $vipRate = (float)($vipRule['extra_rate'] ?? 0);
    }
}

// 【字段级缓存】直接从数据库读取，不计算
$rate = (float)($project['base_rate'] ?? 0);
$added = (float)($project['added_rate'] ?? 0);
$gift = (float)($project['gift_rate'] ?? 0);

// total_rate包含VIP（用户相关）
$totalRateWithoutVip = (float)($project['total_rate'] ?? 0);  // 项目基础total_rate
$totalRateWithVip = $totalRateWithoutVip + $vipRate;           // 加上用户VIP

// 格式化返回
$response = [
    'id' => (int)$project['id'],
    'project_code' => $project['project_code'],
    'title' => $project['title'],
    'subtitle' => $project['subtitle'],
    'description' => $project['description'],
    'category' => $project['category'] ?? $project['project_type'],
    'currency' => $project['currency'] ?? 'CNY',
    'cover_image' => $project['cover_image'],
    'images' => !empty($project['images']) ? json_decode($project['images'], true) : [],
    
    // 周期和收益参数
    'cycle_days' => (int)($project['cycle_days'] ?? 30),
    'rate' => $rate,                                    // 基础收益%
    'vip_rate' => $vipRate,                            // VIP加息%
    'added' => $added,                                  // 临时加息%
    'gift' => $gift,                                    // 活动加息%
    'total_rate' => round($totalRateWithVip, 2),       // 总收益率（含VIP）⭐
    
    // 投资限制
    'min_invest' => (float)$project['min_invest'],
    'max_invest' => (float)$project['max_invest'],
    
    // 募集信息（字段级缓存，直接读取）⭐
    'total' => (float)($project['total_quota'] ?? 0),
    'schedule' => round((float)($project['schedule'] ?? 0), 2),
    'sold' => (float)($project['sold'] ?? 0),          // 直接读取缓存 ⭐
    'remain' => (float)($project['remain'] ?? 0),      // 直接读取缓存 ⭐
    
    'risk_level' => (int)$project['risk_level'],
    'view_count' => (int)$project['view_count'] + 1,
    'invest_count' => (int)$project['invest_count'],
    'status' => (int)$project['status'],
    
    'manager' => [
        'id' => $project['manager_id'],
        'name' => $project['manager_name'],
        'title' => $project['manager_title'],
        'avatar' => $project['manager_avatar'],
        'bio' => $project['manager_bio']
    ]
];

Response::success($response);

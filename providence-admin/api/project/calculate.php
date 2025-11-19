<?php
/**
 * 收益计算API v2.1（企业级）
 * POST /fund/project/calculate
 * 
 * 公式：
 * profit = amount × total_rate / 100
 * total = amount + profit
 * daily_profit = profit / cycle_days
 * 
 * ⚠️ 周期收益，不是年化
 * ⚠️ 前端不做任何计算
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// Token验证
$authUser = Auth::user();
if (!$authUser) {
    Response::error('未登录或登录已过期', 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$input = json_decode(file_get_contents('php://input'), true);
$projectId = (int)($input['project_id'] ?? 0);
$amount = (float)($input['amount'] ?? 0);

if (!$projectId || $amount <= 0) {
    Response::error('参数错误');
}

$db = Database::getInstance();

// 获取项目信息
$project = $db->fetchOne(
    "SELECT cycle_days, base_rate, min_invest, max_invest FROM " . $db->getPrefix() . "invest_projects 
     WHERE id = :id AND status = 1", 
    ['id' => $projectId]
);

if (!$project) {
    Response::error('项目不存在或已下架');
}

// 验证投资金额范围
if ($amount < $project['min_invest']) {
    Response::error("最低投资金额为 {$project['min_invest']}");
}
if ($project['max_invest'] > 0 && $amount > $project['max_invest']) {
    Response::error("最高投资金额为 {$project['max_invest']}");
}

// 获取用户VIP等级
$user = $db->fetchOne("SELECT vip_level FROM " . $db->getPrefix() . "users WHERE id = :id", 
    ['id' => $authUser['user_id']]);

// 获取VIP额外收益率
$vipRule = $db->fetchOne(
    "SELECT extra_rate FROM " . $db->getPrefix() . "vip_interest_rules WHERE vip_level = :level",
    ['level' => $user['vip_level']]
);
$vipExtraRate = $vipRule['extra_rate'] ?? 0;

// 计算周期总收益率
$baseRate = (float)$project['base_rate'];
$totalRate = $baseRate + $vipExtraRate;
$cycleDays = (int)$project['cycle_days'];

// 【唯一计算入口】使用EarningsService
$earnings = EarningsService::calculateEarnings($amount, $baseRate, $vipExtraRate, $cycleDays);

// 返回结果
Response::success([
    'profit' => $earnings['profit'],              // 周期固定收益
    'total' => $earnings['total'],                // 本金+收益
    'daily_profit' => $earnings['daily_profit']   // 日均收益
], '计算成功');

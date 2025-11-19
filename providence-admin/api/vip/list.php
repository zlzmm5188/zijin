<?php
/**
 * VIP等级列表
 * GET /user/level/list
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$db = Database::getInstance();

$sql = "SELECT * FROM " . $db->getPrefix() . "vip_levels ORDER BY id ASC";
$list = $db->fetchAll($sql);

foreach ($list as &$item) {
    $item['id'] = (int)$item['id'];
    $item['min_invest'] = (float)$item['min_invest'];
    $item['daily_withdraw_limit'] = (float)$item['daily_withdraw_limit'];
    $item['withdraw_fee_rate'] = (float)$item['withdraw_fee_rate'];
    $item['invite_reward_rate'] = (float)$item['invite_reward_rate'];
    $item['invite_points'] = (int)$item['invite_points'];
    $item['first_invest_bonus'] = (int)$item['first_invest_bonus'];
    $item['privileges'] = !empty($item['privileges']) ? json_decode($item['privileges'], true) : [];
}

Response::success($list);

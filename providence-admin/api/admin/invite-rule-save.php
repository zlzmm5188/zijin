<?php
/**
 * 邀请奖励规则保存API
 * POST /api/admin/invite-rule-save
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方式错误', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$id = (int)($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$type = trim($data['type'] ?? '');
$level = (int)($data['level'] ?? 1);
$rewardType = trim($data['reward_type'] ?? 'percent');
$rewardValue = (float)($data['reward_value'] ?? 0);
$minAmount = (float)($data['min_amount'] ?? 0);
$maxReward = (float)($data['max_reward'] ?? 0);
$vipLimit = (int)($data['vip_limit'] ?? 0);
$status = (int)($data['status'] ?? 1);
$startTime = $data['start_time'] ?? null;
$endTime = $data['end_time'] ?? null;

// 验证
if (empty($name)) {
    Response::error('规则名称不能为空');
}

if (empty($type)) {
    Response::error('奖励类型不能为空');
}

if ($rewardValue <= 0) {
    Response::error('奖励值必须大于0');
}

$db = Database::getInstance();

try {
    $saveData = [
        'name' => $name,
        'type' => $type,
        'level' => $level,
        'reward_type' => $rewardType,
        'reward_value' => $rewardValue,
        'min_amount' => $minAmount,
        'max_reward' => $maxReward,
        'vip_limit' => $vipLimit,
        'status' => $status,
        'start_time' => $startTime,
        'end_time' => $endTime
    ];

    if ($id > 0) {
        $db->update('invite_reward_rules', $saveData, 'id = :id', ['id' => $id]);
        $message = '更新成功';
    } else {
        $id = $db->insert('invite_reward_rules', $saveData);
        $message = '添加成功';
    }

    Response::success(['id' => $id], $message);

} catch (Exception $e) {
    error_log("邀请规则保存失败: " . $e->getMessage());
    Response::error('保存失败');
}

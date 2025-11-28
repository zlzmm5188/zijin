<?php
/**
 * 任务保存API
 * POST /api/admin/task-save
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方式错误', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$id = (int)($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$code = trim($data['code'] ?? '');
$type = trim($data['type'] ?? 'daily');
$description = trim($data['description'] ?? '');
$icon = trim($data['icon'] ?? '');
$rewardType = trim($data['reward_type'] ?? 'points');
$rewardAmount = (float)($data['reward_amount'] ?? 0);
$targetValue = (int)($data['target_value'] ?? 1);
$targetUnit = trim($data['target_unit'] ?? '');
$vipLimit = (int)($data['vip_limit'] ?? 0);
$startTime = $data['start_time'] ?? null;
$endTime = $data['end_time'] ?? null;
$status = (int)($data['status'] ?? 1);
$sortOrder = (int)($data['sort_order'] ?? 0);

// 验证
if (empty($name)) {
    Response::error('任务名称不能为空');
}

if (empty($code)) {
    Response::error('任务代码不能为空');
}

if ($rewardAmount <= 0) {
    Response::error('奖励数量必须大于0');
}

$db = Database::getInstance();

// 检查代码是否已存在
$existSql = "SELECT id FROM " . $db->getPrefix() . "tasks WHERE code = :code AND id != :id";
$exist = $db->fetchOne($existSql, ['code' => $code, 'id' => $id]);
if ($exist) {
    Response::error('任务代码已存在');
}

try {
    $saveData = [
        'name' => $name,
        'code' => $code,
        'type' => $type,
        'description' => $description,
        'icon' => $icon,
        'reward_type' => $rewardType,
        'reward_amount' => $rewardAmount,
        'target_value' => $targetValue,
        'target_unit' => $targetUnit,
        'vip_limit' => $vipLimit,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'status' => $status,
        'sort_order' => $sortOrder
    ];

    if ($id > 0) {
        $db->update('tasks', $saveData, 'id = :id', ['id' => $id]);
        $message = '更新成功';
    } else {
        $id = $db->insert('tasks', $saveData);
        $message = '添加成功';
    }

    Response::success(['id' => $id], $message);

} catch (Exception $e) {
    error_log("任务保存失败: " . $e->getMessage());
    Response::error('保存失败');
}

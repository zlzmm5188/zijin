<?php
/**
 * 短信通道保存API
 * POST /api/admin/sms-channel-save
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方式错误', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$id = (int)($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$code = trim($data['code'] ?? '');
$provider = trim($data['provider'] ?? '');
$config = $data['config'] ?? [];
$templates = $data['templates'] ?? [];
$priority = (int)($data['priority'] ?? 0);
$status = (int)($data['status'] ?? 1);
$dailyLimit = (int)($data['daily_limit'] ?? 0);

// 验证
if (empty($name)) {
    Response::error('通道名称不能为空');
}

if (empty($code)) {
    Response::error('通道代码不能为空');
}

if (empty($provider)) {
    Response::error('服务商不能为空');
}

$db = Database::getInstance();

// 检查代码是否已存在
$existSql = "SELECT id FROM " . $db->getPrefix() . "sms_channels WHERE code = :code AND id != :id";
$exist = $db->fetchOne($existSql, ['code' => $code, 'id' => $id]);
if ($exist) {
    Response::error('通道代码已存在');
}

try {
    $saveData = [
        'name' => $name,
        'code' => $code,
        'provider' => $provider,
        'config' => json_encode($config, JSON_UNESCAPED_UNICODE),
        'templates' => json_encode($templates, JSON_UNESCAPED_UNICODE),
        'priority' => $priority,
        'status' => $status,
        'daily_limit' => $dailyLimit
    ];

    if ($id > 0) {
        $db->update('sms_channels', $saveData, 'id = :id', ['id' => $id]);
        $message = '更新成功';
    } else {
        $id = $db->insert('sms_channels', $saveData);
        $message = '添加成功';
    }

    Response::success(['id' => $id], $message);

} catch (Exception $e) {
    error_log("短信通道保存失败: " . $e->getMessage());
    Response::error('保存失败');
}

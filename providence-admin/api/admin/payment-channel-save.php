<?php
/**
 * 支付通道保存API
 * POST /api/admin/payment-channel-save
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方式错误', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$id = (int)($data['id'] ?? 0);
$name = trim($data['name'] ?? '');
$code = trim($data['code'] ?? '');
$type = trim($data['type'] ?? '');
$mode = trim($data['mode'] ?? 'qrcode');
$config = $data['config'] ?? [];
$qrcodeImage = trim($data['qrcode_image'] ?? '');
$accountInfo = $data['account_info'] ?? [];
$minAmount = (float)($data['min_amount'] ?? 0);
$maxAmount = (float)($data['max_amount'] ?? 0);
$feeRate = (float)($data['fee_rate'] ?? 0);
$feeFixed = (float)($data['fee_fixed'] ?? 0);
$priority = (int)($data['priority'] ?? 0);
$status = (int)($data['status'] ?? 1);
$dailyLimit = (float)($data['daily_limit'] ?? 0);
$remark = trim($data['remark'] ?? '');

// 验证
if (empty($name)) {
    Response::error('通道名称不能为空');
}

if (empty($code)) {
    Response::error('通道代码不能为空');
}

if (empty($type)) {
    Response::error('支付类型不能为空');
}

$db = Database::getInstance();

// 检查代码是否已存在
$existSql = "SELECT id FROM " . $db->getPrefix() . "payment_channels WHERE code = :code AND id != :id";
$exist = $db->fetchOne($existSql, ['code' => $code, 'id' => $id]);
if ($exist) {
    Response::error('通道代码已存在');
}

try {
    $saveData = [
        'name' => $name,
        'code' => $code,
        'type' => $type,
        'mode' => $mode,
        'config' => json_encode($config, JSON_UNESCAPED_UNICODE),
        'qrcode_image' => $qrcodeImage,
        'account_info' => json_encode($accountInfo, JSON_UNESCAPED_UNICODE),
        'min_amount' => $minAmount,
        'max_amount' => $maxAmount,
        'fee_rate' => $feeRate,
        'fee_fixed' => $feeFixed,
        'priority' => $priority,
        'status' => $status,
        'daily_limit' => $dailyLimit,
        'remark' => $remark
    ];

    if ($id > 0) {
        $db->update('payment_channels', $saveData, 'id = :id', ['id' => $id]);
        $message = '更新成功';
    } else {
        $id = $db->insert('payment_channels', $saveData);
        $message = '添加成功';
    }

    Response::success(['id' => $id], $message);

} catch (Exception $e) {
    error_log("支付通道保存失败: " . $e->getMessage());
    Response::error('保存失败');
}

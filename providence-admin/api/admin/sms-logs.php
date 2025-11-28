<?php
/**
 * 短信发送记录API
 * GET /api/admin/sms-logs
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(10, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;
$phone = trim($_GET['phone'] ?? '');
$status = $_GET['status'] ?? '';
$channelId = $_GET['channel_id'] ?? '';

$where = '1=1';
$params = [];

if (!empty($phone)) {
    $where .= ' AND phone LIKE :phone';
    $params['phone'] = "%{$phone}%";
}

if ($status !== '') {
    $where .= ' AND status = :status';
    $params['status'] = (int)$status;
}

if (!empty($channelId)) {
    $where .= ' AND channel_id = :channel_id';
    $params['channel_id'] = (int)$channelId;
}

try {
    $total = $db->count('sms_logs', $where, $params);

    $sql = "SELECT * FROM " . $db->getPrefix() . "sms_logs 
            WHERE {$where} 
            ORDER BY id DESC 
            LIMIT {$offset}, {$limit}";

    $list = $db->fetchAll($sql, $params);

    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['channel_id'] = (int)$item['channel_id'];
        $item['status'] = (int)$item['status'];
        $statusTexts = ['待发送', '成功', '失败'];
        $item['status_text'] = $statusTexts[$item['status']] ?? '未知';
        $item['params'] = json_decode($item['params'] ?? '{}', true);
    }

    Response::success([
        'code' => 0,
        'msg' => '',
        'count' => $total,
        'data' => $list
    ]);

} catch (Exception $e) {
    error_log("短信记录API错误: " . $e->getMessage());
    Response::error('数据加载失败');
}

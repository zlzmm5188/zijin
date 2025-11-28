<?php
/**
 * 短信通道列表API
 * GET /api/admin/sms-channels
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$db = Database::getInstance();

try {
    $sql = "SELECT * FROM " . $db->getPrefix() . "sms_channels ORDER BY priority DESC, id ASC";
    $list = $db->fetchAll($sql);

    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['priority'] = (int)$item['priority'];
        $item['status'] = (int)$item['status'];
        $item['daily_limit'] = (int)$item['daily_limit'];
        $item['used_today'] = (int)$item['used_today'];
        $item['success_count'] = (int)$item['success_count'];
        $item['fail_count'] = (int)$item['fail_count'];
        $item['config'] = json_decode($item['config'] ?? '{}', true);
        $item['templates'] = json_decode($item['templates'] ?? '[]', true);
        $item['status_text'] = $item['status'] == 1 ? '启用' : '禁用';
        
        // 计算成功率
        $total = $item['success_count'] + $item['fail_count'];
        $item['success_rate'] = $total > 0 ? round($item['success_count'] / $total * 100, 2) : 0;
    }

    Response::success([
        'code' => 0,
        'msg' => '',
        'count' => count($list),
        'data' => $list
    ]);

} catch (Exception $e) {
    error_log("短信通道列表API错误: " . $e->getMessage());
    Response::error('数据加载失败');
}

<?php
/**
 * 任务列表API
 * GET /api/admin/tasks
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$db = Database::getInstance();

$type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';

$where = '1=1';
$params = [];

if (!empty($type)) {
    $where .= ' AND type = :type';
    $params['type'] = $type;
}

if ($status !== '') {
    $where .= ' AND status = :status';
    $params['status'] = (int)$status;
}

try {
    $sql = "SELECT * FROM " . $db->getPrefix() . "tasks 
            WHERE {$where} 
            ORDER BY sort_order ASC, id DESC";
    $list = $db->fetchAll($sql, $params);

    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['status'] = (int)$item['status'];
        $item['sort_order'] = (int)$item['sort_order'];
        $item['target_value'] = (int)$item['target_value'];
        $item['vip_limit'] = (int)$item['vip_limit'];
        $item['reward_amount'] = (float)$item['reward_amount'];
        $item['complete_count'] = (int)$item['complete_count'];
        $item['status_text'] = $item['status'] == 1 ? '启用' : '禁用';
        
        // 类型文本
        $typeTexts = [
            'daily' => '每日任务',
            'once' => '一次性任务',
            'invite' => '邀请任务',
            'invest' => '投资任务',
            'checkin' => '签到任务'
        ];
        $item['type_text'] = $typeTexts[$item['type']] ?? $item['type'];
        
        // 奖励类型文本
        $rewardTexts = [
            'points' => '积分',
            'balance' => '余额',
            'coupon' => '优惠券'
        ];
        $item['reward_type_text'] = $rewardTexts[$item['reward_type']] ?? $item['reward_type'];
    }

    Response::success([
        'code' => 0,
        'msg' => '',
        'count' => count($list),
        'data' => $list
    ]);

} catch (Exception $e) {
    error_log("任务列表API错误: " . $e->getMessage());
    Response::error('数据加载失败');
}

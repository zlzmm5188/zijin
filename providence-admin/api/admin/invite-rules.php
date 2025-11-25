<?php
/**
 * 邀请奖励规则列表API
 * GET /api/admin/invite-rules
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
    $sql = "SELECT * FROM " . $db->getPrefix() . "invite_reward_rules 
            WHERE {$where} 
            ORDER BY type, level ASC";
    $list = $db->fetchAll($sql, $params);

    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['level'] = (int)$item['level'];
        $item['status'] = (int)$item['status'];
        $item['vip_limit'] = (int)$item['vip_limit'];
        $item['reward_value'] = (float)$item['reward_value'];
        $item['min_amount'] = (float)$item['min_amount'];
        $item['max_reward'] = (float)$item['max_reward'];
        $item['status_text'] = $item['status'] == 1 ? '启用' : '禁用';
        
        // 类型文本
        $typeTexts = [
            'register' => '注册奖励',
            'invest' => '投资返佣',
            'recharge' => '充值返佣'
        ];
        $item['type_text'] = $typeTexts[$item['type']] ?? $item['type'];
        
        // 层级文本
        $item['level_text'] = $item['level'] == 1 ? '一级' : '二级';
        
        // 奖励类型文本
        $item['reward_type_text'] = $item['reward_type'] == 'fixed' ? '固定金额' : '百分比';
        
        // 奖励显示
        if ($item['reward_type'] == 'fixed') {
            $item['reward_display'] = '¥' . $item['reward_value'];
        } else {
            $item['reward_display'] = ($item['reward_value'] * 100) . '%';
        }
    }

    Response::success([
        'code' => 0,
        'msg' => '',
        'count' => count($list),
        'data' => $list
    ]);

} catch (Exception $e) {
    error_log("邀请规则列表API错误: " . $e->getMessage());
    Response::error('数据加载失败');
}

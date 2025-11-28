<?php
/**
 * 系统配置列表API
 * GET /api/admin/configs
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$db = Database::getInstance();

$group = $_GET['group'] ?? '';

$where = '1=1';
$params = [];

if (!empty($group)) {
    $where .= ' AND `group` = :group';
    $params['group'] = $group;
}

try {
    $sql = "SELECT * FROM " . $db->getPrefix() . "system_configs 
            WHERE {$where} 
            ORDER BY `group`, sort_order, id ASC";
    $list = $db->fetchAll($sql, $params);

    // 按分组组织
    $grouped = [];
    foreach ($list as $item) {
        $item['id'] = (int)$item['id'];
        $item['is_public'] = (int)$item['is_public'];
        $item['sort_order'] = (int)$item['sort_order'];
        
        // 类型转换
        switch ($item['type']) {
            case 'number':
                $item['value'] = (float)$item['value'];
                break;
            case 'boolean':
                $item['value'] = $item['value'] === 'true' || $item['value'] === '1';
                break;
            case 'json':
                $item['value'] = json_decode($item['value'], true);
                break;
        }
        
        $grouped[$item['group']][] = $item;
    }

    // 分组名称映射
    $groupNames = [
        'site' => '网站设置',
        'finance' => '财务设置',
        'sms' => '短信设置',
        'security' => '安全设置',
        'payment' => '支付设置',
        'invite' => '邀请设置'
    ];

    $result = [];
    foreach ($grouped as $group => $items) {
        $result[] = [
            'group' => $group,
            'group_name' => $groupNames[$group] ?? $group,
            'items' => $items
        ];
    }

    Response::success([
        'code' => 0,
        'msg' => '',
        'count' => count($list),
        'data' => $result,
        'list' => $list
    ]);

} catch (Exception $e) {
    error_log("系统配置API错误: " . $e->getMessage());
    Response::error('数据加载失败');
}

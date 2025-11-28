<?php
/**
 * 邀请奖励记录列表API
 * GET /api/admin/invite-logs
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(10, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;
$userId = $_GET['user_id'] ?? '';
$type = $_GET['type'] ?? '';

$where = '1=1';
$params = [];

if (!empty($userId)) {
    $where .= ' AND l.user_id = :user_id';
    $params['user_id'] = (int)$userId;
}

if (!empty($type)) {
    $where .= ' AND l.type = :type';
    $params['type'] = $type;
}

try {
    $countSql = "SELECT COUNT(*) as count FROM " . $db->getPrefix() . "invite_reward_logs l WHERE {$where}";
    $countResult = $db->fetchOne($countSql, $params);
    $total = $countResult ? (int)$countResult['count'] : 0;

    $sql = "SELECT l.*, 
                   u1.username as user_name, 
                   u2.username as from_user_name
            FROM " . $db->getPrefix() . "invite_reward_logs l
            LEFT JOIN " . $db->getPrefix() . "users u1 ON l.user_id = u1.id
            LEFT JOIN " . $db->getPrefix() . "users u2 ON l.from_user_id = u2.id
            WHERE {$where} 
            ORDER BY l.id DESC 
            LIMIT {$offset}, {$limit}";

    $list = $db->fetchAll($sql, $params);

    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['user_id'] = (int)$item['user_id'];
        $item['from_user_id'] = (int)$item['from_user_id'];
        $item['rule_id'] = (int)$item['rule_id'];
        $item['level'] = (int)$item['level'];
        $item['status'] = (int)$item['status'];
        $item['ref_amount'] = (float)$item['ref_amount'];
        $item['reward_amount'] = (float)$item['reward_amount'];
        $item['status_text'] = $item['status'] == 1 ? '已发放' : '待发放';
        
        $typeTexts = [
            'register' => '注册奖励',
            'invest' => '投资返佣',
            'recharge' => '充值返佣'
        ];
        $item['type_text'] = $typeTexts[$item['type']] ?? $item['type'];
        $item['level_text'] = $item['level'] == 1 ? '一级' : '二级';
    }

    Response::success([
        'code' => 0,
        'msg' => '',
        'count' => $total,
        'data' => $list
    ]);

} catch (Exception $e) {
    error_log("邀请记录API错误: " . $e->getMessage());
    Response::error('数据加载失败');
}

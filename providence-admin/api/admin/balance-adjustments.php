<?php
/**
 * 资产调账记录列表API
 * GET /api/admin/balance-adjustments
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$db = Database::getInstance();

$page = max(1, (int)($_GET['page'] ?? 1));
$limit = min(100, max(10, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;
$userId = $_GET['user_id'] ?? '';
$type = $_GET['type'] ?? '';
$target = $_GET['target'] ?? '';

$where = '1=1';
$params = [];

if (!empty($userId)) {
    $where .= ' AND a.user_id = :user_id';
    $params['user_id'] = (int)$userId;
}

if (!empty($type)) {
    $where .= ' AND a.type = :type';
    $params['type'] = $type;
}

if (!empty($target)) {
    $where .= ' AND a.target = :target';
    $params['target'] = $target;
}

try {
    $countSql = "SELECT COUNT(*) as count FROM " . $db->getPrefix() . "balance_adjustments a WHERE {$where}";
    $countResult = $db->fetchOne($countSql, $params);
    $total = $countResult ? (int)$countResult['count'] : 0;

    $sql = "SELECT a.*, u.username as user_name
            FROM " . $db->getPrefix() . "balance_adjustments a
            LEFT JOIN " . $db->getPrefix() . "users u ON a.user_id = u.id
            WHERE {$where} 
            ORDER BY a.id DESC 
            LIMIT {$offset}, {$limit}";

    $list = $db->fetchAll($sql, $params);

    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['user_id'] = (int)$item['user_id'];
        $item['admin_id'] = (int)$item['admin_id'];
        $item['amount'] = (float)$item['amount'];
        $item['before_amount'] = (float)$item['before_amount'];
        $item['after_amount'] = (float)$item['after_amount'];
        
        $item['type_text'] = $item['type'] == 'ADD' ? '增加' : '扣减';
        $targetTexts = [
            'balance' => '可用余额',
            'frozen' => '冻结金额',
            'points' => '积分'
        ];
        $item['target_text'] = $targetTexts[$item['target']] ?? $item['target'];
    }

    Response::success([
        'code' => 0,
        'msg' => '',
        'count' => $total,
        'data' => $list
    ]);

} catch (Exception $e) {
    error_log("调账记录API错误: " . $e->getMessage());
    Response::error('数据加载失败');
}

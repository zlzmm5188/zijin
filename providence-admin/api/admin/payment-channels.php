<?php
/**
 * 支付通道列表API
 * GET /api/admin/payment-channels
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
    $sql = "SELECT * FROM " . $db->getPrefix() . "payment_channels 
            WHERE {$where} 
            ORDER BY priority DESC, id ASC";
    $list = $db->fetchAll($sql, $params);

    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['priority'] = (int)$item['priority'];
        $item['status'] = (int)$item['status'];
        $item['min_amount'] = (float)$item['min_amount'];
        $item['max_amount'] = (float)$item['max_amount'];
        $item['fee_rate'] = (float)$item['fee_rate'];
        $item['fee_fixed'] = (float)$item['fee_fixed'];
        $item['daily_limit'] = (float)$item['daily_limit'];
        $item['used_today'] = (float)$item['used_today'];
        $item['total_amount'] = (float)$item['total_amount'];
        $item['order_count'] = (int)$item['order_count'];
        $item['config'] = json_decode($item['config'] ?? '{}', true);
        $item['account_info'] = json_decode($item['account_info'] ?? '{}', true);
        $item['status_text'] = $item['status'] == 1 ? '启用' : '禁用';
        
        // 类型文本
        $typeTexts = [
            'bank' => '银行卡',
            'alipay' => '支付宝',
            'wechat' => '微信',
            'usdt' => 'USDT'
        ];
        $item['type_text'] = $typeTexts[$item['type']] ?? $item['type'];
    }

    Response::success([
        'code' => 0,
        'msg' => '',
        'count' => count($list),
        'data' => $list
    ]);

} catch (Exception $e) {
    error_log("支付通道列表API错误: " . $e->getMessage());
    Response::error('数据加载失败');
}

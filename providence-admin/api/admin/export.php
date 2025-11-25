<?php
/**
 * 数据导出API
 * POST /api/admin/export
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方式错误', 405);
}

$data = json_decode(file_get_contents('php://input'), true);

$type = trim($data['type'] ?? '');
$params = $data['params'] ?? [];
$format = trim($data['format'] ?? 'csv');

$supportedTypes = [
    'users' => '用户列表',
    'orders' => '投资订单',
    'recharges' => '充值记录',
    'withdrawals' => '提现记录',
    'wallet_logs' => '资金流水',
    'daily_reports' => '每日报表',
    'invite_logs' => '邀请记录'
];

if (!isset($supportedTypes[$type])) {
    Response::error('不支持的导出类型');
}

$db = Database::getInstance();
$adminId = 1; // 简化处理

try {
    // 创建导出任务
    $taskId = $db->insert('export_tasks', [
        'name' => $supportedTypes[$type] . '_' . date('YmdHis'),
        'type' => $type,
        'params' => json_encode($params),
        'status' => 0,
        'admin_id' => $adminId,
        'started_at' => date('Y-m-d H:i:s')
    ]);

    // 执行导出 (简化版，实际可使用队列处理)
    $exportData = [];
    $headers = [];

    switch ($type) {
        case 'users':
            $headers = ['ID', '用户名', '手机号', '邮箱', 'VIP等级', '累计投资', '状态', '注册时间'];
            $sql = "SELECT id, username, phone, email, vip_level, total_invest, status, created_at 
                    FROM " . $db->getPrefix() . "users ORDER BY id DESC LIMIT 10000";
            $rows = $db->fetchAll($sql);
            foreach ($rows as $row) {
                $exportData[] = [
                    $row['id'],
                    $row['username'],
                    $row['phone'],
                    $row['email'],
                    'VIP' . $row['vip_level'],
                    $row['total_invest'],
                    $row['status'] == 1 ? '正常' : '禁用',
                    $row['created_at']
                ];
            }
            break;

        case 'orders':
            $headers = ['订单号', '用户ID', '项目ID', '金额', '收益率', '周期', '状态', '创建时间'];
            $sql = "SELECT order_no, user_id, project_id, amount, final_rate, cycle_days, status, created_at 
                    FROM " . $db->getPrefix() . "invest_orders ORDER BY id DESC LIMIT 10000";
            $rows = $db->fetchAll($sql);
            foreach ($rows as $row) {
                $statusTexts = ['PENDING' => '待处理', 'RUNNING' => '进行中', 'FINISHED' => '已完成'];
                $exportData[] = [
                    $row['order_no'],
                    $row['user_id'],
                    $row['project_id'],
                    $row['amount'],
                    $row['final_rate'] . '%',
                    $row['cycle_days'] . '天',
                    $statusTexts[$row['status']] ?? $row['status'],
                    $row['created_at']
                ];
            }
            break;

        case 'daily_reports':
            $headers = ['日期', '新增用户', '活跃用户', '充值笔数', '充值金额', '提现笔数', '提现金额', '投资金额', '净收入'];
            $startDate = $params['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
            $endDate = $params['end_date'] ?? date('Y-m-d');
            $sql = "SELECT * FROM " . $db->getPrefix() . "daily_reports 
                    WHERE date BETWEEN :start_date AND :end_date ORDER BY date DESC";
            $rows = $db->fetchAll($sql, ['start_date' => $startDate, 'end_date' => $endDate]);
            foreach ($rows as $row) {
                $exportData[] = [
                    $row['date'],
                    $row['new_users'],
                    $row['active_users'],
                    $row['recharge_count'],
                    $row['recharge_amount'],
                    $row['withdraw_count'],
                    $row['withdraw_amount'],
                    $row['invest_amount'],
                    $row['net_income']
                ];
            }
            break;

        default:
            // 其他类型的默认处理
            break;
    }

    // 生成CSV文件
    $filename = $type . '_' . date('YmdHis') . '.csv';
    $filepath = '/tmp/' . $filename;
    
    $fp = fopen($filepath, 'w');
    // 添加BOM头以支持中文
    fwrite($fp, "\xEF\xBB\xBF");
    
    fputcsv($fp, $headers);
    foreach ($exportData as $row) {
        fputcsv($fp, $row);
    }
    fclose($fp);

    $fileSize = filesize($filepath);
    $rowCount = count($exportData);

    // 更新任务状态
    $db->update('export_tasks', [
        'file_path' => $filepath,
        'file_size' => $fileSize,
        'row_count' => $rowCount,
        'status' => 1,
        'completed_at' => date('Y-m-d H:i:s'),
        'expires_at' => date('Y-m-d H:i:s', strtotime('+7 days'))
    ], 'id = :id', ['id' => $taskId]);

    // 读取文件内容返回
    $content = file_get_contents($filepath);

    Response::success([
        'task_id' => $taskId,
        'filename' => $filename,
        'file_size' => $fileSize,
        'row_count' => $rowCount,
        'content' => base64_encode($content),
        'content_type' => 'text/csv'
    ], '导出成功');

} catch (Exception $e) {
    error_log("数据导出失败: " . $e->getMessage());
    
    if (isset($taskId)) {
        $db->update('export_tasks', [
            'status' => 2,
            'error_msg' => $e->getMessage(),
            'completed_at' => date('Y-m-d H:i:s')
        ], 'id = :id', ['id' => $taskId]);
    }
    
    Response::error('导出失败: ' . $e->getMessage());
}

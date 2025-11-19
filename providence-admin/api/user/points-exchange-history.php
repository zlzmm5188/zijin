<?php
/**
 * 积分兑换历史记录
 * GET /user/points/exchange-history
 */

require_once __DIR__ . '/../../config/bootstrap.php';

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

try {
    $db = Database::getInstance();
    $userId = $authUser['user_id'];

    // 获取分页参数
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : 20;
    $offset = ($page - 1) * $limit;

    // 检查表是否存在，如果不存在则使用points_exchange_logs
    $tableName = 'points_exchange_logs';
    $checkTable = $db->fetchOne("SHOW TABLES LIKE '{$tableName}'");

    if (!$checkTable) {
        // 尝试使用points_exchange_log
        $tableName = 'points_exchange_log';
        $checkTable = $db->fetchOne("SHOW TABLES LIKE '{$tableName}'");
    }

    if (!$checkTable) {
        Response::error('积分兑换记录表不存在，请联系管理员');
    }

    // 构建查询条件
    $whereConditions = ["user_id = :user_id"];
    $params = ['user_id' => $userId];

    // 日期筛选
    if (!empty($_GET['start_date'])) {
        $whereConditions[] = "DATE(created_at) >= :start_date";
        $params['start_date'] = trim($_GET['start_date']);
    }

    if (!empty($_GET['end_date'])) {
        $whereConditions[] = "DATE(created_at) <= :end_date";
        $params['end_date'] = trim($_GET['end_date']);
    }

    $whereClause = implode(' AND ', $whereConditions);

    // 查询总数
    $totalSql = "SELECT COUNT(*) as total FROM {$tableName} WHERE {$whereClause}";
    $totalResult = $db->fetchOne($totalSql, $params);
    $total = intval($totalResult['total'] ?? 0);

    // 查询记录列表
    $sql = "
        SELECT
            id,
            points_used as points,
            amount_received as amount,
            exchange_rate,
            status,
            created_at
        FROM {$tableName}
        WHERE {$whereClause}
        ORDER BY created_at DESC
        LIMIT {$limit} OFFSET {$offset}
    ";

    $records = $db->fetchAll($sql, $params);

    // 格式化记录
    $formattedRecords = array_map(function($record) {
        return [
            'id' => intval($record['id']),
            'points' => floatval($record['points'] ?? $record['points_used'] ?? 0),
            'amount' => floatval($record['amount'] ?? $record['amount_received'] ?? 0),
            'exchange_rate' => floatval($record['exchange_rate'] ?? 0.1),
            'status' => intval($record['status'] ?? 1),
            'status_text' => intval($record['status'] ?? 1) === 1 ? '成功' : '失败',
            'created_at' => $record['created_at'],
            'date' => date('Y-m-d', strtotime($record['created_at'])),
            'time' => date('H:i:s', strtotime($record['created_at']))
        ];
    }, $records);

    Response::success([
        'records' => $formattedRecords,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ]
    ], '获取成功');

} catch (Exception $e) {
    error_log('获取积分兑换历史失败: ' . $e->getMessage());
    Response::error('系统错误，请稍后重试');
}

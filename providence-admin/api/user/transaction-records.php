<?php
/**
 * 获取用户交易记录
 * GET /user/transaction/records
 * 
 * 参数:
 * - page: 页码（默认1）
 * - pageSize: 每页数量（默认20）
 * - type: 类型筛选（可选：recharge/withdraw/invest/profit/transfer等）
 */

header('Content-Type: application/json; charset=utf-8');

try {
    // 引入数据库配置
    $config = require_once __DIR__ . '/../../config/database.php';
    
    // 创建PDO连接
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
    
    // 验证登录
    $headers = getallheaders();
    $token = $headers['token'] ?? $headers['Token'] ?? '';
    
    if (empty($token)) {
        http_response_code(401);
        echo json_encode([
            'code' => 401,
            'message' => '请先登录',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 从token获取用户信息
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'code' => 401,
            'message' => '未登录或登录已过期',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // 获取参数
    $page = max(1, intval($_GET['page'] ?? 1));
    $pageSize = max(1, min(100, intval($_GET['pageSize'] ?? 20)));
    $type = $_GET['type'] ?? '';
    $offset = ($page - 1) * $pageSize;
    
    // 检查是否存在wallet_logs表
    $tableCheckStmt = $pdo->query("SHOW TABLES LIKE 'wallet_logs'");
    $tableExists = $tableCheckStmt->rowCount() > 0;
    
    if (!$tableExists) {
        // 如果wallet_logs不存在，尝试从其他表聚合数据
        
        // 构建联合查询（充值、提现、投资订单等）
        $records = [];
        
        // 1. 查询充值记录
        try {
            $rechargeStmt = $pdo->prepare("
                SELECT 
                    id,
                    'recharge' as type,
                    amount,
                    status,
                    created_at,
                    '充值' as type_name
                FROM recharge_records
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT 50
            ");
            $rechargeStmt->execute([':user_id' => $user['id']]);
            $records = array_merge($records, $rechargeStmt->fetchAll());
        } catch (Exception $e) {
            // 表不存在，跳过
        }
        
        // 2. 查询提现记录
        try {
            $withdrawStmt = $pdo->prepare("
                SELECT 
                    id,
                    'withdraw' as type,
                    amount,
                    status,
                    created_at,
                    '提现' as type_name
                FROM withdraw_records
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT 50
            ");
            $withdrawStmt->execute([':user_id' => $user['id']]);
            $records = array_merge($records, $withdrawStmt->fetchAll());
        } catch (Exception $e) {
            // 表不存在，跳过
        }
        
        // 3. 查询投资订单
        try {
            $investStmt = $pdo->prepare("
                SELECT 
                    id,
                    'invest' as type,
                    amount,
                    status,
                    created_at,
                    '投资' as type_name
                FROM invest_orders
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT 50
            ");
            $investStmt->execute([':user_id' => $user['id']]);
            $records = array_merge($records, $investStmt->fetchAll());
        } catch (Exception $e) {
            // 表不存在，跳过
        }
        
        // 4. 查询日利宝记录
        try {
            $ribaoStmt = $pdo->prepare("
                SELECT 
                    id,
                    CONCAT('ribao_', type) as type,
                    amount,
                    status,
                    created_at,
                    CASE WHEN type = 'in' THEN '转入日利宝' ELSE '转出日利宝' END as type_name
                FROM ribao_logs
                WHERE user_id = :user_id
                ORDER BY created_at DESC
                LIMIT 50
            ");
            $ribaoStmt->execute([':user_id' => $user['id']]);
            $records = array_merge($records, $ribaoStmt->fetchAll());
        } catch (Exception $e) {
            // 表不存在，跳过
        }
        
        // 按时间排序
        usort($records, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // 分页
        $total = count($records);
        $records = array_slice($records, $offset, $pageSize);
        
    } else {
        // 如果wallet_logs存在，直接查询
        $where = 'user_id = :user_id';
        $params = [':user_id' => $user['id']];
        
        if (!empty($type)) {
            $where .= ' AND type = :type';
            $params[':type'] = $type;
        }
        
        // 查询总数
        $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM wallet_logs WHERE {$where}");
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        // 查询记录
        $stmt = $pdo->prepare("
            SELECT 
                id,
                type,
                amount,
                before_balance,
                after_balance,
                status,
                remark,
                created_at
            FROM wallet_logs
            WHERE {$where}
            ORDER BY created_at DESC, id DESC
            LIMIT :offset, :pageSize
        ");
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':pageSize', $pageSize, PDO::PARAM_INT);
        $stmt->execute();
        
        $records = $stmt->fetchAll();
    }
    
    // 格式化数据
    foreach ($records as &$record) {
        $record['amount'] = floatval($record['amount']);
        if (isset($record['before_balance'])) {
            $record['before_balance'] = floatval($record['before_balance']);
        }
        if (isset($record['after_balance'])) {
            $record['after_balance'] = floatval($record['after_balance']);
        }
        $record['status'] = intval($record['status'] ?? 1);
    }
    
    echo json_encode([
        'code' => 1,
        'message' => '操作成功',
        'data' => $records
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'code' => -1,
        'message' => '数据库错误: ' . $e->getMessage(),
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'code' => -1,
        'message' => '服务器错误: ' . $e->getMessage(),
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}


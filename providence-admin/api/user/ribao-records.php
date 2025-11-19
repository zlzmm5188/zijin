<?php
// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, token, Token');

// 处理OPTIONS预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


/**
 * 获取日利宝转入转出记录
 * GET /user/ribao/records
 * 
 * 参数:
 * - page: 页码（默认1）
 * - pageSize: 每页数量（默认20）
 * - type: 类型筛选（可选：in/out）
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
    
    // 验证登录（简单token验证）
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
    
    // 使用Auth类验证token
    require_once __DIR__ . '/../../config/bootstrap.php';
    $authUser = Auth::user();
    
    if (!$authUser) {
        http_response_code(401);
        echo json_encode([
            'code' => 401,
            'message' => '未登录或登录已过期',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $userId = $authUser['user_id'];
    
    // 获取参数
    $page = max(1, intval($_GET['page'] ?? 1));
    $pageSize = max(1, min(100, intval($_GET['pageSize'] ?? 20)));
    $type = $_GET['type'] ?? '';
    $offset = ($page - 1) * $pageSize;
    
    // 构建查询
    $where = 'user_id = :user_id';
    $params = [':user_id' => $userId];
    
    if (in_array($type, ['in', 'out'])) {
        $where .= ' AND type = :type';
        $params[':type'] = $type;
    }
    
    // 查询总数
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM ribao_logs WHERE {$where}");
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
        FROM ribao_logs
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
    
    // 格式化数据
    foreach ($records as &$record) {
        $record['amount'] = floatval($record['amount']);
        $record['before_balance'] = floatval($record['before_balance']);
        $record['after_balance'] = floatval($record['after_balance']);
        $record['status'] = intval($record['status']);
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


<?php
/**
 * 获取用户积分余额
 * GET /user/points/balance
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
        echo json_encode([
            'code' => -1,
            'message' => '请先登录',
            'data' => null
        ]);
        exit;
    }
    
    // 查询用户信息
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE token = :token LIMIT 1");
    $stmt->execute([':token' => $token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        echo json_encode([
            'code' => -1,
            'message' => 'Token无效或已过期',
            'data' => null
        ]);
        exit;
    }
    
    // 查询用户积分
    $stmt = $pdo->prepare("
        SELECT points 
        FROM users 
        WHERE id = :user_id
    ");
    $stmt->execute([':user_id' => $user['id']]);
    $userData = $stmt->fetch();
    
    if (!$userData) {
        echo json_encode([
            'code' => -1,
            'message' => '用户不存在',
            'data' => null
        ]);
        exit;
    }
    
    // 返回积分余额
    echo json_encode([
        'code' => 1,
        'message' => '操作成功',
        'data' => [
            'points' => floatval($userData['points'] ?? 0),
            'formatted' => number_format($userData['points'] ?? 0, 2, '.', ',')
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'code' => -1,
        'message' => '查询失败: ' . $e->getMessage(),
        'data' => null
    ]);
}
?>

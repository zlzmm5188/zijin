<?php
/**
 * 积分兑换
 * POST /user/points/exchange
 * 
 * 参数:
 * - points: 兑换积分数量
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
    
    // 获取请求数据
    $input = json_decode(file_get_contents('php://input'), true);
    $points = intval($input['points'] ?? 0);
    
    // 配置参数
    $EXCHANGE_RATE = 0.1;  // 1积分 = 0.1元
    $MIN_POINTS = 100;     // 最低兑换100积分
    
    // 验证兑换积分
    if ($points < $MIN_POINTS) {
        echo json_encode([
            'code' => -1,
            'message' => "最低兑换{$MIN_POINTS}积分",
            'data' => null
        ]);
        exit;
    }
    
    // 开启事务
    $pdo->beginTransaction();
    
    // 查询当前积分（加锁）
    $stmt = $pdo->prepare("
        SELECT id, points, balance 
        FROM users 
        WHERE id = :user_id 
        FOR UPDATE
    ");
    $stmt->execute([':user_id' => $user['id']]);
    $userData = $stmt->fetch();
    
    if (!$userData) {
        $pdo->rollBack();
        echo json_encode([
            'code' => -1,
            'message' => '用户不存在',
            'data' => null
        ]);
        exit;
    }
    
    $currentPoints = floatval($userData['points']);
    $currentBalance = floatval($userData['balance']);
    
    // 验证积分是否充足
    if ($currentPoints < $points) {
        $pdo->rollBack();
        echo json_encode([
            'code' => -1,
            'message' => '积分不足',
            'data' => null
        ]);
        exit;
    }
    
    // 计算兑换金额
    $amount = $points * $EXCHANGE_RATE;
    $newPoints = $currentPoints - $points;
    $newBalance = $currentBalance + $amount;
    
    // 更新用户积分和余额
    $stmt = $pdo->prepare("
        UPDATE users 
        SET points = :points,
            balance = :balance,
            updated_at = NOW()
        WHERE id = :user_id
    ");
    $stmt->execute([
        ':points' => $newPoints,
        ':balance' => $newBalance,
        ':user_id' => $user['id']
    ]);
    
    // 记录积分兑换日志
    $stmt = $pdo->prepare("
        INSERT INTO points_exchange_logs 
        (user_id, points_used, amount_received, exchange_rate, before_points, after_points, before_balance, after_balance, status, created_at) 
        VALUES 
        (:user_id, :points_used, :amount, :rate, :before_points, :after_points, :before_balance, :after_balance, 1, NOW())
    ");
    $stmt->execute([
        ':user_id' => $user['id'],
        ':points_used' => $points,
        ':amount' => $amount,
        ':rate' => $EXCHANGE_RATE,
        ':before_points' => $currentPoints,
        ':after_points' => $newPoints,
        ':before_balance' => $currentBalance,
        ':after_balance' => $newBalance
    ]);
    
    // 提交事务
    $pdo->commit();
    
    // 返回成功结果
    echo json_encode([
        'code' => 1,
        'message' => '兑换成功',
        'data' => [
            'points_used' => $points,
            'amount_received' => round($amount, 2),
            'remain_points' => $newPoints,
            'new_balance' => round($newBalance, 2)
        ]
    ]);
    
} catch (Exception $e) {
    // 回滚事务
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'code' => -1,
        'message' => '兑换失败: ' . $e->getMessage(),
        'data' => null
    ]);
}
?>

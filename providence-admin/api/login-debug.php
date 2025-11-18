<?php
/**
 * 登录调试版本
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "开始调试...\n";

require_once __DIR__ . '/../config/bootstrap.php';

echo "Bootstrap加载成功\n";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');

echo "用户名: $username\n";
echo "密码: $password\n";

$db = Database::getInstance();

$sql = "SELECT * FROM users WHERE (username = :username OR phone = :username) AND status = 1 LIMIT 1";
echo "SQL: $sql\n";

$user = $db->fetchOne($sql, ['username' => $username]);

if ($user) {
    echo "找到用户: " . $user['username'] . "\n";
    
    $hashedPassword = Auth::hashPassword($password);
    echo "密码hash: $hashedPassword\n";
    echo "数据库hash: " . $user['password'] . "\n";
    
    if ($user['password'] === $hashedPassword) {
        Response::success(['token' => 'test_token'], '登录成功');
    } else {
        Response::error('密码错误');
    }
} else {
    echo "未找到用户\n";
    Response::error('用户不存在');
}

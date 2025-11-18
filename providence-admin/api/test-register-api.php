<?php
require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['code' => -1, 'message' => '请求方法错误']));
}

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');

file_put_contents('/tmp/register_debug.log', date('Y-m-d H:i:s') . " - 开始注册: $username\n", FILE_APPEND);

try {
    $db = Database::getInstance();
    file_put_contents('/tmp/register_debug.log', "数据库连接成功\n", FILE_APPEND);
    
    $new_uid = str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    $new_invite_code = strtoupper(substr(md5(uniqid()), 0, 8));
    
    file_put_contents('/tmp/register_debug.log', "准备插入: UID=$new_uid, 邀请码=$new_invite_code\n", FILE_APPEND);
    
    $userId = $db->insert('users', [
        'uid' => $new_uid,
        'username' => $username,
        'password' => Auth::hashPassword($password),
        'invite_code' => $new_invite_code,
        'vip_level' => 1,
        'trial_balance' => 1000.00,
        'login_ip' => $_SERVER['REMOTE_ADDR'],
        'login_time' => date('Y-m-d H:i:s')
    ]);
    
    file_put_contents('/tmp/register_debug.log', "Insert 返回: " . var_export($userId, true) . "\n", FILE_APPEND);
    
    if (!$userId) {
        throw new Exception('Insert 返回 false');
    }
    
    $token = Auth::generateToken($userId, $username);
    
    echo json_encode([
        'code' => 200,
        'message' => '注册成功',
        'data' => ['token' => $token, 'user_id' => $userId]
    ]);
    
} catch (Exception $e) {
    file_put_contents('/tmp/register_debug.log', "错误: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n", FILE_APPEND);
    echo json_encode(['code' => -1, 'message' => '注册失败: ' . $e->getMessage()]);
}

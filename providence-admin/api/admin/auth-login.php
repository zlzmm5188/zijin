<?php
/**
 * 管理员登录API
 * POST /api/admin/auth/login
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方式错误', 405);
}

$data = json_decode(file_get_contents('php://input'), true);
$username = trim($data['username'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($username) || empty($password)) {
    Response::error('用户名和密码不能为空');
}

$db = Database::getInstance();

// 查询管理员
$sql = "SELECT a.*, r.name as role_name, r.code as role_code, r.permissions 
        FROM " . $db->getPrefix() . "admins a 
        LEFT JOIN " . $db->getPrefix() . "admin_roles r ON a.role_id = r.id 
        WHERE a.username = :username";
$admin = $db->fetchOne($sql, ['username' => $username]);

if (!$admin) {
    // 记录失败日志
    logAdminLoginAttempt(null, $username, false, '用户不存在');
    Response::error('用户名或密码错误');
}

if ($admin['status'] != 1) {
    logAdminLoginAttempt($admin['id'], $username, false, '账号已禁用');
    Response::error('账号已被禁用');
}

// 验证密码
if (!password_verify($password, $admin['password'])) {
    logAdminLoginAttempt($admin['id'], $username, false, '密码错误');
    Response::error('用户名或密码错误');
}

// 生成Token
$token = bin2hex(random_bytes(32));
$expireTime = time() + 86400 * 7; // 7天有效

// 更新登录信息
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$db->update('admins', [
    'last_login_at' => date('Y-m-d H:i:s'),
    'last_login_ip' => $ip,
    'login_count' => $admin['login_count'] + 1
], 'id = :id', ['id' => $admin['id']]);

// 记录登录日志
logAdminLoginAttempt($admin['id'], $username, true);

// 存储Token (可选: 存入Redis或数据库)
$_SESSION['admin_token'] = $token;
$_SESSION['admin_id'] = $admin['id'];
$_SESSION['admin_expire'] = $expireTime;

Response::success([
    'token' => $token,
    'expire_at' => $expireTime,
    'admin' => [
        'id' => (int)$admin['id'],
        'username' => $admin['username'],
        'real_name' => $admin['real_name'],
        'avatar' => $admin['avatar'],
        'role_name' => $admin['role_name'],
        'role_code' => $admin['role_code'],
        'permissions' => json_decode($admin['permissions'] ?? '[]', true)
    ]
], '登录成功');

/**
 * 记录管理员登录日志
 */
function logAdminLoginAttempt($adminId, $username, $success, $reason = '') {
    $db = Database::getInstance();
    
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $browser = getBrowser($userAgent);
    $os = getOS($userAgent);
    
    $data = [
        'admin_id' => $adminId ?? 0,
        'username' => $username,
        'login_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
        'device_info' => $userAgent,
        'browser' => $browser,
        'os' => $os,
        'login_time' => date('Y-m-d H:i:s'),
        'status' => $success ? 1 : 0,
        'fail_reason' => $reason
    ];
    
    try {
        $db->insert('admin_login_logs', $data);
    } catch (Exception $e) {
        error_log("记录管理员登录日志失败: " . $e->getMessage());
    }
}

function getBrowser($userAgent) {
    if (strpos($userAgent, 'Chrome') !== false) return 'Chrome';
    if (strpos($userAgent, 'Firefox') !== false) return 'Firefox';
    if (strpos($userAgent, 'Safari') !== false) return 'Safari';
    if (strpos($userAgent, 'Edge') !== false) return 'Edge';
    if (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) return 'IE';
    return 'Unknown';
}

function getOS($userAgent) {
    if (strpos($userAgent, 'Windows') !== false) return 'Windows';
    if (strpos($userAgent, 'Mac') !== false) return 'MacOS';
    if (strpos($userAgent, 'Linux') !== false) return 'Linux';
    if (strpos($userAgent, 'Android') !== false) return 'Android';
    if (strpos($userAgent, 'iOS') !== false || strpos($userAgent, 'iPhone') !== false) return 'iOS';
    return 'Unknown';
}

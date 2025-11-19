<?php
/**
 * 用户登录API（简化调试版）
 */
require_once __DIR__ . '/../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');

if (empty($username) || empty($password)) {
    Response::error('用户名和密码不能为空');
}

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=providence;charset=utf8mb4",
        "providence",
        "Providence@2024"
    );

    // 直接查询
    $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR phone = ?) AND status = 1 LIMIT 1");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // 记录失败的登录尝试
        try {
            $loginIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $stmt = $pdo->prepare("
                INSERT INTO user_login_logs
                (user_id, login_ip, user_agent, login_time, status, remark)
                VALUES (0, ?, ?, NOW(), 0, ?)
            ");
            $stmt->execute([$loginIp, $userAgent, '用户不存在: ' . $username]);
        } catch (Exception $e) {}

        Response::error('用户不存在或已被禁用');
    }

    // 验证密码（兼容bcrypt和MD5两种格式）
    $passwordValid = false;

    // 优先使用 password_verify（bcrypt格式）
    if (password_verify($password, $user['password'])) {
        $passwordValid = true;
    }
    // 兼容旧的MD5格式
    elseif ($user['password'] === Auth::hashPassword($password)) {
        $passwordValid = true;
    }

    if (!$passwordValid) {
        // 记录密码错误的登录尝试
        try {
            $loginIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $stmt = $pdo->prepare("
                INSERT INTO user_login_logs
                (user_id, login_ip, user_agent, login_time, status, remark)
                VALUES (?, ?, ?, NOW(), 0, ?)
            ");
            $stmt->execute([$user['id'], $loginIp, $userAgent, '密码错误']);
        } catch (Exception $e) {}

        Response::error('密码错误');
    }

    // 生成Token
    $token = Auth::generateToken($user['id'], $user['username']);

    // 记录登录日志
    try {
        $loginIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // 解析设备类型
        $deviceType = 'Unknown';
        if (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
            $deviceType = 'iOS';
        } elseif (stripos($userAgent, 'Android') !== false) {
            $deviceType = 'Android';
        } elseif (stripos($userAgent, 'Windows') !== false) {
            $deviceType = 'Windows';
        } elseif (stripos($userAgent, 'Mac') !== false) {
            $deviceType = 'Mac';
        } elseif (stripos($userAgent, 'Linux') !== false) {
            $deviceType = 'Linux';
        }

        // 插入登录日志
        $stmt = $pdo->prepare("
            INSERT INTO user_login_logs
            (user_id, login_ip, device_type, user_agent, login_time, status)
            VALUES (?, ?, ?, ?, NOW(), 1)
        ");
        $stmt->execute([$user['id'], $loginIp, $deviceType, $userAgent]);
    } catch (Exception $logError) {
        // 登录日志记录失败不影响登录流程
        error_log("登录日志记录失败: " . $logError->getMessage());
    }

    Response::success([
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'username' => $user['username'],
            'phone' => $user['phone'],
            'vip_level' => (int)$user['vip_level'],
            'invite_code' => $user['uid']
        ]
    ], '登录成功');

} catch (Exception $e) {
    Response::error('登录失败: ' . $e->getMessage());
}

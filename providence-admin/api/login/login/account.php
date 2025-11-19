<?php

/**
 * 用户登录API
 * 路径: /api/login/login/account
 * 功能: 用户登录，记录登录日志（IP、设备、时间）
 */
require_once dirname(__DIR__, 3) . '/config/bootstrap.php';

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');

if (empty($username) || empty($password)) {
    Response::error('用户名和密码不能为空');
}

try {
    $db = Database::getInstance();

    // 获取登录IP和设备信息
    $loginIp = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    // 如果使用代理，获取真实IP
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $loginIp = trim($ips[0]);
    } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
        $loginIp = $_SERVER['HTTP_X_REAL_IP'];
    }

    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    // 解析设备类型和详细信息
    $deviceInfo = parseDeviceInfo($userAgent);

    // 查询用户（支持用户名/手机号登录）
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE (username = ? OR phone = ?) AND status = 1 LIMIT 1",
        [$username, $username]
    );

    if (!$user) {
        // 记录失败的登录尝试
        try {
            $db->query(
                "INSERT INTO user_login_logs
                (user_id, login_ip, device_type, device_info, user_agent, login_time, status, remark)
                VALUES (0, ?, ?, ?, ?, NOW(), 0, ?)",
                [
                    $loginIp,
                    $deviceInfo['type'],
                    json_encode($deviceInfo, JSON_UNESCAPED_UNICODE),
                    $userAgent,
                    '用户不存在: ' . $username
                ]
            );
        } catch (Exception $e) {
            error_log('记录登录日志失败: ' . $e->getMessage());
        }

        Response::error('用户不存在或已被禁用');
    }

    // 验证密码
    $passwordHash = hash('sha256', $password . ($user['salt'] ?? ''));
    if ($passwordHash !== $user['password']) {
        // 记录密码错误的登录尝试
        try {
            $db->query(
                "INSERT INTO user_login_logs
                (user_id, login_ip, device_type, device_info, user_agent, login_time, status, remark)
                VALUES (?, ?, ?, ?, ?, NOW(), 0, ?)",
                [
                    $user['id'],
                    $loginIp,
                    $deviceInfo['type'],
                    json_encode($deviceInfo, JSON_UNESCAPED_UNICODE),
                    $userAgent,
                    '密码错误'
                ]
            );
        } catch (Exception $e) {
            error_log('记录登录日志失败: ' . $e->getMessage());
        }

        Response::error('密码错误');
    }

    // 生成Token
    $token = Auth::generateToken($user['id'], $user['username']);

    // 记录成功登录日志
    try {
        // 获取IP归属地（可选，需要第三方API）
        $loginLocation = getIpLocation($loginIp);

        $db->query(
            "INSERT INTO user_login_logs
            (user_id, login_ip, login_location, device_type, device_info, user_agent, login_time, status)
            VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)",
            [
                $user['id'],
                $loginIp,
                $loginLocation,
                $deviceInfo['type'],
                json_encode($deviceInfo, JSON_UNESCAPED_UNICODE),
                $userAgent
            ]
        );
    } catch (Exception $e) {
        // 登录日志记录失败不影响登录流程
        error_log('记录登录日志失败: ' . $e->getMessage());
    }

    // 返回成功
    Response::success([
        'token' => $token,
        'user' => [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'realname' => '',  // users表没有realname字段
            'phone' => $user['phone'] ?? '',
            'vip_level' => (int)($user['vip_level'] ?? 0),
            'avatar' => $user['avatar'] ?? ''
        ]
    ], '登录成功');
} catch (Exception $e) {
    error_log('[Login API] Error: ' . $e->getMessage());
    Response::error('服务器错误: ' . $e->getMessage());
}

/**
 * 解析设备信息
 */
function parseDeviceInfo($userAgent)
{
    $info = [
        'type' => 'Unknown',
        'os' => 'Unknown',
        'browser' => 'Unknown',
        'device' => 'Unknown'
    ];

    if (empty($userAgent)) {
        return $info;
    }

    $ua = strtolower($userAgent);

    // 检测操作系统
    if (strpos($ua, 'iphone') !== false || strpos($ua, 'ipad') !== false) {
        $info['type'] = 'iOS';
        $info['os'] = 'iOS';
        $info['device'] = strpos($ua, 'ipad') !== false ? 'iPad' : 'iPhone';
    } elseif (strpos($ua, 'android') !== false) {
        $info['type'] = 'Android';
        $info['os'] = 'Android';
        // 尝试提取Android版本
        if (preg_match('/android\s([0-9\.]+)/', $ua, $matches)) {
            $info['os'] = 'Android ' . $matches[1];
        }
    } elseif (strpos($ua, 'windows') !== false) {
        $info['type'] = 'Windows';
        $info['os'] = 'Windows';
        if (strpos($ua, 'windows nt 10') !== false) {
            $info['os'] = 'Windows 10/11';
        } elseif (strpos($ua, 'windows nt 6.3') !== false) {
            $info['os'] = 'Windows 8.1';
        } elseif (strpos($ua, 'windows nt 6.2') !== false) {
            $info['os'] = 'Windows 8';
        } elseif (strpos($ua, 'windows nt 6.1') !== false) {
            $info['os'] = 'Windows 7';
        }
    } elseif (strpos($ua, 'mac os') !== false || strpos($ua, 'macintosh') !== false) {
        $info['type'] = 'Mac';
        $info['os'] = 'macOS';
    } elseif (strpos($ua, 'linux') !== false) {
        $info['type'] = 'Linux';
        $info['os'] = 'Linux';
    }

    // 检测浏览器
    if (strpos($ua, 'micromessenger') !== false) {
        $info['browser'] = '微信浏览器';
    } elseif (strpos($ua, 'qqbrowser') !== false) {
        $info['browser'] = 'QQ浏览器';
    } elseif (strpos($ua, 'ucbrowser') !== false) {
        $info['browser'] = 'UC浏览器';
    } elseif (strpos($ua, 'chrome') !== false) {
        $info['browser'] = 'Chrome';
    } elseif (strpos($ua, 'safari') !== false && strpos($ua, 'chrome') === false) {
        $info['browser'] = 'Safari';
    } elseif (strpos($ua, 'firefox') !== false) {
        $info['browser'] = 'Firefox';
    } elseif (strpos($ua, 'edge') !== false) {
        $info['browser'] = 'Edge';
    } elseif (strpos($ua, 'opera') !== false) {
        $info['browser'] = 'Opera';
    }

    return $info;
}

/**
 * 获取IP归属地（简化版，可接入第三方API）
 */
function getIpLocation($ip)
{
    // 内网IP
    if ($ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
        return '内网IP';
    }

    // 这里可以接入第三方IP查询API，如：
    // - ip-api.com
    // - ipip.net
    // - ip.sb

    // 暂时返回空，后续可以接入
    return null;
}

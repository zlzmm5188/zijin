<?php

/**
 * 登录接口
 * POST /api/login/account.php
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');

if (empty($username) || empty($password)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 400,
        'msg' => '用户名和密码不能为空',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = Database::getInstance();

    // 查询用户（支持用户名/手机号登录）
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE (username = ? OR phone = ?) AND status = 1 LIMIT 1",
        [$username, $username]
    );

    if (!$user) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => 400,
            'msg' => '用户不存在或已被禁用',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 验证密码（使用password_verify验证bcrypt）
    if (!password_verify($password, $user['password'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => 400,
            'msg' => '密码错误',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 生成Token
    $token = Auth::generateToken($user['id'], $user['username']);

    // 记录登录日志
    $db->query(
        "INSERT INTO user_login_logs (user_id, login_ip, device_type, device_info, user_agent, login_time, status, remark)
         VALUES (?, ?, 'web', '{}', ?, NOW(), 1, '登录成功')",
        [$user['id'], $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']
    );

    // 返回成功（code: 200格式）
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 200,
        'msg' => '登录成功',
        'data' => [
            'token' => $token,
            'user' => [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'realname' => $user['realname'] ?? '',
                'phone' => $user['phone'] ?? '',
                'vip_level' => (int)$user['vip_level'],
                'avatar' => $user['avatar'] ?? ''
            ]
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('[Login API] Error: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 500,
        'msg' => '服务器错误: ' . $e->getMessage(),
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}

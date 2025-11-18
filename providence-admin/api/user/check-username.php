<?php
/**
 * 检查用户名是否存在
 * POST /index.php/user/user/checkUsername
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');

if (empty($username)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '账号不能为空',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = Database::getInstance();

    // 查询用户是否存在（使用正确的字段名）
    $user = $db->fetchOne(
        "SELECT id, username, realname_status, status FROM users WHERE username = ? LIMIT 1",
        [$username]
    );

    if (!$user) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => -1,
            'msg' => '账号不存在',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 检查账号状态
    if ($user['status'] != 1) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => -1,
            'msg' => '账号已被禁用',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 返回成功
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 1,
        'msg' => '账号验证成功',
        'data' => [
            'username' => $user['username'],
            'has_kyc' => ($user['realname_status'] ?? 0) == 1
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log('[检查用户名] 错误: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '服务器错误',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}

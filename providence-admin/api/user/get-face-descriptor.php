<?php

/**
 * 获取用户人脸特征接口
 * POST /index.php/user/user/getFaceDescriptor
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

    // 查询用户信息（使用正确的字段名）
    $user = $db->fetchOne(
        "SELECT id, username, realname_status, face_descriptor FROM users WHERE username = ? LIMIT 1",
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

    // 检查是否完成KYC（使用realname_status字段）
    $kycStatus = $user['realname_status'] ?? 0;
    if ($kycStatus != 1) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => -1,
            'msg' => '该账号未完成实名认证，无法使用人脸识别找回',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 检查是否有人脸特征
    if (empty($user['face_descriptor'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => -1,
            'msg' => '该账号未注册人脸特征',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 解密人脸特征（如果加密存储）
    $faceDescriptor = json_decode($user['face_descriptor'], true);
    if (!$faceDescriptor || !is_array($faceDescriptor) || count($faceDescriptor) !== 512) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => -1,
            'msg' => '人脸特征数据异常',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 生成临时重置令牌（有效期10分钟）
    $resetToken = bin2hex(random_bytes(16));
    $expiresAt = date('Y-m-d H:i:s', time() + 600);

    // 存储重置令牌
    $db->query(
        "INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at)
         VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE token = ?, expires_at = ?, created_at = NOW()",
        [$user['id'], $resetToken, $expiresAt, $resetToken, $expiresAt]
    );

    // 返回成功
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 1,
        'msg' => '成功',
        'data' => [
            'has_kyc' => true,
            'face_descriptor' => $faceDescriptor,  // 返回512维特征向量
            'reset_token' => $resetToken
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('[获取人脸特征] 错误: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '服务器错误',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}

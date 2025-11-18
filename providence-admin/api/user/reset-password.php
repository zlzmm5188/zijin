<?php

/**
 * 重置密码接口
 * POST /index.php/user/user/resetPassword
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);
$resetToken = trim($input['reset_token'] ?? '');
$newPassword = trim($input['new_password'] ?? '');

if (empty($resetToken) || empty($newPassword)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '参数不完整',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 密码强度验证
if (
    strlen($newPassword) < 8 ||
    !preg_match('/[a-z]/', $newPassword) ||
    !preg_match('/[A-Z]/', $newPassword) ||
    !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $newPassword)
) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '密码格式不符合要求',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = Database::getInstance();

    // 验证重置令牌（必须已通过人工审核）
    $tokenRecord = $db->fetchOne(
        "SELECT t.user_id, t.expires_at, r.status
         FROM password_reset_tokens t
         LEFT JOIN face_review_records r ON t.token = r.reset_token
         WHERE t.token = ? AND t.expires_at > NOW()
         ORDER BY r.id DESC LIMIT 1",
        [$resetToken]
    );

    if (!$tokenRecord) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => -1,
            'msg' => '重置令牌无效或已过期',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 检查是否已通过人工审核
    if ($tokenRecord['status'] !== 'approved') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => -1,
            'msg' => '人工审核未通过，无法重置密码',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 更新密码
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
    $db->query(
        "UPDATE users SET password = ? WHERE id = ?",
        [$hashedPassword, $tokenRecord['user_id']]
    );

    // 删除已使用的令牌
    $db->query("DELETE FROM password_reset_tokens WHERE token = ?", [$resetToken]);

    // 记录操作日志
    $db->query(
        "INSERT INTO audit_logs (user_id, action, description, ip_address, created_at)
         VALUES (?, 'password_reset', '通过人脸识别找回密码', ?, NOW())",
        [$tokenRecord['user_id'], $_SERVER['REMOTE_ADDR'] ?? '']
    );

    // 返回成功
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 1,
        'msg' => '密码重置成功',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('[重置密码] 错误: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '服务器错误',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}

<?php

/**
 * 提交人脸进行人工审核接口
 * POST /index.php/user/user/submitFaceForReview
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$faceImage = $input['face_image'] ?? '';
$similarity = floatval($input['similarity'] ?? 0);
$resetToken = trim($input['reset_token'] ?? '');

if (empty($username) || empty($faceImage) || empty($resetToken)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '参数不完整',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// 验证本地相似度（必须 >= 0.6）
if ($similarity < 0.6) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '本地验证未通过，无法提交审核',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = Database::getInstance();

    // 验证重置令牌
    $tokenRecord = $db->fetchOne(
        "SELECT user_id, expires_at FROM password_reset_tokens
         WHERE token = ? AND expires_at > NOW() LIMIT 1",
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

    // 保存人脸照片到服务器（用于人工审核）
    $uploadDir = dirname(__DIR__, 2) . '/uploads/face-review/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // 解码base64图片
    $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $faceImage));
    if (!$imageData) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => -1,
            'msg' => '图片数据格式错误',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $filename = 'review_' . $tokenRecord['user_id'] . '_' . time() . '.jpg';
    $filepath = $uploadDir . $filename;
    file_put_contents($filepath, $imageData);

    // 生成新的重置令牌（用于后续查询和重置）
    $newResetToken = bin2hex(random_bytes(16));
    $expiresAt = date('Y-m-d H:i:s', time() + 1800); // 30分钟有效期

    // 创建审核记录
    $db->query(
        "INSERT INTO face_review_records (user_id, reset_token, face_image_path, similarity, status, created_at)
         VALUES (?, ?, ?, ?, 'pending', NOW())",
        [$tokenRecord['user_id'], $newResetToken, $filename, $similarity]
    );
    // 获取插入的ID
    $reviewRecord = $db->fetchOne(
        "SELECT id FROM face_review_records WHERE reset_token = ? ORDER BY id DESC LIMIT 1",
        [$newResetToken]
    );
    $reviewId = $reviewRecord ? (int)$reviewRecord['id'] : 0;

    // 更新重置令牌
    $db->query(
        "UPDATE password_reset_tokens SET token = ?, expires_at = ? WHERE token = ?",
        [$newResetToken, $expiresAt, $resetToken]
    );

    // 返回成功
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 1,
        'msg' => '已提交人工审核',
        'data' => [
            'review_id' => $reviewId,
            'reset_token' => $newResetToken,
            'estimated_time' => 5 // 预计5分钟
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('[提交人脸审核] 错误: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '服务器错误',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}

<?php

/**
 * 查询人脸审核状态接口
 * POST /index.php/user/user/checkFaceReview
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);
$resetToken = trim($input['reset_token'] ?? '');

if (empty($resetToken)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '参数不完整',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = Database::getInstance();

    // 查询审核记录
    $review = $db->fetchOne(
        "SELECT r.*, u.username
         FROM face_review_records r
         JOIN password_reset_tokens t ON r.reset_token = t.token
         JOIN users u ON r.user_id = u.id
         WHERE r.reset_token = ? AND t.expires_at > NOW()
         ORDER BY r.id DESC LIMIT 1",
        [$resetToken]
    );

    if (!$review) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => -1,
            'msg' => '审核记录不存在或已过期',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 返回审核状态
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 1,
        'msg' => '成功',
        'data' => [
            'status' => $review['status'], // pending, approved, rejected
            'reset_token' => $review['status'] === 'approved' ? $resetToken : null,
            'reason' => $review['reject_reason'] ?? null,
            'reviewed_at' => $review['reviewed_at'] ?? null
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('[查询审核状态] 错误: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '服务器错误',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
}

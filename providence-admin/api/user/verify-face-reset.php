<?php

/**
 * 验证人脸用于重置密码接口
 * POST /index.php/user/user/verifyFaceReset
 *
 * 注意：此接口用于简化流程的人脸验证
 * 完整流程应使用：checkUsername -> getFaceDescriptor -> submitFaceForReview -> checkFaceReview -> resetPassword
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);
$faceImage = $input['face_image'] ?? '';
$username = trim($input['username'] ?? '');

if (empty($faceImage)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '人脸图片不能为空',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = Database::getInstance();

    // 如果没有提供用户名，返回提示使用完整流程
    if (empty($username)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'code' => -1,
            'msg' => '请使用完整流程：先调用checkUsername获取重置令牌，然后使用submitFaceForReview提交人脸审核',
            'data' => null
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 查询用户信息
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

    // 检查是否完成KYC
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

    // 注意：后端不进行人脸比对，比对应在前端完成
    // 此接口仅用于验证用户身份和返回用户信息
    // 实际的人脸比对和审核应通过 submitFaceForReview 接口完成

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => 200,
        'msg' => '请使用submitFaceForReview接口提交人脸进行审核',
        'data' => [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'similarity' => 0,  // 后端不计算相似度，由前端计算
            'has_face' => true
        ]
    ], JSON_UNESCAPED_UNICODE);
    exit;

} catch (Exception $e) {
    error_log('[验证人脸重置] 错误: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '服务器错误: ' . $e->getMessage(),
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

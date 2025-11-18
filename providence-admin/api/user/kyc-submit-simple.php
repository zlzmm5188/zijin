<?php
/**
 * 实名认证提交接口 - 简化版（无人脸识别）
 * 用户上传身份证和自拍照，直接提交到后台等待审核
 */

require_once __DIR__ . '/../../config/bootstrap.php';

// ========== 主逻辑 ==========

// 只接受POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

// 验证用户登录
$userData = Auth::verifyToken(Auth::getToken());
if (!$userData) {
    Response::error('请先登录', -1, ['need_login' => true]);
}

$userId = $userData['user_id'];
$db = Database::getInstance();

// 检查是否已提交过认证
$stmt = $db->prepare("SELECT * FROM user_kyc WHERE user_id = ? AND status IN (0, 2) ORDER BY id DESC LIMIT 1");
$stmt->execute([$userId]);
$existingKyc = $stmt->fetch();

if ($existingKyc) {
    if ($existingKyc['status'] == 2) {
        Response::error('您已完成实名认证');
    } else {
        Response::error('您的认证正在审核中，请勿重复提交');
    }
}

// 检查上传文件
if (!isset($_FILES['id_card_file']) || !isset($_FILES['selfie'])) {
    Response::error('请上传身份证和自拍照');
}

$idCardFile = $_FILES['id_card_file'];
$selfieFile = $_FILES['selfie'];

// 验证文件类型
$allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
if (!in_array($idCardFile['type'], $allowedTypes) || !in_array($selfieFile['type'], $allowedTypes)) {
    Response::error('只支持JPG和PNG格式');
}

// 验证文件大小（5MB）
if ($idCardFile['size'] > 5 * 1024 * 1024 || $selfieFile['size'] > 5 * 1024 * 1024) {
    Response::error('照片大小不能超过5MB');
}

// 创建上传目录
$uploadDir = '/www/wwwroot/copla/providence/uploads/kyc/' . $userId;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// 保存文件
$timestamp = time();
$idCardPath = $uploadDir . '/id_card_' . $timestamp . '.jpg';
$selfiePath = $uploadDir . '/selfie_' . $timestamp . '.jpg';

if (!move_uploaded_file($idCardFile['tmp_name'], $idCardPath)) {
    Response::error('身份证照片保存失败');
}

if (!move_uploaded_file($selfieFile['tmp_name'], $selfiePath)) {
    @unlink($idCardPath);
    Response::error('自拍照保存失败');
}

// 转换为相对路径
$idCardPathRel = str_replace('/www/wwwroot/copla/providence', '', $idCardPath);
$selfiePathRel = str_replace('/www/wwwroot/copla/providence', '', $selfiePath);

// 获取real_name和id_card（从表单）
$realName = $_POST['real_name'] ?? '';
$idCard = $_POST['id_card'] ?? '';

// 保存到数据库（status=0 待审核）
try {
    $stmt = $db->prepare("
        INSERT INTO user_kyc (
            user_id, real_name, id_card, id_card_front, hand_held_photo,
            submit_time, status, created_at
        ) VALUES (?, ?, ?, ?, ?, NOW(), 0, NOW())
    ");
    
    $stmt->execute([
        $userId,
        $realName,
        $idCard,
        $idCardPathRel,
        $selfiePathRel
    ]);
    
    $kycId = $db->lastInsertId();
    
    // 发送Telegram通知
    try {
        TelegramNotify::notifyKYC($userData['username'] ?? 'User', $userId, $realName);
    } catch (Exception $e) {
        error_log("Telegram通知失败: " . $e->getMessage());
    }
    
    Response::success([
        'kyc_id' => $kycId,
        'status' => 'pending'
    ], '实名认证已提交，等待审核');
    
} catch (Exception $e) {
    // 删除已上传的文件
    @unlink($idCardPath);
    @unlink($selfiePath);
    Response::error('提交失败: ' . $e->getMessage());
}

<?php
/**
 * 提交实名认证 (SRS规范)
 * POST /user/kyc/submit
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$realName = trim($_POST['real_name'] ?? '');
$idCard = trim($_POST['id_card'] ?? '');

if (empty($realName) || empty($idCard)) {
    Response::error('姓名和身份证号不能为空');
}

// 验证身份证格式
if (!preg_match('/^\d{17}[\dXx]$/', $idCard)) {
    Response::error('身份证号格式不正确');
}

$db = Database::getInstance();

// 检查是否已提交过
$exists = $db->fetchOne("SELECT id, status FROM " . $db->getPrefix() . "user_kyc WHERE user_id = :uid",
    ['uid' => $authUser['user_id']]);

if ($exists && $exists['status'] == 1) {
    Response::error('您已通过实名认证，无需重复提交');
}
if ($exists && $exists['status'] == 0) {
    Response::error('您的认证正在审核中，请耐心等待');
}

// 上传图片
$idCardFront = '';
$idCardBack = '';
$handHeld = '';

if (isset($_FILES['id_card_front'])) {
    $result = Upload::uploadFile($_FILES['id_card_front'], 'kyc');
    if ($result['success']) {
        $idCardFront = $result['url'];
    }
}

if (isset($_FILES['id_card_back'])) {
    $result = Upload::uploadFile($_FILES['id_card_back'], 'kyc');
    if ($result['success']) {
        $idCardBack = $result['url'];
    }
}

if (isset($_FILES['hand_held_photo'])) {
    $result = Upload::uploadFile($_FILES['hand_held_photo'], 'kyc');
    if ($result['success']) {
        $handHeld = $result['url'];
    }
}

$db->beginTransaction();
try {
    if ($exists) {
        // 更新
        $db->update('user_kyc', [
            'real_name' => $realName,
            'id_card' => $idCard,
            'id_card_front' => $idCardFront,
            'id_card_back' => $idCardBack,
            'hand_held_photo' => $handHeld,
            'status' => 0
        ], 'user_id = :uid', ['uid' => $authUser['user_id']]);
    } else {
        // 插入
        $db->insert('user_kyc', [
            'user_id' => $authUser['user_id'],
            'real_name' => $realName,
            'id_card' => $idCard,
            'id_card_front' => $idCardFront,
            'id_card_back' => $idCardBack,
            'hand_held_photo' => $handHeld,
            'status' => 0
        ]);
    }
    
    // 更新用户表
    $db->update('users', [
        'real_name' => $realName,
        'id_card' => $idCard,
        'kyc_status' => 1
    ], 'id = :id', ['id' => $authUser['user_id']]);
    
    // 审计日志
    AuditLog::log('member', '提交实名认证', 'user', $authUser['user_id'], 'kyc', $authUser['user_id']);
    
    $db->commit();
    
    Response::success(null, '实名认证提交成功，等待审核');
    
} catch (Exception $e) {
    $db->rollBack();
    Response::error('提交失败: ' . $e->getMessage());
}

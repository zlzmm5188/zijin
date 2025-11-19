<?php
/**
 * 实名审核拒绝
 * POST /api/admin/kyc-reject
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$input = json_decode(file_get_contents('php://input'), true);
$userId = (int)($input['user_id'] ?? 0);
$reason = trim($input['reason'] ?? $input['remark'] ?? '');

if (!$userId) {
    Response::error('用户ID不能为空');
}

if (empty($reason)) {
    Response::error('拒绝原因不能为空');
}

$db = Database::getInstance();

$db->beginTransaction();
try {
    // 查询当前KYC记录
    $kycRecord = $db->fetchOne(
        "SELECT * FROM " . $db->getPrefix() . "user_kyc WHERE user_id = :uid",
        ['uid' => $userId]
    );

    if (!$kycRecord) {
        throw new Exception('实名记录不存在');
    }

    // 更新实名状态
    $affected = $db->query(
        "UPDATE " . $db->getPrefix() . "user_kyc SET status = 2, reject_reason = :reason, reviewed_at = NOW() WHERE user_id = :uid",
        ['reason' => $reason, 'uid' => $userId]
    );

    // 更新用户表
    $db->update('users', ['realname_status' => 2], 'id = :id', ['id' => $userId]);

    // SRS规范：审计日志
    AuditLog::log(
        'member',
        '实名审核拒绝',
        'ADMIN',
        1,
        'users',
        $userId,
        ['realname_status' => $kycRecord['status']],
        ['realname_status' => 2, 'reject_reason' => $reason]
    );

    $db->commit();

    Response::success([
        'user_id' => $userId,
        'status' => 2,
        'reason' => $reason
    ], '已拒绝实名认证');

} catch (Exception $e) {
    $db->rollBack();
    Response::error('操作失败: ' . $e->getMessage());
}

<?php

/**
 * 充值审核拒绝API
 * POST /admin/recharge-reject
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员登录 - 已禁用：无登录模式
$authUser = Auth::user(); // 始终返回 guest 用户（带管理员权限）
// if (!$authUser || !isset($authUser['is_admin']) || !$authUser['is_admin']) {
//     Response::error('无权限访问', 403);
// }

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);
$rechargeId = isset($input['id']) ? (int)$input['id'] : 0;
$reason = isset($input['reason']) ? trim($input['reason']) : '';

if ($rechargeId <= 0) {
    Response::error('充值记录ID无效');
}

if (empty($reason)) {
    Response::error('请输入拒绝原因');
}

$db = Database::getInstance();

try {
    // 开启事务
    $db->beginTransaction();

    // 1. 获取充值记录（加锁）
    $recharge = $db->fetchOne(
        "SELECT * FROM recharge_records WHERE id = :id FOR UPDATE",
        ['id' => $rechargeId]
    );

    if (!$recharge) {
        $db->rollback();
        Response::error('充值记录不存在');
    }

    // 2. 检查状态
    if ($recharge['status'] != 0) {
        $db->rollback();
        $statusMap = [0 => '待审核', 1 => '已通过', 2 => '已拒绝'];
        Response::error('该充值记录已处理，当前状态：' . ($statusMap[$recharge['status']] ?? '未知'));
    }

    // 3. 更新充值记录状态
    $db->update('recharge_records', [
        'status' => 2,
        'reviewed_at' => date('Y-m-d H:i:s'),
        'reviewed_by' => $authUser['user_id'],
        'remark' => $reason,
        'updated_at' => date('Y-m-d H:i:s')
    ], "id = {$rechargeId}");

    // 4. 记录审计日志
    AuditLog::log([
        'user_id' => $authUser['user_id'],
        'action' => 'recharge_reject',
        'target_type' => 'recharge',
        'target_id' => $rechargeId,
        'details' => json_encode([
            'recharge_user_id' => $recharge['user_id'],
            'amount' => $recharge['amount'],
            'currency' => $recharge['currency'],
            'order_no' => $recharge['order_no'],
            'reason' => $reason
        ], JSON_UNESCAPED_UNICODE),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    ]);

    // 提交事务
    $db->commit();

    Response::success([
        'recharge_id' => $rechargeId,
        'user_id' => $recharge['user_id'],
        'amount' => number_format($recharge['amount'], 8, '.', '')
    ], '已拒绝充值申请');
} catch (Exception $e) {
    $db->rollback();
    error_log("充值审核拒绝失败: " . $e->getMessage());
    Response::error('拒绝失败: ' . $e->getMessage());
}

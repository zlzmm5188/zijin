<?php
/**
 * 用户签到
 * POST /user/sign/sign
 */
require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

// 验证用户登录
// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

$userId = $authUser['user_id'];
$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 检查今天是否已签到
    $today = date('Y-m-d');
    $signToday = $db->fetchOne(
        "SELECT id FROM user_sign_logs WHERE user_id = :user_id AND DATE(signed_at) = :today",
        ['user_id' => $userId, 'today' => $today]
    );

    if ($signToday) {
        $db->rollBack();
        Response::error('今天已经签到过了');
    }

    // 获取用户VIP等级
    $user = $db->fetchOne("SELECT vip_level, username FROM users WHERE id = :id", ['id' => $userId]);
    $vipLevel = (int)($user['vip_level'] ?? 0);

    // VIP签到积分规则
    $vipSignPoints = [0 => 6, 1 => 10, 2 => 16, 3 => 20, 4 => 30, 5 => 40, 6 => 50, 7 => 60, 8 => 70];
    $points = $vipSignPoints[$vipLevel] ?? 6;

    // 记录签到
    $signId = $db->insert('user_sign_logs', [
        'user_id' => $userId,
        'points' => $points,
        'signed_at' => date('Y-m-d H:i:s'),
        'created_at' => date('Y-m-d H:i:s')
    ]);

    if (!$signId) {
        throw new Exception('签到记录失败');
    }

    // 更新用户积分（如果有积分表）
    $db->execute(
        "UPDATE users SET points = COALESCE(points, 0) + :points WHERE id = :id",
        ['points' => $points, 'id' => $userId]
    );

    // 记录积分流水
    $db->insert('wallet_logs', [
        'user_id' => $userId,
        'type' => 'SIGN_IN',
        'amount' => $points,
        'balance_after' => 0, // 这里应该查询当前积分，简化处理
        'description' => '每日签到奖励',
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // 审计日志
    AuditLog::log('sign', '用户签到', 'USER', $userId, 'user_sign_logs', $signId, ['username' => $user['username'], 'points' => $points]);

    $db->commit();

    Response::success([
        'points' => $points,
        'sign_id' => $signId,
        'signed_at' => date('Y-m-d H:i:s')
    ], '签到成功，获得' . $points . '积分');

} catch (Exception $e) {
    $db->rollBack();
    error_log("签到API错误: " . $e->getMessage());
    Response::error('签到失败: ' . $e->getMessage());
}

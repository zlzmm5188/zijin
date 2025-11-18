<?php
/**
 * 获取团队信息
 * GET /user/team/team
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     // Response::error(...) [已禁用]);
    // }

$userId = $authUser['user_id'];
$db = Database::getInstance();

try {
    // 获取团队统计（修复SQL参数绑定问题）
    $teamStats = $db->fetchOne(
        "SELECT
            COUNT(DISTINCT CASE WHEN parent_id = :user_id1 THEN id END) as direct_count,
            COUNT(DISTINCT id) as total_count
         FROM users
         WHERE parent_id = :user_id2 OR parent_id IN (SELECT id FROM users WHERE parent_id = :user_id3)",
        ['user_id1' => $userId, 'user_id2' => $userId, 'user_id3' => $userId]
    );

    // 获取直推列表
    $directMembers = $db->fetchAll(
        "SELECT id, username, uid, vip_level, total_invest, created_at
         FROM users
         WHERE parent_id = :user_id
         ORDER BY created_at DESC
         LIMIT 50",
        ['user_id' => $userId]
    );

    Response::success([
        'direct_count' => (int)($teamStats['direct_count'] ?? 0),
        'total_count' => (int)($teamStats['total_count'] ?? 0),
        'direct_members' => $directMembers
    ]);

} catch (Exception $e) {
    error_log("获取团队信息API错误: " . $e->getMessage());
    Response::error('获取团队信息失败: ' . $e->getMessage());
}

<?php
/**
 * 邀请信息 - 修复版（只查询uid）
 * GET /user/user/invite
 */
require_once __DIR__ . './../../config/bootstrap.php';

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     // Response::error(...) [已禁用]);
    // }

$db = Database::getInstance();

// 修复：只查询uid字段（表中没有invite_code字段）
$user = $db->fetchOne("SELECT uid FROM " . $db->getPrefix() . "users WHERE id = :id", ['id' => $authUser['user_id']]);

// 统计邀请数据
$directCount = $db->count('users', 'parent_id = :id', ['id' => $authUser['user_id']]);

// 使用UID作为主要邀请标识
$uid = $user['uid'] ?? null;

Response::success([
    'uid' => $uid,                                    // 用户UID（8位数字）
    'invite' => $uid,                                 // 主要邀请ID（就是uid）
    'invite_url' => $uid ? 'https://' . $uid . '.' . $_SERVER['HTTP_HOST'] : null,
    'direct_count' => $directCount,
    'total_reward' => 0 // TODO: 统计总奖励
]);

<?php
/**
 * 银行卡列表
 * GET /user/bank/list
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

$db = Database::getInstance();

$sql = "SELECT * FROM " . $db->getPrefix() . "bank_cards WHERE user_id = :user_id ORDER BY is_default DESC, id DESC";
$list = $db->fetchAll($sql, ['user_id' => $authUser['user_id']]);

Response::success($list);

<?php
/**
 * 获取银行卡列表
 * GET /pay/bank/list
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

$userId = $authUser['user_id'];
$db = Database::getInstance();

try {
    // 查询用户银行卡
    $sql = "SELECT
                id,
                bank_name as btype,
                bank_card as card,
                bank_branch as branch,
                usdt_address,
                is_default as type,
                created_at
            FROM user_bank_cards
            WHERE user_id = :user_id
            ORDER BY is_default DESC, id DESC";

    $cards = $db->fetchAll($sql, ['user_id' => $userId]);

    // 格式化数据
    foreach ($cards as &$card) {
        $card['id'] = (int)$card['id'];
        $card['type'] = (int)$card['type'];
        $card['can_modify'] = true; // 允许修改
    }

    Response::success($cards, 'ok', 200);

} catch (Exception $e) {
    error_log("银行卡列表API错误: " . $e->getMessage());
    Response::error('获取失败: ' . $e->getMessage());
}

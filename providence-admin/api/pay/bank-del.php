<?php
/**
 * 删除银行卡
 * POST /pay/bank/del
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

$data = json_decode(file_get_contents('php://input'), true);
$cardId = (int)($data['id'] ?? 0);

if ($cardId <= 0) {
    Response::error('参数错误');
}

$userId = $authUser['user_id'];
$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 验证银行卡是否属于当前用户
    $card = $db->fetchOne(
        "SELECT id FROM user_bank_cards WHERE id = :id AND user_id = :user_id",
        ['id' => $cardId, 'user_id' => $userId]
    );

    if (!$card) {
        throw new Exception('银行卡不存在或无权操作');
    }

    // 删除银行卡
    $result = $db->delete('user_bank_cards', 'id = :id AND user_id = :user_id', [
        'id' => $cardId,
        'user_id' => $userId
    ]);

    if (!$result) {
        throw new Exception('删除失败');
    }

    $db->commit();

    Response::success([], '解绑成功', 200);

} catch (Exception $e) {
    $db->rollBack();
    error_log("删除银行卡API错误: " . $e->getMessage());
    Response::error('解绑失败: ' . $e->getMessage());
}

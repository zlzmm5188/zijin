<?php
/**
 * 添加银行卡
 * POST /pay/bank/add
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
$bindType = trim($data['bind_type'] ?? ''); // 'bank' 或 'usdt'
$bankName = trim($data['bank_name'] ?? '');
$bankCard = trim($data['bank_card'] ?? '');
$bankBranch = trim($data['bank_branch'] ?? '');
$usdtAddress = trim($data['usdt_address'] ?? '');
$payPassword = trim($data['pay_password'] ?? '');

// 验证类型
if (!in_array($bindType, ['bank', 'usdt'])) {
    Response::error('绑定类型错误');
}

// 验证必填字段
if ($bindType === 'bank') {
    if (empty($bankName) || empty($bankCard)) {
        Response::error('请填写完整的银行卡信息');
    }
    if (!preg_match('/^\d{16,19}$/', $bankCard)) {
        Response::error('银行卡号格式不正确');
    }
} else {
    if (empty($usdtAddress)) {
        Response::error('请填写USDT钱包地址');
    }
    if (strlen($usdtAddress) < 20) {
        Response::error('USDT地址格式不正确');
    }
}

$userId = $authUser['user_id'];
$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 检查是否已有银行卡
    $existingCount = $db->fetchOne(
        "SELECT COUNT(*) as count FROM user_bank_cards WHERE user_id = :user_id",
        ['user_id' => $userId]
    );

    $isDefault = ($existingCount['count'] == 0) ? 1 : 0;

    // 插入银行卡记录
    $insertData = [
        'user_id' => $userId,
        'bank_name' => $bankName,
        'bank_card' => $bankCard,
        'bank_branch' => $bankBranch,
        'usdt_address' => $usdtAddress,
        'is_default' => $isDefault,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $result = $db->insert('user_bank_cards', $insertData);

    if (!$result) {
        throw new Exception('添加失败');
    }

    $db->commit();

    Response::success([
        'id' => $db->getLastInsertId(),
        'bind_type' => $bindType
    ], '绑定成功', 200);

} catch (Exception $e) {
    $db->rollBack();
    error_log("添加银行卡API错误: " . $e->getMessage());
    Response::error('绑定失败: ' . $e->getMessage());
}

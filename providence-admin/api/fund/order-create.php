<?php
/**
 * 创建投资订单
 * POST /fund/order/create
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
$input = json_decode(file_get_contents('php://input'), true);

$projectId = (int)($input['project_id'] ?? 0);
$amount = (float)($input['amount'] ?? 0);

if ($projectId <= 0) {
    Response::error('项目ID无效');
}

if ($amount <= 0) {
    Response::error('投资金额必须大于0');
}

$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 获取项目信息
    $project = $db->fetchOne(
        "SELECT * FROM invest_projects WHERE id = :id AND status = 1",
        ['id' => $projectId]
    );

    if (!$project) {
        $db->rollBack();
        Response::error('项目不存在或已下架');
    }

    // 检查投资金额范围
    if ($amount < $project['min_invest']) {
        $db->rollBack();
        Response::error('投资金额不能低于' . $project['min_invest']);
    }

    if ($project['max_invest'] > 0 && $amount > $project['max_invest']) {
        $db->rollBack();
        Response::error('投资金额不能超过' . $project['max_invest']);
    }

    // 检查币种
    $currency = $project['currency'];

    // 获取用户钱包余额
    $wallet = $db->fetchOne(
        "SELECT balance FROM wallet_balance WHERE user_id = :user_id AND currency = :currency",
        ['user_id' => $userId, 'currency' => $currency]
    );

    $balance = (float)($wallet['balance'] ?? 0);

    if ($balance < $amount) {
        $db->rollBack();
        Response::error('余额不足，当前' . $currency . '余额: ' . $balance);
    }

    // 计算收益
    $cycleDays = (int)$project['cycle_days'];
    $baseRate = (float)$project['base_rate'];

    // 获取用户VIP加息
    $user = $db->fetchOne("SELECT vip_level FROM users WHERE id = :id", ['id' => $userId]);
    $vipLevel = (int)($user['vip_level'] ?? 0);

    $vipExtraRate = 0;
    if ($vipLevel > 0) {
        $vipRule = $db->fetchOne(
            "SELECT vip_extra_rate FROM vip_level_rules WHERE level = :level",
            ['level' => $vipLevel]
        );
        $vipExtraRate = (float)($vipRule['vip_extra_rate'] ?? 0);
    }

    $totalRate = $baseRate + $vipExtraRate;
    $expectedProfit = $amount * ($totalRate / 100);

    // 生成订单号
    $orderNo = 'INV' . date('YmdHis') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // 计算开始和结束时间
    $startDate = date('Y-m-d H:i:s');
    $endDate = date('Y-m-d H:i:s', strtotime("+{$cycleDays} days"));

    // 创建订单
    $orderData = [
        'order_no' => $orderNo,
        'user_id' => $userId,
        'project_id' => $projectId,
        'amount' => $amount,
        'expected_profit' => $expectedProfit,
        'earned_amount' => 0,
        'cycle_days' => $cycleDays,
        'base_rate' => $baseRate,
        'vip_extra_rate' => $vipExtraRate,
        'total_rate' => $totalRate,
        'currency' => $currency,
        'status' => 1, // 直接进行中
        'start_at' => $startDate,
        'end_at' => $endDate,
        'created_at' => date('Y-m-d H:i:s')
    ];

    $orderId = $db->insert('invest_orders', $orderData);

    if (!$orderId) {
        throw new Exception('订单创建失败');
    }

    // 扣除余额
    $success = $db->update(
        'wallet_balance',
        ['balance' => $balance - $amount, 'frozen' => $balance - $amount],
        'user_id = :user_id AND currency = :currency',
        ['user_id' => $userId, 'currency' => $currency]
    );

    if (!$success) {
        throw new Exception('余额扣除失败');
    }

    // 记录钱包流水
    $db->insert('wallet_logs', [
        'user_id' => $userId,
        'type' => 'INVEST',
        'amount' => -$amount,
        'balance_after' => $balance - $amount,
        'currency' => $currency,
        'description' => '投资项目：' . $project['title'],
        'related_id' => $orderId,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // 更新用户累计投资
    $db->execute(
        "UPDATE users SET total_invest = COALESCE(total_invest, 0) + :amount WHERE id = :id",
        ['amount' => $amount, 'id' => $userId]
    );

    // VIP自动升级检查
    // TODO: 调用 VipService::checkAndUpgrade($userId)

    // 审计日志
    AuditLog::log('order', '用户创建投资订单', 'USER', $userId, 'invest_orders', $orderId, [
        'order_no' => $orderNo,
        'project_id' => $projectId,
        'amount' => $amount,
        'currency' => $currency
    ]);

    $db->commit();

    Response::success([
        'order_id' => $orderId,
        'order_no' => $orderNo,
        'amount' => $amount,
        'expected_profit' => $expectedProfit,
        'start_date' => $startDate,
        'end_date' => $endDate
    ], '投资成功');

} catch (Exception $e) {
    $db->rollBack();
    error_log("创建订单API错误: " . $e->getMessage());
    Response::error('投资失败: ' . $e->getMessage());
}

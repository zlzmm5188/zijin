<?php

/**
 * 投资项目 (SRS + 缓存优化)
 * POST /fund/project/add
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$authUser = Auth::user();
if (!$authUser) {
    Response::error('未登录或登录已过期', 401);
}

// 检查幂等性键（支持多种方式获取）
$idempotencyKey = $_SERVER['HTTP_IDEMPOTENCY_KEY'] ??
    $_SERVER['HTTP_IDEMPOTENCY_KEY'] ??
    ($_POST['idempotency_key'] ?? null) ??
    ($input['idempotency_key'] ?? null);
if ($idempotencyKey) {
    $cached = IdempotencyService::check($idempotencyKey, $authUser['user_id'], '/fund/project/add');
    if ($cached) {
        Response::success($cached['data'], '请勿重复提交');
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$input = json_decode(file_get_contents('php://input'), true);
$projectId = (int)($input['project_id'] ?? 0);
$investAmount = (float)($input['amount'] ?? 0);
$currency = $input['currency'] ?? 'CNY';

if (!$projectId || $investAmount <= 0) {
    Response::error('参数错误');
}

$db = Database::getInstance();

// ⚠️ 开启事务，所有检查都在事务内进行，防止并发问题
$db->beginTransaction();

try {
    // 获取项目（带锁，防止超卖）
    $project = $db->fetchOne(
        "SELECT * FROM " . $db->getPrefix() . "invest_projects WHERE id = :id AND status = 1 FOR UPDATE",
        ['id' => $projectId]
    );

    if (!$project) {
        $db->rollBack();
        Response::error('项目不存在或已下架');
    }

    // 币种隔离检查
    CurrencyMiddleware::validate($currency, $project['currency'] ?? 'CNY');

    // 检查剩余额度（在事务内检查，确保准确性）
    if ($project['remain'] > 0 && $investAmount > $project['remain']) {
        $db->rollBack();
        Response::error('剩余额度不足');
    }

    // 获取用户（带锁，防止并发）
    $user = $db->fetchOne(
        "SELECT * FROM " . $db->getPrefix() . "users WHERE id = :id FOR UPDATE",
        ['id' => $authUser['user_id']]
    );

    // 获取钱包（带锁，防止并发扣款）
    $wallet = $db->fetchOne(
        "SELECT balance FROM wallets WHERE user_id = :user_id AND currency = :currency FOR UPDATE",
        ['user_id' => $authUser['user_id'], 'currency' => $currency]
    );

    if (!$wallet) {
        // 创建钱包
        $db->insert('wallets', [
            'user_id' => $authUser['user_id'],
            'currency' => $currency,
            'balance' => 0,
            'frozen' => 0,
            'total_income' => 0
        ]);
        $walletBalance = 0;
    } else {
        $walletBalance = floatval($wallet['balance'] ?? 0);
    }

    // ⚠️ 在事务内检查余额（防止并发问题）
    if ($walletBalance < $investAmount) {
        $db->rollBack();
        Response::error('余额不足');
    }

    // ⚠️ 检查是否在短时间内重复提交相同订单（防连击）
    $recentOrder = $db->fetchOne(
        "SELECT id, order_no FROM " . $db->getPrefix() . "invest_orders
         WHERE user_id = :user_id
         AND project_id = :project_id
         AND amount = :amount
         AND currency = :currency
         AND created_at > DATE_SUB(NOW(), INTERVAL 5 SECOND)
         LIMIT 1",
        [
            'user_id' => $authUser['user_id'],
            'project_id' => $projectId,
            'amount' => $investAmount,
            'currency' => $currency
        ]
    );

    if ($recentOrder) {
        $db->rollBack();
        Response::error('请勿重复提交，订单已创建：' . $recentOrder['order_no']);
    }

    // 获取VIP加息
    $vipRule = $db->fetchOne(
        "SELECT extra_rate FROM " . $db->getPrefix() . "vip_interest_rules WHERE vip_level = :level",
        ['level' => $user['vip_level']]
    );
    $vipExtraRate = $vipRule['extra_rate'] ?? 0;

    $cycleDays = $project['cycle_days'] ?? 30;
    $baseRate = $project['base_rate'] ?? 0;
    $addedRate = $project['added_rate'] ?? 0;
    $giftRate = $project['gift_rate'] ?? 0;

    // 计算最终收益率
    $finalRate = $baseRate + $vipExtraRate + $addedRate + $giftRate;

    // 使用EarningsService计算
    $earnings = EarningsService::calculateEarnings($investAmount, $baseRate, $vipExtraRate + $addedRate + $giftRate, $cycleDays);

    // ⚠️ 计算每日收益率（用于每日收益发放）
    // final_daily_rate = (final_rate / 100) / cycle_days
    // 例如：总收益率10%，周期30天，每日收益率 = 10% / 30 = 0.3333%
    $finalDailyRate = $cycleDays > 0 ? ($finalRate / 100) / $cycleDays : 0;

    // 生成唯一订单号（确保唯一性）
    $orderNo = 'INV' . date('YmdHis') . rand(10000, 99999) . $authUser['user_id'];
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime("+{$cycleDays} days"));

    // ⚠️ 检查订单号是否已存在（双重保险）
    $existingOrder = $db->fetchOne(
        "SELECT id FROM " . $db->getPrefix() . "invest_orders WHERE order_no = :order_no LIMIT 1",
        ['order_no' => $orderNo]
    );
    if ($existingOrder) {
        // 如果订单号已存在，重新生成
        $orderNo = 'INV' . date('YmdHis') . rand(100000, 999999) . $authUser['user_id'];
    }

    // 创建订单（包含 final_daily_rate 字段）
    $orderData = [
        'order_no' => $orderNo,
        'user_id' => $user['id'],
        'project_id' => $projectId,
        'currency' => $currency,
        'amount' => $investAmount,
        'vip_level' => $user['vip_level'],
        'base_rate' => $baseRate,
        'vip_extra_rate' => $vipExtraRate,
        'added_rate' => $addedRate,
        'gift_rate' => $giftRate,
        'final_rate' => $finalRate,
        'final_daily_rate' => $finalDailyRate, // ⚠️ 添加每日收益率
        'cycle_days' => $cycleDays,
        'expected_profit' => $earnings['profit'],
        'status' => 'RUNNING',
        'start_at' => $startDate,
        'end_at' => $endDate
    ];

    $orderId = $db->insert('invest_orders', $orderData);

    // 扣除钱包余额（已在事务内获取并锁定）
    $newWalletBalance = bcsub($walletBalance, $investAmount, 8);
    $db->update('wallets', [
        'balance' => $newWalletBalance
    ], ['user_id' => $user['id'], 'currency' => $currency]);

    // 同步更新 users 表的余额字段
    if ($currency === 'CNY') {
        $db->update('users', ['balance_cny' => $newWalletBalance], ['id' => $user['id']]);
    } else {
        $db->update('users', ['balance_usdt' => $newWalletBalance], ['id' => $user['id']]);
    }

    // 更新项目统计
    $db->query(
        "UPDATE " . $db->getPrefix() . "invest_projects
         SET invest_count = invest_count + 1, total_invested = total_invested + :amount
         WHERE id = :id",
        ['amount' => $investAmount, 'id' => $projectId]
    );

    // 【更新项目缓存】⭐
    ProjectCacheService::updateCache($projectId);

    // VIP升级
    VipService::checkAndUpgrade($user['id']);

    // 审计日志
    AuditLog::log('project', '投资项目', 'USER', $user['id'], 'invest_order', $orderId);

    $db->commit();

    $result = [
        'order_id' => $orderId,
        'order_no' => $orderNo,
        'invest_amount' => $investAmount,
        'currency' => $currency,
        'profit' => $earnings['profit'],
        'total' => $earnings['total'],
        'daily_profit' => $earnings['daily_profit'],
        'end_date' => $endDate
    ];

    // 保存幂等性响应
    if ($idempotencyKey) {
        IdempotencyService::save($idempotencyKey, $authUser['user_id'], '/fund/project/add', $result);
    }

    Response::success($result, '投资成功');
} catch (Exception $e) {
    $db->rollBack();
    Response::error('投资失败: ' . $e->getMessage());
}

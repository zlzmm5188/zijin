<?php

/**
 * 清理所有历史数据（重新开始）
 * POST /api/admin/clear-all-data.php
 *
 * ⚠️ 危险操作：将删除所有业务数据，但保留管理员账户和基础配置
 */

require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员权限
$authUser = Auth::user();
if (!$authUser) {
    Response::error('请先登录', -1);
}

// 检查是否为管理员（使用is_internal字段）
$db = Database::getInstance();
$adminCheck = $db->fetchOne(
    "SELECT is_internal FROM users WHERE id = :id",
    ['id' => $authUser['user_id']]
);

if (!$adminCheck || !$adminCheck['is_internal']) {
    Response::error('无权限访问', 403);
}

// 只接受POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('只支持POST请求', -1);
}

// 获取请求数据
$input = json_decode(file_get_contents('php://input'), true);
if (!$input && !empty($_POST)) {
    $input = $_POST;
}

$confirm = isset($input['confirm']) ? (bool)$input['confirm'] : false;
$clearType = trim($input['type'] ?? 'business'); // business: 业务数据, all: 全部数据

if (!$confirm) {
    Response::error('请确认操作', -1);
}

$result = [];
$errors = [];

try {
    $db->beginTransaction();

    if ($clearType === 'all') {
        // 清理所有数据（包括内容数据）

        // 1. 清理用户数据（保留内部管理员账户 is_internal=1）
        $db->execute("DELETE FROM users WHERE is_internal = 0 OR is_internal IS NULL");
        $result['users'] = '已清理所有普通用户数据（保留管理员）';

        // 2. 清理投资订单
        $db->execute("DELETE FROM invest_orders");
        $result['orders'] = '已清理所有投资订单';

        // 3. 清理钱包记录（保留管理员钱包）
        $db->execute("DELETE FROM wallets WHERE user_id NOT IN (SELECT id FROM users WHERE is_internal = 1)");
        $result['wallets'] = '已清理所有钱包数据（保留管理员钱包）';

        // 4. 清理充值记录
        $db->execute("DELETE FROM recharges");
        $result['recharges'] = '已清理所有充值记录';

        // 5. 清理提现记录
        $db->execute("DELETE FROM withdrawals");
        $result['withdrawals'] = '已清理所有提现记录';

        // 6. 清理日利宝账户（保留管理员）
        $db->execute("DELETE FROM ribao_accounts WHERE user_id NOT IN (SELECT id FROM users WHERE is_internal = 1)");
        $result['ribao'] = '已清理所有日利宝账户（保留管理员）';

        // 7. 清理登录日志
        $db->execute("DELETE FROM login_logs");
        $result['login_logs'] = '已清理所有登录日志';

        // 8. 清理公司动态
        $db->execute("DELETE FROM company_news");
        $result['company_news'] = '已清理所有公司动态';

        // 9. 清理新闻快讯
        $db->execute("DELETE FROM news");
        $result['news'] = '已清理所有新闻快讯';

        // 10. 清理投资课堂
        $db->execute("DELETE FROM education_courses");
        $result['education'] = '已清理所有投资课堂';

        // 11. 清理积分兑换记录
        $db->execute("DELETE FROM points_exchange");
        $result['points_exchange'] = '已清理所有积分兑换记录';

        // 12. 清理钱包流水
        $db->execute("DELETE FROM wallet_logs");
        $result['wallet_logs'] = '已清理所有钱包流水';
    } else {
        // 只清理业务数据（用户、充值、提现、订单等，保留内容数据）

        // 1. 清理用户数据（保留内部管理员账户 is_internal=1）
        $db->execute("DELETE FROM users WHERE is_internal = 0 OR is_internal IS NULL");
        $result['users'] = '已清理所有普通用户数据（保留管理员）';

        // 2. 清理投资订单
        $db->execute("DELETE FROM invest_orders");
        $result['orders'] = '已清理所有投资订单';

        // 3. 清理钱包记录（保留管理员钱包）
        $db->execute("DELETE FROM wallets WHERE user_id NOT IN (SELECT id FROM users WHERE is_internal = 1)");
        $result['wallets'] = '已清理所有钱包数据（保留管理员钱包）';

        // 4. 清理充值记录
        $db->execute("DELETE FROM recharges");
        $result['recharges'] = '已清理所有充值记录';

        // 5. 清理提现记录
        $db->execute("DELETE FROM withdrawals");
        $result['withdrawals'] = '已清理所有提现记录';

        // 6. 清理日利宝账户（保留管理员）
        $db->execute("DELETE FROM ribao_accounts WHERE user_id NOT IN (SELECT id FROM users WHERE is_internal = 1)");
        $result['ribao'] = '已清理所有日利宝账户（保留管理员）';

        // 7. 清理登录日志
        $db->execute("DELETE FROM login_logs");
        $result['login_logs'] = '已清理所有登录日志';

        // 8. 清理钱包流水
        $db->execute("DELETE FROM wallet_logs");
        $result['wallet_logs'] = '已清理所有钱包流水';

        // 9. 清理积分兑换记录
        $db->execute("DELETE FROM points_exchange");
        $result['points_exchange'] = '已清理所有积分兑换记录';

        // 10. 重置项目投资数据（保留项目配置）
        $db->execute("UPDATE invest_projects SET schedule = 0, invest_count = 0, total_invested = 0, view_count = 0");
        $result['projects'] = '已重置项目投资数据（保留项目配置）';

        // 注意：不清理公司动态、新闻快讯、投资课堂等内容数据
    }

    // 提交事务
    $db->commit();

    // 记录操作日志
    error_log(sprintf(
        "[数据清理] 管理员 %s (ID: %d) 于 %s 执行了全量数据清理",
        $authUser['username'] ?? 'unknown',
        $authUser['user_id'],
        date('Y-m-d H:i:s')
    ));

    Response::success($result, '所有数据已清理完成，系统已重置');
} catch (Exception $e) {
    $db->rollBack();
    error_log("清理所有数据失败: " . $e->getMessage());
    Response::error('清理失败: ' . $e->getMessage());
}

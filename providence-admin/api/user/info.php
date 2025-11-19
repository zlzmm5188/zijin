<?php
/**
 * 获取用户信息
 * GET /user/user/index
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证用户登录
// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

$userId = $authUser['user_id']; // 从token中获取user_id
$db = Database::getInstance();

try {
    // 获取用户完整信息
    $sql = "SELECT
                id,
                uid,
                username,
                phone,
                email,
                realname_status,
                vip_level,
                total_invest,
                is_internal,
                status,
                parent_id,
                created_at,
                updated_at
            FROM users
            WHERE id = :id";

    $user = $db->fetchOne($sql, ['id' => $userId]);

    if (!$user) {
        Response::error('用户不存在');
    }

    // 格式化数据
    $user['id'] = (int)$user['id'];
    $user['vip_level'] = (int)$user['vip_level'];
    $user['total_invest'] = (float)$user['total_invest'];
    $user['realname_status'] = (int)$user['realname_status'];
    $user['is_internal'] = (int)$user['is_internal'];
    $user['status'] = (int)$user['status'];

    // 获取钱包余额
    $walletSql = "SELECT
                    SUM(CASE WHEN currency = 'CNY' THEN balance ELSE 0 END) as cny_balance,
                    SUM(CASE WHEN currency = 'USDT' THEN balance ELSE 0 END) as usdt_balance
                  FROM wallets
                  WHERE user_id = :user_id";

    $wallet = $db->fetchOne($walletSql, ['user_id' => $userId]);

    $user['cny_balance'] = $wallet ? (float)$wallet['cny_balance'] : 0;
    $user['usdt_balance'] = $wallet ? (float)$wallet['usdt_balance'] : 0;

    // 获取邀请人信息
    if (!empty($user['parent_id'])) {
        $parentSql = "SELECT uid, username FROM users WHERE id = :id";
        $parent = $db->fetchOne($parentSql, ['id' => $user['parent_id']]);
        $user['parent_uid'] = $parent ? $parent['uid'] : null;
        $user['parent_username'] = $parent ? $parent['username'] : null;
    } else {
        $user['parent_uid'] = null;
        $user['parent_username'] = null;
    }

    // 获取下级人数
    $teamCountSql = "SELECT COUNT(*) as count FROM users WHERE parent_id = :parent_id";
    $teamCount = $db->fetchOne($teamCountSql, ['parent_id' => $userId]);
    $user['team_count'] = (int)($teamCount['count'] ?? 0);

    // 获取进行中订单数
    $runningOrdersSql = "SELECT COUNT(*) as count FROM invest_orders WHERE user_id = :user_id AND status = 'RUNNING'";
    $runningOrders = $db->fetchOne($runningOrdersSql, ['user_id' => $userId]);
    $user['running_orders'] = (int)($runningOrders['count'] ?? 0);

    // 获取总收益
    $totalProfitSql = "SELECT SUM(earned_amount) as total FROM invest_orders WHERE user_id = :user_id";
    $totalProfit = $db->fetchOne($totalProfitSql, ['user_id' => $userId]);
    $user['total_profit'] = (float)($totalProfit['total'] ?? 0);

    // 获取日利宝余额（从wallets表的ribao_balance字段）
    $ribaoBalanceSql = "SELECT ribao_balance FROM wallets WHERE user_id = :user_id AND currency = 'CNY'";
    $ribaoWallet = $db->fetchOne($ribaoBalanceSql, ['user_id' => $userId]);
    $ribaoBalance = $ribaoWallet && isset($ribaoWallet['ribao_balance']) ? (float)$ribaoWallet['ribao_balance'] : 0;

    // 前端兼容字段：添加前端期望的字段名
    $user['money'] = $user['cny_balance'];  // 钱包余额（CNY可用余额）
    $user['ribao'] = $ribaoBalance;  // 日利宝余额（从wallets.ribao_balance获取）
    $user['tfund'] = $user['total_profit'];  // 总收益（投资订单累计收益）
    $user['level'] = $user['vip_level'];  // VIP等级

    // VIP等级名称
    $vipNames = ['普通会员', 'VIP1', 'VIP2', 'VIP3', 'VIP4', 'VIP5', 'VIP6', 'VIP7', 'VIP8'];
    $user['vip_name'] = $vipNames[$user['vip_level']] ?? '普通会员';

    // 实名状态文本
    $realnameStatusTexts = ['未实名', '审核中', '已实名', '已拒绝'];
    $user['realname_status_text'] = $realnameStatusTexts[$user['realname_status']] ?? '未实名';

    Response::success($user);

} catch (Exception $e) {
    error_log("获取用户信息API错误: " . $e->getMessage());
    Response::error('获取用户信息失败: ' . $e->getMessage());
}

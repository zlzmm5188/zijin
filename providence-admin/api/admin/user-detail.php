<?php
/**
 * 用户详情API
 * GET /api/admin/user-detail.php?id=1
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$userId = (int)($_GET['id'] ?? 0);

if ($userId <= 0) {
    Response::error('用户ID无效');
}

$db = Database::getInstance();

try {
    // 查询用户基本信息
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE id = :id",
        ['id' => $userId]
    );

    if (!$user) {
        Response::error('用户不存在');
    }

    // 格式化数据
    $user['id'] = (int)$user['id'];
    $user['vip_level'] = (int)$user['vip_level'];
    $user['total_invest'] = (float)$user['total_invest'];
    $user['status'] = (int)$user['status'];
    $user['is_internal'] = (int)$user['is_internal'];
    $user['realname_status'] = (int)$user['realname_status'];
    $user['parent_id'] = $user['parent_id'] ? (int)$user['parent_id'] : null;

    // 获取上级信息
    if ($user['parent_id']) {
        $parent = $db->fetchOne(
            "SELECT id, username, uid FROM users WHERE id = :id",
            ['id' => $user['parent_id']]
        );
        $user['parent_info'] = $parent;
    } else {
        $user['parent_info'] = null;
    }

    // 获取实名信息
    $kyc = $db->fetchOne(
        "SELECT * FROM user_kyc WHERE user_id = :uid",
        ['uid' => $userId]
    );
    $user['kyc_info'] = $kyc ?: null;

    // 获取钱包信息
    $wallets = $db->fetchAll(
        "SELECT * FROM wallets WHERE user_id = :uid",
        ['uid' => $userId]
    );

    $user['wallets'] = [];
    foreach ($wallets as $wallet) {
        $user['wallets'][$wallet['currency']] = [
            'balance' => (float)$wallet['balance'],
            'frozen' => (float)$wallet['frozen']
        ];
    }

    // 统计数据
    // 下级人数
    $user['children_count'] = $db->count('users', 'parent_id = :id', ['id' => $userId]);

    // 团队总人数（递归）
    function getTeamCount($db, $userId) {
        $direct = $db->fetchAll(
            "SELECT id FROM users WHERE parent_id = :pid",
            ['pid' => $userId]
        );

        $count = count($direct);
        foreach ($direct as $child) {
            $count += getTeamCount($db, $child['id']);
        }

        return $count;
    }

    $user['team_count'] = getTeamCount($db, $userId);

    // 投资统计
    $investStats = $db->fetchOne(
        "SELECT
            COUNT(*) as order_count,
            SUM(invest_amount) as total_invest,
            SUM(CASE WHEN status = 'running' THEN invest_amount ELSE 0 END) as running_invest
        FROM invest_orders
        WHERE user_id = :uid",
        ['uid' => $userId]
    );

    $user['invest_stats'] = [
        'order_count' => (int)($investStats['order_count'] ?? 0),
        'total_invest' => (float)($investStats['total_invest'] ?? 0),
        'running_invest' => (float)($investStats['running_invest'] ?? 0)
    ];

    // 充值统计
    $rechargeStats = $db->fetchOne(
        "SELECT
            COUNT(*) as count,
            SUM(amount) as total
        FROM recharge_records
        WHERE user_id = :uid AND status = 1",
        ['uid' => $userId]
    );

    $user['recharge_stats'] = [
        'count' => (int)($rechargeStats['count'] ?? 0),
        'total' => (float)($rechargeStats['total'] ?? 0)
    ];

    // 提现统计
    $withdrawStats = $db->fetchOne(
        "SELECT
            COUNT(*) as count,
            SUM(amount) as total
        FROM withdraw_records
        WHERE user_id = :uid AND status = 1",
        ['uid' => $userId]
    );

    $user['withdraw_stats'] = [
        'count' => (int)($withdrawStats['count'] ?? 0),
        'total' => (float)($withdrawStats['total'] ?? 0)
    ];

    Response::success($user, '获取成功');

} catch (Exception $e) {
    error_log("用户详情API错误: " . $e->getMessage());
    Response::error('查询失败: ' . $e->getMessage());
}

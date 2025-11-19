<?php
/**
 * 清空数据API
 * POST /api/admin/clear-data
 * 清空充值、注册、项目等数据，并设置G138688为创始人
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员权限 - 已禁用：无登录模式
$authUser = Auth::user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$input = json_decode(file_get_contents('php://input'), true);
$confirm = isset($input['confirm']) && $input['confirm'] === 'YES_CLEAR_ALL_DATA';

if (!$confirm) {
    Response::error('请确认清空操作');
}

$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 1. 清空项目9的订单（先清空关联订单）
    error_log("开始清空项目9的订单...");
    $db->query("DELETE FROM invest_orders WHERE project_id = 9");
    $deletedOrders = $db->query("SELECT ROW_COUNT()")->fetchColumn();
    error_log("已删除项目9的订单: {$deletedOrders} 条");

    // 2. 删除项目9
    error_log("删除项目9...");
    $db->query("DELETE FROM invest_projects WHERE id = 9");
    error_log("项目9已删除");

    // 3. 清空所有投资订单（包括invest_orders和user_investments）
    error_log("清空所有投资订单...");
    $db->query("DELETE FROM invest_orders");
    // 清空user_investments表（如果存在）
    try {
        $result = $db->query("SHOW TABLES LIKE 'user_investments'");
        if ($result && $result->rowCount() > 0) {
            $db->query("DELETE FROM user_investments");
            error_log("已清空 user_investments 表");
        }
    } catch (Exception $e) {
        // 表不存在，忽略
    }
    error_log("所有投资订单已清空");

    // 4. 清空充值记录
    error_log("清空充值记录...");
    $db->query("DELETE FROM recharge_records");
    error_log("充值记录已清空");

    // 5. 清空提现记录
    error_log("清空提现记录...");
    $db->query("DELETE FROM withdraw_records");
    error_log("提现记录已清空");

    // 6. 清空钱包流水
    error_log("清空钱包流水...");
    $db->query("DELETE FROM wallet_logs");
    error_log("钱包流水已清空");

    // 7. 清空所有用户（除了G138688）
    error_log("清空所有用户（保留G138688）...");
    $db->query("DELETE FROM users WHERE username != 'G138688' AND uid != 'G138688'");
    error_log("其他用户已清空");

    // 8. 设置G138688为创始人
    error_log("设置G138688为创始人...");
    $founder = $db->fetchOne("SELECT * FROM users WHERE username = 'G138688' OR uid = 'G138688'");

    if ($founder) {
        // 更新G138688的信息（兼容uid和referral_code字段）
        $updateData = [
            'vip_level' => 10,
            'balance_cny' => 0,
            'balance_usdt' => 0,
            'points' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // 检查是否有uid字段（邀请码字段）
        $columns = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'uid'");
        if (!empty($columns)) {
            $updateData['uid'] = 'G138688';
        }

        // 检查是否有referral_code字段
        $columns = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'referral_code'");
        if (!empty($columns)) {
            $updateData['referral_code'] = 'G138688';
        }

        // 检查是否有is_founder字段
        $columns = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'is_founder'");
        if (!empty($columns)) {
            $updateData['is_founder'] = 1;
        }

        $where = "username = 'G138688' OR uid = 'G138688'";
        $db->update('users', $updateData, $where);
        error_log("G138688已设置为创始人");
    } else {
        // 如果G138688不存在，创建创始人账户
        $insertData = [
            'username' => 'G138688',
            'phone' => '13868800000',
            'email' => 'founder@copla.top',
            'vip_level' => 10,
            'balance_cny' => 0,
            'balance_usdt' => 0,
            'points' => 0,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // 检查字段并添加
        $columns = $db->fetchAll("SHOW COLUMNS FROM users");
        $columnNames = array_column($columns, 'Field');

        if (in_array('uid', $columnNames)) {
            $insertData['uid'] = 'G138688';
        }
        if (in_array('referral_code', $columnNames)) {
            $insertData['referral_code'] = 'G138688';
        }
        if (in_array('is_founder', $columnNames)) {
            $insertData['is_founder'] = 1;
        }
        if (in_array('password', $columnNames)) {
            // 设置默认密码（需要根据实际情况调整）
            $insertData['password'] = password_hash('G138688@2024', PASSWORD_DEFAULT);
        }

        $db->insert('users', $insertData);
        error_log("已创建G138688创始人账户");
    }

    // 9. 清空其他相关表
    error_log("清空其他相关表...");
    $db->query("DELETE FROM user_sign_logs");
    $db->query("DELETE FROM referral_rewards");
    $db->query("DELETE FROM team_rewards");
    $db->query("DELETE FROM kyc_records");
    $db->query("DELETE FROM audit_log");
    error_log("其他相关表已清空");

    // 10. 清空所有项目（包括invest_projects和projects）
    error_log("清空所有项目...");
    $db->query("DELETE FROM invest_projects");
    // 清空projects表（如果存在）
    try {
        $result = $db->query("SHOW TABLES LIKE 'projects'");
        if ($result && $result->rowCount() > 0) {
            $db->query("DELETE FROM projects");
            error_log("已清空 projects 表");
        }
    } catch (Exception $e) {
        // 表不存在，忽略
    }
    error_log("所有项目已清空");

    // 11. 重置所有用户的余额（包括G138688）
    error_log("重置所有用户余额...");
    $db->query("UPDATE users SET balance_cny = 0, balance_usdt = 0, points = 0, total_invest = 0");
    error_log("所有用户余额已重置");

    // 12. 清空钱包表（如果有独立的钱包表）
    $walletTables = ['wallets', 'user_wallets', 'wallet_accounts'];
    foreach ($walletTables as $table) {
        try {
            $result = $db->query("SHOW TABLES LIKE '{$table}'");
            if ($result && $result->rowCount() > 0) {
                $db->query("DELETE FROM {$table}");
                error_log("已清空 {$table} 表");
            }
        } catch (Exception $e) {
            // 表不存在，忽略
        }
    }

    // 13. 清空项目相关的其他表
    $projectRelatedTables = ['project_managers', 'project_configs', 'project_earnings'];
    foreach ($projectRelatedTables as $table) {
        try {
            $result = $db->query("SHOW TABLES LIKE '{$table}'");
            if ($result && $result->rowCount() > 0) {
                $db->query("DELETE FROM {$table}");
                error_log("已清空 {$table} 表");
            }
        } catch (Exception $e) {
            // 表不存在，忽略
        }
    }

    $db->commit();

    // 记录审计日志
    if (class_exists('AuditLog')) {
        AuditLog::log(
            'system',
            '清空所有数据并设置创始人',
            'ADMIN',
            $authUser['user_id'] ?? 1,
            'system',
            0,
            null,
            ['founder' => 'G138688', 'cleared_at' => date('Y-m-d H:i:s')]
        );
    }

    Response::success([
        'cleared' => [
            'orders' => '所有投资订单（invest_orders, user_investments）',
            'recharges' => '所有充值记录',
            'withdraws' => '所有提现记录',
            'wallet_logs' => '所有钱包流水',
            'users' => '所有用户（保留G138688）',
            'user_balances' => '所有用户余额已重置为0',
            'projects' => '所有项目（invest_projects, projects）',
            'wallets' => '所有钱包表',
            'other' => '签到、返利、KYC等记录'
        ],
        'founder' => [
            'username' => 'G138688',
            'referral_code' => 'G138688',
            'vip_level' => 10,
            'is_founder' => 1
        ]
    ], '数据清空成功，G138688已设置为创始人');

} catch (Exception $e) {
    $db->rollBack();
    error_log("清空数据失败: " . $e->getMessage());
    Response::error('清空失败: ' . $e->getMessage());
}

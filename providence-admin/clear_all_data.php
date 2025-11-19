<?php
/**
 * 一键清除所有后台数据
 * 包括：注册、实名、资金统计、项目等
 *
 * 使用方法：直接在浏览器访问此文件，或通过命令行执行
 */

require_once __DIR__ . '/config/bootstrap.php';

// 安全检查：只允许POST请求或命令行执行
if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('请使用POST请求或命令行执行此脚本');
}

// 如果是浏览器访问，需要确认
if (php_sapi_name() !== 'cli' && (!isset($_POST['confirm']) || $_POST['confirm'] !== 'YES_CLEAR_ALL_DATA')) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>清除所有数据 - 确认</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 600px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .warning-box {
                background: #fff3cd;
                border: 2px solid #ffc107;
                border-radius: 8px;
                padding: 20px;
                margin-bottom: 20px;
            }
            .warning-box h2 {
                color: #856404;
                margin-top: 0;
            }
            .warning-box ul {
                color: #856404;
                line-height: 1.8;
            }
            form {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            input[type="text"] {
                width: 100%;
                padding: 10px;
                border: 2px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
                margin: 10px 0;
            }
            button {
                background: #dc3545;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 4px;
                font-size: 16px;
                cursor: pointer;
                width: 100%;
            }
            button:hover {
                background: #c82333;
            }
        </style>
    </head>
    <body>
        <div class="warning-box">
            <h2>⚠️ 危险操作警告</h2>
            <p>此操作将清除以下所有数据：</p>
            <ul>
                <li>所有用户注册数据（保留G138688）</li>
                <li>所有实名认证数据（KYC记录）</li>
                <li>所有充值记录</li>
                <li>所有提现记录</li>
                <li>所有投资订单</li>
                <li>所有项目数据</li>
                <li>所有资金统计和钱包数据</li>
                <li>所有返利、签到等记录</li>
            </ul>
            <p><strong>此操作不可恢复！</strong></p>
        </div>
        <form method="POST">
            <p>请输入 <strong>YES_CLEAR_ALL_DATA</strong> 确认清除：</p>
            <input type="text" name="confirm" placeholder="请输入确认文字" required>
            <button type="submit">确认清除所有数据</button>
        </form>
    </body>
    </html>
    <?php
    exit;
}

$db = Database::getInstance();

try {
    echo "开始清除所有数据...\n";
    $db->beginTransaction();

    // 1. 清空所有投资订单
    echo "1. 清空投资订单...\n";
    $db->query("DELETE FROM invest_orders");
    try {
        $result = $db->query("SHOW TABLES LIKE 'user_investments'");
        if ($result && !empty($result->fetchAll())) {
            $db->query("DELETE FROM user_investments");
        }
    } catch (Exception $e) {}
    echo "   ✓ 投资订单已清空\n";

    // 2. 清空充值记录
    echo "2. 清空充值记录...\n";
    $db->query("DELETE FROM recharge_records");
    echo "   ✓ 充值记录已清空\n";

    // 3. 清空提现记录
    echo "3. 清空提现记录...\n";
    $db->query("DELETE FROM withdraw_records");
    echo "   ✓ 提现记录已清空\n";

    // 4. 清空钱包流水
    echo "4. 清空钱包流水...\n";
    $db->query("DELETE FROM wallet_logs");
    echo "   ✓ 钱包流水已清空\n";

    // 5. 清空实名认证数据（KYC记录）
    echo "5. 清空实名认证数据...\n";
    $db->query("DELETE FROM kyc_records");
    echo "   ✓ 实名认证数据已清空\n";

    // 6. 清空所有用户（除了G138688）
    echo "6. 清空所有用户（保留G138688）...\n";
    $db->query("DELETE FROM users WHERE (username != 'G138688' AND uid != 'G138688') OR (username IS NULL AND uid IS NULL)");
    echo "   ✓ 其他用户已清空\n";

    // 7. 设置G138688为创始人并重置余额
    echo "7. 设置G138688为创始人...\n";
    $founder = $db->fetchOne("SELECT * FROM users WHERE username = 'G138688' OR uid = 'G138688' LIMIT 1");

    if ($founder) {
        $updateData = [
            'vip_level' => 10,
            'balance_cny' => 0,
            'balance_usdt' => 0,
            'points' => 0,
            'total_invest' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $columns = $db->fetchAll("SHOW COLUMNS FROM users");
        $columnNames = array_column($columns, 'Field');

        if (in_array('uid', $columnNames)) {
            $updateData['uid'] = 'G138688';
        }
        if (in_array('referral_code', $columnNames)) {
            $updateData['referral_code'] = 'G138688';
        }
        if (in_array('is_founder', $columnNames)) {
            $updateData['is_founder'] = 1;
        }

        $where = "username = 'G138688' OR uid = 'G138688'";
        $db->update('users', $updateData, $where);
        echo "   ✓ G138688已设置为创始人\n";
    } else {
        // 创建创始人账户
        $insertData = [
            'username' => 'G138688',
            'phone' => '13868800000',
            'email' => 'founder@copla.top',
            'vip_level' => 10,
            'balance_cny' => 0,
            'balance_usdt' => 0,
            'points' => 0,
            'total_invest' => 0,
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

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
            $insertData['password'] = password_hash('G138688@2024', PASSWORD_DEFAULT);
        }

        $db->insert('users', $insertData);
        echo "   ✓ 已创建G138688创始人账户\n";
    }

    // 8. 清空所有项目
    echo "8. 清空所有项目...\n";
    $db->query("DELETE FROM invest_projects");
    try {
        $result = $db->query("SHOW TABLES LIKE 'projects'");
        if ($result && !empty($result->fetchAll())) {
            $db->query("DELETE FROM projects");
        }
    } catch (Exception $e) {}
    echo "   ✓ 所有项目已清空\n";

    // 9. 清空其他相关表
    echo "9. 清空其他相关表...\n";
    $tables = [
        'user_sign_logs',
        'referral_rewards',
        'team_rewards',
        'audit_log',
        'earnings_records',
        'ribao_logs'
    ];
    foreach ($tables as $table) {
        try {
            $db->query("DELETE FROM {$table}");
        } catch (Exception $e) {}
    }
    echo "   ✓ 其他相关表已清空\n";

    // 10. 清空钱包表
    echo "10. 清空钱包表...\n";
    $walletTables = ['wallets', 'user_wallets', 'wallet_accounts'];
    foreach ($walletTables as $table) {
        try {
            $result = $db->query("SHOW TABLES LIKE '{$table}'");
            if ($result && !empty($result->fetchAll())) {
                $db->query("DELETE FROM {$table}");
            }
        } catch (Exception $e) {}
    }
    echo "   ✓ 钱包表已清空\n";

    // 11. 清空项目相关表
    echo "11. 清空项目相关表...\n";
    $projectTables = ['project_managers', 'project_configs', 'project_earnings', 'project_categories'];
    foreach ($projectTables as $table) {
        try {
            $result = $db->query("SHOW TABLES LIKE '{$table}'");
            if ($result && !empty($result->fetchAll())) {
                $db->query("DELETE FROM {$table}");
            }
        } catch (Exception $e) {}
    }
    echo "   ✓ 项目相关表已清空\n";

    // 12. 重置所有用户余额（双重保险）
    echo "12. 重置所有用户余额...\n";
    $db->query("UPDATE users SET balance_cny = 0, balance_usdt = 0, points = 0, total_invest = 0");
    echo "   ✓ 所有用户余额已重置为0\n";

    $db->commit();

    echo "\n✅ 所有数据清除完成！\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "已清除：\n";
    echo "  ✓ 所有用户注册数据（保留G138688）\n";
    echo "  ✓ 所有实名认证数据（KYC记录）\n";
    echo "  ✓ 所有充值记录\n";
    echo "  ✓ 所有提现记录\n";
    echo "  ✓ 所有投资订单\n";
    echo "  ✓ 所有项目数据\n";
    echo "  ✓ 所有资金统计和钱包数据\n";
    echo "  ✓ 所有返利、签到等记录\n";
    echo "\n创始人账户：G138688（VIP10）\n";

    // 如果是浏览器访问，显示成功页面
    if (php_sapi_name() !== 'cli') {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>清除完成</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    max-width: 600px;
                    margin: 50px auto;
                    padding: 20px;
                    background: #f5f5f5;
                }
                .success-box {
                    background: #d4edda;
                    border: 2px solid #28a745;
                    border-radius: 8px;
                    padding: 20px;
                }
                .success-box h2 {
                    color: #155724;
                    margin-top: 0;
                }
                .success-box ul {
                    color: #155724;
                    line-height: 1.8;
                }
            </style>
        </head>
        <body>
            <div class="success-box">
                <h2>✅ 数据清除完成</h2>
                <p>所有数据已成功清除：</p>
                <ul>
                    <li>✓ 所有用户注册数据（保留G138688）</li>
                    <li>✓ 所有实名认证数据（KYC记录）</li>
                    <li>✓ 所有充值记录</li>
                    <li>✓ 所有提现记录</li>
                    <li>✓ 所有投资订单</li>
                    <li>✓ 所有项目数据</li>
                    <li>✓ 所有资金统计和钱包数据</li>
                    <li>✓ 所有返利、签到等记录</li>
                </ul>
                <p><strong>创始人账户：G138688（VIP10）</strong></p>
            </div>
        </body>
        </html>
        <?php
    }

} catch (Exception $e) {
    $db->rollBack();
    $error = "清除失败: " . $e->getMessage();
    echo "\n❌ {$error}\n";
    error_log($error);

    if (php_sapi_name() !== 'cli') {
        echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>清除失败</title></head><body>";
        echo "<h2 style='color:red;'>❌ 清除失败</h2>";
        echo "<p>{$error}</p>";
        echo "</body></html>";
    }
}

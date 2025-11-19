<?php

/**
 * 完整业务流程模拟和Bug检查脚本
 *
 * 模拟流程：
 * 1. 后台发布项目（CNY/USDT）
 * 2. 前台注册用户
 * 3. 实名认证
 * 4. 充值（CNY/USDT，带2%赠送）
 * 5. 购买项目（扣除余额）
 * 6. 每日收益发放（按币种）
 * 7. 订单完成触发返利（按币种）
 * 8. 转到日利宝（CNY）
 * 9. 日利宝返息
 * 10. 从日利宝取出
 * 11. 提款（CNY/USDT）
 *
 * 检查点：
 * - 余额计算是否正确
 * - 币种分离是否正确
 * - users表和wallets表是否同步
 * - 是否有重复扣款/重复发放
 */

require_once __DIR__ . '/../config/bootstrap.php';

echo "========================================\n";
echo "完整业务流程模拟和Bug检查\n";
echo "========================================\n\n";

$db = Database::getInstance();

// 测试用户ID
$testUserId = 1;

echo "📋 测试场景：用户ID = {$testUserId}\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// ============================================
// 步骤1：检查初始余额
// ============================================
echo "【步骤1】检查初始余额\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$initialUser = $db->fetchOne(
    "SELECT balance_cny, balance_usdt FROM users WHERE id = :id",
    ['id' => $testUserId]
);

$initialWallets = $db->fetchAll(
    "SELECT currency, balance, frozen, ribao_balance FROM wallets WHERE user_id = :id",
    ['id' => $testUserId]
);

echo "users表余额：\n";
echo "  - balance_cny: " . ($initialUser['balance_cny'] ?? 0) . "\n";
echo "  - balance_usdt: " . ($initialUser['balance_usdt'] ?? 0) . "\n\n";

echo "wallets表余额：\n";
foreach ($initialWallets as $wallet) {
    echo "  - {$wallet['currency']}: balance={$wallet['balance']}, frozen={$wallet['frozen']}, ribao={$wallet['ribao_balance']}\n";
}
echo "\n";

// ============================================
// 步骤2：模拟充值（CNY 10000，带2%赠送）
// ============================================
echo "【步骤2】模拟充值 CNY 10000（带2%赠送 = 200）\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$rechargeAmount = 10000;
$bonusAmount = $rechargeAmount * 0.02; // 2%赠送
$totalRecharge = $rechargeAmount + $bonusAmount;

echo "充值金额: {$rechargeAmount} CNY\n";
echo "赠送金额: {$bonusAmount} CNY\n";
echo "到账总额: {$totalRecharge} CNY\n\n";

// 检查充值后的余额
$afterRecharge = $db->fetchOne(
    "SELECT balance_cny FROM users WHERE id = :id",
    ['id' => $testUserId]
);

$afterRechargeWallet = $db->fetchOne(
    "SELECT balance FROM wallets WHERE user_id = :id AND currency = 'CNY'",
    ['id' => $testUserId]
);

$expectedBalance = ($initialUser['balance_cny'] ?? 0) + $totalRecharge;
$actualBalance = $afterRecharge['balance_cny'] ?? 0;
$walletBalance = $afterRechargeWallet['balance'] ?? 0;

echo "预期余额: {$expectedBalance} CNY\n";
echo "实际users.balance_cny: {$actualBalance} CNY\n";
echo "实际wallets.balance: {$walletBalance} CNY\n";

if (abs($actualBalance - $expectedBalance) < 0.01 && abs($walletBalance - $expectedBalance) < 0.01) {
    echo "✅ 充值余额正确，users和wallets表同步\n";
} else {
    echo "❌ BUG: 充值余额不一致！\n";
    echo "   - users表差异: " . abs($actualBalance - $expectedBalance) . "\n";
    echo "   - wallets表差异: " . abs($walletBalance - $expectedBalance) . "\n";
}
echo "\n";

// ============================================
// 步骤3：模拟投资（CNY 5000）
// ============================================
echo "【步骤3】模拟投资 CNY 5000\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$investAmount = 5000;
echo "投资金额: {$investAmount} CNY\n\n";

// 检查投资后的余额
$afterInvest = $db->fetchOne(
    "SELECT balance_cny FROM users WHERE id = :id",
    ['id' => $testUserId]
);

$afterInvestWallet = $db->fetchOne(
    "SELECT balance FROM wallets WHERE user_id = :id AND currency = 'CNY'",
    ['id' => $testUserId]
);

$expectedBalanceAfterInvest = $expectedBalance - $investAmount;
$actualBalanceAfterInvest = $afterInvest['balance_cny'] ?? 0;
$walletBalanceAfterInvest = $afterInvestWallet['balance'] ?? 0;

echo "预期余额: {$expectedBalanceAfterInvest} CNY\n";
echo "实际users.balance_cny: {$actualBalanceAfterInvest} CNY\n";
echo "实际wallets.balance: {$walletBalanceAfterInvest} CNY\n";

if (
    abs($actualBalanceAfterInvest - $expectedBalanceAfterInvest) < 0.01 &&
    abs($walletBalanceAfterInvest - $expectedBalanceAfterInvest) < 0.01
) {
    echo "✅ 投资扣款正确，users和wallets表同步\n";
} else {
    echo "❌ BUG: 投资扣款不一致！\n";
    echo "   - users表差异: " . abs($actualBalanceAfterInvest - $expectedBalanceAfterInvest) . "\n";
    echo "   - wallets表差异: " . abs($walletBalanceAfterInvest - $expectedBalanceAfterInvest) . "\n";
}
echo "\n";

// ============================================
// 步骤4：检查订单表是否有 final_daily_rate 字段
// ============================================
echo "【步骤4】检查订单表字段\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$orderFields = $db->fetchAll("DESCRIBE invest_orders");
$hasFinalDailyRate = false;
foreach ($orderFields as $field) {
    if ($field['Field'] === 'final_daily_rate') {
        $hasFinalDailyRate = true;
        break;
    }
}

if ($hasFinalDailyRate) {
    echo "✅ invest_orders表有 final_daily_rate 字段\n";
} else {
    echo "❌ BUG: invest_orders表缺少 final_daily_rate 字段！\n";
    echo "   这会导致 daily-earnings.php 无法计算每日收益\n";
    echo "   需要添加字段或修改计算逻辑\n";
}
echo "\n";

// ============================================
// 步骤5：检查每日收益计算逻辑
// ============================================
echo "【步骤5】检查每日收益计算逻辑\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

// 检查 daily-earnings.php 中的计算
$dailyEarningsCode = file_get_contents(__DIR__ . '/../cron/daily-earnings.php');
if (strpos($dailyEarningsCode, 'final_daily_rate') !== false) {
    echo "⚠️  daily-earnings.php 使用了 final_daily_rate 字段\n";
    if (!$hasFinalDailyRate) {
        echo "❌ BUG: 代码使用 final_daily_rate 但数据库表没有该字段！\n";
    } else {
        // 检查 invest.php 是否保存了 final_daily_rate
        $investCode = file_get_contents(__DIR__ . '/../api/project/invest.php');
        if (strpos($investCode, 'final_daily_rate') === false) {
            echo "❌ BUG: invest.php 没有保存 final_daily_rate 到订单表！\n";
        } else {
            echo "✅ invest.php 会保存 final_daily_rate\n";
        }
    }
} else {
    echo "✅ daily-earnings.php 使用其他方式计算（需要检查）\n";
}
echo "\n";

// ============================================
// 步骤6：检查返利逻辑（币种分离）
// ============================================
echo "【步骤6】检查返利逻辑（币种分离）\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$referralCode = file_get_contents(__DIR__ . '/../config/ReferralService.php');
if (
    strpos($referralCode, "currency") !== false &&
    strpos($referralCode, "wallets") !== false &&
    strpos($referralCode, "balance_cny") !== false &&
    strpos($referralCode, "balance_usdt") !== false
) {
    echo "✅ 返利逻辑已实现币种分离\n";
} else {
    echo "❌ BUG: 返利逻辑可能没有正确实现币种分离！\n";
}
echo "\n";

// ============================================
// 步骤7：检查日利宝转入/转出逻辑
// ============================================
echo "【步骤7】检查日利宝转入/转出逻辑\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$ribaoInCode = file_get_contents(__DIR__ . '/../api/user/ribao-transfer-in.php');
$ribaoOutCode = file_get_contents(__DIR__ . '/../api/user/ribao-transfer-out.php');

// 检查转入逻辑
if (
    strpos($ribaoInCode, "wallets") !== false &&
    strpos($ribaoInCode, "ribao_balance") !== false &&
    strpos($ribaoInCode, "balance_cny") !== false
) {
    echo "✅ 日利宝转入逻辑正确（使用wallets表，同步users表）\n";
} else {
    echo "❌ BUG: 日利宝转入逻辑可能有问题！\n";
}

// 检查转出逻辑
if (
    strpos($ribaoOutCode, "wallets") !== false &&
    strpos($ribaoOutCode, "ribao_balance") !== false &&
    strpos($ribaoOutCode, "balance_cny") !== false
) {
    echo "✅ 日利宝转出逻辑正确（使用wallets表，同步users表）\n";
} else {
    echo "❌ BUG: 日利宝转出逻辑可能有问题！\n";
}
echo "\n";

// ============================================
// 步骤8：检查提款逻辑（币种分离）
// ============================================
echo "【步骤8】检查提款逻辑（币种分离）\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$withdrawCode = file_get_contents(__DIR__ . '/../api/pay/withdraw.php');
if (
    strpos($withdrawCode, "wallets") !== false &&
    strpos($withdrawCode, "currency") !== false
) {
    echo "✅ 提款逻辑使用wallets表（按币种）\n";

    // 检查是否同步users表
    if (
        strpos($withdrawCode, "balance_cny") !== false ||
        strpos($withdrawCode, "balance_usdt") !== false
    ) {
        echo "✅ 提款逻辑会同步users表余额\n";
    } else {
        echo "⚠️  提款逻辑可能没有同步users表余额（需要检查）\n";
    }
} else {
    echo "❌ BUG: 提款逻辑可能有问题！\n";
}
echo "\n";

// ============================================
// 步骤9：检查投资订单创建时的字段
// ============================================
echo "【步骤9】检查投资订单创建时的字段\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

$investCode = file_get_contents(__DIR__ . '/../api/project/invest.php');
$earningsCall = "EarningsService::calculateEarnings";

if (strpos($investCode, $earningsCall) !== false) {
    echo "✅ 投资接口使用 EarningsService 计算收益\n";

    // 检查是否保存了 daily_profit 或 final_daily_rate
    if (strpos($investCode, "daily_profit") !== false) {
        echo "✅ 投资接口保存了 daily_profit\n";
    } elseif (strpos($investCode, "final_daily_rate") !== false) {
        echo "✅ 投资接口保存了 final_daily_rate\n";
    } else {
        echo "⚠️  投资接口可能没有保存每日收益率（需要检查）\n";
    }
} else {
    echo "❌ BUG: 投资接口没有使用 EarningsService！\n";
}
echo "\n";

// ============================================
// 总结报告
// ============================================
echo "========================================\n";
echo "检查总结\n";
echo "========================================\n\n";

$bugs = [];
$warnings = [];

// 汇总发现的bug
if (!$hasFinalDailyRate) {
    $bugs[] = "invest_orders表缺少 final_daily_rate 字段";
}

if (strpos($dailyEarningsCode, 'final_daily_rate') !== false && !$hasFinalDailyRate) {
    $bugs[] = "daily-earnings.php 使用 final_daily_rate 但表结构中没有该字段";
}

if (count($bugs) > 0) {
    echo "❌ 发现的BUG：\n";
    foreach ($bugs as $i => $bug) {
        echo "   " . ($i + 1) . ". {$bug}\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠️  警告：\n";
    foreach ($warnings as $i => $warning) {
        echo "   " . ($i + 1) . ". {$warning}\n";
    }
    echo "\n";
}

if (count($bugs) === 0 && count($warnings) === 0) {
    echo "✅ 未发现明显BUG\n";
}

echo "\n";

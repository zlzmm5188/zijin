<?php

/**
 * 币种兑换接口（CNY ↔ USDT）
 * POST /pay/currency-exchange
 *
 * 🔒 安全特性:
 * ✅ Token认证（已禁用：无登录模式）
 * ✅ 余额检查（原子操作）
 * ✅ 并发控制（FOR UPDATE 行锁）
 * ✅ 幂等性保证（request_id）
 * ✅ 事务处理（完整回滚）
 * ✅ 流水记录（可追溯）
 *
 * 修复内容:
 * 1. 支持 CNY → USDT 和 USDT → CNY 双向兑换
 * 2. 使用 wallets 表进行币种分离
 * 3. 同步更新 users 表余额
 * 4. 防重复提交机制
 */

require_once __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

// 🔒 验证用户登录 - 已禁用：无登录模式
$authUser = Auth::user(); // 始终返回 guest 用户
// if (!$authUser) {
//     Response::error('未登录或登录已过期', 401);
// }

$userId = $authUser['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

$fromAmount = (float)($input['amount'] ?? 0);
$fromCurrency = trim($input['currency'] ?? 'CNY');
$toCurrency = trim($input['to_currency'] ?? 'USDT');
$requestId = trim($input['request_id'] ?? ''); // 幂等性ID

// ✅ 参数验证
if ($fromAmount <= 0) {
    Response::error('兑换金额必须大于0');
}

if (!in_array($fromCurrency, ['CNY', 'USDT']) || !in_array($toCurrency, ['CNY', 'USDT'])) {
    Response::error('不支持的币种');
}

if ($fromCurrency === $toCurrency) {
    Response::error('不能兑换相同币种');
}

// 最低兑换金额
if ($fromCurrency === 'CNY' && $fromAmount < 100) {
    Response::error('CNY最低兑换金额为100元');
}

if ($fromCurrency === 'USDT' && $fromAmount < 10) {
    Response::error('USDT最低兑换金额为10');
}

$db = Database::getInstance();

try {
    $db->beginTransaction();

    // 🔒 幂等性检查（防止重复提交）
    if (!empty($requestId)) {
        $existingExchange = $db->fetchOne(
            "SELECT id, order_no, status FROM wallet_logs
             WHERE user_id = :user_id
             AND biz_type = 'CURRENCY_EXCHANGE'
             AND meta LIKE :request_id
             LIMIT 1",
            [
                'user_id' => $userId,
                'request_id' => '%"request_id":"' . $requestId . '"%'
            ]
        );

        if ($existingExchange) {
            $db->rollBack();
            Response::success([
                'order_no' => $existingExchange['order_no'] ?? '',
                'status' => 'duplicate',
                'is_duplicate' => true
            ], '兑换订单已存在');
        }
    }

    // 获取汇率（从系统配置或API）
    $exchangeRate = 7.2; // 默认汇率 1 USDT = 7.2 CNY
    $rateConfig = $db->fetchOne(
        "SELECT config_value FROM system_config WHERE config_key = 'usdt_exchange_rate' LIMIT 1",
        []
    );
    if ($rateConfig && $rateConfig['config_value']) {
        $exchangeRate = (float)$rateConfig['config_value'];
    }

    // 计算兑换后的金额
    if ($fromCurrency === 'CNY' && $toCurrency === 'USDT') {
        // CNY → USDT: 除以汇率
        $toAmount = bcdiv($fromAmount, $exchangeRate, 8);
    } else {
        // USDT → CNY: 乘以汇率
        $toAmount = bcmul($fromAmount, $exchangeRate, 8);
    }

    // 🔒 查询源币种钱包（使用FOR UPDATE行锁防止并发）
    $fromWallet = $db->fetchOne(
        "SELECT id, balance, frozen
         FROM wallets
         WHERE user_id = :user_id AND currency = :currency
         FOR UPDATE",
        ['user_id' => $userId, 'currency' => $fromCurrency]
    );

    if (!$fromWallet) {
        // 创建源币种钱包（使用 INSERT IGNORE 或先检查再插入）
        try {
            $fromWalletId = $db->insert('wallets', [
                'user_id' => $userId,
                'currency' => $fromCurrency,
                'balance' => 0,
                'frozen' => 0,
                'total_income' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // 如果插入失败，重新查询（可能并发创建了）
            if (!$fromWalletId) {
                $fromWallet = $db->fetchOne(
                    "SELECT id, balance, frozen
                     FROM wallets
                     WHERE user_id = :user_id AND currency = :currency
                     FOR UPDATE",
                    ['user_id' => $userId, 'currency' => $fromCurrency]
                );
                if ($fromWallet) {
                    $fromBalance = (float)$fromWallet['balance'];
                    $fromWalletId = $fromWallet['id'];
                } else {
                    throw new Exception('创建源币种钱包失败');
                }
            } else {
                $fromBalance = 0;
            }
        } catch (Exception $e) {
            // 如果是因为唯一索引冲突，重新查询
            if (
                strpos($e->getMessage(), 'Duplicate entry') !== false ||
                strpos($e->getMessage(), 'UNIQUE constraint') !== false
            ) {
                $fromWallet = $db->fetchOne(
                    "SELECT id, balance, frozen
                     FROM wallets
                     WHERE user_id = :user_id AND currency = :currency
                     FOR UPDATE",
                    ['user_id' => $userId, 'currency' => $fromCurrency]
                );
                if ($fromWallet) {
                    $fromBalance = (float)$fromWallet['balance'];
                    $fromWalletId = $fromWallet['id'];
                } else {
                    throw new Exception('创建源币种钱包失败');
                }
            } else {
                throw $e;
            }
        }
    } else {
        $fromBalance = (float)$fromWallet['balance'];
        $fromWalletId = $fromWallet['id'];
    }

    // ⚠️ 检查源币种余额（在事务内检查，确保准确性）
    if ($fromBalance < $fromAmount) {
        $db->rollBack();
        Response::error("{$fromCurrency}余额不足，当前可用余额: {$fromBalance} {$fromCurrency}");
    }

    // 🔒 查询目标币种钱包（使用FOR UPDATE行锁防止并发）
    $toWallet = $db->fetchOne(
        "SELECT id, balance, frozen
         FROM wallets
         WHERE user_id = :user_id AND currency = :currency
         FOR UPDATE",
        ['user_id' => $userId, 'currency' => $toCurrency]
    );

    if (!$toWallet) {
        // 创建目标币种钱包
        try {
            $toWalletId = $db->insert('wallets', [
                'user_id' => $userId,
                'currency' => $toCurrency,
                'balance' => 0,
                'frozen' => 0,
                'total_income' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // 如果插入失败，重新查询（可能并发创建了）
            if (!$toWalletId) {
                $toWallet = $db->fetchOne(
                    "SELECT id, balance, frozen
                     FROM wallets
                     WHERE user_id = :user_id AND currency = :currency
                     FOR UPDATE",
                    ['user_id' => $userId, 'currency' => $toCurrency]
                );
                if ($toWallet) {
                    $toBalance = (float)$toWallet['balance'];
                    $toWalletId = $toWallet['id'];
                } else {
                    throw new Exception('创建目标币种钱包失败');
                }
            } else {
                $toBalance = 0;
            }
        } catch (Exception $e) {
            // 如果是因为唯一索引冲突，重新查询
            if (
                strpos($e->getMessage(), 'Duplicate entry') !== false ||
                strpos($e->getMessage(), 'UNIQUE constraint') !== false
            ) {
                $toWallet = $db->fetchOne(
                    "SELECT id, balance, frozen
                     FROM wallets
                     WHERE user_id = :user_id AND currency = :currency
                     FOR UPDATE",
                    ['user_id' => $userId, 'currency' => $toCurrency]
                );
                if ($toWallet) {
                    $toBalance = (float)$toWallet['balance'];
                    $toWalletId = $toWallet['id'];
                } else {
                    throw new Exception('创建目标币种钱包失败');
                }
            } else {
                throw $e;
            }
        }
    } else {
        $toBalance = (float)$toWallet['balance'];
        $toWalletId = $toWallet['id'];
    }

    // 🔒 原子操作：扣除源币种余额，增加目标币种余额
    // ⚠️ 使用原子SQL操作确保数据一致性

    // 1. 扣除源币种余额（原子操作，带余额检查）
    $stmt1 = $db->query(
        "UPDATE wallets
         SET balance = balance - :amount,
             updated_at = NOW()
         WHERE id = :wallet_id
           AND balance >= :amount",
        [
            'amount' => $fromAmount,
            'wallet_id' => $fromWalletId
        ]
    );

    if (!$stmt1 || $stmt1->rowCount() === 0) {
        $db->rollBack();
        Response::error('扣除余额失败，余额不足或并发冲突');
    }

    // 2. 增加目标币种余额（原子操作）
    $stmt2 = $db->query(
        "UPDATE wallets
         SET balance = balance + :amount,
             updated_at = NOW()
         WHERE id = :wallet_id",
        [
            'amount' => $toAmount,
            'wallet_id' => $toWalletId
        ]
    );

    if (!$stmt2 || $stmt2->rowCount() === 0) {
        $db->rollBack();
        Response::error('增加目标币种余额失败');
    }

    // 3. 重新查询余额（确保获取最新值）
    $fromWalletAfter = $db->fetchOne(
        "SELECT balance FROM wallets WHERE id = :id",
        ['id' => $fromWalletId]
    );
    $toWalletAfter = $db->fetchOne(
        "SELECT balance FROM wallets WHERE id = :id",
        ['id' => $toWalletId]
    );

    $newFromUserBalance = (float)($fromWalletAfter['balance'] ?? 0);
    $newToUserBalance = (float)($toWalletAfter['balance'] ?? 0);

    // 4. 同步更新 users 表余额（确保一致性）
    $fromBalanceField = $fromCurrency === 'USDT' ? 'balance_usdt' : 'balance_cny';
    $toBalanceField = $toCurrency === 'USDT' ? 'balance_usdt' : 'balance_cny';

    $stmt3 = $db->query(
        "UPDATE users
         SET {$fromBalanceField} = :from_balance,
             {$toBalanceField} = :to_balance,
             updated_at = NOW()
         WHERE id = :user_id",
        [
            'from_balance' => $newFromUserBalance,
            'to_balance' => $newToUserBalance,
            'user_id' => $userId
        ]
    );

    if (!$stmt3 || $stmt3->rowCount() === 0) {
        $db->rollBack();
        Response::error('同步用户余额失败');
    }

    // 📝 生成兑换订单号
    $exchangeNo = 'EX' . date('YmdHis') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT) . $userId;

    // 📊 记录钱包流水（源币种）
    $db->insert('wallet_logs', [
        'user_id' => $userId,
        'wallet_id' => $fromWalletId,
        'currency' => $fromCurrency,
        'change_amount' => -$fromAmount,
        'balance_after' => $newFromUserBalance,
        'biz_type' => 'CURRENCY_EXCHANGE',
        'ref_id' => 0,
        'meta' => json_encode([
            'order_no' => $exchangeNo,
            'from_currency' => $fromCurrency,
            'from_amount' => $fromAmount,
            'to_currency' => $toCurrency,
            'to_amount' => $toAmount,
            'exchange_rate' => $exchangeRate,
            'request_id' => $requestId,
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE),
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // 📊 记录钱包流水（目标币种）
    $db->insert('wallet_logs', [
        'user_id' => $userId,
        'wallet_id' => $toWalletId,
        'currency' => $toCurrency,
        'change_amount' => $toAmount,
        'balance_after' => $newToBalance,
        'biz_type' => 'CURRENCY_EXCHANGE',
        'ref_id' => 0,
        'meta' => json_encode([
            'order_no' => $exchangeNo,
            'from_currency' => $fromCurrency,
            'from_amount' => $fromAmount,
            'to_currency' => $toCurrency,
            'to_amount' => $toAmount,
            'exchange_rate' => $exchangeRate,
            'request_id' => $requestId,
            'timestamp' => time()
        ], JSON_UNESCAPED_UNICODE),
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // 📝 记录审计日志
    AuditLog::log([
        'user_id' => $userId,
        'action' => 'currency_exchange',
        'target_type' => 'wallet',
        'target_id' => $userId,
        'details' => json_encode([
            'order_no' => $exchangeNo,
            'from_currency' => $fromCurrency,
            'from_amount' => $fromAmount,
            'to_currency' => $toCurrency,
            'to_amount' => $toAmount,
            'exchange_rate' => $exchangeRate,
            'from_balance_before' => $fromBalance,
            'from_balance_after' => $newFromUserBalance,
            'to_balance_before' => $toBalance,
            'to_balance_after' => $newToBalance
        ], JSON_UNESCAPED_UNICODE),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    ]);

    $db->commit();

    // ✅ 返回成功结果
    Response::success([
        'order_no' => $exchangeNo,
        'from_currency' => $fromCurrency,
        'from_amount' => number_format($fromAmount, 8, '.', ''),
        'to_currency' => $toCurrency,
        'to_amount' => number_format($toAmount, 8, '.', ''),
        'exchange_rate' => $exchangeRate,
        'from_balance_after' => number_format($newFromUserBalance, 8, '.', ''),
        'to_balance_after' => number_format($newToBalance, 8, '.', '')
    ], '兑换成功');
} catch (Exception $e) {
    $db->rollBack();
    error_log("币种兑换API错误 [用户:{$userId}]: " . $e->getMessage());
    Response::error('兑换失败: ' . $e->getMessage());
}

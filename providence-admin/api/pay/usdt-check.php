<?php
/**
 * USDT链上交易监控接口
 * GET /index.php/pay/usdt/check
 *
 * 检查指定订单是否已收到USDT付款
 */

require_once __DIR__ . '/../../config/bootstrap.php';

// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

$orderNo = trim($_GET['order_no'] ?? '');

if (empty($orderNo)) {
    Response::error('订单号不能为空');
}

$userId = $authUser['user_id'];
$db = Database::getInstance();

try {
    // 查询充值订单
    $recharge = $db->fetchOne(
        "SELECT id, user_id, order_no, amount, status, meta FROM recharge_records
         WHERE order_no = :order_no AND payment_method = 'usdt' AND currency = 'USDT'
         LIMIT 1",
        ['order_no' => $orderNo]
    );

    if (!$recharge) {
        Response::error('订单不存在');
    }

    // 验证订单归属
    if ($recharge['user_id'] != $userId) {
        Response::error('无权访问此订单');
    }

    // 如果订单已成功，直接返回
    if ($recharge['status'] == 1) {
        Response::success([
            'order_no' => $recharge['order_no'],
            'amount' => $recharge['amount'],
            'status' => 1,
            'status_text' => '已到账'
        ], '订单已到账');
    }

    // 使用TronGrid API检查链上交易
    $platformAddress = 'TVU2B61wJEk6VAvdPDA3KQGD2Dpz888888';
    $expectedAmount = floatval($recharge['amount']);

    // 获取订单创建时间（Unix时间戳）
    $orderCreatedAt = strtotime($recharge['created_at'] ?? date('Y-m-d H:i:s'));

    // 检查链上是否有匹配的USDT交易
    $paymentTx = TronGridService::checkUsdtPayment($platformAddress, $expectedAmount, $orderCreatedAt);

    if ($paymentTx && $paymentTx['confirmed']) {
        // 检测到付款，自动到账
        $db->beginTransaction();

        try {
            // 更新充值记录状态
            $meta = json_decode($recharge['meta'], true) ?? [];
            $meta['txid'] = $paymentTx['txid'];
            $meta['from_address'] = $paymentTx['from'];
            $meta['block_timestamp'] = $paymentTx['block_timestamp'];
            $meta['payment_time'] = date('Y-m-d H:i:s', $paymentTx['block_timestamp']);
            $meta['auto_credited'] = true;

            $db->update('recharge_records', [
                'status' => 1, // 已支付
                'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE),
                'updated_at' => date('Y-m-d H:i:s')
            ], ['id' => $recharge['id']]);

            // 增加用户USDT余额
            // 获取或创建USDT钱包
            $usdtWallet = $db->fetchOne(
                "SELECT balance FROM wallets WHERE user_id = :user_id AND currency = 'USDT' LIMIT 1",
                ['user_id' => $userId]
            );

            if (!$usdtWallet) {
                // 创建USDT钱包
                $db->insert('wallets', [
                    'user_id' => $userId,
                    'currency' => 'USDT',
                    'balance' => 0,
                    'frozen' => 0,
                    'total_income' => 0
                ]);
                $balanceBefore = 0;
            } else {
                $balanceBefore = floatval($usdtWallet['balance'] ?? 0);
            }

            // 计算赠送金额（2%赠送，1万送200）
            $bonusRate = 0.02; // 2%
            $bonusAmount = $expectedAmount * $bonusRate;
            $totalAmount = $expectedAmount + $bonusAmount;
            $newUsdtBalance = $balanceBefore + $totalAmount;

            // 更新USDT钱包余额
            $db->update('wallets', [
                'balance' => $newUsdtBalance
            ], ['user_id' => $userId, 'currency' => 'USDT']);

            // 同步更新users表的balance_usdt字段
            $db->update('users', [
                'balance_usdt' => $newUsdtBalance
            ], ['id' => $userId]);

                // 记录充值资金流水
                $db->insert('wallet_logs', [
                    'user_id' => $userId,
                    'type' => 'recharge',
                    'amount' => $expectedAmount,
                    'currency' => 'USDT',
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceBefore + $expectedAmount,
                    'order_no' => $orderNo,
                    'remark' => "USDT充值到账（链上自动确认）充值金额：{$expectedAmount} USDT",
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                // 记录赠送资金流水
                if ($bonusAmount > 0) {
                    $db->insert('wallet_logs', [
                        'user_id' => $userId,
                        'type' => 'bonus',
                        'amount' => $bonusAmount,
                        'currency' => 'USDT',
                        'balance_before' => $balanceBefore + $expectedAmount,
                        'balance_after' => $newUsdtBalance,
                        'order_no' => $orderNo,
                        'remark' => "USDT充值赠送（2%赠送，充值{$expectedAmount} USDT，赠送{$bonusAmount} USDT）",
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }

                // 更新充值记录的meta，记录赠送信息
                $meta['bonus_rate'] = $bonusRate;
                $meta['bonus_amount'] = $bonusAmount;
                $meta['total_amount'] = $totalAmount;
                $db->update('recharge_records', [
                    'meta' => json_encode($meta, JSON_UNESCAPED_UNICODE)
                ], ['id' => $recharge['id']]);
            }

            // 记录审核日志
            $db->insert('audit_log', [
                'user_id' => $userId,
                'action' => 'USDT_RECHARGE_SUCCESS',
                'amount' => $expectedAmount,
                'currency' => 'USDT',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'detail' => "USDT充值订单 {$orderNo} 链上确认成功，交易ID: {$paymentTx['txid']}",
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $db->commit();

            Response::success([
                'order_no' => $recharge['order_no'],
                'amount' => $recharge['amount'],
                'bonus_amount' => isset($bonusAmount) ? $bonusAmount : 0,
                'total_amount' => isset($totalAmount) ? $totalAmount : $expectedAmount,
                'status' => 1,
                'status_text' => '已到账',
                'txid' => $paymentTx['txid'],
                'payment_time' => date('Y-m-d H:i:s', $paymentTx['block_timestamp']),
                'auto_credited' => true
            ], '检测到付款，余额已自动到账（含2%赠送）');

        } catch (Exception $e) {
            $db->rollBack();
            error_log("USDT自动到账处理错误: " . $e->getMessage());
            Response::error('处理失败：' . $e->getMessage());
        }
    } else {
        // 未检测到付款
        Response::success([
            'order_no' => $recharge['order_no'],
            'amount' => $recharge['amount'],
            'status' => 0,
            'status_text' => '待支付',
            'platform_address' => $platformAddress,
            'note' => '系统正在监控链上交易，检测到付款后自动到账'
        ], '等待支付');
    }

} catch (Exception $e) {
    error_log("USDT监控检查错误 [用户:{$userId}, 订单:{$orderNo}]: " . $e->getMessage());
    Response::error('检查失败：' . $e->getMessage());
}

<?php

/**
 * 获取USDT充值信息（平台收款地址）
 * GET /pay/us/info
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证用户登录
// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

// 返回平台USDT收款地址（固定地址）
Response::success([
    'usdt_address' => 'TVU2B61wJEk6VAvdPDA3KQGD2Dpz888888',
    'network' => 'TRC20',
    'min_amount' => 10,
    'tips' => [
        '请确保转账网络为TRC20',
        '最低充值金额为10 USDT',
        '转账后系统将自动监控链上交易',
        '检测到付款后自动到账'
    ]
]);

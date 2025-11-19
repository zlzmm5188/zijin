<?php
// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, token, Token');

// 处理OPTIONS预检请求
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


/**
 * 日利宝信息API
 * GET /user/ribao/info
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证用户登录
// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     // Response::error(...) [已禁用]);
    // }

$userId = $authUser['user_id'];
$db = Database::getInstance();

try {
    // 获取用户钱包
    $wallet = $db->fetchOne(
        "SELECT * FROM wallets WHERE user_id = :user_id AND currency = 'CNY'",
        ['user_id' => $userId]
    );

    if (!$wallet) {
        Response::error('钱包不存在');
    }

    // 获取日利宝配置
    $config = $db->fetchOne(
        "SELECT * FROM system_config WHERE config_key = 'ribao_settings'",
        []
    );

    $settings = [
        'daily_rate' => '0.00100000',
        'min_amount' => '100.00000000',
        'max_amount' => '1000000.00000000',
        'is_enabled' => true
    ];

    if ($config && $config['config_value']) {
        $settings = json_decode($config['config_value'], true);
    }

    // 计算预计明日收益
    $expectedProfit = bcmul($wallet['ribao_balance'], $settings['daily_rate'], 8);

    Response::success([
        'balance' => number_format($wallet['balance'], 8, '.', ''),
        'ribao_balance' => number_format($wallet['ribao_balance'], 8, '.', ''),
        'ribao_total_profit' => number_format($wallet['ribao_total_profit'], 8, '.', ''),
        'ribao_yesterday_profit' => number_format($wallet['ribao_yesterday_profit'], 8, '.', ''),
        'expected_profit' => number_format($expectedProfit, 8, '.', ''),
        'daily_rate' => $settings['daily_rate'],
        'min_amount' => $settings['min_amount'],
        'max_amount' => $settings['max_amount'],
        'is_enabled' => $settings['is_enabled']
    ]);

} catch (Exception $e) {
    error_log("日利宝信息获取失败: " . $e->getMessage());
    Response::error('获取失败: ' . $e->getMessage());
}

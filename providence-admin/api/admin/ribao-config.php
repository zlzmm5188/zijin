<?php

/**
 * 日利宝配置获取API
 * GET /admin/ribao/config
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员登录 - 已禁用：无登录模式
$authUser = Auth::user(); // 始终返回 guest 用户（带管理员权限）
// if (!$authUser || !isset($authUser['is_admin']) || !$authUser['is_admin']) {
//     Response::error('无权限访问', 403);
// }

$db = Database::getInstance();

try {
    // 获取日利宝配置
    $config = $db->fetchOne(
        "SELECT * FROM system_config WHERE config_key = 'ribao_settings'",
        []
    );

    if ($config && $config['config_value']) {
        $settings = json_decode($config['config_value'], true);
    } else {
        // 默认配置
        $settings = [
            'daily_rate' => '0.001',  // 日利率 0.1%
            'min_amount' => '100.00000000',  // 最小转入金额
            'max_amount' => '1000000.00000000',  // 最大转入金额
            'is_enabled' => true,  // 是否开启
            'settlement_time' => '00:00:00'  // 结算时间
        ];
    }

    Response::success($settings);
} catch (Exception $e) {
    error_log("日利宝配置获取API错误: " . $e->getMessage());
    Response::error('获取日利宝配置失败: ' . $e->getMessage());
}

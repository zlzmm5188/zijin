<?php
/**
 * 日利宝配置保存API
 * POST /admin/ribao/config-save
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员登录
$authUser = Auth::user();
if (!$authUser || !isset($authUser['is_admin']) || !$authUser['is_admin']) {
    Response::error('无权限访问', 403);
}

// 获取POST数据
$input = json_decode(file_get_contents('php://input'), true);

$dailyRate = isset($input['daily_rate']) ? trim($input['daily_rate']) : '';
$minAmount = isset($input['min_amount']) ? trim($input['min_amount']) : '';
$maxAmount = isset($input['max_amount']) ? trim($input['max_amount']) : '';
$isEnabled = isset($input['is_enabled']) ? (bool)$input['is_enabled'] : true;
$settlementTime = isset($input['settlement_time']) ? trim($input['settlement_time']) : '00:00:00';

// 验证参数
if ($dailyRate === '' || $minAmount === '' || $maxAmount === '') {
    Response::error('请填写完整配置信息');
}

if (!is_numeric($dailyRate) || $dailyRate < 0 || $dailyRate > 1) {
    Response::error('日利率范围：0-1');
}

if (!is_numeric($minAmount) || $minAmount < 0) {
    Response::error('最小金额无效');
}

if (!is_numeric($maxAmount) || $maxAmount < $minAmount) {
    Response::error('最大金额必须大于最小金额');
}

$db = Database::getInstance();

try {
    // 开启事务
    $db->beginTransaction();

    // 构造配置数据
    $settings = [
        'daily_rate' => number_format($dailyRate, 8, '.', ''),
        'min_amount' => number_format($minAmount, 8, '.', ''),
        'max_amount' => number_format($maxAmount, 8, '.', ''),
        'is_enabled' => $isEnabled,
        'settlement_time' => $settlementTime
    ];

    // 检查配置是否存在
    $existConfig = $db->fetchOne(
        "SELECT * FROM system_config WHERE config_key = 'ribao_settings'",
        []
    );

    if ($existConfig) {
        // 更新配置
        $db->update('system_config', [
            'config_value' => json_encode($settings, JSON_UNESCAPED_UNICODE),
            'updated_at' => date('Y-m-d H:i:s')
        ], "config_key = 'ribao_settings'");
    } else {
        // 插入配置
        $db->insert('system_config', [
            'config_key' => 'ribao_settings',
            'config_value' => json_encode($settings, JSON_UNESCAPED_UNICODE),
            'description' => '日利宝配置',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    // 记录审计日志
    AuditLog::log([
        'user_id' => $authUser['user_id'],
        'action' => 'ribao_config_update',
        'target_type' => 'config',
        'target_id' => 0,
        'details' => json_encode($settings, JSON_UNESCAPED_UNICODE),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    ]);

    // 提交事务
    $db->commit();

    Response::success($settings, '日利宝配置更新成功');

} catch (Exception $e) {
    $db->rollback();
    error_log("日利宝配置保存失败: " . $e->getMessage());
    Response::error('配置保存失败: ' . $e->getMessage());
}

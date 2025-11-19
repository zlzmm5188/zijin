<?php
/**
 * Telegram Bot通知功能测试脚本
 * 访问此页面测试Telegram通知
 */

require_once __DIR__ . '/config/TelegramNotify.php';

header('Content-Type: application/json; charset=utf-8');

// 测试连接
echo "开始测试Telegram Bot通知...\n\n";

$result = TelegramNotify::testConnection();

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => '✅ Telegram Bot通知发送成功！',
        'response' => $result
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'message' => '❌ Telegram Bot通知发送失败！请检查配置。'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

echo "\n\n可用的通知方法：\n";
echo "- notifyUserRegistered() - 用户注册通知\n";
echo "- notifyRecharge() - 充值通知\n";
echo "- notifyWithdraw() - 提现通知\n";
echo "- notifyInvestment() - 投资通知\n";
echo "- notifyKYC() - KYC认证通知\n";
echo "- notifyError() - 系统错误通知\n";
echo "- notify() - 自定义通知\n";

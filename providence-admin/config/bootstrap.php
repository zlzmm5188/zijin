<?php

/**
 * 框架引导文件 (SRS v1.0 + 企业级)
 */

// 加载Composer自动加载
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// 加载辅助函数
if (file_exists(__DIR__ . '/functions.php')) {
    require_once __DIR__ . '/functions.php';
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

date_default_timezone_set('Asia/Shanghai');

// 核心类
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Upload.php';
require_once __DIR__ . '/AuditLog.php';
require_once __DIR__ . '/SignValidator.php';
require_once __DIR__ . '/Validator.php';  // SRS规范：数据验证器

// 业务服务类（SRS规范）
require_once __DIR__ . '/VipService.php';
require_once __DIR__ . '/EarningsService.php';
require_once __DIR__ . '/ReferralService.php';
require_once __DIR__ . '/WalletService.php';
require_once __DIR__ . '/OrderService.php';
require_once __DIR__ . '/CurrencyMiddleware.php';
require_once __DIR__ . '/IdempotencyService.php';
require_once __DIR__ . '/ProjectCacheService.php';  // 项目缓存服务
require_once __DIR__ . '/../api/services/PaymentService.php';  // 支付服务
require_once __DIR__ . '/../api/services/TronGridService.php';  // TronGrid链上监控服务

// ============================================
// CORS 配置 - 修复版（支持 Credentials）
// ============================================

// 允许的域名白名单
$allowedOrigins = [
    'https://copla.top',
    'https://qiantai.frevix.top',
    'https://houtai.frevix.top',
    'https://api.frevix.top',
    'https://xin.frevix.top',
    'http://localhost:3000',
    'http://localhost:8080',
];

// 获取请求来源
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

// 如果来源在白名单中，设置对应的 CORS 头
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
} else {
    // 开发环境：允许所有来源（生产环境应该注释掉）
    header("Access-Control-Allow-Origin: *");
}

// 其他 CORS 头
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, token, sign, timestamp, X-Signature, X-Timestamp, X-Nonce, Idempotency-Key');
header('Access-Control-Max-Age: 86400'); // 预检请求缓存24小时

// OPTIONS预检请求直接返回
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(0);
}

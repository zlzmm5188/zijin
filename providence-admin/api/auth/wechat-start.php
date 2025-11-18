<?php

/**
 * 微信登录入口
 * GET /index.php/auth/wechat/start?return=xxx
 */
require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

// 获取返回地址
$returnUrl = $_GET['return'] ?? '';
$returnUrl = urldecode($returnUrl);

// 检查是否在微信内
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isWechat = strpos($userAgent, 'MicroMessenger') !== false;

if (!$isWechat) {
    // 不在微信内，返回错误或跳转到普通登录
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'code' => -1,
        'msg' => '请在微信内打开',
        'data' => null
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// TODO: 实现微信OAuth授权流程
// 1. 获取微信授权code
// 2. 通过code获取openid和用户信息
// 3. 创建或更新用户
// 4. 生成token
// 5. 跳转回returnUrl

// 临时返回：功能未实现
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'code' => -1,
    'msg' => '微信登录功能暂未实现',
    'data' => null
], JSON_UNESCAPED_UNICODE);
exit;

<?php

/**
 * API路由入口 v2.2（含审核接口）
 */

// CORS头（必须在最前面）
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, token, sign, timestamp, X-Signature, X-Timestamp, X-Nonce, Idempotency-Key');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once __DIR__ . '/../config/bootstrap.php';

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// 处理多种路径格式
// 1. /api/index.php/admin/xxx
// 2. /api/admin/xxx (通过 .htaccess 重写)
// 3. /providence-admin/api/index.php/admin/xxx
// 4. /providence-admin/api/admin/xxx

// 移除基础路径
$path = str_replace(['/providence-admin/api', '/api'], '', $path);
// 移除 index.php 前缀（如果有）
$path = preg_replace('#^/index\.php/#', '', $path);
// 移除开头的斜杠
$path = trim($path, '/');

// 记录路径解析（用于调试）
if (isset($_GET['debug'])) {
    error_log("API路径解析: URI={$uri}, Path={$path}");
}

$routes = [
    // 用户认证
    'login/account' => 'login.php',
    'login/login/account' => 'login.php', // 兼容旧路径
    'login/reg/account' => 'register.php',
    'auth/wechat/start' => 'auth/wechat-start.php',

    // 用户信息
    'user/user/index' => 'user/info.php',
    'user/user/invite' => 'user/invite.php',
    'user/user/setPayPassword' => 'user/set-pay-password.php',
    'user/user/checkUsername' => 'user/check-username.php',
    'user/user/getFaceDescriptor' => 'user/get-face-descriptor.php',
    'user/user/submitFaceForReview' => 'user/submit-face-review.php',
    'user/user/checkFaceReview' => 'user/check-face-review.php',
    'user/kyc-submit-simple' => 'user/kyc-submit-simple.php',  // KYC提交（简化版）

    // 管理员 - KYC审核
    'admin/kyc-list' => 'admin/kyc-list.php',          // KYC列表
    'admin/kyc-approve' => 'admin/kyc-approve.php',    // 通过审核
    'admin/kyc-reject' => 'admin/kyc-reject.php',      // 拒绝审核
    'user/user/resetPassword' => 'user/reset-password.php',
    'user/user/verifyFaceReset' => 'user/verify-face-reset.php',

    // VIP
    'user/level/list' => 'vip/list.php',
    'user/vip/progress' => 'user/vip-progress.php',
    'user/vip/info' => 'user/vip-progress.php',  // 别名

    // 项目
    'fund/project/all' => 'project/list.php',
    'fund/project/detail' => 'project/detail.php',
    'fund/project/add' => 'project/invest.php',
    'fund/project/calculate' => 'project/calculate.php',
    'fund/order/create' => 'fund/order-create.php',

    // 实名
    'user/kyc/submit' => 'kyc/submit.php',

    // 订单
    'user/order/list' => 'order/list.php',
    'user/project/list' => 'user/project-list.php',

    // 试用金
    'user/trial/claim' => 'trial/claim.php',

    // 财务
    'user/recharge/add' => 'finance/recharge.php',
    'user/withdraw/add' => 'finance/withdraw.php',
    'user/bank/list' => 'finance/bank_list.php',

    // 支付API
    'pay/pay/recharge' => 'pay/recharge.php',
    'pay/us/recharge' => 'pay/us-recharge.php',
    'pay/bank/list' => 'pay/bank-list.php',
    'pay/bank/add' => 'pay/bank-add.php',
    'pay/bank/del' => 'pay/bank-del.php',
    'pay/pay/withdraw' => 'pay/withdraw.php',
    'pay/us/info' => 'pay/usdt-info.php',
    'pay/create' => 'pay/create.php',
    'pay/notify' => 'pay/notify.php',
    'pay/query' => 'pay/query.php',
    'pay/usdt/check' => 'pay/usdt-check.php',
    'pay/pending-order' => 'pay/pending-order.php',
    'pay/currency-exchange' => 'pay/currency-exchange.php',

    // 积分
    'user/points/balance' => 'points/balance.php',
    'user/points/exchange' => 'user/points-exchange.php',
    'user/points/exchange-history' => 'user/points-exchange-history.php',
    'user/points/logs' => 'points/logs.php',

    // 签到
    'user/sign/info' => 'user/sign-info.php',
    'user/sign/sign' => 'user/sign-do.php',

    // 团队
    'user/team/team' => 'user/team-info.php',
    'user/team/rewards_status' => 'user/team-rewards-status.php',
    'user/team/claim_reward' => 'user/team-claim-reward.php',

    // 收益
    'user/profit/calendar' => 'user/profit-calendar.php',

    // 交易记录
    'user/transaction/records' => 'user/transaction-records.php',

    // 日利宝
    'user/ribao/info' => 'user/ribao-info.php',
    'user/ribao/transfer-in' => 'user/ribao-transfer-in.php',
    'user/ribao/transfer-out' => 'user/ribao-transfer-out.php',
    'user/ribao/records' => 'user/ribao-records.php',

    // 上传
    'upload' => 'upload.php',

    // AI服务
    'ai/chat' => 'ai/chat.php',

    // 管理员API
    'admin/stats' => 'admin/stats.php',
    'admin/users' => 'admin/users.php',
    'admin/user-set-internal' => 'admin/user-set-internal.php',
    'admin/team-tree' => 'admin/team-tree.php',
    'admin/user-detail' => 'admin/user-detail.php',
    'admin/user-update' => 'admin/user-update.php',
    'admin/user-team-tree' => 'admin/user-team-tree.php',
    'admin/user-reset-password' => 'admin/user-reset-password.php',
    'admin/wallet-logs' => 'admin/wallet-logs.php',

    // 项目管理API
    'admin/projects' => 'admin/projects.php',
    'admin/project-detail' => 'admin/project-detail.php',
    'admin/project-save' => 'admin/project-save.php',
    'admin/project-delete' => 'admin/project-delete.php',
    'admin/clear-data' => 'admin/clear-data.php',
    'admin/clear-all-data' => 'admin/clear-all-data.php',
    'admin/categories' => 'admin/categories.php',
    'admin/category-save' => 'admin/category-save.php',

    // 订单管理API ⭐ 新增
    'admin/orders' => 'admin/orders.php',
    'admin/order-detail' => 'admin/order-detail.php',

    // 审核API
    'admin/kyc-approve' => 'admin/kyc-approve.php',
    'admin/kyc-reject' => 'admin/kyc-reject.php',
    'admin/recharges' => 'admin/recharges.php',
    'admin/recharge-approve' => 'admin/recharge-approve.php',
    'admin/recharge-reject' => 'admin/recharge-reject.php',
    'admin/withdrawals' => 'admin/withdrawals.php',
    'admin/withdraw-approve' => 'admin/withdraw-approve.php',
    'admin/withdraw-reject' => 'admin/withdraw-reject.php',

    // 日志管理API
    'admin/login-logs' => 'admin/login-logs.php',

    // 日利宝管理API
    'admin/ribao/users' => 'admin/ribao-users.php',
    'admin/ribao/profits' => 'admin/ribao-profits.php',
    'admin/ribao/config' => 'admin/ribao-config.php',
    'admin/ribao/config-save' => 'admin/ribao-config-save.php',

    // 活动管理API
    'admin/activities' => 'admin/activities.php',
    'admin/activity-detail' => 'admin/activity-detail.php',
    'admin/activity-save' => 'admin/activity-save.php',
    'admin/activity-delete' => 'admin/activity-delete.php',

    // 文章管理API
    'admin/articles' => 'admin/articles.php',
    'admin/article-detail' => 'admin/article-detail.php',
    'admin/article-save' => 'admin/article-save.php',
    'admin/article-delete' => 'admin/article-delete.php',
];

if (isset($routes[$path])) {
    $file = __DIR__ . '/' . $routes[$path];
    if (file_exists($file)) {
        require $file;
    } else {
        Response::error('API文件不存在: ' . $routes[$path]);
    }
} else {
    Response::error('API接口未定义: ' . $path);
}

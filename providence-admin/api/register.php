<?php

/**
 * 用户注册API - UID改为8位纯数字（带调试日志）
 * POST /login/reg/account
 */

// 记录请求开始
file_put_contents('/tmp/register_api.log', date('Y-m-d H:i:s') . " - 注册API被调用\n", FILE_APPEND);
file_put_contents('/tmp/register_api.log', "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'NULL') . "\n", FILE_APPEND);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/TelegramNotify.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    file_put_contents('/tmp/register_api.log', "错误: 不是POST请求\n", FILE_APPEND);
    Response::error('请求方法错误');
}

$rawInput = file_get_contents('php://input');
file_put_contents('/tmp/register_api.log', "原始输入: $rawInput\n", FILE_APPEND);

$input = json_decode($rawInput, true);
file_put_contents('/tmp/register_api.log', "解析后: " . json_encode($input) . "\n", FILE_APPEND);

$username = trim($input['username'] ?? '');
$password = trim($input['password'] ?? '');
$phone = trim($input['phone'] ?? '');
$email = trim($input['email'] ?? '');
$invite_code = trim($input['invite_code'] ?? $input['inviteCode'] ?? $input['invite'] ?? '');

file_put_contents('/tmp/register_api.log', "用户名: $username, 密码长度: " . strlen($password) . "\n", FILE_APPEND);

// 验证必填字段
if (empty($username) || empty($password)) {
    file_put_contents('/tmp/register_api.log', "错误: 用户名或密码为空\n\n", FILE_APPEND);
    Response::error('用户名和密码不能为空');
}

// SRS规范：密码强度验证（大写+小写+数字+特殊符号）
$passwordCheck = Validator::validatePasswordStrength($password);
if ($passwordCheck !== true) {
    Response::error($passwordCheck);
}

// 验证用户名格式
$usernameCheck = Validator::validateUsername($username);
if ($usernameCheck !== true) {
    Response::error($usernameCheck);
}

$db = Database::getInstance();

// 检查用户名是否已存在
$exists = $db->fetchOne("SELECT id FROM " . $db->getPrefix() . "users WHERE username = :username LIMIT 1", ['username' => $username]);
if ($exists) {
    Response::error('用户名已存在');
}

// 检查手机号
if (!empty($phone)) {
    $exists = $db->fetchOne("SELECT id FROM " . $db->getPrefix() . "users WHERE phone = :phone LIMIT 1", ['phone' => $phone]);
    if ($exists) {
        Response::error('手机号已被注册');
    }
}

// 检查邮箱
if (!empty($email)) {
    $exists = $db->fetchOne("SELECT id FROM " . $db->getPrefix() . "users WHERE email = :email LIMIT 1", ['email' => $email]);
    if ($exists) {
        Response::error('邮箱已被注册');
    }
}

// 处理邀请码：支持UID和invite_code
$parent_id = null;
if (!empty($invite_code)) {
    $parent = $db->fetchOne("SELECT id FROM " . $db->getPrefix() . "users WHERE invite_code = :code LIMIT 1", ['code' => $invite_code]);
    if (!$parent) {
        $parent = $db->fetchOne("SELECT id FROM " . $db->getPrefix() . "users WHERE uid = :uid LIMIT 1", ['uid' => $invite_code]);
    }
    if ($parent) {
        $parent_id = $parent['id'];
    }
}

// 生成唯一8位纯数字UID
do {
    $new_uid = str_pad(mt_rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    $exists = $db->fetchOne("SELECT id FROM " . $db->getPrefix() . "users WHERE uid = :uid LIMIT 1", ['uid' => $new_uid]);
} while ($exists);

file_put_contents('/tmp/register_api.log', "生成UID: $new_uid (8位随机)\n", FILE_APPEND);

// 生成唯一邀请码
do {
    $new_invite_code = strtoupper(substr(md5(uniqid()), 0, 8));
    $exists = $db->fetchOne("SELECT id FROM " . $db->getPrefix() . "users WHERE invite_code = :code LIMIT 1", ['code' => $new_invite_code]);
} while ($exists);

// 开始事务
$db->beginTransaction();

try {
    // 构建插入数据（只包含非空字段）
    $insertData = [
        'uid' => $new_uid,
        'username' => $username,
        'password' => Auth::hashPassword($password),
        'invite_code' => $new_invite_code,
        'parent_id' => $parent_id,
        'vip_level' => 1,
        'trial_balance' => 1000.00,
        'login_ip' => $_SERVER['REMOTE_ADDR'],
        'login_time' => date('Y-m-d H:i:s')
    ];

    if (!empty($phone)) {
        $insertData['phone'] = $phone;
    }
    if (!empty($email)) {
        $insertData['email'] = $email;
    }

    $userId = $db->insert('users', $insertData);

    if (!$userId) {
        throw new Exception('用户创建失败');
    }

    file_put_contents('/tmp/register_api.log', "用户创建成功: ID=$userId, UID=$new_uid\n", FILE_APPEND);

    // 如果有推荐人，发放邀请奖励
    if ($parent_id) {
        $parent = $db->fetchOne("SELECT vip_level, points FROM " . $db->getPrefix() . "users WHERE id = :id", ['id' => $parent_id]);
        $vipConfig = $db->fetchOne("SELECT invite_points FROM " . $db->getPrefix() . "vip_levels WHERE id = :level", ['level' => $parent['vip_level']]);

        if ($vipConfig && $vipConfig['invite_points'] > 0) {
            $newPoints = $parent['points'] + $vipConfig['invite_points'];
            $db->update('users', ['points' => $newPoints], 'id = :id', ['id' => $parent_id]);

            $db->insert('points_logs', [
                'user_id' => $parent_id,
                'type' => 'invite',
                'points' => $vipConfig['invite_points'],
                'balance_before' => $parent['points'],
                'balance_after' => $newPoints,
                'remark' => '邀请新用户注册奖励'
            ]);
        }
    }

    $db->commit();

    // 生成Token
    $token = Auth::generateToken($userId, $username);

    file_put_contents('/tmp/register_api.log', "Token生成成功\n\n", FILE_APPEND);

    // 发送Telegram通知
    try {
        TelegramNotify::notifyUserRegistered($username, $new_uid, $invite_code);
        file_put_contents('/tmp/register_api.log', "Telegram通知已发送\n", FILE_APPEND);
    } catch (Exception $e) {
        // 通知失败不影响注册流程
        error_log("Telegram通知失败: " . $e->getMessage());
        file_put_contents('/tmp/register_api.log', "Telegram通知失败: " . $e->getMessage() . "\n", FILE_APPEND);
    }

    Response::success([
        'token' => $token,
        'user' => [
            'id' => $userId,
            'uid' => $new_uid,  // 返回8位数字ID
            'username' => $username,
            'invite_code' => $new_invite_code,
            'vip_level' => 1,
            'balance' => 0,
            'trial_balance' => 1000.00,
            'points' => 0
        ]
    ], '注册成功');
} catch (Exception $e) {
    $db->rollBack();
    file_put_contents('/tmp/register_api.log', "错误: " . $e->getMessage() . "\n\n", FILE_APPEND);
    Response::error('注册失败: ' . $e->getMessage());
}

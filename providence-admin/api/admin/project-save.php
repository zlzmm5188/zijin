<?php

/**
 * 项目添加/编辑API
 * POST /api/admin/project-save.php
 * 支持创建和更新项目
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员权限
$authUser = Auth::user();
if (!$authUser) {
    Response::error('请先登录', -1);
}

// 检查是否为管理员（使用is_internal字段）
$db = Database::getInstance();
$adminCheck = $db->fetchOne(
    "SELECT is_internal FROM users WHERE id = :id",
    ['id' => $authUser['user_id']]
);

if (!$adminCheck || !$adminCheck['is_internal']) {
    Response::error('无权限访问', 403);
}

// 只接受POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误', -1);
}

try {
    // 获取请求数据（支持JSON和FormData格式）
    $input = [];

    // 1. 尝试解析JSON
    $rawInput = file_get_contents('php://input');
    if (!empty($rawInput)) {
        $jsonData = json_decode($rawInput, true);
        if ($jsonData) {
            $input = $jsonData;
        }
    }

    // 2. 如果不是JSON，使用POST数据（FormData）
    if (empty($input) && !empty($_POST)) {
        $input = $_POST;
    }

    // 3. 记录接收到的数据（调试用）
    error_log("Project Save Input: " . json_encode($input));
    error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));

    // 获取表单数据
    $id = isset($input['id']) ? (int)$input['id'] : 0;
    $project_code = trim($input['project_code'] ?? '');
    $title = trim($input['title'] ?? '');
    $subtitle = trim($input['subtitle'] ?? '');
    $description = trim($input['description'] ?? '');
    $category = trim($input['category'] ?? '');
    $currency = trim($input['currency'] ?? '');
    $cycle_days = (int)($input['cycle_days'] ?? 0);
    $base_rate = (float)($input['base_rate'] ?? 0);
    $added_rate = (float)($input['added_rate'] ?? 0);
    $gift_rate = (float)($input['gift_rate'] ?? 0);
    $min_invest = (float)($input['min_invest'] ?? 0);
    $max_invest = (float)($input['max_invest'] ?? 0);
    $total_quota = (float)($input['total_quota'] ?? 0);
    $manager_id = (int)($input['manager_id'] ?? 0);
    $status = (int)($input['status'] ?? 0);

    // 新增字段
    $is_index = (int)($input['is_index'] ?? 0);
    $sort = (int)($input['sort'] ?? 100);
    $payment_type = (int)($input['payment_type'] ?? 0);
    $vip_min = (int)($input['vip_min'] ?? 0);
    $need_referral = (int)($input['need_referral'] ?? 0);
    $team_member_required = (int)($input['team_member_required'] ?? 0);
    $mcount = (int)($input['mcount'] ?? 0);

    // 数据验证
    if (empty($title)) {
        Response::error('项目名称不能为空');
    }

    if (empty($category)) {
        Response::error('项目分类不能为空');
    }

    $validCategories = ['IPO', 'BOND', 'FUND', 'FIXED', 'INVEST'];
    if (!in_array($category, $validCategories)) {
        Response::error('项目分类无效');
    }

    if (empty($currency)) {
        Response::error('币种不能为空');
    }

    $validCurrencies = ['CNY', 'USDT'];
    if (!in_array($currency, $validCurrencies)) {
        Response::error('币种无效');
    }

    if ($cycle_days <= 0) {
        Response::error('投资周期必须大于0');
    }

    if ($base_rate < 0) {
        Response::error('基础利率不能为负数');
    }

    if ($min_invest <= 0) {
        Response::error('最小投资额必须大于0');
    }

    if ($max_invest < $min_invest) {
        Response::error('最大投资额不能小于最小投资额');
    }

    if ($total_quota <= 0) {
        Response::error('总额度必须大于0');
    }

    // 计算总利率（周期收益）
    $total_rate = $base_rate + $added_rate + $gift_rate;

    // 开始事务
    $db->beginTransaction();

    $data = [
        'title' => $title,
        'subtitle' => $subtitle,
        'description' => $description,
        'category' => $category,
        'currency' => $currency,
        'cycle_days' => $cycle_days,
        'base_rate' => $base_rate,
        'added_rate' => $added_rate,
        'gift_rate' => $gift_rate,
        'total_rate' => $total_rate,
        'min_invest' => $min_invest,
        'max_invest' => $max_invest,
        'total_quota' => $total_quota,
        'manager_id' => $manager_id,
        'status' => $status,
        'is_index' => $is_index,
        'sort' => $sort,
        'payment_type' => $payment_type,
        'vip_min' => $vip_min,
        'need_referral' => $need_referral,
        'team_member_required' => $team_member_required,
        'mcount' => $mcount,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    if ($id > 0) {
        // 更新项目
        // 检查项目是否存在
        $existProject = $db->fetchOne(
            "SELECT id, version FROM invest_projects WHERE id = :id",
            ['id' => $id]
        );

        if (!$existProject) {
            Response::error('项目不存在');
        }

        // 更新版本号
        $data['version'] = (int)$existProject['version'] + 1;

        error_log("准备更新项目，ID: $id");
        error_log("更新数据: " . json_encode($data, JSON_UNESCAPED_UNICODE));

        $success = $db->update('invest_projects', $data, 'id = :where_id', ['where_id' => $id]);

        error_log("更新结果: " . ($success ? 'true' : 'false'));

        if (!$success) {
            error_log("更新失败！数据: " . json_encode($data, JSON_UNESCAPED_UNICODE));
            throw new Exception('更新项目失败');
        }

        // 记录审计日志
        AuditLog::log(
            'project',
            '更新项目',
            'ADMIN',
            1,
            'invest_projects',
            $id,
            $existProject,
            $data
        );

        $db->commit();

        Response::success([
            'id' => $id,
            'version' => $data['version']
        ], '项目更新成功');
    } else {
        // 创建项目
        // 生成项目编号
        if (empty($project_code)) {
            $project_code = 'PRJ' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        }

        $data['project_code'] = $project_code;
        $data['version'] = 1;
        $data['schedule'] = 0;
        $data['view_count'] = 0;
        $data['invest_count'] = 0;
        $data['total_invested'] = 0;
        $data['sold'] = 0;
        $data['remain'] = $total_quota;
        $data['created_at'] = date('Y-m-d H:i:s');

        $newId = $db->insert('invest_projects', $data);

        if (!$newId) {
            throw new Exception('创建项目失败');
        }

        // 记录审计日志
        AuditLog::log(
            'project',
            '创建项目',
            'ADMIN',
            1,
            'invest_projects',
            $newId,
            null,
            $data
        );

        $db->commit();

        Response::success([
            'id' => $newId,
            'project_code' => $project_code,
            'version' => 1
        ], '项目创建成功');
    }
} catch (Exception $e) {
    $db->rollBack();
    Response::error('操作失败: ' . $e->getMessage());
}

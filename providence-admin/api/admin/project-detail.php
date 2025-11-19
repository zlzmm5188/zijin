<?php

/**
 * 项目详情API
 * GET /api/admin/project-detail.php?id=1
 * 获取单个项目详情
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员权限
// TODO: 实现管理员Token验证

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    Response::error('项目ID无效');
}

$db = Database::getInstance();

try {
    // 先测试简单查询
    $testSql = "SELECT COUNT(*) as cnt FROM invest_projects WHERE id = :id";
    $testResult = $db->fetchOne($testSql, ['id' => $id]);
    error_log("Test Count Result: " . json_encode($testResult));

    // 查询项目详情
    $sql = "
        SELECT
            p.*
        FROM invest_projects p
        WHERE p.id = :id
    ";

    error_log("Project Detail SQL: " . $sql);
    error_log("Project ID: " . $id);

    $project = $db->fetchOne($sql, ['id' => $id]);

    error_log("Project Query Result: " . ($project ? 'Found' : 'Not Found'));
    if ($project) {
        error_log("Project Data Keys: " . implode(', ', array_keys($project)));
    }

    if (!$project) {
        Response::error('项目不存在');
    }

    // 查询管理员信息
    if (!empty($project['manager_id'])) {
        $managerSql = "SELECT name, avatar, title, introduction FROM project_managers WHERE id = :id";
        $manager = $db->fetchOne($managerSql, ['id' => $project['manager_id']]);
        if ($manager) {
            $project['manager_name'] = $manager['name'];
            $project['manager_avatar'] = $manager['avatar'];
            $project['manager_title'] = $manager['title'];
            $project['manager_introduction'] = $manager['introduction'];
        }
    }

    // 格式化数据
    $project['min_invest'] = (float)$project['min_invest'];
    $project['max_invest'] = (float)($project['max_invest'] ?? 0);
    $project['total_quota'] = (float)($project['total_quota'] ?? 0);

    // 计算已售和剩余（基于schedule进度）
    $schedule = (float)($project['schedule'] ?? 0);
    $totalQuota = $project['total_quota'];
    $project['sold'] = $totalQuota > 0 ? $totalQuota * $schedule / 100 : 0;
    $project['remain'] = $totalQuota - $project['sold'];
    $project['total_invested'] = $project['sold']; // 已售=已投资

    $project['base_rate'] = (float)$project['base_rate'];
    $project['added_rate'] = (float)$project['added_rate'];
    $project['gift_rate'] = (float)$project['gift_rate'];
    $project['total_rate'] = (float)$project['total_rate'];

    $project['cycle_days'] = (int)$project['cycle_days'];
    $project['status'] = (int)$project['status'];
    $project['is_index'] = (int)($project['is_index'] ?? 0);
    $project['sort'] = (int)($project['sort'] ?? 0);
    $project['payment_type'] = (int)($project['payment_type'] ?? 0);
    $project['vip_min'] = (int)($project['vip_min'] ?? 0);
    $project['need_referral'] = (int)($project['need_referral'] ?? 0);
    $project['team_member_required'] = (int)($project['team_member_required'] ?? 0);
    $project['mcount'] = (int)($project['mcount'] ?? 0);
    $project['risk_level'] = (int)($project['risk_level'] ?? 1);
    $project['version'] = (int)($project['version'] ?? 1);
    $project['view_count'] = (int)($project['view_count'] ?? 0);
    $project['invest_count'] = (int)($project['invest_count'] ?? 0);

    $project['schedule'] = $schedule;

    Response::success($project, '获取成功');
} catch (Exception $e) {
    Response::error('查询失败: ' . $e->getMessage());
}

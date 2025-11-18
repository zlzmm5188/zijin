<?php
/**
 * 项目配置更新API
 * POST /api/admin/project-config.php
 * 快速更新项目购买限制配置
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员权限
// TODO: 实现管理员Token验证

// 只接受POST请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$db = Database::getInstance();

try {
    // 获取项目ID
    $id = (int)($_POST['id'] ?? 0);

    if ($id <= 0) {
        Response::error('项目ID无效');
    }

    // 检查项目是否存在
    $project = $db->fetchOne(
        "SELECT id, title FROM invest_projects WHERE id = :id",
        ['id' => $id]
    );

    if (!$project) {
        Response::error('项目不存在');
    }

    // 获取配置参数
    $is_index = (int)($_POST['is_index'] ?? 0);
    $sort = (int)($_POST['sort'] ?? 100);
    $payment_type = (int)($_POST['payment_type'] ?? 0);
    $vip_min = (int)($_POST['vip_min'] ?? 0);
    $need_referral = (int)($_POST['need_referral'] ?? 0);
    $team_member_required = (int)($_POST['team_member_required'] ?? 0);
    $mcount = (int)($_POST['mcount'] ?? 0);
    $status = (int)($_POST['status'] ?? 0);

    // 数据验证
    if ($vip_min < 0 || $vip_min > 8) {
        Response::error('VIP等级必须在0-8之间');
    }

    if ($team_member_required < 0) {
        Response::error('团队人数要求不能为负数');
    }

    if ($mcount < 0) {
        Response::error('购买次数限制不能为负数');
    }

    // 开始事务
    $db->beginTransaction();

    $data = [
        'is_index' => $is_index,
        'sort' => $sort,
        'payment_type' => $payment_type,
        'vip_min' => $vip_min,
        'need_referral' => $need_referral,
        'team_member_required' => $team_member_required,
        'mcount' => $mcount,
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $success = $db->update('invest_projects', $data, ['id' => $id]);

    if (!$success) {
        throw new Exception('更新配置失败');
    }

    // 记录审计日志
    AuditLog::log(
        'project',
        '更新项目配置',
        'ADMIN',
        1,
        'invest_projects',
        $id,
        $project,
        $data
    );

    $db->commit();

    Response::success([
        'id' => $id,
        'updated' => true
    ], '配置更新成功');

} catch (Exception $e) {
    $db->rollBack();
    Response::error('操作失败: ' . $e->getMessage());
}

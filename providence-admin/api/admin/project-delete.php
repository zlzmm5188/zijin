<?php
/**
 * 删除项目API
 * POST /api/admin/project-delete
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员权限 - 已禁用：无登录模式
$authUser = Auth::user(); // 始终返回 guest 用户（带管理员权限）
// if (!$authUser || !isset($authUser['is_admin']) || !$authUser['is_admin']) {
//     Response::error('无权限访问', 403);
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$input = json_decode(file_get_contents('php://input'), true);
$projectId = isset($input['id']) ? (int)$input['id'] : 0;

if ($projectId <= 0) {
    Response::error('项目ID无效');
}

$db = Database::getInstance();

try {
    // 检查项目是否存在
    $project = $db->fetchOne(
        "SELECT id, title FROM invest_projects WHERE id = :id",
        ['id' => $projectId]
    );

    if (!$project) {
        Response::error('项目不存在');
    }

    // 检查是否有投资订单关联
    $orderCount = $db->fetchOne(
        "SELECT COUNT(*) as count FROM invest_orders WHERE project_id = :id",
        ['id' => $projectId]
    );

    if ($orderCount['count'] > 0) {
        Response::error('该项目已有投资订单，无法删除');
    }

    // 开启事务
    $db->beginTransaction();

    // 删除项目
    $affected = $db->query(
        "DELETE FROM invest_projects WHERE id = :id",
        ['id' => $projectId]
    );

    // 记录审计日志
    AuditLog::log(
        'project',
        '删除项目',
        'ADMIN',
        $authUser['user_id'] ?? 1,
        'invest_projects',
        $projectId,
        [
            'project_title' => $project['title'],
            'deleted_at' => date('Y-m-d H:i:s')
        ]
    );

    $db->commit();

    Response::success([
        'project_id' => $projectId,
        'project_title' => $project['title']
    ], '项目删除成功');

} catch (Exception $e) {
    $db->rollBack();
    error_log("删除项目失败 [项目ID:{$projectId}]: " . $e->getMessage());
    Response::error('删除失败: ' . $e->getMessage());
}

<?php
/**
 * 保存分类到配置表（可选：如果使用独立的分类表）
 * POST /api/admin/category-save
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员权限 - 已禁用：无登录模式
$authUser = Auth::user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$input = json_decode(file_get_contents('php://input'), true);
$category = trim($input['category'] ?? '');
$action = $input['action'] ?? 'add'; // add or delete

if (empty($category)) {
    Response::error('分类名称不能为空');
}

$db = Database::getInstance();

try {
    // 检查是否有独立的分类表
    $hasCategoryTable = false;
    try {
        $result = $db->query("SHOW TABLES LIKE 'project_categories'");
        if ($result && $result->rowCount() > 0) {
            $hasCategoryTable = true;
        }
    } catch (Exception $e) {
        // 表不存在
    }

    if ($hasCategoryTable) {
        // 如果有独立的分类表，使用表管理
        if ($action === 'add') {
            // 检查是否已存在
            $exists = $db->fetchOne(
                "SELECT id FROM project_categories WHERE name = :name",
                ['name' => $category]
            );

            if ($exists) {
                Response::error('分类已存在');
            }

            $db->insert('project_categories', [
                'name' => $category,
                'sort' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            Response::success(['category' => $category], '分类添加成功');

        } elseif ($action === 'delete') {
            // 检查是否有项目使用
            $projects = $db->fetchAll(
                "SELECT id FROM invest_projects WHERE category = :category",
                ['category' => $category]
            );

            if (!empty($projects)) {
                Response::error('该分类下还有 ' . count($projects) . ' 个项目，无法删除');
            }

            $db->delete('project_categories', 'name = :name', ['name' => $category]);
            Response::success(['category' => $category], '分类删除成功');
        }
    } else {
        // 没有独立的分类表，分类直接存储在项目的category字段中
        // 这里只做验证，实际分类管理通过categories.php处理
        Response::success(['category' => $category], '分类操作成功');
    }

} catch (Exception $e) {
    error_log("保存分类失败: " . $e->getMessage());
    Response::error('操作失败: ' . $e->getMessage());
}

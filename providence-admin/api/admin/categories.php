<?php

/**
 * 项目分类管理API
 * GET /api/admin/categories - 获取分类列表
 * POST /api/admin/categories - 添加分类
 * DELETE /api/admin/categories - 删除分类
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员权限 - 已禁用：无登录模式
$authUser = Auth::user();

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();

try {
    // 检查是否存在 project_categories 表，如果不存在则创建
    $hasCategoryTable = false;
    try {
        $result = $db->fetchAll("SHOW TABLES LIKE 'project_categories'");
        if (!empty($result)) {
            $hasCategoryTable = true;
        } else {
            // 创建分类表
            $db->query("CREATE TABLE IF NOT EXISTS `project_categories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(50) NOT NULL COMMENT '分类名称',
                `sort` int(11) DEFAULT 0 COMMENT '排序',
                `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `name` (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='项目分类表'");
            $hasCategoryTable = true;
            error_log("已创建 project_categories 表");

            // 插入默认分类
            $defaultCategories = ['IPO', 'BOND', 'FUND', 'FIXED', 'INVEST'];
            foreach ($defaultCategories as $index => $cat) {
                try {
                    $db->insert('project_categories', [
                        'name' => $cat,
                        'sort' => $index
                    ]);
                } catch (Exception $e) {
                    // 忽略重复插入错误
                }
            }
        }
    } catch (Exception $e) {
        error_log("检查/创建分类表失败: " . $e->getMessage());
    }

    if ($method === 'GET') {
        // 获取分类列表
        $categoryList = [];

        if ($hasCategoryTable) {
            // 从分类表获取
            $categories = $db->fetchAll("SELECT name FROM project_categories ORDER BY sort ASC, id ASC");
            $categoryList = array_column($categories, 'name');
        }

        // 如果分类表为空，从项目表获取已使用的分类
        if (empty($categoryList)) {
            $categories = $db->fetchAll("SELECT DISTINCT category FROM invest_projects WHERE category IS NOT NULL AND category != '' ORDER BY category");
            $categoryList = array_column($categories, 'category');
        }

        // 如果还是没有分类，返回默认分类
        if (empty($categoryList)) {
            $categoryList = ['IPO', 'BOND', 'FUND', 'FIXED', 'INVEST'];
        }

        Response::success([
            'list' => $categoryList
        ], '获取成功');
    } elseif ($method === 'POST') {
        // 添加分类
        $input = json_decode(file_get_contents('php://input'), true);
        $category = trim($input['category'] ?? '');

        if (empty($category)) {
            Response::error('分类名称不能为空');
        }

        if ($hasCategoryTable) {
            // 检查分类是否已存在
            $exists = $db->fetchOne(
                "SELECT id FROM project_categories WHERE name = :name",
                ['name' => $category]
            );

            if ($exists) {
                Response::error('分类已存在');
            }

            // 插入新分类
            $db->insert('project_categories', [
                'name' => $category,
                'sort' => 0
            ]);

            Response::success([
                'category' => $category
            ], '分类添加成功');
        } else {
            // 如果没有分类表，检查是否在项目中使用过
            $exists = $db->fetchOne(
                "SELECT COUNT(*) as count FROM invest_projects WHERE category = :category",
                ['category' => $category]
            );

            if ($exists && $exists['count'] > 0) {
                Response::error('分类已存在');
            }

            Response::success([
                'category' => $category
            ], '分类添加成功（将在创建项目时生效）');
        }
    } elseif ($method === 'DELETE') {
        // 删除分类
        $input = json_decode(file_get_contents('php://input'), true);
        $category = trim($input['category'] ?? '');

        if (empty($category)) {
            Response::error('分类名称不能为空');
        }

        // 检查是否有项目使用该分类
        $projects = $db->fetchAll(
            "SELECT id, title FROM invest_projects WHERE category = :category",
            ['category' => $category]
        );

        if (!empty($projects)) {
            Response::error('该分类下还有 ' . count($projects) . ' 个项目，无法删除');
        }

        if ($hasCategoryTable) {
            // 从分类表删除
            $db->delete('project_categories', 'name = :name', ['name' => $category]);
        }

        Response::success([
            'category' => $category
        ], '分类删除成功');
    } else {
        Response::error('请求方法不支持');
    }
} catch (Exception $e) {
    error_log("分类管理失败: " . $e->getMessage());
    Response::error('操作失败: ' . $e->getMessage());
}

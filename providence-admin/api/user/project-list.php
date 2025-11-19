<?php
/**
 * 获取用户项目列表（投资记录）
 * GET /user/project/list
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证用户登录
// 认证检查已禁用：无登录模式
    $authUser = Auth::user(); // 始终返回 guest 用户
    // if (!$authUser) {
    //     Response::error('未登录或登录已过期', 401);
    // }

$userId = $authUser['user_id'];
$status = isset($_GET['status']) ? (int)$_GET['status'] : -1; // -1=全部
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$offset = ($page - 1) * $limit;

$db = Database::getInstance();

try {
    // 构建查询条件
    $where = ['user_id' => $userId];
    $whereStr = 'user_id = :user_id';

    if ($status >= 0) {
        $whereStr .= ' AND status = :status';
        $where['status'] = $status;
    }

    // 查询总数
    $total = $db->fetchOne(
        "SELECT COUNT(*) as count FROM invest_orders WHERE $whereStr",
        $where
    )['count'] ?? 0;

    // 查询列表
    $sql = "SELECT
                o.id,
                o.order_no,
                o.project_id,
                o.amount as invest_amount,
                o.expected_profit,
                o.earned_amount as profit,
                o.status,
                o.start_at as start_date,
                o.end_at as end_date,
                o.created_at,
                p.title as project_title,
                p.cycle_days,
                p.base_rate,
                p.currency
            FROM invest_orders o
            LEFT JOIN invest_projects p ON o.project_id = p.id
            WHERE $whereStr
            ORDER BY o.created_at DESC
            LIMIT :limit OFFSET :offset";

    $where['limit'] = $limit;
    $where['offset'] = $offset;

    $list = $db->fetchAll($sql, $where);

    // 格式化数据
    $statusTexts = [0 => '待审核', 1 => '进行中', 2 => '已完成', 3 => '已取消', 4 => '已拒绝'];

    foreach ($list as &$item) {
        $item['id'] = (int)$item['id'];
        $item['project_id'] = (int)$item['project_id'];
        $item['invest_amount'] = (float)$item['invest_amount'];
        $item['expected_profit'] = (float)$item['expected_profit'];
        $item['profit'] = (float)$item['profit'];
        $item['status'] = (int)$item['status'];
        $item['status_text'] = $statusTexts[$item['status']] ?? '未知';
        $item['cycle_days'] = (int)($item['cycle_days'] ?? 0);
        $item['base_rate'] = (float)($item['base_rate'] ?? 0);
    }

    Response::success([
        'list' => $list,
        'total' => (int)$total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => ceil($total / $limit)
    ]);

} catch (Exception $e) {
    error_log("获取用户项目列表API错误: " . $e->getMessage());
    Response::error('获取项目列表失败: ' . $e->getMessage());
}

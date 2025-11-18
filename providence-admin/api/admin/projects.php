<?php
/**
 * 项目列表API
 * GET /api/admin/projects
 * 支持分页、筛选、排序
 */
require_once __DIR__ . '/../../config/bootstrap.php';

// 验证管理员权限 - 已禁用：无登录模式
$authUser = Auth::user(); // 始终返回 guest 用户（带管理员权限）
// if (!$authUser || !isset($authUser['is_admin']) || !$authUser['is_admin']) {
//     Response::error('无权限访问', 403);
// }

// 获取参数
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = max(1, min(100, (int)($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

// 筛选参数
$category = trim($_GET['category'] ?? '');
$currency = trim($_GET['currency'] ?? '');
$status = isset($_GET['status']) ? (int)$_GET['status'] : -1;
$keyword = trim($_GET['keyword'] ?? '');

$db = Database::getInstance();

// 构建WHERE条件
$where = ['1=1'];
$params = [];

if (!empty($category)) {
    $where[] = "category = :category";
    $params['category'] = $category;
}

if (!empty($currency)) {
    $where[] = "currency = :currency";
    $params['currency'] = $currency;
}

if ($status >= 0) {
    $where[] = "status = :status";
    $params['status'] = $status;
}

if (!empty($keyword)) {
    $where[] = "(title LIKE :keyword OR project_code LIKE :keyword)";
    $params['keyword'] = "%{$keyword}%";
}

$whereStr = implode(' AND ', $where);

try {
    // 查询总数
    $countSql = "SELECT COUNT(*) FROM invest_projects WHERE {$whereStr}";
    $total = $db->fetchOne($countSql, $params)['COUNT(*)'] ?? 0;

    // 查询列表
    $sql = "
        SELECT
            p.*,
            m.name as manager_name
        FROM invest_projects p
        LEFT JOIN project_managers m ON p.manager_id = m.id
        WHERE {$whereStr}
        ORDER BY p.sort DESC, p.id DESC
        LIMIT {$limit} OFFSET {$offset}
    ";

    $projects = $db->fetchAll($sql, $params);

    // 格式化数据
    foreach ($projects as &$project) {
        // 格式化金额
        $project['min_invest'] = (float)$project['min_invest'];
        $project['max_invest'] = (float)$project['max_invest'];
        $project['total_quota'] = (float)$project['total_quota'];
        $project['sold'] = (float)$project['sold'];
        $project['remain'] = (float)$project['remain'];
        $project['total_invested'] = (float)$project['total_invested'];

        // 格式化利率
        $project['base_rate'] = (float)$project['base_rate'];
        $project['added_rate'] = (float)$project['added_rate'];
        $project['gift_rate'] = (float)$project['gift_rate'];
        $project['total_rate'] = (float)$project['total_rate'];

        // 格式化整数
        $project['cycle_days'] = (int)$project['cycle_days'];
        $project['status'] = (int)$project['status'];
        $project['is_index'] = (int)$project['is_index'];
        $project['sort'] = (int)$project['sort'];
        $project['payment_type'] = (int)$project['payment_type'];
        $project['vip_min'] = (int)$project['vip_min'];
        $project['need_referral'] = (int)$project['need_referral'];
        $project['team_member_required'] = (int)$project['team_member_required'];
        $project['mcount'] = (int)$project['mcount'];
        $project['risk_level'] = (int)$project['risk_level'];
        $project['version'] = (int)$project['version'];
        $project['view_count'] = (int)$project['view_count'];
        $project['invest_count'] = (int)$project['invest_count'];

        // 格式化进度
        $project['schedule'] = (float)$project['schedule'];

        // 添加状态文本
        $project['status_text'] = $project['status'] == 1 ? '启用' : '禁用';
        $project['is_index_text'] = $project['is_index'] == 1 ? '首页' : '-';

        // 添加支付方式文本
        $paymentTypes = ['不限', '仅USDT', '仅CNY'];
        $project['payment_type_text'] = $paymentTypes[$project['payment_type']] ?? '不限';

        // 添加VIP要求文本
        $project['vip_min_text'] = $project['vip_min'] > 0 ? "VIP{$project['vip_min']}+" : '无限制';
    }

    Response::success([
        'list' => $projects,
        'pagination' => [
            'total' => (int)$total,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($total / $limit)
        ],
        'filters' => [
            'categories' => ['IPO', 'BOND', 'FUND', 'FIXED', 'INVEST'],
            'currencies' => ['CNY', 'USDT'],
            'statuses' => [
                ['value' => -1, 'label' => '全部'],
                ['value' => 1, 'label' => '启用'],
                ['value' => 0, 'label' => '禁用']
            ]
        ]
    ], '获取成功');

} catch (Exception $e) {
    Response::error('查询失败: ' . $e->getMessage());
}

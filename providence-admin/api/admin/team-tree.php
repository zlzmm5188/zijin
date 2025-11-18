<?php
/**
 * 团队树形图数据API
 * GET /admin/team-tree?user_id=x
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$userId = (int)($_GET['user_id'] ?? 0);

if (!$userId) {
    Response::error('用户ID不能为空');
}

$db = Database::getInstance();

// 获取用户信息
$user = $db->fetchOne("SELECT * FROM " . $db->getPrefix() . "users WHERE id = :id", ['id' => $userId]);
if (!$user) {
    Response::error('用户不存在');
}

// 递归获取团队树（最多3层）
function getTeamTree($db, $userId, $level = 1, $maxLevel = 3) {
    if ($level > $maxLevel) {
        return [];
    }
    
    $sql = "SELECT id, username, vip_level, balance, created_at, is_internal,
            (SELECT COUNT(*) FROM " . $db->getPrefix() . "users WHERE parent_id = u.id) as children_count,
            (SELECT COALESCE(SUM(invest_amount), 0) FROM " . $db->getPrefix() . "user_investments WHERE user_id = u.id) as total_invest
            FROM " . $db->getPrefix() . "users u
            WHERE parent_id = :pid
            ORDER BY id DESC";
    
    $children = $db->fetchAll($sql, ['pid' => $userId]);
    
    foreach ($children as &$child) {
        $child['level'] = $level;
        $child['total_invest'] = (float)$child['total_invest'];
        $child['children'] = getTeamTree($db, $child['id'], $level + 1, $maxLevel);
    }
    
    return $children;
}

// 获取团队统计
$level1Count = $db->count('users', 'parent_id = :id', ['id' => $userId]);

$level2Sql = "SELECT COUNT(*) as count FROM " . $db->getPrefix() . "users 
              WHERE parent_id IN (SELECT id FROM " . $db->getPrefix() . "users WHERE parent_id = :id)";
$level2Result = $db->fetchOne($level2Sql, ['id' => $userId]);
$level2Count = $level2Result['count'] ?? 0;

// 团队总人数（递归统计）
function countAllTeam($db, $userId) {
    $directCount = $db->count('users', 'parent_id = :id', ['id' => $userId]);
    $total = $directCount;
    
    $children = $db->fetchAll("SELECT id FROM " . $db->getPrefix() . "users WHERE parent_id = :id", ['id' => $userId]);
    foreach ($children as $child) {
        $total += countAllTeam($db, $child['id']);
    }
    
    return $total;
}

$totalTeam = countAllTeam($db, $userId);

// 团队总投资
$teamInvestSql = "SELECT COALESCE(SUM(i.invest_amount), 0) as total
                  FROM " . $db->getPrefix() . "user_investments i
                  INNER JOIN " . $db->getPrefix() . "users u ON i.user_id = u.id
                  WHERE u.parent_id = :id OR u.id IN (
                      SELECT id FROM " . $db->getPrefix() . "users WHERE parent_id IN (
                          SELECT id FROM " . $db->getPrefix() . "users WHERE parent_id = :id
                      )
                  )";
$teamInvestResult = $db->fetchOne($teamInvestSql, ['id' => $userId]);
$teamInvest = $teamInvestResult['total'] ?? 0;

Response::success([
    'user' => [
        'id' => $user['id'],
        'username' => $user['username'],
        'vip_level' => $user['vip_level']
    ],
    'stats' => [
        'level1_count' => (int)$level1Count,
        'level2_count' => (int)$level2Count,
        'total_team' => (int)$totalTeam,
        'team_invest' => (float)$teamInvest
    ],
    'tree' => getTeamTree($db, $userId, 1, 3)
]);

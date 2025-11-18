<?php
/**
 * 用户团队树API
 * GET /api/admin/user-team-tree.php?id=1
 * 获取用户的完整下级团队树形结构
 */
require_once __DIR__ . '/../../config/bootstrap.php';

$userId = (int)($_GET['id'] ?? 0);

if ($userId <= 0) {
    Response::error('用户ID无效');
}

$db = Database::getInstance();

try {
    // 递归获取团队树
    function getTeamTree($db, $userId, $level = 1, $maxLevel = 10) {
        if ($level > $maxLevel) {
            return [];
        }

        // 获取直推用户
        $children = $db->fetchAll(
            "SELECT
                id,
                uid,
                username,
                vip_level,
                total_invest,
                realname_status,
                status,
                created_at
            FROM users
            WHERE parent_id = :pid
            ORDER BY id DESC",
            ['pid' => $userId]
        );

        $tree = [];

        foreach ($children as $child) {
            // 获取该用户的下级数量
            $childrenCount = $db->count('users', 'parent_id = :id', ['id' => $child['id']]);

            // 获取该用户的投资统计
            $investStats = $db->fetchOne(
                "SELECT
                    COUNT(*) as order_count,
                    SUM(invest_amount) as total_invest
                FROM invest_orders
                WHERE user_id = :uid",
                ['uid' => $child['id']]
            );

            $node = [
                'id' => (int)$child['id'],
                'uid' => $child['uid'],
                'username' => $child['username'],
                'vip_level' => (int)$child['vip_level'],
                'vip_name' => 'VIP' . $child['vip_level'],
                'total_invest' => (float)$child['total_invest'],
                'order_count' => (int)($investStats['order_count'] ?? 0),
                'realname_status' => (int)$child['realname_status'],
                'status' => (int)$child['status'],
                'children_count' => $childrenCount,
                'level' => $level,
                'created_at' => $child['created_at'],
                'children' => []
            ];

            // 递归获取下级
            if ($childrenCount > 0) {
                $node['children'] = getTeamTree($db, $child['id'], $level + 1, $maxLevel);
            }

            $tree[] = $node;
        }

        return $tree;
    }

    // 获取根用户信息
    $rootUser = $db->fetchOne(
        "SELECT id, uid, username, vip_level FROM users WHERE id = :id",
        ['id' => $userId]
    );

    if (!$rootUser) {
        Response::error('用户不存在');
    }

    // 获取团队树
    $tree = getTeamTree($db, $userId);

    // 统计信息
    function countTeamStats($tree) {
        $stats = [
            'total_members' => 0,
            'total_invest' => 0,
            'level_distribution' => []
        ];

        foreach ($tree as $node) {
            $stats['total_members']++;
            $stats['total_invest'] += $node['total_invest'];

            $level = $node['level'];
            if (!isset($stats['level_distribution'][$level])) {
                $stats['level_distribution'][$level] = 0;
            }
            $stats['level_distribution'][$level]++;

            if (!empty($node['children'])) {
                $childStats = countTeamStats($node['children']);
                $stats['total_members'] += $childStats['total_members'];
                $stats['total_invest'] += $childStats['total_invest'];

                foreach ($childStats['level_distribution'] as $l => $count) {
                    if (!isset($stats['level_distribution'][$l])) {
                        $stats['level_distribution'][$l] = 0;
                    }
                    $stats['level_distribution'][$l] += $count;
                }
            }
        }

        return $stats;
    }

    $stats = countTeamStats($tree);

    Response::success([
        'root' => $rootUser,
        'tree' => $tree,
        'stats' => $stats
    ], '获取成功');

} catch (Exception $e) {
    error_log("用户团队树API错误: " . $e->getMessage());
    Response::error('查询失败: ' . $e->getMessage());
}

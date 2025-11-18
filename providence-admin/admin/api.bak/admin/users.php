<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
$db = Database::getInstance();

try {
    $users = $db->fetchAll("SELECT id, username, phone, vip_level, balance, status FROM " . $db->getPrefix() . "users ORDER BY id DESC LIMIT 20");
    
    foreach ($users as &$u) {
        $u['vip_name'] = 'VIP' . ($u['vip_level'] ?? 0);
        $u['children_count'] = 0;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['code' => 0, 'msg' => '', 'count' => count($users), 'data' => $users]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['code' => -1, 'msg' => $e->getMessage(), 'data' => []]);
}

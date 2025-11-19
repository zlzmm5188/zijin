<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
$db = Database::getInstance();

try {
    $list = $db->fetchAll("SELECT * FROM " . $db->getPrefix() . "vip_interest_rules ORDER BY vip_level ASC");
    header('Content-Type: application/json');
    echo json_encode(['code' => 1, 'msg' => '', 'data' => $list]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['code' => -1, 'msg' => $e->getMessage(), 'data' => []]);
}

<?php
header('Content-Type: application/json');
echo json_encode([
    'code' => 0, 'msg' => '', 'count' => 2,
    'data' => [
        ['id' => 1, 'order_no' => 'INV20241110001', 'username' => 'testuser1', 'project_title' => '稳健增长基金', 'invest_amount' => '10000.00', 'status' => 1, 'created_at' => '2024-11-10 10:00:00'],
        ['id' => 2, 'order_no' => 'INV20241110002', 'username' => 'testuser2', 'project_title' => '科技创新项目', 'invest_amount' => '50000.00', 'status' => 2, 'created_at' => '2024-11-09 11:00:00']
    ]
]);

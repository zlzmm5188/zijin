<?php
header('Content-Type: application/json');
echo json_encode([
    'code' => 0, 'msg' => '', 'count' => 2,
    'data' => [
        ['id' => 1, 'title' => '稳健增长基金', 'min_invest' => '1000', 'daily_rate' => '0.15%', 'total_days' => 30, 'status' => 1, 'created_at' => '2024-11-01 10:00:00'],
        ['id' => 2, 'title' => '科技创新项目', 'min_invest' => '5000', 'daily_rate' => '0.25%', 'total_days' => 60, 'status' => 1, 'created_at' => '2024-11-02 11:00:00']
    ]
]);

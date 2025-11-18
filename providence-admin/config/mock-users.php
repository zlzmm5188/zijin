<?php
// 模拟用户数据（实际应从数据库读取）
header('Content-Type: application/json');

$data = [
    'code' => 0,
    'msg' => '',
    'count' => 3,
    'data' => [
        [
            'id' => 1,
            'username' => 'testuser1',
            'phone' => '13800138001',
            'email' => 'test1@example.com',
            'vip_level' => 'VIP1',
            'balance' => '1000.00',
            'status' => 1,
            'created_at' => '2024-11-01 10:00:00'
        ],
        [
            'id' => 2,
            'username' => 'testuser2',
            'phone' => '13800138002',
            'email' => 'test2@example.com',
            'vip_level' => 'VIP2',
            'balance' => '5000.00',
            'status' => 1,
            'created_at' => '2024-11-02 11:00:00'
        ],
        [
            'id' => 3,
            'username' => 'testuser3',
            'phone' => '13800138003',
            'email' => 'test3@example.com',
            'vip_level' => 'VIP1',
            'balance' => '2000.00',
            'status' => 1,
            'created_at' => '2024-11-03 12:00:00'
        ]
    ]
];

echo json_encode($data);

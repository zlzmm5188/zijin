<?php
header('Content-Type: application/json');
echo json_encode([
    'code' => 0, 'msg' => '', 'count' => 2,
    'data' => [
        ['id' => 1, 'order_no' => 'RCH20241110001', 'username' => 'testuser1', 'amount' => '1000.00', 'payment_method' => '银行卡', 'status' => 0, 'created_at' => '2024-11-10 10:00:00'],
        ['id' => 2, 'order_no' => 'RCH20241110002', 'username' => 'testuser2', 'amount' => '5000.00', 'payment_method' => 'USDT', 'status' => 1, 'created_at' => '2024-11-10 11:00:00']
    ]
]);

<?php
header('Content-Type: application/json');
echo json_encode([
    'code' => 0, 'msg' => '', 'count' => 2,
    'data' => [
        ['id' => 1, 'order_no' => 'WTH20241110001', 'username' => 'testuser1', 'amount' => '500.00', 'fee' => '2.50', 'actual_amount' => '497.50', 'status' => 0, 'created_at' => '2024-11-10 10:00:00'],
        ['id' => 2, 'order_no' => 'WTH20241110002', 'username' => 'testuser2', 'amount' => '1000.00', 'fee' => '5.00', 'actual_amount' => '995.00', 'status' => 3, 'created_at' => '2024-11-10 11:00:00']
    ]
]);

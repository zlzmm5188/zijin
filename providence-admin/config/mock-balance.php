<?php
header('Content-Type: application/json');
echo json_encode([
    'code' => 0, 'msg' => '', 'count' => 3,
    'data' => [
        ['id' => 1, 'username' => 'testuser1', 'type' => '充值', 'amount' => '+1000.00', 'balance_after' => '1000.00', 'remark' => '银行卡充值', 'created_at' => '2024-11-10 10:00:00'],
        ['id' => 2, 'username' => 'testuser1', 'type' => '投资', 'amount' => '-500.00', 'balance_after' => '500.00', 'remark' => '投资：稳健增长基金', 'created_at' => '2024-11-10 11:00:00'],
        ['id' => 3, 'username' => 'testuser1', 'type' => '收益', 'amount' => '+7.50', 'balance_after' => '507.50', 'remark' => '每日收益', 'created_at' => '2024-11-10 12:00:00']
    ]
]);

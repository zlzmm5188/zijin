<?php
// 测试接口文件
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, token');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}

// 获取POST数据
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// 记录日志
file_put_contents('/tmp/face_api_test.log', date('Y-m-d H:i:s') . ' - ' . $input . "\n", FILE_APPEND);

// 返回测试数据
echo json_encode([
    'code' => 200,
    'msg' => '接口测试成功',
    'data' => [
        'similarity' => 85.5,
        'passed' => true,
        'threshold' => 80,
        'message' => '人脸验证通过（测试模式）'
    ],
    'debug' => [
        'received_data' => $data,
        'server_time' => date('Y-m-d H:i:s'),
        'method' => $_SERVER['REQUEST_METHOD']
    ]
]);

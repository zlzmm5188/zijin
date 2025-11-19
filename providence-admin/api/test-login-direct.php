<?php
// 直接测试登录逻辑
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 记录请求信息
$log = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'none',
    'raw_input' => file_get_contents('php://input'),
    'post_data' => $_POST,
    'get_data' => $_GET
];

file_put_contents('/tmp/login_debug.log', date('Y-m-d H:i:s') . "\n" . json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n", FILE_APPEND);

echo json_encode($log, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

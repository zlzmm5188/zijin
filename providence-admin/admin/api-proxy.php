<?php
/**
 * API代理 v2（修复版本）
 */
$path = $_GET['path'] ?? '';
if (empty($path) || strpos($path, '..') !== false) {
    header('Content-Type: application/json');
    echo json_encode(['code' => -1, 'message' => '参数错误']);
    exit;
}

$apiFile = __DIR__ . '/../api/' . $path;

if (!file_exists($apiFile)) {
    header('Content-Type: application/json');
    echo json_encode(['code' => -1, 'message' => 'API文件不存在: ' . $path]);
    exit;
}

// 清除所有输出缓冲
while (ob_get_level()) {
    ob_end_clean();
}

// 包含API文件
chdir(dirname($apiFile));
require $apiFile;

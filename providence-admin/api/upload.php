<?php
/**
 * 图片上传API
 * POST /upload
 * Content-Type: multipart/form-data
 */
require_once __DIR__ . '/../config/bootstrap.php';

// 验证登录
$authUser = Auth::user();
if (!$authUser) {
    Response::error('未登录或登录已过期', 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('请求方法错误');
}

$type = $_POST['type'] ?? 'images'; // images/kyc/certificate

if (empty($_FILES)) {
    Response::error('没有文件上传');
}

// 上传文件
$results = [];

if (isset($_FILES['file'])) {
    // 单文件上传
    $result = Upload::uploadFile($_FILES['file'], $type);
    if ($result['success']) {
        $results[] = $result['url'];
    } else {
        Response::error($result['msg']);
    }
} elseif (isset($_FILES['files'])) {
    // 多文件上传
    $results = Upload::uploadMultiple($_FILES['files'], $type);
}

if (empty($results)) {
    Response::error('文件上传失败');
}

// 记录审计日志
AuditLog::log(
    'finance',
    '上传文件',
    'user',
    $authUser['user_id'],
    'upload',
    null,
    null,
    ['type' => $type, 'files' => $results]
);

Response::success([
    'urls' => $results,
    'count' => count($results)
], '上传成功');

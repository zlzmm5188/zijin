<?php
/**
 * 应用配置文件
 * 使用动态域名，避免硬编码
 */

// 动态获取当前域名
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'api.frevix.top';

return [
    'app_name' => 'PROVIDENCE',
    'app_url' => $protocol . '://' . str_replace('api.', '', $host),  // https://frevix.top
    'admin_url' => $protocol . '://' . str_replace('api.', 'houtai.', $host),  // https://houtai.frevix.top
    'api_url' => $protocol . '://' . $host,  // https://api.frevix.top
    
    // 安全配置
    'jwt_secret' => 'Providence_JWT_Secret_Key_2024_Secure_Random_String',
    'password_salt' => 'Providence_Password_Salt_2024',
    
    // 文件上传
    'upload_path' => __DIR__ . '/../uploads/',
    'upload_max_size' => 5 * 1024 * 1024, // 5MB
    'upload_allowed_ext' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
    
    // 分页配置
    'page_size' => 20,
    
    // 时区
    'timezone' => 'Asia/Shanghai',
];

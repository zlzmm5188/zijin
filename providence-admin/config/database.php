<?php
/**
 * 数据库配置文件（修复表前缀）
 */
return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'providence',
    'username' => 'providence',
    'password' => 'Providence@2024',
    'charset' => 'utf8mb4',
    'prefix' => '',  // ⭐ 去掉前缀（表没有前缀）
];

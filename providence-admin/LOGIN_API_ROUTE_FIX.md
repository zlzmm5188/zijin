# 登录API路由修复报告

## 问题描述
前端调用 `/index.php/login/account`，但路由映射表中只有 `login/login/account`，导致路由不匹配。

## 问题原因
前端代码使用 `/index.php/login/account` 路径，但API路由处理没有正确解析这种格式。

## 修复内容

### 1. 添加路由映射 ✅
在路由映射表中添加：
```php
'login/account' => 'login.php',  // 直接访问 /api/login/account
```

### 2. 优化路径解析 ✅
添加对 `/index.php/xxx` 格式路径的支持：
```php
// 处理 /index.php/xxx 格式的路径
if (strpos($path, '/index.php/') !== false) {
    $path = substr($path, strpos($path, '/index.php/') + 10);
}
```

## 修复后的路由支持

现在支持以下登录路径：
- `/api/login/account` ✅
- `/api/login/login/account` ✅（旧接口兼容）
- `/api/login` ✅
- `/index.php/login/account` ✅（新增）

## 修复文件
- `/www/wwwroot/providence-admin/api/index.php` - 已添加路由映射和路径解析

## 状态
✅ 修复完成

# KYC人脸比对Token验证修复报告

## 问题描述
用户提交KYC人脸认证时，系统提示"提交失败，请先登录"。

## 问题根源

### 1. 数据库字段错误
`kyc-face-verify.php` 原代码尝试查询数据库的 `token` 字段：
```php
$stmt = $db->prepare("SELECT id, username FROM users WHERE token = ? AND status = 1");
```

但是 `users` 表**根本没有 `token` 字段**！

### 2. Token存储方式
Providence系统使用**JWT Token**，Token不存储在数据库中：
- Token由 `Auth::generateToken()` 生成（JWT格式）
- Token存储在**前端LocalStorage**中（`providence_token`）
- 后端通过 `Auth::verifyToken()` 验证JWT签名和过期时间

## 修复方案

### 1. 引入bootstrap配置
```php
require_once __DIR__ . '/../providence-admin/config/bootstrap.php';
```

这会自动加载：
- `Auth` 类（JWT验证）
- `Database` 类（数据库连接）
- `Response` 类（标准响应）

### 2. 修改数据库连接方式
**修复前**：
```php
function getDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [...]
        );
        return $pdo;
    } catch (PDOException $e) {
        jsonResponse(-1, '数据库连接失败');
    }
}
```

**修复后**：
```php
function getDB() {
    return Database::getInstance();  // 使用统一的数据库单例
}
```

### 3. 修改Token验证逻辑
**修复前**（错误）：
```php
function verifyUser() {
    $token = $_POST['token'] ?? '';
    
    // ❌ 查询不存在的 token 字段
    $stmt = $db->prepare("SELECT id, username FROM users WHERE token = ? AND status = 1");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(-1, '登录已过期，请重新登录');
    }
    
    return $user;
}
```

**修复后**（正确）：
```php
function verifyUser() {
    // 1. 从多个来源获取token
    $token = $_POST['token'] ?? $_SERVER['HTTP_TOKEN'] ?? $_GET['token'] ?? '';
    
    // 2. 从Authorization头获取
    if (empty($token) && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'];
        if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            $token = $matches[1];
        }
    }
    
    // 3. 从getallheaders获取
    if (empty($token) && function_exists('getallheaders')) {
        $headers = getallheaders();
        $token = $headers['token'] ?? $headers['Token'] ?? '';
    }
    
    if (empty($token)) {
        jsonResponse(-1, '请先登录', ['need_login' => true]);
    }
    
    // 4. ✅ 使用Auth类验证JWT Token
    $userData = Auth::verifyToken($token);
    
    if (!$userData) {
        jsonResponse(-1, '登录已过期，请重新登录', ['need_login' => true]);
    }
    
    // 5. 从数据库获取完整用户信息（根据user_id查询）
    $db = getDB();
    $userId = $userData['user_id'];
    $stmt = $db->prepare("SELECT id, username FROM users WHERE id = ? AND status = 1");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(-1, '用户不存在或已被禁用', ['need_login' => true]);
    }
    
    return $user;
}
```

## JWT Token工作原理

### Token生成（注册/登录时）
```php
// Auth::generateToken($userId, $username)
$payload = [
    'user_id' => 12345678,
    'username' => 'TestUser123',
    'exp' => time() + 86400 * 7,  // 7天过期
    'iat' => time()
];

$token = "$header.$payload.$signature";  // JWT格式
// 返回前端存储到localStorage['providence_token']
```

### Token验证（API调用时）
```php
// Auth::verifyToken($token)
1. 验证JWT签名（防止篡改）
2. 检查是否过期（exp字段）
3. 返回用户信息：{ user_id: 12345678, username: 'TestUser123' }
```

## 前端Token发送

### 当前实现（FormData）
```javascript
const formData = new FormData();
formData.append('token', localStorage.getItem('providence_token'));
formData.append('real_name', name);
formData.append('id_card', idCard);
formData.append('id_card_file', idCardImage);
formData.append('selfie', faceImage);

fetch('/kyc-face-verify.php', {
    method: 'POST',
    body: formData
});
```

后端从 `$_POST['token']` 获取 ✅

## 测试验证

### 1. 快速测试
访问测试页面：https://copla.top/test-kyc-token.html
- 检查LocalStorage中是否有token
- 模拟调用KYC API测试token验证

### 2. 完整流程测试
1. 访问 https://copla.top/register.html（或登录）
2. 注册成功后，自动保存token到LocalStorage
3. 访问 https://copla.top/kyc-verification.html
4. 上传身份证照片
5. 进行人脸识别
6. 点击"提交认证"
7. **应该成功提交，不再提示"请先登录"** ✅

## 相关文件
- ✅ `/www/wwwroot/copla/providence/kyc-face-verify.php` - 修复Token验证
- ✅ `/www/wwwroot/copla/providence-admin/config/Auth.php` - JWT验证类
- ✅ `/www/wwwroot/copla/providence/kyc-verification-ocr.js` - 前端发送Token
- ✅ `/www/wwwroot/copla/providence/test-kyc-token.html` - Token测试工具

## 状态
✅ KYC Token验证修复完成
✅ JWT验证集成完成
✅ 数据库查询修复完成
✅ 测试工具已创建

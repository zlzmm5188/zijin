# Telegram Bot通知系统配置完成

## 配置信息
- **Bot Token**: `6659119581:AAF_JdYkhXR2cCJo19YeQdrYLCyBzYxKivE`
- **Bot用户名**: `@sadhjiosdfhosdf_bot`
- **接收Chat ID**: `6159132946`

## 文件清单
1. `/www/wwwroot/copla/providence-admin/config/TelegramNotify.php` - 通知服务类
2. `/www/wwwroot/copla/providence-admin/test-telegram-bot.php` - 测试脚本

## 测试方法
访问测试页面：https://houtaiadmin.copla.top/test-telegram-bot.php

## 可用通知方法

### 1. 用户注册通知
```php
TelegramNotify::notifyUserRegistered($username, $userId, $inviteCode);
```

### 2. 充值通知
```php
TelegramNotify::notifyRecharge($username, $userId, $amount, $currency, $orderId);
```

### 3. 提现通知
```php
TelegramNotify::notifyWithdraw($username, $userId, $amount, $currency, $orderId);
```

### 4. 投资通知
```php
TelegramNotify::notifyInvestment($username, $userId, $projectName, $amount, $currency);
```

### 5. KYC认证通知
```php
TelegramNotify::notifyKYC($username, $userId, $realname);
```

### 6. 系统错误通知
```php
TelegramNotify::notifyError($errorType, $errorMessage, $file, $line);
```

### 7. 自定义通知
```php
TelegramNotify::notify($title, $content, $icon);
```

## 集成到API示例

### 在注册API中添加通知
```php
// api/register.php
require_once __DIR__ . '/../config/TelegramNotify.php';

// ... 注册成功后
TelegramNotify::notifyUserRegistered($username, $userId, $inviteCode);
```

### 在充值API中添加通知
```php
// api/finance/recharge.php
require_once __DIR__ . '/../../config/TelegramNotify.php';

// ... 充值申请提交后
TelegramNotify::notifyRecharge($username, $userId, $amount, $currency, $orderId);
```

### 在提现API中添加通知
```php
// api/finance/withdraw.php
require_once __DIR__ . '/../../config/TelegramNotify.php';

// ... 提现申请提交后
TelegramNotify::notifyWithdraw($username, $userId, $amount, $currency, $orderId);
```

## 修改配置

如需修改Bot Token或Chat ID，编辑文件：
`/www/wwwroot/copla/providence-admin/config/TelegramNotify.php`

```php
private static $botToken = 'YOUR_BOT_TOKEN';
private static $chatId = 'YOUR_CHAT_ID';
```

## 注意事项
1. 确保服务器可以访问 `https://api.telegram.org`
2. 确保PHP已启用curl扩展
3. 消息格式使用Markdown语法
4. 建议在生产环境中添加消息队列，避免API调用阻塞

## 状态
✅ 已配置完成，等待测试

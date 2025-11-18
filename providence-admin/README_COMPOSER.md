# Providence API - Composer 依赖说明

## 📦 已安装的PHP开发库

### 核心依赖

1. **Guzzle HTTP Client** (`guzzlehttp/guzzle`)
   - 强大的HTTP客户端库
   - 用于API请求、Webhook调用
   - 支持异步请求、重试机制

2. **Monolog** (`monolog/monolog`)
   - 专业的日志记录库
   - 支持多种日志处理器（文件、数据库、邮件等）
   - 日志级别管理

3. **PHP Dotenv** (`vlucas/phpdotenv`)
   - 环境变量管理
   - 从.env文件加载配置
   - 安全存储敏感信息

4. **Firebase JWT** (`firebase/php-jwt`)
   - JWT Token生成和验证
   - 用于API身份认证
   - 支持多种加密算法

5. **Ramsey UUID** (`ramsey/uuid`)
   - UUID生成器
   - 生成唯一标识符
   - 支持多种UUID版本

6. **Respect Validation** (`respect/validation`)
   - 数据验证库
   - 表单验证、API参数验证
   - 丰富的验证规则

7. **Symfony VarDumper** (`symfony/var-dumper`)
   - 调试工具
   - 美化变量输出
   - 开发调试助手

8. **League CSV** (`league/csv`)
   - CSV文件处理
   - 导入导出功能
   - 数据报表生成

9. **PHPMailer** (`phpmailer/phpmailer`)
   - 邮件发送库
   - 支持SMTP、Sendmail等
   - HTML邮件支持

## 🚀 使用方法

### 1. 自动加载

在PHP文件顶部添加：

```php
require_once __DIR__ . '/vendor/autoload.php';
```

### 2. 使用示例

#### HTTP请求（Guzzle）
```php
use GuzzleHttp\Client;

$client = new Client();
$response = $client->get('https://api.example.com/data');
$data = json_decode($response->getBody(), true);
```

#### 日志记录（Monolog）
```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log = new Logger('api');
$log->pushHandler(new StreamHandler('logs/api.log', Logger::INFO));
$log->info('API请求', ['endpoint' => '/user/info']);
```

#### JWT Token
```php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$key = "your-secret-key";
$payload = ['user_id' => 123, 'exp' => time() + 3600];
$token = JWT::encode($payload, $key, 'HS256');
```

#### 数据验证
```php
use Respect\Validation\Validator as v;

$email = 'user@example.com';
if (v::email()->validate($email)) {
    echo "有效邮箱";
}
```

#### UUID生成
```php
use Ramsey\Uuid\Uuid;

$uuid = Uuid::uuid4();
echo $uuid->toString();
```

#### 环境变量
```php
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dbHost = $_ENV['DB_HOST'];
```

## 📁 目录结构

```
providence-admin/
├── vendor/              # Composer依赖包
├── composer.json        # 依赖配置
├── composer.lock        # 锁定版本
└── src/                 # 项目源代码（PSR-4）
```

## 🔧 常用命令

```bash
# 安装依赖
composer install

# 更新依赖
composer update

# 添加新包
composer require package/name

# 移除包
composer remove package/name

# 查看已安装包
composer show

# 自动加载优化
composer dump-autoload -o
```

## ⚠️ 注意事项

1. **不要提交vendor目录**到Git
2. **提交composer.json和composer.lock**
3. 生产环境使用 `composer install --no-dev`
4. 定期更新依赖：`composer update`

## 🔒 安全建议

- 使用.env文件存储敏感配置
- 不要将.env文件提交到版本控制
- 定期更新依赖包修复安全漏洞
- 使用 `composer audit` 检查安全漏洞

---

**创建时间**: 2025-01-XX
**PHP版本**: 8.2.28
**Composer版本**: 最新稳定版

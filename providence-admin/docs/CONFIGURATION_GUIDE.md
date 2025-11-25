# PROVIDENCE 后台管理系统配置说明

## 目录
1. [系统要求](#系统要求)
2. [安装部署](#安装部署)
3. [数据库配置](#数据库配置)
4. [目录结构](#目录结构)
5. [功能模块说明](#功能模块说明)
6. [前后台对接](#前后台对接)
7. [常见问题](#常见问题)

---

## 系统要求

### 服务器环境
- **Web服务器**: Nginx / Apache
- **PHP版本**: 7.4+ (推荐 8.0+)
- **数据库**: MySQL 5.7+ / MariaDB 10.3+
- **PHP扩展**: 
  - PDO
  - PDO_MySQL
  - JSON
  - cURL
  - mbstring
  - OpenSSL

### 推荐配置
- CPU: 2核+
- 内存: 4GB+
- 硬盘: 50GB+ SSD

---

## 安装部署

### 1. 上传文件
将 `providence-admin` 目录上传到服务器网站根目录。

### 2. 配置Nginx (推荐)

```nginx
server {
    listen 80;
    server_name admin.yourdomain.com;
    root /www/wwwroot/providence-admin;
    index index.html index.php;

    # API重写规则
    location /api/ {
        try_files $uri $uri/ /api/index.php?$query_string;
    }

    # PHP处理
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # 静态文件缓存
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }
}
```

### 3. 配置Apache (.htaccess)

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /api/
    
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php/$1 [L]
</IfModule>
```

### 4. 初始化数据库

```bash
# 进入MySQL
mysql -u root -p

# 创建数据库
CREATE DATABASE providence CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 导入数据库
mysql -u root -p providence < database/full_install.sql
mysql -u root -p providence < database/admin_management_schema.sql
```

### 5. 配置数据库连接

编辑 `config/database.php`:

```php
<?php
return [
    'host' => 'localhost',      // 数据库地址
    'port' => 3306,             // 数据库端口
    'database' => 'providence', // 数据库名
    'username' => 'root',       // 用户名
    'password' => 'your_password', // 密码
    'charset' => 'utf8mb4',
    'prefix' => 'prov_',        // 表前缀
];
```

---

## 目录结构

```
providence-admin/
├── admin/                  # 后台管理界面
│   ├── index.html         # 主框架页面
│   ├── login.html         # 登录页面
│   ├── dashboard.html     # 数据概览
│   ├── users.html         # 用户管理
│   ├── admins-manage.html # 管理员管理
│   ├── tasks.html         # 任务中心
│   ├── invite-rewards.html # 邀请奖励
│   ├── payment-config.html # 支付配置
│   ├── sms-config.html    # 短信配置
│   ├── balance-adjust.html # 资产调账
│   ├── reports.html       # 数据报表
│   └── ...
├── api/                    # API接口目录
│   ├── index.php          # API路由入口
│   ├── admin/             # 管理接口
│   │   ├── admins.php
│   │   ├── admin-save.php
│   │   ├── roles.php
│   │   ├── tasks.php
│   │   ├── invite-rules.php
│   │   ├── payment-channels.php
│   │   ├── sms-channels.php
│   │   ├── balance-adjust.php
│   │   ├── daily-reports.php
│   │   ├── export.php
│   │   └── ...
│   ├── user/              # 用户接口
│   ├── finance/           # 财务接口
│   └── ...
├── config/                 # 配置文件
│   ├── database.php       # 数据库配置
│   ├── bootstrap.php      # 引导文件
│   ├── Database.php       # 数据库类
│   ├── Response.php       # 响应类
│   └── ...
├── database/               # 数据库脚本
│   ├── full_install.sql   # 完整安装脚本
│   ├── admin_management_schema.sql # 管理模块表
│   └── ...
├── docs/                   # 文档
│   ├── API_DOCUMENTATION_V2.md
│   └── ...
└── public/                 # 静态资源
    ├── css/
    ├── js/
    └── uploads/
```

---

## 功能模块说明

### 1. 用户管理
- 用户列表查询、搜索
- 用户信息编辑
- 实名认证审核 (KYC)
- VIP等级管理
- 登录日志查看
- 团队结构树状图

### 2. 财务管理
- **充值审核**: 审核用户充值申请
- **提现审核**: 审核用户提现申请
- **资金流水**: 查看所有资金变动记录
- **资产调账**: 手动增减用户余额/积分

### 3. 项目管理
- 投资项目CRUD
- 投资订单管理
- 日利宝配置

### 4. 营销管理
- **任务中心**: 配置每日任务、一次性任务等
- **邀请奖励**: 配置注册奖励、充值返佣、投资返佣

### 5. 系统配置
- **支付通道**: 配置银行卡、支付宝、微信、USDT收款
- **短信配置**: 配置短信服务商、模板
- **管理员管理**: 管理员账号、角色权限

### 6. 数据报表
- 每日数据统计
- 趋势图表
- 数据导出 (CSV)

---

## 前后台对接

### API基础地址
后台API基础地址应与前台保持一致，默认为 `/api`。

### 跨域配置
API已配置CORS头，支持跨域请求：
```php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, token');
```

### 认证方式
- 前台用户：通过 `token` 请求头传递
- 后台管理员：通过 `localStorage` 存储 Token

### 数据同步
后台和前台共用同一数据库，数据实时同步，无需额外对接。

---

## 常见问题

### Q1: 登录后显示空白
**原因**: API接口无法访问
**解决**: 
1. 检查Nginx/Apache重写规则
2. 检查PHP是否正常运行
3. 查看浏览器控制台错误信息

### Q2: 数据库连接失败
**原因**: 数据库配置错误
**解决**: 
1. 检查 `config/database.php` 配置
2. 确认MySQL服务正常运行
3. 确认数据库用户权限

### Q3: 文件上传失败
**原因**: 目录权限或PHP配置
**解决**:
1. 设置 `public/uploads` 目录权限为 755
2. 检查 PHP 的 `upload_max_filesize` 配置
3. 检查 Nginx 的 `client_max_body_size` 配置

### Q4: 页面样式错乱
**原因**: CDN资源加载失败
**解决**:
1. 检查网络连接
2. 将CDN资源下载到本地

---

## 默认账号

- **用户名**: admin
- **密码**: admin123

⚠️ 请在首次登录后立即修改密码！

---

## 技术支持

如有问题，请查看 `docs/` 目录下的其他文档或联系技术支持。

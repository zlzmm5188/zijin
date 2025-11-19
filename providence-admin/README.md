# PROVIDENCE 后台管理系统

## 📋 项目简介

PROVIDENCE 专属后台管理系统，为前台投资理财平台提供完整的API服务和管理界面。

**开发时间**: 2024-11-10  
**技术栈**: PHP 7.4+ / MySQL 5.7+ / LayUI  
**前台地址**: https://4kp3l0iq.top

---

## 🚀 功能特性

### API接口 (已完成)
- ✅ 用户登录/注册
- ✅ 用户信息获取
- ✅ 项目列表/详情
- ✅ 投资功能
- ✅ VIP等级系统
- ✅ 积分系统
- ✅ 邀请推荐系统
- ✅ 充值提现
- ✅ 银行卡管理
- ✅ 团队管理
- ✅ 体验金系统

### 管理后台 (开发中)
- 🔄 用户管理
- 🔄 项目管理
- 🔄 财务审核
- 🔄 内容发布
- 🔄 数据统计
- 🔄 系统配置

---

## 📁 目录结构

```
providence-admin/
├── api/                    # API接口目录
│   ├── index.php          # 路由入口
│   ├── login.php          # 登录接口
│   ├── register.php       # 注册接口
│   ├── user/              # 用户相关
│   ├── project/           # 项目相关
│   ├── finance/           # 财务相关
│   ├── vip/               # VIP相关
│   ├── points/            # 积分相关
│   └── team/              # 团队相关
├── admin/                  # 管理后台 (待开发)
├── config/                 # 配置文件
│   ├── app.php            # 应用配置
│   ├── database.php       # 数据库配置
│   ├── Database.php       # 数据库操作类
│   ├── Response.php       # API响应类
│   ├── Auth.php           # 认证类
│   └── bootstrap.php      # 框架引导
├── database/               # 数据库文件
│   └── install.sql        # 安装脚本
├── public/                 # 静态资源
├── uploads/                # 上传文件
├── logs/                   # 日志文件
├── install.sh             # 安装脚本
└── README.md              # 说明文档
```

---

## 🔧 安装部署

### 1. 环境要求

- PHP 7.4 或更高版本
- MySQL 5.7 或更高版本
- Nginx 或 Apache
- PHP扩展: PDO, PDO_MySQL, JSON, OpenSSL

### 2. 安装步骤

#### 方式一：使用安装脚本 (推荐)

```bash
cd /www/wwwroot/providence-admin
chmod +x install.sh
./install.sh
```

#### 方式二：手动安装

```bash
# 1. 创建数据库
mysql -uroot -p
CREATE DATABASE providence DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# 2. 导入数据库
mysql -uroot -p providence < database/install.sql

# 3. 修改配置文件
vim config/database.php  # 修改数据库连接信息
vim config/app.php       # 修改应用配置

# 4. 设置权限
chmod -R 755 .
chmod -R 777 logs/
chmod -R 777 uploads/
```

### 3. Nginx 配置

在宝塔面板中添加反向代理或使用以下配置：

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /www/wwwroot/providence-admin/public;
    index index.php index.html;
    
    # API接口
    location /providence-admin/api/ {
        alias /www/wwwroot/providence-admin/api/;
        try_files $uri $uri/ /providence-admin/api/index.php?$query_string;
        
        location ~ \.php$ {
            fastcgi_pass unix:/tmp/php-cgi.sock;
            fastcgi_index index.php;
            include fastcgi.conf;
        }
    }
    
    # 管理后台
    location /providence-admin/admin/ {
        alias /www/wwwroot/providence-admin/admin/;
        try_files $uri $uri/ /providence-admin/admin/index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi.sock;
        fastcgi_index index.php;
        include fastcgi.conf;
    }
}
```

---

## 🔌 API接口文档

### 基础信息

- **Base URL**: `http://your-domain.com/providence-admin/api`
- **认证方式**: Token (请求头添加 `token: xxx`)
- **响应格式**: JSON

### 响应结构

```json
{
  "code": 1,           // 1成功 0失败
  "msg": "操作成功",    // 提示信息
  "data": {},          // 返回数据
  "time": 1699603200   // 时间戳
}
```

### 核心接口

#### 1. 用户登录
```http
POST /login/login/account
Content-Type: application/json

{
  "username": "user123",
  "password": "123456"
}
```

#### 2. 用户注册
```http
POST /login/reg/account
Content-Type: application/json

{
  "username": "newuser",
  "password": "123456",
  "phone": "13800138000",
  "email": "user@example.com",
  "invite_code": "ABC12345"
}
```

#### 3. 获取用户信息
```http
GET /user/user/index
Headers: token: your_token_here
```

#### 4. 项目列表
```http
GET /fund/project/all?page=1&limit=20
```

#### 5. 项目详情
```http
GET /fund/project/detail?id=1
```

#### 6. 投资项目
```http
POST /fund/project/add
Headers: token: your_token_here
Content-Type: application/json

{
  "project_id": 1,
  "amount": 10000
}
```

---

## 🗃️ 数据库设计

### 核心数据表 (20张)

1. **prov_users** - 用户表
2. **prov_vip_levels** - VIP等级配置
3. **prov_projects** - 投资项目
4. **prov_project_managers** - 项目经理
5. **prov_user_investments** - 用户投资订单
6. **prov_earnings_records** - 收益记录
7. **prov_recharge_records** - 充值记录
8. **prov_withdraw_records** - 提现记录
9. **prov_bank_cards** - 银行卡
10. **prov_balance_logs** - 资金流水
11. **prov_points_logs** - 积分明细
12. **prov_news_flash** - 新闻快讯
13. **prov_company_news** - 公司动态
14. **prov_financial_calendar** - 财经日历
15. **prov_education_courses** - 投资课堂
16. **prov_shop_products** - 积分商城
17. **prov_points_exchange_records** - 积分兑换记录
18. **prov_admins** - 管理员
19. **prov_admin_logs** - 操作日志
20. **prov_system_config** - 系统配置

---

## 🔐 默认账户

### 管理员
- 用户名: `admin`
- 密码: `admin123`

### 测试用户 (需自行注册)
- 可通过API `/login/reg/account` 注册

---

## ⚙️ 系统配置

### 修改数据库配置
编辑 `config/database.php`:
```php
return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'providence',
    'username' => 'providence',
    'password' => 'your_password',
    'charset' => 'utf8mb4',
    'prefix' => 'prov_',
];
```

### 修改应用配置
编辑 `config/app.php`:
```php
return [
    'app_url' => 'http://your-domain.com',
    'jwt_secret' => 'your_jwt_secret_key',
    'password_salt' => 'your_password_salt',
];
```

---

## 📊 开发进度

- [x] 数据库设计
- [x] 核心类库开发
- [x] 用户认证API
- [x] 项目管理API
- [x] 财务系统API (部分)
- [ ] 管理后台界面
- [ ] 定时任务 (收益发放)
- [ ] 数据统计报表

---

## 🐛 故障排查

### 1. 数据库连接失败
- 检查 `config/database.php` 配置
- 确认MySQL服务正常运行
- 验证数据库用户权限

### 2. API返回403
- 检查Nginx配置
- 确认PHP文件权限
- 查看 `logs/error.log`

### 3. Token验证失败
- 确认请求头包含正确的token
- 检查token是否过期 (7天)
- 验证JWT密钥配置

---

## 📞 技术支持

如有问题，请查看日志文件：
- PHP错误日志: `logs/error.log`
- Nginx错误日志: `/www/wwwlogs/nginx_error.log`

---

## 📝 更新日志

### v1.0.0 (2024-11-10)
- ✅ 完成核心框架搭建
- ✅ 完成用户认证系统
- ✅ 完成项目投资功能
- ✅ 完成数据库设计

---

**© 2024 PROVIDENCE | Powered by PHP**

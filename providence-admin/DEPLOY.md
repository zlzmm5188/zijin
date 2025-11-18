# PROVIDENCE 后台系统 - 快速部署指南

## 🚀 一键部署（推荐）

### 步骤1：进入目录并运行安装脚本

```bash
cd /www/wwwroot/providence-admin
chmod +x install.sh
./install.sh
```

按提示输入MySQL root密码即可完成安装。

---

## 📌 手动部署步骤

### 1. 创建数据库

通过宝塔面板或命令行：

```bash
mysql -uroot -p
```

```sql
CREATE DATABASE providence DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'providence'@'localhost' IDENTIFIED BY 'Providence@2024';
GRANT ALL PRIVILEGES ON providence.* TO 'providence'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 2. 导入数据库

```bash
cd /www/wwwroot/providence-admin
mysql -uroot -p providence < database/install.sql
```

### 3. 配置Nginx

**通过宝塔面板：**

1. 登录宝塔面板
2. 网站 -> 添加站点
   - 域名：填写您的域名或留空使用IP
   - 根目录：`/www/wwwroot/providence-admin/public`
   - PHP版本：7.4或更高

3. 配置反向代理或伪静态
   - 网站设置 -> 配置文件
   - 添加以下location配置：

```nginx
# API接口配置
location /providence-admin/api/ {
    alias /www/wwwroot/providence-admin/api/;
    index index.php;
    
    # 伪静态规则
    if (!-e $request_filename) {
        rewrite ^/providence-admin/api/(.*)$ /providence-admin/api/index.php?$1 last;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/tmp/php-cgi.sock;  # 根据实际情况修改
        fastcgi_index index.php;
        include fastcgi.conf;
        fastcgi_param SCRIPT_FILENAME $request_filename;
    }
}

# 管理后台配置
location /providence-admin/admin/ {
    alias /www/wwwroot/providence-admin/admin/;
    index index.php index.html;
}
```

### 4. 设置文件权限

```bash
cd /www/wwwroot/providence-admin
chmod -R 755 .
chmod -R 777 logs/
chmod -R 777 uploads/
```

### 5. 修改配置文件

编辑 `config/database.php`:
```bash
vim config/database.php
```

确认数据库信息正确：
```php
return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'providence',
    'username' => 'providence',
    'password' => 'Providence@2024',  // 如果修改了密码，请相应更改
    'charset' => 'utf8mb4',
    'prefix' => 'prov_',
];
```

---

## ✅ 测试验证

### 1. 测试API是否正常

```bash
curl http://YOUR_DOMAIN/providence-admin/api/fund/project/all
```

预期返回：
```json
{
  "code": 1,
  "msg": "操作成功",
  "data": {
    "list": [...],
    "total": 3,
    "page": 1,
    "pageSize": 20
  },
  "time": 1699603200
}
```

### 2. 测试注册功能

```bash
curl -X POST http://YOUR_DOMAIN/providence-admin/api/login/reg/account \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "password": "123456"
  }'
```

### 3. 测试登录功能

```bash
curl -X POST http://YOUR_DOMAIN/providence-admin/api/login/login/account \
  -H "Content-Type: application/json" \
  -d '{
    "username": "testuser",
    "password": "123456"
  }'
```

---

## 🔧 前台对接配置

修改前台的 `config.js` 文件：

```javascript
const API_CONFIG = {
  // 修改为您的后台API地址
  baseURL: 'http://YOUR_DOMAIN/providence-admin/api',
  
  // 其他配置保持不变
  tokenKey: 'providence_token',
  timeout: 10000,
  debug: true
};
```

---

## 📊 当前状态

✅ **已完成功能：**
- 数据库设计（20张表）
- 核心框架（Database、Response、Auth类）
- 用户系统API（登录、注册、信息获取）
- 项目系统API（列表、详情、投资）
- VIP等级系统
- 积分系统
- 邀请推荐系统

🔄 **待开发功能：**
- 管理后台界面
- 充值提现审核功能
- 财务管理完整API
- 内容管理（新闻、课堂等）
- 数据统计报表
- 定时任务（自动收益发放）

---

## 🎯 后续开发计划

### 第一阶段：完善API接口
1. 充值提现完整流程
2. 银行卡管理API
3. 订单查询API
4. VIP升级API
5. 积分兑换API

### 第二阶段：管理后台界面
1. 登录界面
2. 仪表盘
3. 用户管理
4. 项目管理
5. 财务审核
6. 内容发布

### 第三阶段：定时任务
1. 每日收益自动发放
2. VIP等级自动升级
3. 数据统计生成

---

## 🆘 常见问题

### Q1: API返回空白或404
**A:** 检查Nginx配置，确保location规则正确，PHP-FPM正常运行

### Q2: 数据库连接失败
**A:** 检查config/database.php配置，确认MySQL服务运行，用户权限正确

### Q3: Token验证失败
**A:** 确认请求头包含token字段，检查config/app.php中的jwt_secret配置

### Q4: 文件上传失败
**A:** 检查uploads/目录权限，确保为777

---

## 📞 技术支持

- **文档位置**: /www/wwwroot/providence-admin/README.md
- **日志位置**: /www/wwwroot/providence-admin/logs/error.log
- **数据库SQL**: /www/wwwroot/providence-admin/database/install.sql

---

**部署完成后，请立即：**
1. 修改默认管理员密码
2. 修改config/app.php中的密钥
3. 修改数据库用户密码
4. 配置HTTPS证书（生产环境）

**© 2024 PROVIDENCE**

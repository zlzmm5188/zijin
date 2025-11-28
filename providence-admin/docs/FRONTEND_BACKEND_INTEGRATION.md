# 前后台对接操作指引

## 概述

PROVIDENCE 系统采用前后端分离架构：
- **前台 (providence)**: 用户端移动网页应用
- **后台 (providence-admin)**: 管理员管理界面

两者共用同一数据库和API服务，实现数据实时同步。

---

## 系统架构

```
┌─────────────────┐     ┌─────────────────┐
│   前台 (Web)    │     │   后台 (Admin)  │
│  providence/    │     │ providence-admin│
└────────┬────────┘     └────────┬────────┘
         │                       │
         └───────────┬───────────┘
                     │
              ┌──────┴──────┐
              │   API 层    │
              │ /api/*.php  │
              └──────┬──────┘
                     │
              ┌──────┴──────┐
              │   数据库    │
              │   MySQL     │
              └─────────────┘
```

---

## 目录部署

### 推荐部署结构
```
/www/wwwroot/
├── yourdomain.com/          # 前台网站
│   └── (providence 内容)
├── admin.yourdomain.com/    # 后台管理
│   └── (providence-admin 内容)
└── api.yourdomain.com/      # API服务
    └── (providence-admin/api 内容)
```

### 单域名部署
```
/www/wwwroot/yourdomain.com/
├── /                        # 前台 (providence)
├── /admin/                  # 后台 (providence-admin/admin)
└── /api/                    # API (providence-admin/api)
```

---

## API 地址配置

### 前台配置 (config.js)
```javascript
// providence/config.js
const API_CONFIG = {
    BASE_URL: 'https://api.yourdomain.com',
    // 或单域名部署
    // BASE_URL: '/api',
};
```

### 后台配置
后台页面中的API调用已配置为相对路径 `../api`，无需额外配置。

---

## 数据库配置

前后台共用同一数据库，只需配置一次：

```php
// providence-admin/config/database.php
return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'providence',
    'username' => 'db_user',
    'password' => 'db_password',
    'charset' => 'utf8mb4',
    'prefix' => 'prov_',
];
```

---

## 数据表对应关系

| 功能模块 | 前台使用 | 后台管理 | 数据表 |
|----------|----------|----------|--------|
| 用户 | ✅ 注册/登录/资料 | ✅ 列表/审核/编辑 | prov_users |
| 钱包 | ✅ 余额/交易 | ✅ 流水/调账 | prov_wallets, prov_wallet_logs |
| 充值 | ✅ 申请充值 | ✅ 审核充值 | prov_recharge_records |
| 提现 | ✅ 申请提现 | ✅ 审核提现 | prov_withdraw_records |
| 项目 | ✅ 浏览/投资 | ✅ 发布/管理 | prov_invest_projects |
| 订单 | ✅ 我的订单 | ✅ 订单列表 | prov_invest_orders |
| VIP | ✅ VIP等级/权益 | ✅ 规则配置 | prov_vip_interest_rules |
| 任务 | ✅ 任务列表/完成 | ✅ 任务配置 | prov_tasks, prov_user_tasks |
| 邀请 | ✅ 邀请好友/奖励 | ✅ 规则配置 | prov_invite_reward_rules |
| KYC | ✅ 实名提交 | ✅ 实名审核 | prov_user_kyc |

---

## 主要API接口对接

### 用户相关

| 功能 | 前台接口 | 后台接口 |
|------|----------|----------|
| 用户登录 | POST /login/account | - |
| 用户注册 | POST /login/reg/account | - |
| 用户信息 | GET /user/user/index | GET /admin/users |
| 用户详情 | - | GET /admin/user-detail |

### 财务相关

| 功能 | 前台接口 | 后台接口 |
|------|----------|----------|
| 申请充值 | POST /user/recharge/add | - |
| 充值审核 | - | POST /admin/recharge-approve |
| 申请提现 | POST /pay/pay/withdraw | - |
| 提现审核 | - | POST /admin/withdraw-approve |

### 项目相关

| 功能 | 前台接口 | 后台接口 |
|------|----------|----------|
| 项目列表 | GET /fund/project/all | GET /admin/projects |
| 项目详情 | GET /fund/project/detail | GET /admin/project-detail |
| 投资下单 | POST /fund/project/add | - |
| 订单管理 | GET /user/order/list | GET /admin/orders |

---

## 工作流程示例

### 充值流程
```
1. 用户在前台申请充值
   POST /user/recharge/add
   
2. 系统创建充值记录 (状态: 待审核)
   INSERT INTO prov_recharge_records

3. 管理员在后台看到待审核充值
   GET /admin/recharges?status=0

4. 管理员审核通过
   POST /admin/recharge-approve

5. 系统更新用户余额
   UPDATE prov_wallets

6. 用户在前台看到余额更新
   GET /user/user/index
```

### 提现流程
```
1. 用户在前台申请提现
   POST /pay/pay/withdraw

2. 系统冻结用户余额，创建提现记录
   
3. 管理员在后台看到待审核提现
   GET /admin/withdrawals?status=0

4. 管理员审核通过/打款
   POST /admin/withdraw-approve

5. 系统解冻并扣减余额
```

---

## 安全注意事项

### 1. Token 验证
- 前台API需要用户Token验证
- 后台API需要管理员Token验证
- Token存储在localStorage中

### 2. 权限控制
后台管理员按角色分配权限：
- super_admin: 所有权限
- finance_admin: 财务相关权限
- user_admin: 用户相关权限
- operation_admin: 项目/活动权限

### 3. 日志记录
- 用户登录日志: prov_user_login_logs
- 管理员登录日志: prov_admin_login_logs
- 管理员操作日志: prov_admin_operation_logs
- 资产调账日志: prov_balance_adjustments

---

## 常见对接问题

### Q1: 前台看不到后台添加的数据
**原因**: 缓存问题
**解决**: 
- 检查Redis/Memcached缓存
- 清除浏览器缓存
- 确认数据已正确入库

### Q2: 后台审核后前台未更新
**原因**: 前台未刷新数据
**解决**:
- 用户重新登录
- 前台刷新页面
- 检查API响应

### Q3: 跨域请求失败
**原因**: CORS配置问题
**解决**:
```php
// API入口添加CORS头
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, token');
```

---

## 部署检查清单

- [ ] 数据库配置正确
- [ ] API接口可访问
- [ ] 前后台域名/路径配置
- [ ] CORS跨域配置
- [ ] 文件上传目录权限
- [ ] 定时任务配置 (收益结算等)
- [ ] SSL证书配置
- [ ] 修改默认管理员密码

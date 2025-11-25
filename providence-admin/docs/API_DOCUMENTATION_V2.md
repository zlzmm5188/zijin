# PROVIDENCE 后台管理系统 API 接口文档

## 版本信息
- **版本**: v2.0
- **更新日期**: 2024-11-25
- **基础URL**: `/api`

---

## 目录

1. [认证接口](#认证接口)
2. [管理员管理](#管理员管理)
3. [用户管理](#用户管理)
4. [财务管理](#财务管理)
5. [支付配置](#支付配置)
6. [短信配置](#短信配置)
7. [任务中心](#任务中心)
8. [邀请奖励](#邀请奖励)
9. [数据报表](#数据报表)
10. [系统配置](#系统配置)

---

## 通用规范

### 请求头
```
Content-Type: application/json
Authorization: Bearer {token}
```

### 响应格式
```json
{
    "code": 1,        // 1=成功, 0=失败
    "msg": "操作成功",
    "data": {}        // 返回数据
}
```

### 分页参数
- `page`: 页码，默认1
- `limit`: 每页数量，默认20，最大100

### 分页响应
```json
{
    "code": 1,
    "data": {
        "code": 0,
        "count": 100,
        "data": []
    }
}
```

---

## 认证接口

### 管理员登录
**POST** `/admin/auth/login`

请求参数：
```json
{
    "username": "admin",
    "password": "password123"
}
```

响应：
```json
{
    "code": 1,
    "msg": "登录成功",
    "data": {
        "token": "xxx",
        "expire_at": 1700000000,
        "admin": {
            "id": 1,
            "username": "admin",
            "real_name": "系统管理员",
            "role_name": "超级管理员",
            "role_code": "super_admin",
            "permissions": ["*"]
        }
    }
}
```

---

## 管理员管理

### 获取管理员列表
**GET** `/admin/admins`

参数：
| 参数 | 类型 | 说明 |
|------|------|------|
| page | int | 页码 |
| limit | int | 每页数量 |
| keyword | string | 搜索关键词 |
| role_id | int | 角色ID |
| status | int | 状态(1正常/0禁用) |

### 保存管理员
**POST** `/admin/admin-save`

请求参数：
```json
{
    "id": 0,            // 0=新增, >0=编辑
    "username": "test",
    "password": "123456",
    "real_name": "测试",
    "phone": "13800138000",
    "email": "test@example.com",
    "role_id": 2,
    "status": 1
}
```

### 获取角色列表
**GET** `/admin/roles`

### 保存角色
**POST** `/admin/role-save`

请求参数：
```json
{
    "id": 0,
    "name": "财务管理员",
    "code": "finance_admin",
    "description": "管理充值提现",
    "permissions": ["finance.*"],
    "status": 1,
    "sort_order": 1
}
```

---

## 用户管理

### 获取用户列表
**GET** `/admin/users`

参数：
| 参数 | 类型 | 说明 |
|------|------|------|
| page | int | 页码 |
| limit | int | 每页数量 |
| keyword | string | 搜索用户名/手机号 |

### 获取用户详情
**GET** `/admin/user-detail`

参数：
| 参数 | 类型 | 说明 |
|------|------|------|
| id | int | 用户ID |

### 更新用户信息
**POST** `/admin/user-update`

### 重置用户密码
**POST** `/admin/user-reset-password`

---

## 财务管理

### 获取充值列表
**GET** `/admin/recharges`

参数：
| 参数 | 类型 | 说明 |
|------|------|------|
| status | int | 状态(0待审/1通过/2拒绝) |

### 审核充值-通过
**POST** `/admin/recharge-approve`

```json
{
    "id": 1,
    "remark": "审核通过"
}
```

### 审核充值-拒绝
**POST** `/admin/recharge-reject`

```json
{
    "id": 1,
    "reason": "凭证不清晰"
}
```

### 获取提现列表
**GET** `/admin/withdrawals`

### 审核提现-通过
**POST** `/admin/withdraw-approve`

### 审核提现-拒绝
**POST** `/admin/withdraw-reject`

### 获取资金流水
**GET** `/admin/wallet-logs`

参数：
| 参数 | 类型 | 说明 |
|------|------|------|
| user_id | int | 用户ID |
| type | string | 类型 |

### 资产调账
**POST** `/admin/balance-adjust`

请求参数：
```json
{
    "user_id": 123,
    "currency": "CNY",      // CNY/USDT
    "type": "ADD",          // ADD/SUBTRACT
    "target": "balance",    // balance/frozen/points
    "amount": 100.00,
    "reason": "系统补偿",
    "remark": "订单xxx补偿"
}
```

响应：
```json
{
    "code": 1,
    "msg": "调账成功",
    "data": {
        "order_no": "ADJ20241125123456",
        "before_amount": 1000.00,
        "after_amount": 1100.00
    }
}
```

### 获取调账记录
**GET** `/admin/balance-adjustments`

---

## 支付配置

### 获取支付通道列表
**GET** `/admin/payment-channels`

参数：
| 参数 | 类型 | 说明 |
|------|------|------|
| type | string | 类型(bank/alipay/wechat/usdt) |
| status | int | 状态 |

### 保存支付通道
**POST** `/admin/payment-channel-save`

请求参数：
```json
{
    "id": 0,
    "name": "支付宝收款",
    "code": "alipay_main",
    "type": "alipay",
    "mode": "qrcode",
    "qrcode_image": "/uploads/qrcode.png",
    "account_info": {
        "account": "13800138000",
        "name": "张三"
    },
    "min_amount": 100,
    "max_amount": 50000,
    "fee_rate": 0,
    "fee_fixed": 0,
    "daily_limit": 100000,
    "priority": 10,
    "status": 1
}
```

---

## 短信配置

### 获取短信通道列表
**GET** `/admin/sms-channels`

### 保存短信通道
**POST** `/admin/sms-channel-save`

请求参数：
```json
{
    "id": 0,
    "name": "阿里云短信",
    "code": "aliyun_main",
    "provider": "aliyun",
    "config": {
        "appId": "xxx",
        "appSecret": "xxx",
        "sign": "PROVIDENCE"
    },
    "templates": [],
    "priority": 10,
    "daily_limit": 1000,
    "status": 1
}
```

### 获取短信发送记录
**GET** `/admin/sms-logs`

参数：
| 参数 | 类型 | 说明 |
|------|------|------|
| phone | string | 手机号 |
| status | int | 状态(0待发/1成功/2失败) |

---

## 任务中心

### 获取任务列表
**GET** `/admin/tasks`

参数：
| 参数 | 类型 | 说明 |
|------|------|------|
| type | string | 类型(daily/once/invite/invest) |
| status | int | 状态 |

### 保存任务
**POST** `/admin/task-save`

请求参数：
```json
{
    "id": 0,
    "name": "每日签到",
    "code": "daily_checkin",
    "type": "daily",
    "description": "每日签到获取积分",
    "reward_type": "points",
    "reward_amount": 10,
    "target_value": 1,
    "target_unit": "次",
    "vip_limit": 0,
    "sort_order": 1,
    "status": 1
}
```

---

## 邀请奖励

### 获取奖励规则列表
**GET** `/admin/invite-rules`

参数：
| 参数 | 类型 | 说明 |
|------|------|------|
| type | string | 类型(register/invest/recharge) |

### 保存奖励规则
**POST** `/admin/invite-rule-save`

请求参数：
```json
{
    "id": 0,
    "name": "一级充值返佣",
    "type": "recharge",
    "level": 1,
    "reward_type": "percent",
    "reward_value": 0.02,
    "min_amount": 100,
    "max_reward": 1000,
    "vip_limit": 0,
    "status": 1
}
```

### 获取奖励记录
**GET** `/admin/invite-logs`

参数：
| 参数 | 类型 | 说明 |
|------|------|------|
| user_id | int | 用户ID |
| type | string | 类型 |

---

## 数据报表

### 获取每日报表
**GET** `/admin/daily-reports`

参数：
| 参数 | 类型 | 说明 |
|------|------|------|
| start_date | string | 开始日期(YYYY-MM-DD) |
| end_date | string | 结束日期(YYYY-MM-DD) |

响应：
```json
{
    "code": 1,
    "data": {
        "data": [
            {
                "date": "2024-11-25",
                "new_users": 10,
                "active_users": 50,
                "recharge_count": 5,
                "recharge_amount": 50000.00,
                "withdraw_count": 3,
                "withdraw_amount": 20000.00,
                "invest_amount": 100000.00,
                "profit_amount": 1000.00,
                "net_income": 30000.00
            }
        ],
        "summary": {
            "total_new_users": 100,
            "total_recharge_amount": 500000.00,
            "total_withdraw_amount": 200000.00,
            "total_net_income": 300000.00
        }
    }
}
```

### 数据导出
**POST** `/admin/export`

请求参数：
```json
{
    "type": "users",    // users/orders/daily_reports
    "params": {
        "start_date": "2024-01-01",
        "end_date": "2024-12-31"
    }
}
```

响应：
```json
{
    "code": 1,
    "data": {
        "task_id": 1,
        "filename": "users_20241125.csv",
        "file_size": 1024,
        "row_count": 100,
        "content": "base64编码的CSV内容",
        "content_type": "text/csv"
    }
}
```

---

## 系统配置

### 获取系统配置
**GET** `/admin/configs`

参数：
| 参数 | 类型 | 说明 |
|------|------|------|
| group | string | 配置分组 |

响应：
```json
{
    "code": 1,
    "data": {
        "data": [
            {
                "group": "site",
                "group_name": "网站设置",
                "items": [
                    {
                        "key": "name",
                        "value": "PROVIDENCE",
                        "type": "string",
                        "description": "网站名称"
                    }
                ]
            }
        ]
    }
}
```

### 保存系统配置
**POST** `/admin/config-save`

请求参数（批量保存）：
```json
{
    "configs": [
        {
            "group": "site",
            "key": "name",
            "value": "PROVIDENCE",
            "type": "string",
            "description": "网站名称"
        }
    ]
}
```

单个保存：
```json
{
    "group": "finance",
    "key": "min_recharge",
    "value": "100",
    "type": "number",
    "description": "最低充值金额"
}
```

---

## 错误码说明

| 错误码 | 说明 |
|--------|------|
| 1 | 成功 |
| 0 | 失败 |
| 401 | 未授权/Token无效 |
| 403 | 无权限 |
| 404 | 资源不存在 |
| 405 | 请求方式错误 |
| 500 | 服务器错误 |

---

## 更新日志

### v2.0 (2024-11)
- 新增管理员管理模块
- 新增短信配置模块
- 新增支付通道配置模块
- 新增任务中心模块
- 新增邀请奖励配置模块
- 新增资产调账功能
- 新增数据报表与导出功能
- 新增系统配置管理

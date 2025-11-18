# Providence 后台管理系统 - API 接口文档

## 📋 文档版本
- 版本号：v2.0
- 更新时间：2025-11-11
- 适用系统：Providence 投资管理系统后台

---

## 🔐 认证说明

### 认证方式
所有API请求需要携带Token进行身份验证：
- **Header**: `Authorization: Bearer {token}`
- **Cookie**: `admin_token={token}`

### 统一响应格式
```json
{
  "code": 1,           // 1=成功, -1=失败, 401=未登录, 403=无权限
  "message": "string", // 响应消息
  "data": {}           // 响应数据
}
```

---

## 📊 1. 仪表盘统计

### 1.1 获取系统统计数据
**接口**: `GET /api/admin/stats`
**权限**: 管理员
**响应**:
```json
{
  "code": 1,
  "data": {
    "total_users": 1000,
    "total_orders": 5000,
    "total_amount": "1000000.00000000",
    "today_new_users": 10,
    "pending_kyc": 5,
    "pending_recharge": 3,
    "pending_withdraw": 8
  }
}
```

---

## 👥 2. 用户管理

### 2.1 用户列表
**接口**: `GET /api/admin/users`
**权限**: 管理员
**参数**:
- `page` (int): 页码，默认1
- `limit` (int): 每页数量，默认20
- `keyword` (string): 搜索关键词（用户名/手机号）
- `vip_level` (int): VIP等级筛选
- `status` (int): 状态筛选（0=禁用，1=正常）

**响应**:
```json
{
  "code": 0,  // ⚠️ 特殊：LayUI表格要求code=0
  "msg": "",
  "count": 1000,
  "data": [
    {
      "id": 1,
      "username": "user001",
      "phone": "138****0001",
      "email": "user@example.com",
      "vip_level": 3,
      "total_invest": "50000.00000000",
      "balance": "1000.00000000",
      "status": 1,
      "is_internal": 0,
      "created_at": "2025-01-01 00:00:00"
    }
  ]
}
```

### 2.2 用户详情
**接口**: `GET /api/admin/user-detail`
**权限**: 管理员
**参数**:
- `id` (int): 用户ID

**响应**:
```json
{
  "code": 1,
  "data": {
    "id": 1,
    "username": "user001",
    "phone": "13800000001",
    "email": "user@example.com",
    "vip_level": 3,
    "total_invest": "50000.00000000",
    "balance_cny": "1000.00000000",
    "balance_usdt": "100.00000000",
    "parent_id": 0,
    "inviter_username": null,
    "team_count": 10,
    "running_orders": 5,
    "total_profit": "5000.00000000",
    "kyc_status": 1,
    "kyc_name": "张三",
    "kyc_id_number": "110101********1234"
  }
}
```

### 2.3 更新用户信息
**接口**: `POST /api/admin/user-update`
**权限**: 管理员
**参数**:
```json
{
  "id": 1,
  "username": "newname",
  "phone": "13800000001",
  "email": "new@example.com",
  "vip_level": 4,
  "status": 1,
  "is_internal": 0
}
```

**响应**:
```json
{
  "code": 1,
  "message": "更新成功",
  "data": {}
}
```

### 2.4 重置用户密码
**接口**: `POST /api/admin/user-reset-password`
**权限**: 管理员
**参数**:
```json
{
  "user_id": 1,
  "new_password": "123456"
}
```

### 2.5 设置内部用户
**接口**: `POST /api/admin/user-set-internal`
**权限**: 管理员
**参数**:
```json
{
  "user_id": 1,
  "is_internal": 1  // 0=否, 1=是
}
```

### 2.6 用户团队树
**接口**: `GET /api/admin/user-team-tree`
**权限**: 管理员
**参数**:
- `user_id` (int): 用户ID

**响应**:
```json
{
  "code": 1,
  "data": {
    "id": 1,
    "username": "user001",
    "children": [
      {
        "id": 2,
        "username": "user002",
        "children": []
      }
    ]
  }
}
```

---

## 📋 3. 实名认证管理

### 3.1 实名审核通过
**接口**: `POST /api/admin/kyc-approve`
**权限**: 管理员
**参数**:
```json
{
  "id": 1,         // KYC记录ID
  "remark": "审核通过"
}
```

### 3.2 实名审核拒绝
**接口**: `POST /api/admin/kyc-reject`
**权限**: 管理员
**参数**:
```json
{
  "id": 1,
  "reason": "证件照不清晰"
}
```

---

## 💰 4. 充值管理

### 4.1 充值记录列表
**接口**: `GET /api/admin/recharges`
**权限**: 管理员
**参数**:
- `page` (int): 页码
- `limit` (int): 每页数量
- `status` (int): 状态（0=待审核，1=已通过，2=已拒绝）
- `currency` (string): 币种（CNY/USDT）
- `keyword` (string): 搜索关键词

**响应**:
```json
{
  "code": 1,
  "data": {
    "list": [
      {
        "id": 1,
        "user_id": 1,
        "username": "user001",
        "phone": "138****0001",
        "amount": "1000.00000000",
        "currency": "CNY",
        "payment_method": "银行转账",
        "order_no": "R202501010001",
        "certificate_images": "[\"https://...\"]",
        "status": 0,
        "created_at": "2025-01-01 10:00:00"
      }
    ],
    "pagination": {
      "current_page": 1,
      "total_pages": 10,
      "total": 200,
      "limit": 20
    },
    "stats": {
      "pending": 5,
      "approved_today": 10,
      "total_amount_today": "50000.00000000"
    }
  }
}
```

### 4.2 充值审核通过
**接口**: `POST /api/admin/recharge-approve`
**权限**: 管理员
**参数**:
```json
{
  "id": 1,
  "remark": "审核通过"
}
```

**响应**:
```json
{
  "code": 1,
  "message": "充值审核通过",
  "data": {
    "recharge_id": 1,
    "user_id": 1,
    "amount": "1000.00000000",
    "new_balance": "11000.00000000"
  }
}
```

### 4.3 充值审核拒绝
**接口**: `POST /api/admin/recharge-reject`
**权限**: 管理员
**参数**:
```json
{
  "id": 1,
  "reason": "凭证无效"
}
```

---

## 💸 5. 提现管理

### 5.1 提现记录列表
**接口**: `GET /api/admin/withdrawals`
**权限**: 管理员
**参数**:
- `page` (int): 页码
- `limit` (int): 每页数量
- `status` (int): 状态（0=待审核，1=已通过，2=已拒绝，3=已打款）
- `currency` (string): 币种
- `keyword` (string): 搜索关键词

**响应**: 结构类似充值记录列表

### 5.2 提现审核通过
**接口**: `POST /api/admin/withdraw-approve`
**权限**: 管理员
**参数**:
```json
{
  "id": 1,
  "actual_amount": "990.00000000",  // 实际打款金额（扣除手续费后）
  "remark": "审核通过"
}
```

**响应**:
```json
{
  "code": 1,
  "message": "提现审核通过",
  "data": {
    "withdraw_id": 1,
    "user_id": 1,
    "amount": "1000.00000000",
    "actual_amount": "990.00000000",
    "fee": "10.00000000"
  }
}
```

### 5.3 提现审核拒绝
**接口**: `POST /api/admin/withdraw-reject`
**权限**: 管理员
**参数**:
```json
{
  "id": 1,
  "reason": "银行卡信息错误",
  "return_balance": true  // 是否退回余额
}
```

---

## 📊 6. 订单管理

### 6.1 订单列表
**接口**: `GET /api/admin/orders`
**权限**: 管理员
**参数**:
- `page` (int): 页码
- `limit` (int): 每页数量
- `status` (int): 状态（0=运行中，1=已完成，2=已退款）
- `start_date` (string): 开始日期
- `end_date` (string): 结束日期
- `keyword` (string): 搜索关键词

**响应**:
```json
{
  "code": 1,
  "data": {
    "list": [
      {
        "id": 1,
        "order_no": "O202501010001",
        "user_id": 1,
        "username": "user001",
        "project_name": "稳健计划",
        "invest_amount": "10000.00000000",
        "profit": "300.00000000",
        "cycle_days": 30,
        "status": 0,
        "start_date": "2025-01-01",
        "end_date": "2025-01-31",
        "created_at": "2025-01-01 10:00:00"
      }
    ],
    "pagination": {...},
    "stats": {
      "running": 100,
      "completed_today": 10
    }
  }
}
```

### 6.2 订单详情
**接口**: `GET /api/admin/order-detail`
**权限**: 管理员
**参数**:
- `id` (int): 订单ID

---

## 🏦 7. 项目管理

### 7.1 项目列表
**接口**: `GET /api/admin/projects`
**权限**: 管理员
**参数**:
- `page` (int): 页码
- `limit` (int): 每页数量
- `status` (int): 状态

**响应**:
```json
{
  "code": 1,
  "data": {
    "list": [
      {
        "id": 1,
        "name": "稳健计划",
        "rate": "0.01000000",
        "vip_extra_rate": "0.00300000",
        "total_rate": "0.01300000",
        "cycle_days": 30,
        "min_invest": "1000.00000000",
        "max_invest": "100000.00000000",
        "total_amount": "1000000.00000000",
        "sold": "500000.00000000",
        "remain": "500000.00000000",
        "status": 1,
        "created_at": "2025-01-01 00:00:00"
      }
    ]
  }
}
```

### 7.2 项目详情
**接口**: `GET /api/admin/project-detail`
**权限**: 管理员
**参数**:
- `id` (int): 项目ID

### 7.3 保存项目
**接口**: `POST /api/admin/project-save`
**权限**: 管理员
**参数**:
```json
{
  "id": 0,  // 0=新增, >0=编辑
  "name": "稳健计划",
  "rate": "0.01000000",
  "vip_extra_rate": "0.00300000",
  "cycle_days": 30,
  "min_invest": "1000.00000000",
  "max_invest": "100000.00000000",
  "total_amount": "1000000.00000000",
  "currency": "CNY",
  "status": 1
}
```

---

## 💼 8. 钱包流水

### 8.1 钱包流水列表
**接口**: `GET /api/admin/wallet-logs`
**权限**: 管理员
**参数**:
- `page` (int): 页码
- `limit` (int): 每页数量
- `user_id` (int): 用户ID
- `type` (string): 类型（recharge/withdraw/invest/profit/refund/ribao_in/ribao_out/ribao_profit）
- `currency` (string): 币种
- `start_date` (string): 开始日期
- `end_date` (string): 结束日期

**响应**:
```json
{
  "code": 1,
  "data": {
    "list": [
      {
        "id": 1,
        "user_id": 1,
        "username": "user001",
        "type": "recharge",
        "amount": "1000.00000000",
        "balance_before": "10000.00000000",
        "balance_after": "11000.00000000",
        "currency": "CNY",
        "description": "充值到账",
        "created_at": "2025-01-01 10:00:00"
      }
    ],
    "pagination": {...},
    "stats": {
      "total_in": "50000.00000000",
      "total_out": "20000.00000000",
      "count": 100
    }
  }
}
```

---

## 👤 9. 登录历史

### 9.1 登录日志列表
**接口**: `GET /api/admin/login-logs`
**权限**: 管理员
**参数**:
- `page` (int): 页码
- `limit` (int): 每页数量
- `user_id` (int): 用户ID
- `status` (int): 状态（0=失败，1=成功）
- `start_date` (string): 开始日期
- `end_date` (string): 结束日期
- `keyword` (string): 搜索关键词

**响应**:
```json
{
  "code": 1,
  "data": {
    "list": [
      {
        "id": 1,
        "user_id": 1,
        "username": "user001",
        "phone": "138****0001",
        "login_ip": "192.168.1.1",
        "login_location": "北京市",
        "device_type": "iOS",
        "login_time": "2025-01-01 10:00:00",
        "status": 1,
        "remark": null
      }
    ],
    "pagination": {...},
    "stats": {
      "total_logins": 1000,
      "success_today": 100,
      "failed_today": 5
    }
  }
}
```

---

## 💎 10. 日利宝管理

### 10.1 日利宝用户列表
**接口**: `GET /api/admin/ribao/users`
**权限**: 管理员
**参数**:
- `page` (int): 页码
- `limit` (int): 每页数量
- `keyword` (string): 搜索关键词
- `min_balance` (string): 最小余额

**响应**:
```json
{
  "code": 1,
  "data": {
    "list": [
      {
        "user_id": 1,
        "username": "user001",
        "phone": "138****0001",
        "vip_level": 3,
        "ribao_balance": "50000.00000000",
        "ribao_total_profit": "1500.00000000",
        "ribao_yesterday_profit": "50.00000000",
        "last_update": "2025-01-01 00:00:00"
      }
    ],
    "pagination": {...},
    "stats": {
      "total_balance": "5000000.00000000",
      "total_profit": "150000.00000000",
      "user_count": 100
    }
  }
}
```

### 10.2 日利宝收益记录
**接口**: `GET /api/admin/ribao/profits`
**权限**: 管理员
**参数**:
- `page` (int): 页码
- `limit` (int): 每页数量
- `user_id` (int): 用户ID
- `start_date` (string): 开始日期
- `end_date` (string): 结束日期

**响应**:
```json
{
  "code": 1,
  "data": {
    "list": [
      {
        "id": 1,
        "user_id": 1,
        "username": "user001",
        "amount": "50.00000000",
        "balance_before": "50000.00000000",
        "balance_after": "50050.00000000",
        "description": "日利宝每日收益",
        "created_at": "2025-01-01 00:00:00"
      }
    ],
    "pagination": {...},
    "stats": {
      "total_profit": "15000.00000000",
      "count": 300
    }
  }
}
```

### 10.3 获取日利宝配置
**接口**: `GET /api/admin/ribao/config`
**权限**: 管理员
**响应**:
```json
{
  "code": 1,
  "data": {
    "daily_rate": "0.00100000",
    "min_amount": "100.00000000",
    "max_amount": "1000000.00000000",
    "is_enabled": true,
    "settlement_time": "00:00:00"
  }
}
```

### 10.4 保存日利宝配置
**接口**: `POST /api/admin/ribao/config-save`
**权限**: 管理员
**参数**:
```json
{
  "daily_rate": "0.00100000",
  "min_amount": "100.00000000",
  "max_amount": "1000000.00000000",
  "is_enabled": true,
  "settlement_time": "00:00:00"
}
```

---

## 📝 注意事项

### 金额字段格式
⚠️ **所有金额字段统一使用 decimal string 格式**
- 格式：`"1000.00000000"` (8位小数)
- 禁止使用：int、float、科学计数法
- 前端显示时可以四舍五入，但API传输必须保持精度

### 分页参数
- `page`: 从1开始
- `limit`: 建议范围1-100，超出自动限制

### 错误代码
- `1`: 成功
- `-1`: 失败（通用错误）
- `401`: 未登录
- `403`: 无权限
- `404`: 资源不存在
- `422`: 参数验证失败

### 日期格式
- 日期时间：`Y-m-d H:i:s` (例：2025-01-01 10:00:00)
- 日期：`Y-m-d` (例：2025-01-01)
- 时间：`H:i:s` (例：10:00:00)

---

## 🔄 更新记录

### v2.0 (2025-11-11)
- ✅ 新增充值审核管理API
- ✅ 新增提现审核管理API
- ✅ 新增用户钱包流水API
- ✅ 新增用户登录历史API
- ✅ 新增日利宝管理API（用户列表/收益记录/配置管理）
- ✅ 统一所有金额字段为decimal(20,8)格式
- ✅ 完善数据库Schema注释
- ✅ 修复API响应格式不一致问题

### v1.0 (2025-01-01)
- 初始版本发布

---

**文档维护**: Providence 开发团队
**最后更新**: 2025-11-11 02:00:00

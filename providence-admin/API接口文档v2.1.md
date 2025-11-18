# PROVIDENCE API接口文档 v2.1

**更新时间：2024-11-10**  
**遵循规范：SRS_CORE_RULES.md + 企业级安全规范**

---

## 📋 核心原则

1. **周期收益计算**（不是年化）
2. **币种严格隔离**（CNY/USDT）
3. **前端不做数学**
4. **后端统一计算**
5. **Token+Sign双重验证**

---

## 🔌 核心接口

### 1. 项目详情 ⭐

```http
GET /fund/project/detail?id=1
```

**响应：**
```json
{
  "code": 1,
  "message": "操作成功",
  "data": {
    "id": 1,
    "project_code": "PRJ202411001",
    "title": "稳健增长基金",
    "category": "FUND",
    "currency": "CNY",
    
    // 周期收益参数（重要）⭐
    "cycle_days": 30,              // 周期天数
    "base_rate": 4.5,              // 基础收益%（30天周期）
    "vip_extra_rate": 0.3,         // VIP额外%（当前用户VIP）
    "total_rate": 4.8,             // 周期总收益率 ⭐ 新增
    
    // 投资限制
    "min_invest": 1000,
    "max_invest": 100000,
    
    // 募集信息 ⭐
    "total_quota": 5000000,        // 总额度
    "sold": 1250000,               // 已募集 ⭐ 新增
    "remain": 3750000,             // 剩余额度 ⭐ 新增
    "schedule": 25.00,             // 进度%
    
    "manager": { ... }
  }
}
```

**重要说明：**
- `total_rate` = `base_rate` + `vip_extra_rate`
- 所有rate都是**周期收益率**，不是年化
- `sold` = 已募集金额
- `remain` = `total_quota` - `sold`

---

### 2. 收益计算API ⭐

```http
POST /fund/project/calculate
Content-Type: application/json
Headers: token: xxx

{
  "project_id": 1,
  "amount": 10000
}
```

**响应：**
```json
{
  "code": 1,
  "message": "计算成功",
  "data": {
    "profit": 480,           // 周期固定收益 = amount × total_rate / 100
    "total": 10480,          // 本金 + 收益
    "daily_profit": 16       // profit / cycle_days
  }
}
```

**计算公式：**
```
profit = amount × total_rate / 100
total = amount + profit
daily_profit = profit / cycle_days
```

**⚠️ 重要：**
- 收益不是年化计算
- 周期是天制
- 前端不做任何计算，只显示此API返回值

---

### 3. 投资项目 ⭐

```http
POST /fund/project/add
Content-Type: application/json
Headers: 
  token: xxx
  X-Signature: xxx
  X-Timestamp: xxx
  X-Nonce: xxx

{
  "project_id": 1,
  "amount": 10000,
  "currency": "CNY"
}
```

**响应：**
```json
{
  "code": 1,
  "message": "投资成功",
  "data": {
    "order_id": 12345,
    "order_no": "INV20241110001",
    "invest_amount": 10000,
    "currency": "CNY",
    "profit": 480,              // 周期总收益
    "total": 10480,             // 本金+收益
    "daily_profit": 16,         // 日均收益
    "end_date": "2024-12-10",
    "status": "RUNNING"
  }
}
```

**执行流程：**
1. Token + Sign 双重验证
2. 币种隔离检查
3. SELECT ... FOR UPDATE 锁定钱包
4. 计算收益（EarningsService）
5. 创建订单（status=RUNNING）
6. 扣除余额
7. VIP自动升级检查
8. 记录audit_log
9. 返回结果

---

### 4. 用户登录

```http
POST /login/login/account
Content-Type: application/json

{
  "username": "user123",
  "password": "123456"
}
```

**响应：**
```json
{
  "code": 1,
  "message": "登录成功",
  "data": {
    "token": "eyJhbGc...",
    "user": {
      "id": 1,
      "username": "user123",
      "vip_level": 1,
      "balance_cny": 1000.00,
      "balance_usdt": 0.00000000,
      ...
    }
  }
}
```

**登录时自动记录：**
- IP地址和归属地
- 设备类型（mobile/desktop/tablet）
- 浏览器和操作系统
- 登录时间
- 写入audit_log

---

### 5. 图片上传

```http
POST /upload
Content-Type: multipart/form-data
Headers: token: xxx

Form Data:
  file: [图片文件]
  type: kyc|certificate|avatar
```

**响应：**
```json
{
  "code": 1,
  "message": "上传成功",
  "data": {
    "urls": [
      "/uploads/kyc/20241110/abc123_1699603200.jpg"
    ],
    "count": 1
  }
}
```

---

### 6. 实名认证提交

```http
POST /user/kyc/submit
Content-Type: multipart/form-data
Headers: token: xxx

Form Data:
  real_name: 张三
  id_card: 110101199001011234
  id_card_front: [文件]
  id_card_back: [文件]
  hand_held_photo: [文件]
```

**响应：**
```json
{
  "code": 1,
  "message": "实名认证提交成功，等待审核",
  "data": null
}
```

---

## 📊 字段说明

### 周期收益相关

| 字段 | 类型 | 说明 | 示例 |
|------|------|------|------|
| `cycle_days` | number | 周期天数 | 30 |
| `base_rate` | number | 基础收益%（周期） | 4.5 |
| `vip_extra_rate` | number | VIP额外%（周期） | 0.3 |
| `total_rate` | number | 周期总收益率 | 4.8 |
| `profit` | number | 周期总收益金额 | 480 |
| `daily_profit` | number | 日均收益 | 16 |

### 募集信息

| 字段 | 类型 | 说明 |
|------|------|------|
| `total_quota` | number | 总募集额度 |
| `sold` | number | 已募集金额 |
| `remain` | number | 剩余额度 |
| `schedule` | number | 完成进度% |

---

## 🔐 安全验证

### Token验证（所有API）

```
Header: token: eyJhbGc...
```

### Sign签名验证（资金类API）

```
Headers:
  X-Signature: abc123...
  X-Timestamp: 1699603200
  X-Nonce: xyz789...
  
算法:
sign = HMAC-SHA256(
  method + '\n' + 
  path + '\n' + 
  timestamp + '\n' + 
  nonce + '\n' + 
  sha256(body)
)
```

### 幂等性键（可选）

```
Header: Idempotency-Key: unique-key-123
```

用于防止重复提交（充值/提现/投资）

---

## 💰 计算示例

### 示例1：VIP0用户

```
投资金额: 10,000
周期: 30天
基础收益: 4.5%（30天周期）
VIP额外: 0%

计算:
total_rate = 4.5 + 0 = 4.5%
profit = 10000 × 4.5 / 100 = 450
total = 10000 + 450 = 10,450
daily_profit = 450 / 30 = 15
```

### 示例2：VIP1用户

```
投资金额: 10,000
周期: 30天
基础收益: 4.5%（30天周期）
VIP额外: 0.3%

计算:
total_rate = 4.5 + 0.3 = 4.8%
profit = 10000 × 4.8 / 100 = 480
total = 10000 + 480 = 10,480
daily_profit = 480 / 30 = 16
```

### 示例3：长周期项目

```
投资金额: 50,000
周期: 60天
基础收益: 15%（60天周期）
VIP额外: 0.9%（VIP4）

计算:
total_rate = 15 + 0.9 = 15.9%
profit = 50000 × 15.9 / 100 = 7,950
total = 50000 + 7950 = 57,950
daily_profit = 7950 / 60 = 132.5
```

---

## ⚠️ 重要提醒

### 前端开发者必读

1. **禁止在前端计算任何收益**
2. **所有金额显示来自API返回**
3. **投资前调用 /calculate 获取预期收益**
4. **只负责展示，不负责计算**

### 错误示例（禁止）

```javascript
// ❌ 禁止
const profit = amount * rate / 100;
const daily = profit / days;

// ✅ 正确
const res = await api.post('/fund/project/calculate', {
  project_id: 1,
  amount: 10000
});
display(res.data.profit);  // 只显示
```

---

**© 2024 PROVIDENCE | API文档 v2.1**

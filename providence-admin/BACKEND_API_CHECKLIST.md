# PROVIDENCE 后端API接口规范清单

**版本：v2.1 Final**  
**更新时间：2024-11-10**

---

## 📋 API接口清单

### 1. 项目详情 GET /fund/project/detail?id=1

**JSON样本返回：**

```json
{
  "code": 1,
  "message": "操作成功",
  "data": {
    "id": 1,
    "project_code": "PRJ202411001",
    "title": "稳健增长基金",
    "subtitle": "低风险稳定收益",
    "description": "专注于稳定增长的优质企业投资组合",
    "category": "FUND",
    "currency": "CNY",
    "cover_image": "/images/project1.jpg",
    "images": ["/images/p1.jpg", "/images/p2.jpg"],
    
    "cycle_days": 30,
    
    "rate": 4.5,
    "vip_rate": 0.3,
    "added": 0.2,
    "gift": 0.0,
    "total_rate": 5.0,
    
    "min_invest": 1000,
    "max_invest": 100000,
    
    "total": 5000000,
    "schedule": 25.50,
    "sold": 1275000,
    "remain": 3725000,
    
    "risk_level": 1,
    "view_count": 1234,
    "invest_count": 89,
    "status": 1,
    
    "manager": {
      "id": 1,
      "name": "张明",
      "title": "首席投资官",
      "avatar": "/images/manager1.jpg",
      "bio": "15年投资经验..."
    }
  }
}
```

**字段说明：**

| 字段 | 类型 | 说明 | 计算方式 |
|------|------|------|----------|
| `rate` | number | 基础收益% | 从项目表读取 |
| `vip_rate` | number | VIP额外加息% | 从VIP规则表读取 |
| `added` | number | 额外临时加息% | 从项目表读取 |
| `gift` | number | 活动加息% | 从项目表读取 |
| `total_rate` | number | 总收益率% | **后端计算**：rate+vip_rate+added+gift |
| `total` | number | 募集上限总额 | 从项目表读取 |
| `schedule` | number | 募集进度% | 从项目表读取 |
| `sold` | number | 已募集金额 | **后端计算**：total × schedule / 100 |
| `remain` | number | 剩余额度 | **后端计算**：total - sold |

⚠️ **重要**：`total_rate`、`sold`、`remain` 都由后端计算，前端不做任何数学运算！

---

### 2. 收益计算 POST /fund/project/calculate

**请求：**

```json
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
    "profit": 500,
    "total": 10500,
    "daily_profit": 16.67
  }
}
```

**计算逻辑（后端）：**

```
total_rate = rate + vip_rate + added + gift
profit = amount × total_rate / 100
total = amount + profit
daily_profit = profit / cycle_days
```

**字段说明：**

| 字段 | 类型 | 说明 |
|------|------|------|
| `profit` | number | 周期总收益 |
| `total` | number | 本金+收益 |
| `daily_profit` | number | 日均收益 |

---

## 📊 完整计算示例

### 案例：VIP1用户投资

**项目信息：**
- 基础收益(rate): 4.5%
- VIP加息(vip_rate): 0.3% (VIP1)
- 临时加息(added): 0.2%
- 活动加息(gift): 0%
- 周期: 30天

**用户投资：**
- 金额: 10,000

**后端计算过程：**

```
1. total_rate = 4.5 + 0.3 + 0.2 + 0 = 5.0%

2. profit = 10000 × 5.0 / 100 = 500

3. total = 10000 + 500 = 10,500

4. daily_profit = 500 / 30 = 16.67
```

**返回给前端：**
```json
{
  "profit": 500,
  "total": 10500,
  "daily_profit": 16.67
}
```

**前端展示：**
```
预计收益：¥500
到期可得：¥10,500
日均收益：¥16.67
```

---

## ⚠️ 前端开发规范

### 禁止行为

```javascript
// ❌ 禁止：前端计算total_rate
const totalRate = rate + vipRate + added + gift;

// ❌ 禁止：前端计算profit
const profit = amount * totalRate / 100;

// ❌ 禁止：前端计算sold
const sold = total * schedule / 100;
```

### 正确做法

```javascript
// ✅ 正确：直接显示后端返回值
<div>总收益率: {{ project.total_rate }}%</div>
<div>已募集: ¥{{ project.sold }}</div>
<div>剩余额度: ¥{{ project.remain }}</div>

// ✅ 正确：调用calculate API
const res = await api.post('/fund/project/calculate', {
  project_id: 1,
  amount: 10000
});

<div>预计收益: ¥{{ res.data.profit }}</div>
<div>到期可得: ¥{{ res.data.total }}</div>
```

---

## 🔌 API字段映射表

### /fund/project/detail 字段映射

| 后端字段 | 前端显示 | 示例值 |
|---------|---------|--------|
| `rate` | 基础收益 | 4.5% |
| `vip_rate` | VIP加成 | +0.3% |
| `added` | 临时加息 | +0.2% |
| `gift` | 活动加息 | +0% |
| `total_rate` | 总收益率 | 5.0% ⭐ |
| `cycle_days` | 投资周期 | 30天 |
| `min_invest` | 起投金额 | ¥1,000 |
| `max_invest` | 单笔上限 | ¥100,000 |
| `total` | 募集上限 | ¥5,000,000 |
| `schedule` | 募集进度 | 25.5% |
| `sold` | 已募集 | ¥1,275,000 ⭐ |
| `remain` | 剩余额度 | ¥3,725,000 ⭐ |

### /fund/project/calculate 字段映射

| 后端字段 | 前端显示 |
|---------|---------|
| `profit` | 预计收益 |
| `total` | 到期可得 |
| `daily_profit` | 日均收益 |

---

## 💡 关键提醒

1. **后端计算**：total_rate、sold、remain、profit 全部后端计算
2. **前端职责**：只负责显示，不做数学，不做逻辑
3. **收益类型**：所有收益率都是周期收益率，不是年化
4. **币种隔离**：CNY/USDT严格隔离，不可跨币种

---

**© 2024 PROVIDENCE | 后端API规范**

# ✅ 后端字段已确认 - 立即可对接

> **确认时间：** 2025-11-10
> **状态：** 🟢 字段已固定，前端可直接对接

---

## 🎯 核心确认事项

### ✅ 1. 关键字段由后端返回

| 字段 | 说明 | 状态 |
|------|------|------|
| `total_rate` | 总周期收益率（非年化） | ✅ 后端返回 |
| `sold` | 已募集金额 | ✅ 后端返回 |
| `remain` | 剩余额度 | ✅ 后端返回 |

### ✅ 2. 收益类型标准化

**⚠️ 重要：所有收益均为周期收益，非年化！**

```javascript
// ✅ 正确理解
total_rate = 9.8%   // 表示：整个投资周期的总收益率（如90天获得9.8%）
rate = 9.2%         // 表示：周期基础收益率
vip_rate = 0.3%     // 表示：周期VIP加息
added = 0.2%        // 表示：周期增值收益
gift = 0.1%         // 表示：周期红包收益

// ❌ 错误理解
total_rate ≠ 年化收益率  // 不是年化！
```

### ✅ 3. 前端职责明确

```
前端只负责：
1. 调用 API 获取数据
2. 格式化显示（千分位、百分比符号）
3. 用户输入验证

前端禁止：
1. 任何数学计算（收益、加息、本息等）
2. 字段累加（total_rate、sold、remain 等）
3. 百分比转换（直接显示后端返回的数值）
```

---

## 📋 字段映射表（最终版）

### 项目详情页 (project-detail.html)

| UI 显示 | 后端字段 | 数据类型 | 显示格式 | 示例 |
|---------|---------|---------|---------|------|
| 周期收益率 | `total_rate` | Number | `{value}%` | `9.8%` |
| VIP加息 | `vip_rate` | Number | `+{value}%` | `+0.3%` |
| 增值收益 | `added` | Number | `+{value}%` | `+0.2%` |
| 红包收益 | `gift` | Number | `+{value}%` | `+0.1%` |
| 投资周期 | `day` | Number | `{day}天` 或 `{month}个月` | `90天` 或 `3个月` |
| 起投金额 | `min` | Number | `¥{value}` 千分位 | `¥10,000` |
| 最高金额 | `max` | Number | `¥{value}` 千分位 | `¥1,000,000` |
| 总募集额 | `total` | Number | `¥{value}` 或 `{value}万` | `500万` |
| 已募集额 | `sold` | Number | `¥{value}` 或 `{value}万` | `325万` |
| 剩余额度 | `remain` | Number | `¥{value}` 或 `{value}万` | `175万` |
| 募集进度 | `schedule` | Number | `{value}%` | `65.0%` |

### 收益计算（用户输入后调用API）

| UI 显示 | 后端字段 | 数据类型 | 显示格式 | 示例 |
|---------|---------|---------|---------|------|
| 预计收益 | `calc.profit` | Number | `¥{value}` 千分位 | `¥2,452.05` |
| 到期本息 | `calc.total` | Number | `¥{value}` 千分位 | `¥102,452.05` |
| 日均收益 | `calc.daily_profit` | Number | `¥{value}` | `¥27.25` |

---

## 🔌 API 端点（已确认）

### 1. 项目详情 API

```
GET /fund/project/detail?id={project_id}
```

**返回示例：**
```json
{
  "code": 200,
  "data": {
    "id": 1,
    "title": "固收优选一期",
    "code": "FX202501",
    "category_name": "固收优选",
    "status": 1,
    "rate": 9.2,           // ⚠️ 周期收益率，非年化
    "vip_rate": 0.3,
    "added": 0.2,
    "gift": 0.1,
    "total_rate": 9.8,     // ⚠️ 周期总收益率，非年化
    "day": 90,
    "min": 10000,
    "max": 1000000,
    "vip": 0,
    "total": 5000000,
    "sold": 3250000,       // ✅ 后端计算
    "remain": 1750000,     // ✅ 后端计算
    "schedule": 65.0,
    "payment_desc": "到期还本付息"
  }
}
```

### 2. 收益计算 API

```
POST /fund/project/calculate
```

**请求：**
```json
{
  "project_id": 1,
  "amount": 100000
}
```

**返回：**
```json
{
  "code": 200,
  "data": {
    "profit": 2452.05,      // ✅ 周期收益（非年化）
    "total": 102452.05,     // ✅ 到期本息
    "daily_profit": 27.25   // ✅ 日均收益
  }
}
```

---

## ✅ 前端代码示例（正确用法）

### 显示周期收益率

```javascript
// ✅ 正确：直接显示后端返回的值
const totalRate = parseFloat(projectData.total_rate) || 0;
document.getElementById('rate').textContent = totalRate.toFixed(2) + '%';

// ❌ 错误：不要计算
const totalRate = baseRate + vipRate + addedRate + giftRate;  // 禁止！
```

### 显示募集数据

```javascript
// ✅ 正确：直接显示后端返回的值
const soldAmount = parseFloat(projectData.sold) || 0;
const remainAmount = parseFloat(projectData.remain) || 0;

document.getElementById('sold').textContent = formatMoney(soldAmount);
document.getElementById('remain').textContent = formatMoney(remainAmount);

// ❌ 错误：不要计算
const soldAmount = totalAmount * (schedule / 100);  // 禁止！
const remainAmount = totalAmount - soldAmount;      // 禁止！
```

### 计算预估收益

```javascript
// ✅ 正确：调用后端API
async function calculateProfit(projectId, amount) {
  const result = await fetch('https://api.frevix.top/fund/project/calculate', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'token': userToken
    },
    body: JSON.stringify({ project_id: projectId, amount: amount })
  });
  const data = await result.json();

  if (data.code === 200) {
    document.getElementById('profit').textContent = '¥' + formatMoney(data.data.profit);
    document.getElementById('total').textContent = '¥' + formatMoney(data.data.total);
  }
}

// ❌ 错误：不要前端计算
const profit = amount * (totalRate / 100);  // 禁止！
const total = amount + profit;              // 禁止！
```

---

## 📐 格式化函数（仅展示用）

```javascript
// 金额格式化（千分位）
function formatMoney(value) {
  const num = parseFloat(value) || 0;
  if (num >= 100000000) return (num / 100000000).toFixed(2) + '亿';
  if (num >= 10000) return (num / 10000).toFixed(1) + '万';
  return num.toLocaleString('zh-CN', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

// 百分比格式化（仅添加%符号）
function formatPercent(value, decimals = 2) {
  const num = parseFloat(value) || 0;
  return num.toFixed(decimals) + '%';
}

// VIP加息格式化（显示+号）
function formatVIPRate(value) {
  const num = parseFloat(value) || 0;
  if (num === 0) return '—';
  return '+' + num.toFixed(1) + '%';
}

// 周期格式化（天→月→年）
function formatCycle(day) {
  const d = parseInt(day) || 0;
  if (d === 0) return '—';
  if (d < 30) return d + '天';
  if (d < 365) {
    const months = Math.round(d / 30);
    return months + '个月';
  }
  const years = (d / 365).toFixed(1);
  return years + '年';
}
```

---

## ⚠️ 特别注意事项

### 1. 周期收益 vs 年化收益

**后端返回的是周期收益率，不是年化！**

```
示例：
项目周期：90天
total_rate：9.8%

含义：投资90天，获得9.8%的收益
不是：年化收益率9.8%

前端显示时：
✅ 正确："周期收益率 9.8%"
✅ 正确："预期收益 9.8%（90天）"
❌ 错误："年化收益率 9.8%"
```

### 2. UI 文案建议

| 场景 | 推荐文案 | 避免文案 |
|------|---------|---------|
| 收益率显示 | "预期收益率 9.8%" | "年化收益率 9.8%" |
| 收益率标签 | "周期收益" | "年化收益" |
| 计算器标题 | "收益试算" | "年化收益计算" |

---

## 🚀 立即开始对接

### Step 1: 确认配置

```javascript
// /www/wwwroot/providence/config.js
const API_CONFIG = {
  baseURL: 'https://api.frevix.top',  // ✅ 已确认
  tokenKey: 'providence_token',
  timeout: 10000,
  debug: false  // 生产环境改为 false
};
```

### Step 2: 使用字段映射

```javascript
// /www/wwwroot/providence/project-detail.js
const API_FIELD_MAP = {
  totalRate: 'total_rate',    // ✅ 已确认存在
  soldAmount: 'sold',         // ✅ 已确认存在
  remainAmount: 'remain',     // ✅ 已确认存在
  categoryName: 'category_name'  // ✅ 已确认字段名
};
```

### Step 3: 测试验证

```bash
# 测试项目详情API
curl "https://api.frevix.top/fund/project/detail?id=1" \
  -H "token: YOUR_TOKEN"

# 测试收益计算API
curl -X POST "https://api.frevix.top/fund/project/calculate" \
  -H "Content-Type: application/json" \
  -H "token: YOUR_TOKEN" \
  -d '{"project_id": 1, "amount": 100000}'
```

---

## 📝 TODO 清单

- [x] 字段定义确认
- [x] API 端点确认
- [x] 前端代码重构
- [x] 文档更新（周期收益说明）
- [ ] 前端对接测试
- [ ] UI 文案调整（移除"年化"字样）
- [ ] 上线发布

---

**✅ 字段已确认，前端可立即开始对接！**

**重要提醒：所有收益均为周期收益，请在UI中移除"年化"字样！**

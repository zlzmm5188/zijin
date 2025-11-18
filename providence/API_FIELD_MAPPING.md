# Providence API 字段映射文档

> **更新时间：** 2025-11-10
> **baseURL：** `https://api.frevix.top`
> **原则：** 前端不参与数学计算，所有收益/加息/本息由后端返回

---

## 📋 目录

1. [项目详情页 (project-detail.html)](#1-项目详情页)
2. [项目列表页 (projects.html)](#2-项目列表页)
3. [我的投资 (my-investments.html)](#3-我的投资)
4. [用户中心 (profile.html)](#4-用户中心)
5. [收益日历 (profit-calendar.html)](#5-收益日历)

---

## 1. 项目详情页 (project-detail.html)

### API 端点

#### 获取项目详情
```
GET /fund/project/detail?id={project_id}
```

### 后端返回字段（完整）

```json
{
  "code": 200,
  "msg": "success",
  "data": {
    "id": 1,
    "title": "固收优选一期",
    "code": "FX202501",
    "category_id": 1,
    "category_name": "固收优选",
    "status": 1,

    // === 核心收益字段（后端计算） ===
    "rate": 9.2,              // 基础周期收益率（%，非年化！）
    "vip_rate": 0.3,          // VIP加息（%）
    "added": 0.2,             // 增值收益（%）
    "gift": 0.1,              // 红包收益（%）
    "total_rate": 9.8,        // 总周期收益率（后端计算：rate+vip_rate+added+gift，⚠️ 非年化！）

    // === 投资参数 ===
    "day": 90,                // 投资周期（天）
    "min": 10000,             // 起投金额（元）
    "max": 1000000,           // 最高金额（元）
    "vip": 0,                 // VIP等级要求（0=无限制）

    // === 募集进度 ===
    "total": 5000000,         // 总募集额（元）
    "sold": 3250000,          // 已募集额（元，后端计算）
    "remain": 1750000,        // 剩余额度（元，后端计算）
    "schedule": 65.0,         // 募集进度（%，后端计算）

    // === 收益计算（用户输入金额后，后端返回） ===
    "calc": {
      "amount": 100000,       // 用户输入金额
      "profit": 2452.05,      // 预计收益（后端计算）
      "total": 102452.05,     // 到期本息（后端计算）
      "daily_profit": 27.25   // 日均收益（后端计算）
    },

    // === 其他信息 ===
    "description": "...",     // 产品描述
    "risk_level": 2,          // 风险等级（1-5）
    "payment_type": 1,        // 付息方式（1=到期还本付息）
    "payment_desc": "到期还本付息"
  }
}
```

### 前端字段映射

| UI 展示 | 后端字段 | 格式化方式 | 禁止前端计算 |
|--------|---------|-----------|------------|
| **项目名称** | `title` | 直接展示 | - |
| **产品代码** | `code` | 直接展示 | - |
| **周期收益率** | `total_rate` | `{value}%` ⚠️ 非年化 | ❌ 禁止 `rate+vip_rate+added+gift` |
| **VIP加息** | `vip_rate` | `+{value}%` | ❌ 禁止前端计算 |
| **增值收益** | `added` | `+{value}%` | ❌ 禁止前端计算 |
| **红包收益** | `gift` | `+{value}%` | ❌ 禁止前端计算 |
| **投资周期** | `day` | `{day}天` 或 `{month}个月` | 仅格式化 |
| **起投金额** | `min` | `¥{value}` 千分位 | - |
| **最高金额** | `max` | `¥{value}` 千分位 | - |
| **总募集** | `total` | `¥{value}` 或 `{value}万` | - |
| **已募集** | `sold` | `¥{value}` 或 `{value}万` | ❌ 禁止 `total * schedule` |
| **剩余额度** | `remain` | `¥{value}` 或 `{value}万` | ❌ 禁止 `total - sold` |
| **募集进度** | `schedule` | `{value}%` 进度条 | ❌ 禁止前端计算 |
| **预计收益** | `calc.profit` | `¥{value}` 千分位 | ❌ 禁止前端计算收益 |
| **到期本息** | `calc.total` | `¥{value}` 千分位 | ❌ 禁止 `amount + profit` |

### ⚠️ 禁止的前端计算

```javascript
// ❌ 禁止：前端计算收益
const profit = amount * (rate / 100) * (day / 365);
const total = amount + profit;

// ✅ 正确：调用后端API
const result = await API.fund.calculate({
  project_id: 1,
  amount: 100000
});
// 使用 result.data.profit 和 result.data.total
```

---

## 2. 项目列表页 (projects.html)

### API 端点

```
GET /fund/project/all
```

### 后端返回字段

```json
{
  "code": 200,
  "data": [
    {
      "id": 1,
      "name": "新手专区",
      "list": [
        {
          "id": 101,
          "title": "新手福利一号",
          "rate": 8.5,              // 基础年化
          "vip_rate": 0.2,          // VIP加息
          "added": 0.1,             // 增值收益
          "gift": 0.0,              // 红包收益
          "total_rate": 8.8,        // 总年化（后端计算）
          "day": 30,
          "min": 5000,
          "max": 50000,
          "schedule": 45.5,         // 募集进度
          "status": 1               // 1=募集中 0=已结束
        }
      ]
    }
  ]
}
```

### 前端字段映射

| UI 展示 | 后端字段 | 格式化 |
|--------|---------|-------|
| **目标年化** | `total_rate` | `{value}%` |
| **投资周期** | `day` | `{day}天` |
| **起投金额** | `min` | `¥{value}` |

---

## 3. 我的投资 (my-investments.html)

### API 端点

```
GET /user/order/list?type=fund
```

### 后端返回字段

```json
{
  "code": 200,
  "data": {
    "list": [
      {
        "order_id": "PV20251110001",
        "project_id": 1,
        "project_title": "固收优选一期",
        "amount": 100000,           // 投资本金
        "profit": 2452.05,          // 累计收益（后端计算）
        "profit_today": 27.25,      // 今日收益（后端计算）
        "total_value": 102452.05,   // 当前市值（后端计算）
        "rate": 9.8,                // 实际年化（包含VIP加息）
        "start_date": "2025-10-15",
        "end_date": "2026-01-13",
        "days_remain": 45,          // 剩余天数（后端计算）
        "status": 1                 // 0=持有中 1=已到期 2=已赎回
      }
    ],
    "summary": {
      "total_amount": 500000,       // 总投资本金
      "total_profit": 12356.78,     // 总收益（后端计算）
      "profit_today": 136.25,       // 今日总收益（后端计算）
      "total_value": 512356.78      // 总市值（后端计算）
    }
  }
}
```

### 前端字段映射

| UI 展示 | 后端字段 | 禁止计算 |
|--------|---------|---------|
| **累计收益** | `profit` | ❌ 禁止前端计算 |
| **今日收益** | `profit_today` | ❌ 禁止前端计算 |
| **当前市值** | `total_value` | ❌ 禁止 `amount + profit` |
| **总收益** | `summary.total_profit` | ❌ 禁止求和 |

---

## 4. 用户中心 (profile.html)

### API 端点

```
GET /user/user/index
```

### 后端返回字段

```json
{
  "code": 200,
  "data": {
    "id": 12345678,
    "username": "user123",
    "mobile": "138****8888",
    "level": 3,                   // VIP等级

    // === 资产数据（后端计算） ===
    "money": 125678.90,           // 可用余额
    "frozen": 500000.00,          // 冻结资金（投资中）
    "total_asset": 625678.90,     // 总资产（后端计算：money + frozen）
    "total_income": 23456.78,     // 总收益（后端计算）
    "income_today": 256.45,       // 今日收益（后端计算）

    // === VIP 进度 ===
    "recharges": 250000,          // 累计充值
    "current_level": 3,           // 当前等级
    "next_level": 4,              // 下一等级
    "next_amount": 800000,        // 下一等级所需金额
    "need_amount": 550000,        // 还需充值金额（后端计算）
    "progress": 31.25             // 升级进度（%，后端计算）
  }
}
```

### 前端字段映射

| UI 展示 | 后端字段 | 禁止计算 |
|--------|---------|---------|
| **总资产** | `total_asset` | ❌ 禁止 `money + frozen` |
| **总收益** | `total_income` | ❌ 禁止前端累加 |
| **今日收益** | `income_today` | ❌ 禁止前端计算 |
| **VIP进度** | `progress` | ❌ 禁止 `recharges/next_amount` |
| **还需金额** | `need_amount` | ❌ 禁止 `next_amount - recharges` |

---

## 5. 收益日历 (profit-calendar.html)

### API 端点

```
GET /user/profit/calendar?year=2025&month=11
```

### 后端返回字段

```json
{
  "code": 200,
  "data": {
    "month_summary": {
      "total_profit": 8234.56,    // 本月总收益（后端计算）
      "avg_daily": 274.49,        // 日均收益（后端计算）
      "max_daily": 356.78,        // 单日最高收益
      "days_with_profit": 30      // 有收益天数
    },
    "daily": [
      {
        "date": "2025-11-01",
        "profit": 256.45,         // 当日收益（后端计算）
        "source": [
          {
            "project_id": 1,
            "project_title": "固收优选一期",
            "amount": 100000,
            "profit": 26.85        // 该项目当日收益（后端计算）
          }
        ]
      }
    ]
  }
}
```

---

## 📐 通用格式化函数

```javascript
// 金额格式化（千分位）
function formatMoney(value) {
  const num = parseFloat(value) || 0;
  if (num >= 100000000) return (num / 100000000).toFixed(2) + '亿';
  if (num >= 10000) return (num / 10000).toFixed(2) + '万';
  return num.toLocaleString('zh-CN', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

// 百分比格式化
function formatPercent(value, decimals = 2) {
  const num = parseFloat(value) || 0;
  return num.toFixed(decimals) + '%';
}

// VIP加息格式化（永远显示+）
function formatVIPRate(value) {
  const num = parseFloat(value) || 0;
  if (num === 0) return '—';
  return '+' + num.toFixed(1) + '%';
}

// 周期格式化
function formatCycle(day) {
  if (!day || day === 0) return '—';
  if (day < 30) return day + '天';
  if (day < 365) {
    const months = Math.round(day / 30);
    return months + '个月';
  }
  const years = (day / 365).toFixed(1);
  return years + '年';
}
```

---

## ⚠️ 关键原则

### 1. 禁止前端计算的场景

| 类型 | 说明 | 后端字段 |
|------|------|---------|
| **收益计算** | 任何涉及本金×利率×时间的计算 | `calc.profit` |
| **本息总额** | 本金 + 收益 | `calc.total` |
| **VIP加息** | VIP等级对应的加息幅度 | `vip_rate` |
| **总年化** | 基础利率 + VIP加息 + 其他加成 | `total_rate` |
| **募集进度** | 已募集 ÷ 总募集 | `schedule` |
| **资产汇总** | 任何资产类加总 | `total_asset` |
| **收益汇总** | 多个订单的收益累加 | `total_income` |

### 2. 前端仅负责

| 类型 | 说明 | 示例 |
|------|------|------|
| **格式化显示** | 添加千分位、货币符号、单位 | `¥125,678.90` |
| **单位转换** | 元→万→亿（纯展示） | `12.56万` |
| **日期格式化** | 时间戳→日期字符串 | `2025-11-10` |
| **文案拼接** | UI文案组装 | `距离VIP4还需55万` |
| **交互逻辑** | 输入验证、按钮状态 | - |

### 3. API 调用时机

| 场景 | 时机 | API |
|------|------|-----|
| **页面加载** | `DOMContentLoaded` | `/fund/project/detail` |
| **用户输入** | `input事件 + debounce` | `/fund/project/calculate` |
| **提交投资** | `点击确认按钮` | `/fund/project/add` |

---

## 🔧 待实现 API 端点

### 收益计算器
```
POST /fund/project/calculate
Request: { project_id: 1, amount: 100000 }
Response: { profit: 2452.05, total: 102452.05, daily_profit: 27.25 }
```

### VIP 进度查询
```
GET /user/vip/progress
Response: { current_level: 3, next_level: 4, progress: 31.25, need_amount: 550000 }
```

---

**文档维护者：** AI Frontend Architect
**审核者：** Providence Backend Team
**最后同步：** 2025-11-10

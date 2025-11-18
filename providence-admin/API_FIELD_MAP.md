# PROVIDENCE API字段映射表

**版本：v2.1 Final**  
**用途：前后端字段对接规范**

---

## 📋 项目详情接口 GET /fund/project/detail

### JSON样本（已测试）

```json
{
  "code": 1,
  "message": "操作成功",
  "data": {
    "id": 1,
    "project_code": "PRJ202411001",
    "title": "稳健增长基金",
    "subtitle": "低风险稳定收益",
    "category": "FUND",
    "currency": "CNY",
    
    "cycle_days": 30,
    
    "rate": 4.5,
    "vip_rate": 0.3,
    "added": 0.2,
    "gift": 0,
    "total_rate": 5.0,
    
    "min_invest": 1000,
    "max_invest": 100000,
    
    "total": 5000000,
    "schedule": 25.5,
    "sold": 1275000,
    "remain": 3725000
  }
}
```

### 字段映射表

| 后端字段 | 数据类型 | 含义 | 前端显示示例 | 计算方式 |
|---------|---------|------|------------|----------|
| `rate` | number | 基础收益% | "基础收益：4.5%" | 从项目表读取 |
| `vip_rate` | number | VIP额外加息% | "VIP加成：+0.3%" | 从VIP规则表读取 |
| `added` | number | 额外临时加息% | "临时加息：+0.2%" | 从项目表读取 |
| `gift` | number | 活动加息% | "活动加息：+0%" | 从项目表读取 |
| `total_rate` | number | 总收益率% | "总收益率：5.0%" | **后端计算**⭐ |
| `cycle_days` | number | 周期天数 | "投资周期：30天" | 从项目表读取 |
| `total` | number | 募集上限 | "募集上限：¥500万" | 从项目表读取 |
| `schedule` | number | 募集进度% | "进度：25.5%" | 从项目表读取 |
| `sold` | number | 已募集金额 | "已募集：¥127.5万" | **后端计算**⭐ |
| `remain` | number | 剩余额度 | "剩余：¥372.5万" | **后端计算**⭐ |

### 后端计算公式

```
total_rate = rate + vip_rate + added + gift
sold = total × schedule / 100
remain = total - sold
```

⚠️ **前端禁止计算这些字段，只能显示后端返回值！**

---

## 📊 收益计算接口 POST /fund/project/calculate

### 请求样本

```json
{
  "project_id": 1,
  "amount": 10000
}
```

### 响应样本

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

### 字段映射表

| 后端字段 | 数据类型 | 含义 | 前端显示示例 | 计算公式 |
|---------|---------|------|------------|----------|
| `profit` | number | 周期总收益 | "预计收益：¥500" | amount × total_rate / 100 |
| `total` | number | 本金+收益 | "到期可得：¥10,500" | amount + profit |
| `daily_profit` | number | 日均收益 | "日均：¥16.67" | profit / cycle_days |

### 后端计算公式

```
total_rate = rate + vip_rate + added + gift  (从detail获取)
profit = amount × total_rate / 100
total = amount + profit
daily_profit = profit / cycle_days
```

---

## 🎨 前端展示规范

### 项目详情页

```html
<!-- ✅ 正确：直接显示后端值 -->
<div class="rate-info">
  <div>基础收益：{{ rate }}%</div>
  <div>VIP加成：+{{ vip_rate }}%</div>
  <div>临时加息：+{{ added }}%</div>
  <div>活动加息：+{{ gift }}%</div>
  <div class="total-rate">总收益率：{{ total_rate }}%</div>  <!-- 后端计算 -->
</div>

<div class="fund-info">
  <div>募集上限：¥{{ total | formatNumber }}</div>
  <div>已募集：¥{{ sold | formatNumber }}</div>               <!-- 后端计算 -->
  <div>剩余额度：¥{{ remain | formatNumber }}</div>           <!-- 后端计算 -->
  <div>进度：{{ schedule }}%</div>
</div>

<!-- ❌ 禁止：前端计算 -->
<!-- <div>总收益率：{{ rate + vipRate + added + gift }}%</div> -->
<!-- <div>已募集：¥{{ total * schedule / 100 }}</div> -->
```

### 收益计算器

```javascript
// ✅ 正确：调用API获取
async function calculateEarnings(projectId, amount) {
  const res = await api.post('/fund/project/calculate', {
    project_id: projectId,
    amount: amount
  });
  
  // 只显示，不计算
  showProfit(res.data.profit);
  showTotal(res.data.total);
  showDailyProfit(res.data.daily_profit);
}

// ❌ 禁止：前端计算
// const profit = amount * totalRate / 100;
// const total = amount + profit;
```

---

## 💰 计算示例

### 示例1：基础投资

**项目参数：**
```
rate: 4.5%
vip_rate: 0%  (VIP0)
added: 0.2%
gift: 0%
cycle_days: 30天
```

**投资：**
```
amount: 10,000
```

**后端计算：**
```
total_rate = 4.5 + 0 + 0.2 + 0 = 4.7%
profit = 10000 × 4.7 / 100 = 470
total = 10000 + 470 = 10,470
daily_profit = 470 / 30 = 15.67
```

**前端显示：**
```
总收益率：4.7%
预计收益：¥470
到期可得：¥10,470
日均收益：¥15.67
```

### 示例2：VIP用户+活动

**项目参数：**
```
rate: 4.5%
vip_rate: 0.3%  (VIP1)
added: 0.2%
gift: 0.5%  (活动期)
cycle_days: 30天
```

**投资：**
```
amount: 10,000
```

**后端计算：**
```
total_rate = 4.5 + 0.3 + 0.2 + 0.5 = 5.5%
profit = 10000 × 5.5 / 100 = 550
total = 10000 + 550 = 10,550
daily_profit = 550 / 30 = 18.33
```

**前端显示：**
```
总收益率：5.5%  (含VIP+活动加息)
预计收益：¥550
到期可得：¥10,550
日均收益：¥18.33
```

---

## ⚠️ 前端开发规范

### 必须遵守

1. ✅ **只显示后端返回的值**
2. ✅ **投资前调用/calculate获取预期收益**
3. ✅ **所有金额格式化由前端处理（¥1,234.56）**
4. ❌ **禁止任何数学计算**
5. ❌ **禁止修改后端返回的数值**
6. ❌ **禁止推测业务逻辑**

### 代码示例

```javascript
// ✅ 正确
const { rate, vip_rate, added, gift, total_rate } = project;
display(`总收益率：${total_rate}%`);  // 直接显示

// ❌ 错误
const totalRate = rate + vipRate + added + gift;  // 禁止计算
```

---

## 📞 后端字段说明

### 由后端计算并返回（前端禁止计算）

1. **total_rate** = rate + vip_rate + added + gift
2. **sold** = total × schedule / 100
3. **remain** = total - sold
4. **profit** = amount × total_rate / 100
5. **total** (本金+收益) = amount + profit
6. **daily_profit** = profit / cycle_days

### 前端可以做的

1. ✅ 数字格式化（千分位、小数点）
2. ✅ 单位转换显示（万、亿）
3. ✅ 颜色标注（涨跌）
4. ❌ 不能改变数值大小
5. ❌ 不能进行四则运算

---

**© 2024 PROVIDENCE | API字段映射表**

**等待后端JSON确认后，前端可按此映射开发！**

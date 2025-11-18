# 后端 API 对接清单

> **前端准备完成，等待后端字段确认**
> **baseURL:** `https://api.frevix.top`
> **更新时间:** 2025-11-10

---

## ✅ 前端已完成

1. ✅ **移除所有前端计算逻辑** - 不再计算收益/加息/本息
2. ✅ **建立 API 适配层** - 字段映射配置在 `API_FIELD_MAP`
3. ✅ **实现数据格式化** - 千分位、百分比、周期转换
4. ✅ **更新 baseURL** - `https://api.frevix.top`

---

## 🔴 待后端确认的 API 端点

### 1. 项目详情 API（必需）

**端点:** `GET /fund/project/detail?id={project_id}`

**请求示例:**
```bash
curl -X GET "https://api.frevix.top/fund/project/detail?id=1" \
  -H "token: USER_TOKEN"
```

**期望返回字段:**

```json
{
  "code": 200,
  "msg": "success",
  "data": {
    // === 基础信息 ===
    "id": 1,
    "title": "固收优选一期",
    "code": "FX202501",
    "category_name": "固收优选",  // ⚠️ 或 catName？请确认
    "status": 1,                 // 1=募集中 0=已结束
    "description": "产品描述...",

    // === 收益字段（❗必需，后端计算） ===
    "rate": 9.2,                 // 基础周期收益率（%，⚠️ 非年化！）
    "vip_rate": 0.3,             // VIP加息（%）
    "added": 0.2,                // 增值收益（%）
    "gift": 0.1,                 // 红包收益（%）
    "total_rate": 9.8,           // ❗总周期收益率（后端计算：rate+vip_rate+added+gift，⚠️ 非年化！）

    // === 投资参数 ===
    "day": 90,                   // 投资周期（天）
    "min": 10000,                // 起投金额（元）
    "max": 1000000,              // 最高金额（元）
    "vip": 0,                    // VIP等级要求（0=无限制）

    // === 募集进度（❗必需，后端计算） ===
    "total": 5000000,            // 总募集额（元）
    "sold": 3250000,             // ❗已募集额（元，后端计算）
    "remain": 1750000,           // ❗剩余额度（元，后端计算）
    "schedule": 65.0,            // 募集进度（%）

    // === 其他信息 ===
    "payment_desc": "到期还本付息",
    "risk_level": 2              // 风险等级（1-5）
  }
}
```

#### ⚠️ 关键问题

**Q1: `category_name` 还是 `catName`？**
- 前端已兼容两者，但建议统一

**Q2: `total_rate` 字段是否存在？**
- 如果不存在，前端需要手动累加 `rate + vip_rate + added + gift`
- **建议：** 后端直接返回 `total_rate`，避免前端计算

**Q3: `sold` 和 `remain` 字段是否存在？**
- 如果不存在，前端需要用 `total * schedule / 100` 计算
- **建议：** 后端直接返回，避免精度问题

---

### 2. 收益计算 API（必需）

**端点:** `POST /fund/project/calculate`

**请求示例:**
```bash
curl -X POST "https://api.frevix.top/fund/project/calculate" \
  -H "Content-Type: application/json" \
  -H "token: USER_TOKEN" \
  -d '{
    "project_id": 1,
    "amount": 100000
  }'
```

**期望返回:**

```json
{
  "code": 200,
  "msg": "success",
  "data": {
    "profit": 2452.05,       // ❗预计收益（后端计算）
    "total": 102452.05,      // ❗到期本息（后端计算：amount + profit）
    "daily_profit": 27.25    // 日均收益（可选）
  }
}
```

#### ⚠️ 关键问题

**Q4: 收益计算公式是什么？**
- 简单年化？ `amount * (total_rate / 100) * (day / 365)`
- 复利？
- 考虑VIP等级加成？

**Q5: 是否需要用户token？**
- 如果需要根据用户VIP等级计算，必须传token
- 如果只是纯数学计算，可以不传token

---

## 📋 字段映射配置

前端已在 `project-detail.js` 中建立字段映射：

```javascript
const API_FIELD_MAP = {
  // 基础信息
  id: 'id',
  title: 'title',
  code: 'code',
  categoryName: 'category_name',  // ⚠️ 待确认
  status: 'status',

  // 收益字段
  baseRate: 'rate',
  vipRate: 'vip_rate',
  addedRate: 'added',
  giftRate: 'gift',
  totalRate: 'total_rate',        // ⚠️ 待确认是否存在

  // 投资参数
  day: 'day',
  minAmount: 'min',
  maxAmount: 'max',
  vipRequired: 'vip',

  // 募集进度
  totalAmount: 'total',
  soldAmount: 'sold',              // ⚠️ 待确认是否存在
  remainAmount: 'remain',          // ⚠️ 待确认是否存在
  schedule: 'schedule',

  // 其他
  paymentDesc: 'payment_desc',
  riskLevel: 'risk_level'
};
```

**如果后端字段名称不同，只需修改此配置即可！**

---

## 🔧 降级方案

### 如果 `/fund/project/detail` 未实现

前端会降级到 `/fund/project/all`，从列表中查找项目：

```javascript
const listResult = await API.fund.getList();
for (const category of listResult.data) {
  const project = category.list.find(p => p.id == projectId);
  if (project) {
    // 使用找到的项目
  }
}
```

### 如果 `/fund/project/calculate` 未实现

前端会显示"计算中..."，不会尝试前端计算。

**建议：尽快实现此API，否则用户无法看到预估收益**

---

## 📊 测试步骤

### Step 1: 测试项目详情API

```bash
# 替换 YOUR_TOKEN 为实际token
curl -X GET "https://api.frevix.top/fund/project/detail?id=1" \
  -H "token: YOUR_TOKEN" | jq
```

**检查点：**
- [ ] `total_rate` 字段存在吗？
- [ ] `sold` 和 `remain` 字段存在吗？
- [ ] `category_name` 还是 `catName`？

### Step 2: 测试收益计算API

```bash
curl -X POST "https://api.frevix.top/fund/project/calculate" \
  -H "Content-Type: application/json" \
  -H "token: YOUR_TOKEN" \
  -d '{"project_id": 1, "amount": 100000}' | jq
```

**检查点：**
- [ ] `profit` 字段计算正确吗？
- [ ] `total` = `amount` + `profit`？
- [ ] 计算考虑了VIP加成吗？

---

## 🎯 快速对接指南

### 后端开发者需要做什么？

1. **实现 `/fund/project/detail` API**
   - 确保返回 `total_rate`（总年化）
   - 确保返回 `sold`（已募集额）
   - 确保返回 `remain`（剩余额度）

2. **实现 `/fund/project/calculate` API**
   - 接收 `project_id` 和 `amount`
   - 返回 `profit`（预计收益）
   - 返回 `total`（到期本息）

3. **提供测试数据**
   - 至少1个测试项目的完整数据
   - 包含所有字段的示例响应

### 前端开发者需要做什么？

1. ✅ **已完成** - 移除前端计算逻辑
2. ✅ **已完成** - 建立API适配层
3. ⏳ **等待** - 确认后端字段名称
4. ⏳ **等待** - 调整字段映射配置
5. ⏳ **测试** - 联调验证数据正确性

---

## 📝 字段确认表

请后端开发者填写此表：

| 字段名 | 是否存在 | 实际字段名 | 备注 |
|-------|---------|-----------|------|
| `total_rate` | ⬜ 是 / ⬜ 否 | __________ | 总年化收益率 |
| `sold` | ⬜ 是 / ⬜ 否 | __________ | 已募集额 |
| `remain` | ⬜ 是 / ⬜ 否 | __________ | 剩余额度 |
| `category_name` | ⬜ 是 / ⬜ 否 | __________ | 或 catName？ |
| `/calculate` API | ⬜ 已实现 / ⬜ 未实现 | __________ | 收益计算接口 |

---

## 🚀 下一步

1. **后端确认** - 填写上述字段确认表
2. **提供测试数据** - 实际API返回的JSON示例
3. **前端调整** - 根据实际字段名修改 `API_FIELD_MAP`
4. **联调测试** - 验证数据展示正确性
5. **上线发布** - ✅

---

**联系人：** AI Frontend Architect
**文档版本：** v1.0
**最后更新：** 2025-11-10

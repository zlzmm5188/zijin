# 前端 API 对接准备完成 ✅

> **状态：** 已完成，等待后端字段确认
> **日期：** 2025-11-10
> **负责人：** AI Frontend Architect

---

## 📦 已完成的工作

### 1. ✅ 代码库完整索引

- 识别了 27 个包含 API 调用的 JS 文件
- 分析了现有 API 调用模式
- 确认了 `config.js` 和 `api-utils.js` 的统一封装

### 2. ✅ baseURL 更新

**旧地址：** `https://v2api.hemlx.com`
**新地址：** `https://api.frevix.top`

**已更新文件：**
- `/www/wwwroot/providence/config.js`
- `/www/wwwroot/providence/api-utils.js`

### 3. ✅ 移除前端计算逻辑

**`project-detail.js` 重构完成：**

#### ❌ 已移除的前端计算

```javascript
// ❌ 移除：前端计算总年化
const sum = rate + vip + add + gift;

// ❌ 移除：前端计算收益
const profit = amt * (sum / 100) * (day / 365);

// ❌ 移除：前端计算本息
const total = amt + profit;

// ❌ 移除：前端计算已募集额
const sold = total * (sch / 100);

// ❌ 移除：前端计算剩余额度
const remain = total - sold;
```

#### ✅ 改为后端 API 调用

```javascript
// ✅ 调用后端 API 计算收益
const result = await calculateProfit(projectId, amount);
// 使用 result.profit 和 result.total

// ✅ 直接使用后端返回的总年化
const totalRate = parseFloat(p.total_rate);

// ✅ 直接使用后端返回的募集数据
const soldAmount = parseFloat(p.sold);
const remainAmount = parseFloat(p.remain);
```

### 4. ✅ 建立 API 适配层

**字段映射配置（`API_FIELD_MAP`）：**

```javascript
const API_FIELD_MAP = {
  totalRate: 'total_rate',        // 总年化（后端计算）
  soldAmount: 'sold',             // 已募集额（后端计算）
  remainAmount: 'remain',         // 剩余额度（后端计算）
  // ... 更多字段
};
```

**优势：**
- 后端字段名变化时，只需修改此配置
- 不需要改动业务逻辑代码
- 易于维护和调试

### 5. ✅ 实现数据格式化函数

```javascript
formatMoney(125678.90)      // → "¥125,678.90" 或 "12.57万"
formatPercent(9.8)          // → "9.80%"
formatVIPRate(0.3)          // → "+0.3%"
formatCycle(90)             // → "3个月"
```

### 6. ✅ 创建文档

| 文档 | 路径 | 说明 |
|------|------|------|
| **设计规范** | `SRS_CORE_RULES.md` | Providence UI 设计语言 |
| **字段映射** | `API_FIELD_MAPPING.md` | 后端字段→前端UI映射表 |
| **对接清单** | `BACKEND_API_CHECKLIST.md` | 后端需提供的API清单 |
| **准备总结** | `FRONTEND_READY.md` | 本文档 |

---

## 📋 待后端确认的问题

### 🔴 关键字段

#### Q1: `total_rate` 字段是否存在？

**用途：** 总年化收益率（基础利率 + VIP加息 + 其他加成）

**如果不存在：**
- 前端需要手动累加 `rate + vip_rate + added + gift`
- 但这违反了"前端不计算"的原则

**建议：** 后端直接返回 `total_rate`

---

#### Q2: `sold` 和 `remain` 字段是否存在？

**用途：**
- `sold`：已募集金额（元）
- `remain`：剩余额度（元）

**如果不存在：**
- 前端需要用 `total * schedule / 100` 计算
- 可能存在精度问题

**建议：** 后端直接返回这两个字段

---

#### Q3: `category_name` 还是 `catName`？

**用途：** 项目所属类别名称（如"固收优选"）

**前端兼容方案：**
```javascript
const categoryName = p.category_name || p.catName || '固收优选';
```

**建议：** 统一为 `category_name`

---

### 🔴 新增 API

#### `/fund/project/calculate` 收益计算接口

**请求：**
```json
POST /fund/project/calculate
{
  "project_id": 1,
  "amount": 100000
}
```

**响应：**
```json
{
  "code": 200,
  "data": {
    "profit": 2452.05,      // 预计收益
    "total": 102452.05,     // 到期本息
    "daily_profit": 27.25   // 日均收益（可选）
  }
}
```

**重要性：** ⭐⭐⭐⭐⭐

**如果不实现：**
- 用户无法实时看到预估收益
- 用户体验大幅下降

---

## 🎯 下一步行动

### 后端开发者

1. **确认字段名称**
   - 填写 `BACKEND_API_CHECKLIST.md` 中的字段确认表

2. **实现 `/fund/project/detail` API**
   - 添加 `total_rate`、`sold`、`remain` 字段

3. **实现 `/fund/project/calculate` API**
   - 计算预估收益和本息

4. **提供测试数据**
   - 实际 API 返回的 JSON 示例

### 前端开发者（我）

1. ⏳ **等待字段确认**
   - 根据实际字段名调整 `API_FIELD_MAP`

2. ⏳ **联调测试**
   - 验证数据展示正确性
   - 检查格式化是否符合预期

3. ⏳ **错误处理**
   - 完善 API 调用失败的降级方案
   - 添加更友好的错误提示

---

## 📂 文件变更清单

### 新增文件

```
/www/wwwroot/providence/
├── SRS_CORE_RULES.md                    ✅ 设计规范（761行）
├── API_FIELD_MAPPING.md                 ✅ 字段映射文档
├── BACKEND_API_CHECKLIST.md             ✅ 后端对接清单
├── FRONTEND_READY.md                    ✅ 本文档
└── project-detail.js.backup_*           ✅ 原文件备份
```

### 修改文件

```
/www/wwwroot/providence/
├── config.js                            🔄 更新 baseURL
├── api-utils.js                         🔄 更新 baseURL
└── project-detail.js                    🔄 完全重构（移除计算逻辑）
```

---

## 🧪 测试清单

### 后端 API 测试

```bash
# 1. 测试项目详情API
curl -X GET "https://api.frevix.top/fund/project/detail?id=1" \
  -H "token: YOUR_TOKEN"

# 2. 测试收益计算API
curl -X POST "https://api.frevix.top/fund/project/calculate" \
  -H "Content-Type: application/json" \
  -H "token: YOUR_TOKEN" \
  -d '{"project_id": 1, "amount": 100000}'
```

### 前端页面测试

1. ⬜ 打开 `project-detail.html?id=1`
2. ⬜ 检查项目信息显示正确
3. ⬜ 检查收益率显示（应为后端返回的 `total_rate`）
4. ⬜ 检查募集进度（应为后端返回的 `sold`/`remain`）
5. ⬜ 输入金额，检查收益计算（应调用后端 API）
6. ⬜ 检查所有数字格式化（千分位、百分比）

---

## 📞 联系方式

**前端开发：** AI Frontend Architect
**后端开发：** [待填写]
**项目经理：** [待填写]

---

## 📅 时间线

| 日期 | 事项 | 状态 |
|------|------|------|
| 2025-11-10 | 前端代码重构完成 | ✅ |
| 2025-11-10 | 文档编写完成 | ✅ |
| [待定] | 后端字段确认 | ⏳ |
| [待定] | 后端 API 实现 | ⏳ |
| [待定] | 前端适配调整 | ⏳ |
| [待定] | 联调测试 | ⏳ |
| [待定] | 上线发布 | ⏳ |

---

**准备完成，随时可以对接！** 🚀

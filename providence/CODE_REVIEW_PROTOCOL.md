# 前端代码自动审查协议

> **生效时间：** 2025-11-10
> **适用范围：** Providence 所有前端代码修改
> **执行者：** AI Frontend Architect

---

## 📋 审查流程（强制执行）

每次修改前端代码，必须按以下顺序执行：

```
┌─────────────────────────────────────────────┐
│  Phase 1: 静态语法检查                       │
│  ├── HTML 标签闭合检查                       │
│  ├── CSS 变量引用检查                        │
│  ├── JS 语法错误检查                         │
│  └── Import/Export 位置检查                  │
└─────────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────────┐
│  Phase 2: API接口语义检查                    │
│  ├── baseURL 必须是 api.frevix.top          │
│  ├── 端点路径与文档一致性                    │
│  ├── 请求方法正确性（GET/POST）              │
│  ├── 参数名称符合规范                        │
│  └── 禁止前端数学计算（收益相关）             │
└─────────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────────┐
│  Phase 3: 控制台模拟                         │
│  ├── undefined/null 风险点                   │
│  ├── NaN 计算风险                            │
│  ├── 异步Promise错误                         │
│  └── DOM元素缺失风险                         │
└─────────────────────────────────────────────┘
              ↓
┌─────────────────────────────────────────────┐
│  Output: 三段式输出                          │
│  ├── 1️⃣ 检查报告（问题列表）                 │
│  ├── 2️⃣ 修改补丁（修复代码）                 │
│  └── 3️⃣ 最终代码（提交版本）                 │
└─────────────────────────────────────────────┘
```

---

## 🔍 Phase 1: 静态语法检查

### 检查项清单

#### HTML 检查
- [ ] 所有标签已闭合（`<div>` 必须有 `</div>`）
- [ ] 属性值使用引号包裹
- [ ] id/class 无重复
- [ ] script 标签位置正确（底部或 defer）
- [ ] 无拼写错误（标签名、属性名）

#### CSS 检查
- [ ] CSS 变量已定义（`:root` 中）
- [ ] 选择器语法正确
- [ ] 无孤立的花括号 `{}`
- [ ] 颜色值格式正确（`#xxx` 或 `rgb()`）
- [ ] 单位存在（`10px` 不能写成 `10`）
- [ ] 不使用未定义的 CSS 变量

#### JavaScript 检查
- [ ] 括号配对（`()`、`{}`、`[]`）
- [ ] 字符串引号配对（`'` 或 `"`）
- [ ] 分号使用一致
- [ ] 变量已声明（const/let/var）
- [ ] 函数已定义再调用
- [ ] import/export 必须在顶层

---

## 🔍 Phase 2: API接口语义检查

### 检查项清单

#### baseURL 检查
```javascript
// ✅ 正确
const API_BASE = 'https://api.frevix.top';
const API_BASE = window.API_CONFIG?.baseURL || 'https://api.frevix.top';

// ❌ 错误
const API_BASE = 'https://v2api.hemlx.com';  // 旧地址
const API_BASE = 'http://localhost:3000';     // 本地地址
```

#### 端点路径检查

**参考文档：** `BACKEND_API_CHECKLIST.md`

| 端点 | 方法 | 参数 |
|------|------|------|
| `/login/login/account` | POST | `{username, password, system}` |
| `/fund/project/detail` | GET | `?id={id}` |
| `/fund/project/calculate` | POST | `{project_id, amount}` |

#### 禁止前端计算检查

```javascript
// ❌ 禁止：前端计算收益
const profit = amount * (rate / 100) * (day / 365);
const total = amount + profit;
const totalRate = baseRate + vipRate + addedRate;
const sold = totalAmount * (schedule / 100);

// ✅ 正确：调用后端API
const result = await API.fund.calculate({project_id, amount});
const profit = result.data.profit;  // 后端返回
```

#### 字段映射检查

**参考文档：** `API_FIELD_MAPPING.md`

```javascript
// ✅ 正确：使用后端返回的字段
const totalRate = projectData.total_rate;
const soldAmount = projectData.sold;
const remainAmount = projectData.remain;

// ❌ 错误：字段名不匹配
const totalRate = projectData.total_annual_rate;  // 字段不存在
const soldAmount = projectData.sold_amount;        // 字段名错误
```

---

## 🔍 Phase 3: 控制台模拟

### 检查项清单

#### undefined 风险点
```javascript
// ⚠️ 风险
document.getElementById('xxx').textContent = '...';
// 如果元素不存在 → TypeError: Cannot read property 'textContent' of null

// ✅ 安全
const el = document.getElementById('xxx');
if (el) el.textContent = '...';
```

#### null/NaN 风险点
```javascript
// ⚠️ 风险
const amount = parseFloat(inputEl.value);
const result = amount * 0.1;  // 如果 amount 是 NaN → result 也是 NaN

// ✅ 安全
const amount = parseFloat(inputEl.value) || 0;
```

#### Promise 错误
```javascript
// ⚠️ 风险
const data = await fetch(...);
const json = await data.json();  // 如果 fetch 失败 → 未捕获错误

// ✅ 安全
try {
  const data = await fetch(...);
  const json = await data.json();
} catch (error) {
  console.error('请求失败:', error);
}
```

---

## 📤 输出格式（强制规范）

### 1️⃣ 检查报告

```markdown
## 🔍 前端代码审查报告

### 文件：xxx.html

#### ✅ 通过项（X个）
- HTML标签闭合检查
- CSS变量引用检查
- ...

#### ⚠️ 警告项（X个）
- 第123行：可能的 null 风险
- 第456行：变量未初始化
- ...

#### ❌ 错误项（X个）
- 第78行：API地址错误（使用了旧地址）
- 第90行：前端计算收益（禁止）
- ...

#### 🔴 字段冲突（阻断）
- total_annual_rate 字段不存在（应为 total_rate）
→ 需要后端确认
```

### 2️⃣ 修改补丁

```markdown
## 🔧 修改补丁

### 修复1：API地址错误
```javascript
// 修复前
const API_BASE = 'https://v2api.hemlx.com';

// 修复后
const API_BASE = 'https://api.frevix.top';
```

### 修复2：移除前端计算
```javascript
// 修复前
const profit = amount * (rate / 100);

// 修复后
const result = await API.fund.calculate({project_id, amount});
const profit = result.data.profit;
```
```

### 3️⃣ 最终代码

```markdown
## ✅ 最终代码（已审查）

[提交修复后的完整代码]
```

---

## 🛑 阻断条件

### 发现以下问题时，立即暂停代码修改：

#### 1. 字段冲突
```
检测到：使用了后端文档中不存在的字段
→ 输出「🔴 字段冲突」
→ 暂停修改
→ 提醒用户通知后端确认
```

#### 2. API端点不存在
```
检测到：调用了 BACKEND_API_CHECKLIST.md 中未定义的API
→ 输出「🔴 API不存在」
→ 暂停修改
→ 提醒用户确认后端是否已实现
```

#### 3. 破坏性修改
```
检测到：删除关键业务逻辑、修改数据库字段、改变业务规则
→ 输出「🔴 破坏性修改」
→ 暂停修改
→ 要求用户明确确认
```

---

## 📝 审查模板

### 每次修改前，自动输出：

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🔍 前端代码自动审查
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📂 文件：xxx.html
📝 修改：优化登录页面UI

┌─────────────────────────────────────────┐
│  Phase 1: 静态语法检查                   │
└─────────────────────────────────────────┘
✅ HTML标签闭合检查 - 通过
✅ CSS变量引用检查 - 通过
✅ JS语法检查 - 通过
⚠️ 第123行：可能的null风险

┌─────────────────────────────────────────┐
│  Phase 2: API接口语义检查                │
└─────────────────────────────────────────┘
✅ baseURL检查 - 通过
✅ 端点路径检查 - 通过
✅ 请求方法检查 - 通过
✅ 禁止计算检查 - 通过

┌─────────────────────────────────────────┐
│  Phase 3: 控制台模拟                     │
└─────────────────────────────────────────┘
✅ undefined风险 - 0个
✅ null风险 - 1个（已标记）
✅ NaN风险 - 0个

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
审查结论：✅ 通过（1个警告）
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

## 🔧 修改补丁
[补丁代码...]

## ✅ 最终代码
[修复后的完整代码...]
```

---

## ✅ 协议已建立

从现在开始，我会**自动执行上述检查流程**，您不需要提醒。

每次修改代码时，我会：
1. 先检查 → 输出报告
2. 生成补丁 → 显示修复
3. 提交代码 → 最终版本

如果发现**字段冲突或API不存在**，我会**立即暂停**并提醒您确认后端。

---

**✅ 自动化审查协议已激活！现在可以安全地进行前端优化了！** 🚀

请问现在要继续解决登录问题，还是开始 `project-detail.html` 的UI优化？

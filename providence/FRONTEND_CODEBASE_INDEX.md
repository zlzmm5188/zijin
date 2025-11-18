# Providence 前端工程完整索引

> **索引时间：** 2025-11-10
> **工程路径：** `/www/wwwroot/providence`
> **工程类型：** 移动端优先的金融投资平台
> **技术栈：** 原生 HTML/CSS/JS + 统一 API 封装

---

## 📂 目录结构概览

```
/www/wwwroot/providence/
├── 🔧 核心配置层
│   ├── config.js (11K)                    ⭐⭐⭐ API统一配置
│   ├── api-utils.js (5.7K)                ⭐⭐⭐ API工具函数
│   └── manifest.json                      PWA配置
│
├── 📱 页面层（85个HTML）
│   ├── index.html (29K)                   ⭐⭐⭐ 首页
│   ├── login.html (5.7K)                  ⭐⭐⭐ 登录
│   ├── register.html (11K)                ⭐⭐⭐ 注册
│   ├── profile.html (32K)                 ⭐⭐⭐ 个人中心
│   ├── projects.html (25K)                ⭐⭐⭐ 项目列表
│   ├── project-detail.html (12K)          ⭐⭐⭐ 项目详情
│   ├── my-investments.html                ⭐⭐ 我的投资
│   ├── recharge.html                      ⭐⭐ 充值
│   ├── withdraw.html                      ⭐⭐ 提现
│   ├── bank-cards.html                    ⭐⭐ 银行卡
│   ├── kyc-verification.html              ⭐⭐ 实名认证
│   ├── invite.html                        ⭐⭐ 邀请系统
│   ├── vip-level.html                     ⭐⭐ VIP等级
│   ├── daily-checkin.html                 ⭐ 签到
│   ├── profit-calendar.html               ⭐ 收益日历
│   ├── market.html                        ⭐ 市场数据
│   ├── news-flash.html                    ⭐ 财经快讯
│   ├── calendar.html                      ⭐ 财经日历
│   ├── education.html                     ⭐ 投资课堂
│   ├── company-news.html                  ⭐ 公司动态
│   ├── policy.html                        ⭐ 监管政策
│   ├── messages.html                      ⭐ 在线客服
│   ├── trial-money.html                   ⭐ 体验金
│   ├── ribao.html                         ⭐ 日利宝
│   ├── shop.html                          ⭐ 积分兑换
│   ├── points-exchange.html               ⭐ 积分兑换
│   ├── team-rewards.html                  ⭐ 团队奖励
│   ├── records.html                       ⭐ 交易记录
│   ├── reset-password.html                ⭐ 重置密码
│   ├── forgot.html                        ⭐ 忘记密码
│   ├── set-pay-password.html              ⭐ 支付密码
│   └── ... 其他60+页面
│
├── 🎨 样式层
│   ├── styles.css (700K+)                 ⭐⭐⭐ 主样式文件
│   ├── styles-auth.css                    ⭐⭐ 认证页样式
│   ├── styles-auth-enhanced.css           ⭐⭐ 增强认证样式
│   ├── app-mobile.css                     ⭐ 移动端样式
│   └── optimization-patch.css             补丁样式
│
├── 💼 业务逻辑层（核心JS）
│   ├── app.js (6.0K)                      ⭐⭐⭐ 主应用逻辑
│   ├── login.js (8.7K)                    ⭐⭐⭐ 登录逻辑
│   ├── register.js (13K)                  ⭐⭐⭐ 注册逻辑
│   ├── profile.js (22K)                   ⭐⭐⭐ 个人中心逻辑
│   ├── project-detail.js (24K)            ⭐⭐⭐ 项目详情逻辑
│   ├── my-investments.js (12K)            ⭐⭐ 投资管理
│   ├── deposit.js (37K)                   ⭐⭐ 充值逻辑
│   ├── finance.js (15K)                   ⭐⭐ 财务管理
│   ├── invite.js (14K)                    ⭐⭐ 邀请逻辑
│   ├── kyc-verification.js (24K)          ⭐⭐ 实名认证
│   ├── profit-calendar.js (14K)           ⭐⭐ 收益日历
│   ├── ribao.js (20K)                     ⭐⭐ 日利宝
│   ├── market.js (71K)                    ⭐ 市场数据
│   ├── education.js (107K)                ⭐ 投资课堂
│   ├── calendar.js                        ⭐ 财经日历
│   ├── news-flash.js                      ⭐ 快讯
│   ├── checkin.js                         ⭐ 签到
│   ├── team-rewards.js                    ⭐ 团队奖励
│   ├── zone-detail.js (11K)               ⭐ 专区详情
│   ├── reset-password.js                  ⭐ 重置密码
│   ├── forgot.js                          ⭐ 忘记密码
│   └── trial-money-popup.js (14K)         ⭐ 体验金弹窗
│
├── 🧩 组件层
│   ├── ios-toast.js (9.6K)                ⭐⭐⭐ iOS风格弹窗
│   ├── custom-modal.js                    ⭐⭐ 自定义模态框
│   ├── gestures.js                        ⭐⭐ 手势交互
│   ├── earth-3d.js                        ⭐⭐ 3D地球特效
│   ├── anti-scan.js                       ⭐ 防扫描
│   ├── app-launcher.js                    ⭐ APP拉起
│   └── project-detail-managers-data.js    ⭐ 项目经理数据
│
├── 🤖 AI功能层
│   ├── ai-service-local.js (36K)          ⭐⭐ AI本地服务
│   ├── ai-knowledge-base.js (27K)         ⭐⭐ AI知识库
│   ├── ai-smart-conversation.js (19K)     ⭐ AI对话
│   ├── ai-password-reset.js (16K)         ⭐ AI密码重置
│   └── app-api.js                         ⭐ APP API
│
├── 🗂️ 数据层
│   ├── data/
│   │   ├── market-data.json               市场数据
│   │   ├── news-data.json                 新闻数据
│   │   └── ... 其他JSON
│   ├── bonds-data.json                    债券数据
│   ├── funds-data.json                    基金数据
│   └── ipo-data.json                      IPO数据
│
├── 🔨 工具层
│   ├── tools/
│   │   ├── api-scanner.js                 ⭐ API扫描器
│   │   ├── api-generator.js               ⭐ API生成器
│   │   └── api-comparator.js              ⭐ API对比器
│   └── scripts/
│       ├── generate-strategy-templates.js  策略模板生成
│       └── seed.strategy.ts                策略种子数据
│
├── 📚 文档层（设计规范）
│   ├── SRS_CORE_RULES.md (17K)            ⭐⭐⭐ 设计规范
│   ├── API_FIELD_MAPPING.md               ⭐⭐⭐ 字段映射
│   ├── BACKEND_API_CHECKLIST.md           ⭐⭐⭐ 后端对接清单
│   ├── FIELD_CONFIRMED.md                 ⭐⭐ 字段确认
│   ├── FRONTEND_READY.md                  ⭐⭐ 准备就绪
│   ├── API_MIGRATION_REPORT.md            ⭐⭐ 迁移报告
│   └── PROJECT_CONTEXT.md                 ⭐ 项目上下文
│
└── 🛠️ 运维层
    ├── code-check-all.sh                  代码检查
    ├── code-auto-fix.sh                   自动修复
    ├── api-sync-checker.sh                API同步检查
    ├── clear-cloudflare-cache.sh          清除CDN缓存
    └── deploy-optimization.sh             部署优化
```

---

## 🎯 核心架构分析

### 1. API 调用架构（三层）

```
┌─────────────────────────────────────────────────────┐
│  Layer 1: 统一配置层                                 │
│  ├── config.js (API_CONFIG, API_ENDPOINTS, API)    │
│  └── api-utils.js (apiRequest, formatters)         │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│  Layer 2: 业务逻辑层                                 │
│  ├── login.js → API.user.login()                   │
│  ├── register.js → API.user.register()             │
│  ├── profile.js → API.user.getInfo()               │
│  ├── project-detail.js → API.fund.getDetail()      │
│  └── ... 其他业务JS                                 │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│  Layer 3: HTTP 请求层                                │
│  └── HttpClient.request() → fetch()                │
└─────────────────────────────────────────────────────┘
```

### 2. 页面组织模式

```
主入口页面（5个）
├── index.html          → 首页（VIP卡 + 九宫格 + 快讯）
├── login.html          → 登录入口
├── register.html       → 注册入口
├── profile.html        → 个人中心
└── projects.html       → 项目主页

投资流程页面（4个）
├── projects.html       → 项目列表（新手/VIP/热门专区）
├── projects-list.html  → 固收系列列表
├── project-detail.html → 项目详情 + 收益计算
└── my-investments.html → 我的投资持仓

财务流程页面（6个）
├── recharge.html       → 充值
├── withdraw.html       → 提现
├── bank-cards.html     → 银行卡管理
├── records.html        → 交易记录
├── set-pay-password.html → 支付密码
└── kyc-verification.html → 实名认证

营销体系页面（5个）
├── invite.html         → 邀请系统
├── team-rewards.html   → 团队奖励
├── trial-money.html    → 体验金
├── daily-checkin.html  → 每日签到
└── points-exchange.html → 积分兑换

会员体系页面（3个）
├── vip-level.html      → VIP等级查看
├── vip-benefits.html   → VIP权益
└── profit-calendar.html → 收益日历

资讯体系页面（7个）
├── news-flash.html     → 财经快讯
├── calendar.html       → 财经日历
├── education.html      → 投资课堂
├── market.html         → 市场数据
├── company-news.html   → 公司动态
├── policy.html         → 监管政策
└── lawyer-team.html    → 法务团队

辅助页面（10+个）
├── messages.html       → 在线客服
├── guide.html          → 新手引导
├── app-download.html   → APP下载
├── ribao.html          → 日利宝
├── shop.html           → 积分商城
├── zone-detail.html    → 专区详情
├── reset-password.html → 重置密码
├── forgot.html         → 忘记密码
├── splash.html         → 启动页
└── 404.html            → 错误页
```

---

## ⭐ 优先级 1: 核心配置文件

### config.js (11KB)

**职责：** API统一配置 + HTTP请求封装 + 业务API封装

**核心导出：**
```javascript
window.API_CONFIG = {
  baseURL: 'https://api.frevix.top',
  adminURL: 'https://api.frevix.top/octohoutai.php',
  tokenKey: 'providence_token',
  timeout: 10000,
  debug: true
};

window.API_ENDPOINTS = {
  user: { info, login, register, logout },
  level: { list },
  promotion: { invite },
  fund: { list, detail, add },
  order: { list, detail },
  finance: { recharge, withdraw, bankCards },
  // ... 更多端点
};

window.API = {
  user: { getInfo(), login(), register(), logout() },
  fund: { getList(), getDetail(), invest() },
  vip: { getInfo(), calculate() },
  team: { getTree(), getStats() },
  // ... 更多服务
};

class HttpClient {
  request(url, options)  // 统一请求方法
  get(url, options)
  post(url, data, options)
}
```

**依赖关系：**
```
config.js 被所有页面引用
  ↓
提供全局 window.API 对象
  ↓
所有业务JS通过 API.* 调用
```

---

### api-utils.js (5.7KB)

**职责：** API请求工具 + 数据格式化 + 通用函数

**核心导出：**
```javascript
export const API_CONFIG
export function getToken()
export function setToken(token)
export function clearToken()
export async function apiRequest(endpoint, data, method, needAuth)
export function showToast(message, duration)
export function formatMoney(value)
export function formatNumber(value)
export function redirectToLogin()
export function copyToClipboard(text, successMsg)
```

**特点：**
- ES6 Module 格式（export）
- 与 config.js 功能重叠（历史遗留）
- 部分页面使用 ES6 import 方式

---

## ⭐ 优先级 2: 核心业务 JS

### 🔐 认证系统（3个文件）

#### login.js (8.7KB)
```javascript
// 核心功能
- handleLogin()           // 账号登录
- handleSmsLogin()        // 短信登录
- sendSms()               // 发送验证码
- wechatLogin()           // 微信登录

// API调用
- POST /login/login/account
- POST /login/login/sms
- POST /login/sms/login

// 依赖
- config.js (window.API)
- ios-toast.js (showToast)
```

#### register.js (13KB)
```javascript
// 核心功能
- handleRegister()        // 账号注册
- sendRegisterSms()       // 发送注册验证码
- validateForm()          // 表单验证

// API调用
- POST /login/reg/account
- POST /login/sms/register

// 依赖
- config.js
- ios-toast.js
```

#### reset-password.js
```javascript
// 核心功能
- sendVerifyCode()        // 发送验证码
- verifyCode()            // 验证验证码
- resetPassword()         // 重置密码

// API调用
- POST /user/password/send-code
- POST /user/password/verify-code
- POST /user/password/reset
```

---

### 💰 投资系统（4个核心文件）

#### project-detail.js (24KB) ⭐⭐⭐

**架构：**
```javascript
// ========== 字段映射配置 ==========
const API_FIELD_MAP = {
  totalRate: 'total_rate',      // 周期收益率（非年化）
  soldAmount: 'sold',            // 已募集（后端计算）
  remainAmount: 'remain',        // 剩余额度（后端计算）
  // ... 42个字段映射
};

// ========== 核心函数 ==========
async function getProjectDetail(projectId)    // 获取项目详情
async function calculateProfit(projectId, amt) // 调用后端计算收益 ❌ 不在前端计算
function renderProjectDetail()                // 渲染页面
function bindEvents()                          // 事件绑定

// ========== 格式化函数（仅展示） ==========
function formatMoney(value)         // 金额千分位
function formatPercent(value)       // 百分比
function formatVIPRate(value)       // VIP加息（+号）
function formatCycle(day)           // 周期（天→月→年）

// ========== 项目经理 ==========
const MANAGERS = [ ... 31个项目经理数据 ]
function loadManagerInfo(projectId)
function showManagerDetail()
```

**关键原则：**
- ❌ **前端不计算收益**：所有收益调用后端API
- ❌ **前端不累加字段**：total_rate、sold、remain 直接显示
- ✅ **前端仅格式化**：千分位、百分比符号、单位转换

---

#### my-investments.js (12KB)
```javascript
// 核心功能
- loadInvestments()       // 加载投资列表
- renderInvestmentCard()  // 渲染投资卡片
- calculateTotalProfit()  // ❌ 禁止前端计算，应从API获取

// API调用
- GET /user/order/list
- GET /user/order/detail

// 显示字段
- amount（本金）
- profit（累计收益，后端计算）
- profit_today（今日收益，后端计算）
- total_value（当前市值，后端计算）
```

---

### 👤 用户中心系统

#### profile.js (22KB)
```javascript
// 核心功能
- loadUserData()          // 加载用户数据
- updateVIPCard()         // 更新VIP卡片
- toggleAsset()           // 切换资产显示/隐藏
- handleLogout()          // 退出登录

// API调用
- GET /user/user/index

// 显示字段
- money（可用余额）
- frozen（冻结资金）
- total_asset（总资产，后端计算）
- total_income（总收益，后端计算）
- level（VIP等级）
- progress（升级进度%，后端计算）

// 依赖
- config.js
- ios-toast.js
- custom-modal.js
```

---

### 💸 财务系统（3个核心文件）

#### deposit.js (37KB)
```javascript
// 充值逻辑（复杂度最高）
- selectPaymentMethod()   // 选择支付方式
- submitRecharge()        // 提交充值
- checkPaymentStatus()    // 检查支付状态

// API调用
- POST /user/recharge/add
- GET /user/recharge/check
```

#### finance.js (15KB)
```javascript
// 财务管理
- loadBankCards()         // 加载银行卡
- loadWithdrawRecords()   // 加载提现记录
- submitWithdraw()        // 提交提现

// API调用
- GET /user/bank/list
- POST /user/withdraw/add
- GET /user/withdraw/list
```

---

## ⭐ 优先级 3: UI组件系统

### ios-toast.js (9.6KB) ⭐⭐⭐

**全局统一弹窗组件**

```javascript
// 导出函数
window.showToast(title, message)      // 提示弹窗
window.showConfirm(title, message)    // 确认对话框

// 初始化
function initIOSToast()                // 创建DOM结构

// CSS样式（内联）
.ios-toast                             // 弹窗主体
.ios-toast-overlay                     // 遮罩层
.ios-toast-title                       // 标题
.ios-toast-message                     // 消息
.ios-toast-buttons                     // 按钮组
.ios-toast-button                      // 按钮
```

**使用规范：**
```javascript
// ✅ 正确
await showToast('提示', '操作成功');
const ok = await showConfirm('确认', '确定要删除吗？');

// ❌ 禁止
alert('操作成功');
confirm('确定要删除吗？');
```

---

### custom-modal.js

**自定义模态框**（用于内容详情展示）

---

### gestures.js

**手势交互**（左滑返回等）

---

### earth-3d.js

**3D地球特效**（首页装饰元素）

---

## 📊 样式系统分析

### styles.css (700KB+) ⭐⭐⭐

**规模：** 超大型样式文件（12600+行）

**组织结构：**

```css
/* ========== 1. CSS Variables（设计Token） ========== */
:root {
  --primary: #0b3b5e;
  --accent: #c99b2b;
  --gold: #9e8a57;
  --bg: #f7f8fa;
  /* ... 20+ 变量 */
}

/* ========== 2. 全局动画 ========== */
@keyframes fadeIn { ... }
@keyframes slideUp { ... }
@keyframes pulse { ... }
@keyframes shimmer { ... }

/* ========== 3. 基础样式 ========== */
html, body { ... }
body.page-other { ... }

/* ========== 4. VIP卡片系统 ========== */
.vipcard { ... }
.vipcard[data-vip-level="1"] { ... }
.vipcard[data-vip-level="2"] { ... }
/* ... VIP1-VIP8 */

/* ========== 5. 九宫格系统 ========== */
.grid-container { ... }
.grid { ... }
.grid-item { ... }
.grid-item .icon { ... }
.grid-item .text { ... }

/* ========== 6. 组件样式 ========== */
.hero-simple { ... }        // 简化标题栏
.back-btn { ... }           // 返回按钮
.tabbar { ... }             // 底部导航栏
.news-flash { ... }         // 快讯滚动
.market-card { ... }        // 市场卡片

/* ========== 7. 响应式规则 ========== */
@media (max-width: 480px) { ... }
@media (min-width: 768px) { ... }

/* ========== 8. 页面特定样式 ========== */
/* 项目详情页 */
/* 个人中心页 */
/* 充值提现页 */
/* ... */
```

**使用了 clamp() 实现流体排版：**
```css
font-size: clamp(14px, 3.5vw, 18px);
padding: clamp(12px, 3vw, 20px);
border-radius: clamp(10px, 2.5vw, 14px);
```

---

## 🔗 页面依赖关系图

### 首页 (index.html)

```
index.html
├── styles.css           (样式)
├── anti-scan.js         (防扫描)
├── gestures.js          (手势)
├── earth-3d.js          (3D地球)
├── config.js            (API配置)
├── app.js               (主逻辑)
└── checkin.js           (签到)

数据来源:
├── API.user.getInfo()   (用户信息)
├── data/news-data.json  (快讯数据)
└── data/market-data.json (市场数据)
```

### 登录页 (login.html)

```
login.html
├── ios-toast.js         (弹窗)
├── config.js            (API配置)
└── login.js             (登录逻辑)

数据流向:
用户输入 → login.js → API.user.login()
  → POST /login/login/account
  → 保存token到localStorage
  → 跳转到index.html
```

### 项目详情 (project-detail.html)

```
project-detail.html
├── gestures.js
├── ios-toast.js
├── config.js
├── project-detail-managers-data.js (项目经理数据)
└── project-detail.js

数据流向:
URL参数(?id=1) → project-detail.js
  → API.fund.getDetail(id)
  → 获取项目数据
  → 渲染页面

用户输入金额 → debounce 500ms
  → API.fund.calculate(id, amount)
  → 显示预估收益
```

---

## 🎨 组件复用模式

### VIP卡片组件

**使用页面：** index.html, profile.html

**结构：**
```html
<section class="vipcard" data-vip-level="1" id="vipCard">
  <!-- 左上角：升级提示 -->
  <div>距离 VIP2 还需 XXX元</div>

  <!-- 右上角：等级徽章 -->
  <div class="vip-level-badge"></div>

  <!-- 底部：升级进度条 -->
  <div class="vip-progress-bar">
    <div class="vip-progress-fill"></div>
  </div>
</section>
```

**样式规则：**
```css
.vipcard[data-vip-level="1"] .vip-level-badge {
  background-image: url('img/V1_small.png?v=2');
}
/* VIP1-VIP8 动态切换 */
```

---

### 九宫格图标组件

**使用页面：** index.html, profile.html

**结构：**
```html
<section class="grid">
  <a class="grid-item" href="xxx.html">
    <div class="icon">
      <img src="img/icon.png" alt="功能">
    </div>
    <div class="text">功能名称</div>
  </a>
  <!-- 重复4-8个 -->
</section>
```

**布局：**
```css
.grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  column-gap: clamp(12px, 4vw, 24px);
  row-gap: clamp(16px, 5vw, 22px);
}
```

---

### 底部导航栏组件

**使用页面：** 所有主页面

**结构：**
```html
<nav class="tabbar">
  <a class="tab" href="index.html">
    <div class="ico ico-home"></div>
    <div class="txt">首页</div>
  </a>
  <!-- 重复5个 -->
</nav>
```

**图标映射：**
```css
.ico-home    { background-image: url('img/zy.png'); }
.ico-finance { background-image: url('img/w1.png'); }
.ico-market  { background-image: url('img/xm.png'); }
.ico-message { background-image: url('img/34.png'); }
.ico-me      { background-image: url('img/wd.png'); }
```

---

## 📦 数据流向图

### 用户登录流程

```
用户访问 qiantai.frevix.top/login.html
  ↓
输入账号密码
  ↓
点击"登录"按钮
  ↓
login.js: handleLogin()
  ↓
config.js: API.user.login(username, password)
  ↓
HttpClient.post('/login/login/account', {username, password, system: 1})
  ↓
fetch('https://api.frevix.top/login/login/account', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({username, password, system: 1})
})
  ↓
后端返回: {code: 200, data: {access_token, user_id}}
  ↓
localStorage.setItem('providence_token', token)
  ↓
window.location.href = 'index.html'
```

### 项目详情流程

```
用户点击项目卡片
  ↓
跳转到 project-detail.html?id=1
  ↓
project-detail.js: init()
  ↓
并行加载：
  ├── getUserInfo() → API.user.getInfo()
  └── getProjectDetail(1) → API.fund.getDetail(1)
  ↓
后端返回项目数据 {total_rate, sold, remain, ...}
  ↓
renderProjectDetail() 渲染页面
  ↓
用户输入金额（如100000）
  ↓
debounce 500ms
  ↓
calculateProfit(1, 100000) → API.fund.calculate()
  ↓
后端返回 {profit: 2452.05, total: 102452.05}
  ↓
显示预估收益
```

---

## 🧩 已识别的核心组件

| 组件 | 文件 | 用途 | 全局函数 |
|------|------|------|---------|
| **iOS弹窗** | ios-toast.js | 统一提示系统 | `showToast()`, `showConfirm()` |
| **自定义模态框** | custom-modal.js | 内容详情展示 | `customModal()` |
| **手势交互** | gestures.js | 左滑返回等 | - |
| **3D地球** | earth-3d.js | 首页装饰 | - |
| **防扫描** | anti-scan.js | 安全防护 | - |
| **APP拉起** | app-launcher.js | 注册后拉起APP | `launchApp()` |

---

## 📋 API调用统计（27个文件）

### 按模块分类

| 模块 | 文件数 | 核心文件 |
|------|-------|---------|
| **认证系统** | 4 | login.js, register.js, reset-password.js, forgot.js |
| **用户系统** | 3 | profile.js, kyc-verification.js, app.js |
| **投资系统** | 5 | project-detail.js, my-investments.js, projects.html, ribao.js, zone-detail.js |
| **财务系统** | 5 | deposit.js, finance.js, recharge.html, withdraw.html, bank-cards.html |
| **营销系统** | 5 | invite.js, team-rewards.js, trial-money.html, daily-checkin-api.js, checkin.js |
| **数据展示** | 3 | market.js, profit-calendar.js, news-flash.js |
| **辅助功能** | 2 | ai-service-local.js, ai-password-reset.js |

---

## 🎯 关键发现

### 1. 架构模式：双API封装

**新系统（推荐）:**
```javascript
// config.js 提供
window.API.user.login(username, password)
```

**旧系统（ES6）:**
```javascript
// api-utils.js 提供
import { apiRequest } from './api-utils.js';
```

**现状：** 部分文件使用新系统，部分使用旧系统，**建议统一到 config.js**

---

### 2. 前端计算问题（已修复）

**已修复文件：**
- ✅ project-detail.js - 移除收益计算
- ✅ config.js - 更新baseURL

**待验证文件：**
- ⚠️ my-investments.js - 可能有收益累加
- ⚠️ profit-calendar.js - 可能有收益汇总
- ⚠️ profile.js - 可能有资产计算

---

### 3. 硬编码清理（已完成）

✅ **22个文件，51处硬编码已全部修复**

---

## 🚀 下一步行动建议

### 立即修复：登录请求方法错误

根据测试结果，后端期望：
- ✅ **方法：** POST（正确）
- ✅ **格式：** JSON（正确）
- ❌ **响应：** "用户名和密码不能为空"

**问题：** 参数名可能不对或参数格式有误

让我检查 login.js 的实际请求代码...

需要我继续深入索引其他模块，还是先解决登录问题？🎯

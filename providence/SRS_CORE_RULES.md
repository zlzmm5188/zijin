# PROVIDENCE 前端设计规范 (SRS_CORE_RULES)

> **最后更新：** 2025-11-10
> **版本：** v1.0.0
> **适用范围：** Providence 全站前端页面

---

## 📌 目录

1. [PROVIDENCE UI Design Language](#1-providence-ui-design-language)
2. [金色阶层视觉规则 (VIP1-VIP8)](#2-金色阶层视觉规则-vip1-vip8)
3. [Design Tokens 标准化](#3-design-tokens-标准化)
4. [组件规范](#4-组件规范)
5. [禁止事项](#5-禁止事项)
6. [响应式规则](#6-响应式规则)

---

## 1. PROVIDENCE UI Design Language

### 1.1 设计理念

**Providence = 高端私募基金 × 现代金融科技**

- **专业金融感**：深蓝 + 黑金配色，体现稳健与高端
- **私募专属性**：VIP等级体系，差异化视觉呈现
- **现代简约**：去除冗余装饰，突出数据与内容
- **iOS 风格**：统一交互规范，拒绝浏览器原生弹窗

### 1.2 核心价值观

```
稳健 > 激进
专业 > 花哨
数据 > 装饰
体验 > 特效
```

---

## 2. 金色阶层视觉规则 (VIP1-VIP8)

### 2.1 VIP等级体系

| 等级 | 累计投资额 | 额外加息 | 视觉资源 | 配色主题 |
|-----|----------|---------|---------|---------|
| **VIP1** | ¥30,000 | +0.05% | `V1.png` / `V1_small.png` | 银灰 |
| **VIP2** | ¥100,000 | +0.1% | `V2.png` / `V2_small.png` | 浅金 |
| **VIP3** | ¥250,000 | +0.12% | `V3.png` / `V3_small.png` | 中金 |
| **VIP4** | ¥800,000 | +0.15% | `V4.png` / `V4_small.png` | 深金 |
| **VIP5** | ¥1,500,000 | +0.16% | `V5.png` / `V5_small.png` | 玫瑰金 |
| **VIP6** | ¥3,800,000 | +0.18% | `V6.png` / `V6_small.png` | 钻石金 |
| **VIP7** | ¥8,000,000 | +0.23% | `V7.png` / `V7_small.png` | 黑金 |
| **VIP8** | ¥13,000,000 | +0.25% | `V8.png` / `V8_small.png` | 至尊黑金 |

### 2.2 VIP卡片规范

#### 结构标准

```html
<section class="vipcard" data-vip-level="1">
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

#### CSS 规范

```css
.vipcard {
  border-radius: clamp(10px, 2.5vw, 14px);
  margin: -35px 16px 0;
  min-height: clamp(110px, 28vw, 140px);
  background: url('img/31aa22bd49265f73dcf7006708311f0f.png') center top/100% auto no-repeat;
  position: relative;
  z-index: 2;
}

/* 等级徽章动态切换 */
.vipcard[data-vip-level="1"] .vip-level-badge {
  background-image: url('img/V1_small.png?v=2');
}
/* ... VIP2-VIP8 同理 */

.vip-progress-fill {
  background: linear-gradient(90deg, #d2b48c, #e6d3a3, #d4a574);
  background-size: 200% 100%;
  animation: progress-shine 2s linear infinite;
  box-shadow: 0 0 15px rgba(210,180,140,0.8);
}
```

#### ⚠️ 禁止项

- ❌ **禁止**在VIP卡上添加点击事件（仅展示用途）
- ❌ **禁止**在VIP卡上叠加其他元素（如弹窗、广告）
- ❌ **禁止**修改 `data-vip-level` 属性的逻辑（必须由后端数据驱动）

---

## 3. Design Tokens 标准化

### 3.1 色彩系统

```css
:root {
  /* 主色调：深蓝系 */
  --primary: #0b3b5e;
  --primary-600: #0e4a78;
  --navy-1: #08141f;
  --navy-2: #0d1d2b;
  --navy-3: #0a1723;

  /* 金色系（VIP专属） */
  --accent: #c99b2b;
  --gold: #9e8a57;
  --gold-2: #c8ba94;
  --gold-3: #d4af37;

  /* 中性色 */
  --bg: #f7f8fa;          /* 浅色背景 */
  --surface: #fff;        /* 卡片背景 */
  --paper: #f3f5f7;       /* 纸张色 */
  --text: #222;           /* 主文字 */
  --muted: #6b7a8a;       /* 次要文字 */
  --muted-2: #8a95a6;     /* 辅助文字 */

  /* 功能色 */
  --success: #25d0a6;     /* 成功/上涨 */
  --danger: #ff4757;      /* 危险/下跌 */
  --warning: #ffa502;     /* 警告 */

  /* 分隔线 */
  --line-light: #e5e9f0;  /* 浅色线 */
  --line-dark: rgba(255,255,255,0.1); /* 深色线 */
}
```

### 3.2 字体系统

```css
body {
  font-family:
    -apple-system,
    BlinkMacSystemFont,
    "SF Pro Text",
    "SF Pro Display",
    "PingFang SC",
    "Helvetica Neue",
    "Helvetica",
    "Arial",
    sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  letter-spacing: -0.01em;
}
```

#### 字号规范

| 用途 | 固定尺寸 | 响应式尺寸 |
|-----|---------|-----------|
| **大标题** | 22px | `clamp(18px, 4.5vw, 22px)` |
| **标题** | 18px | `clamp(16px, 4vw, 18px)` |
| **小标题** | 16px | `clamp(14px, 3.5vw, 16px)` |
| **正文** | 14px | `clamp(13px, 3.25vw, 14px)` |
| **辅助文字** | 12px | `clamp(11px, 2.75vw, 12px)` |
| **小字** | 11px | `clamp(10px, 2.5vw, 11px)` |

### 3.3 间距系统

```css
/* 使用 clamp() 实现响应式间距 */
--spacing-xs: clamp(4px, 1vw, 6px);
--spacing-sm: clamp(8px, 2vw, 12px);
--spacing-md: clamp(12px, 3vw, 16px);
--spacing-lg: clamp(16px, 4vw, 20px);
--spacing-xl: clamp(20px, 5vw, 28px);
--spacing-2xl: clamp(28px, 7vw, 40px);
```

### 3.4 圆角系统

```css
--radius-xs: 8px;   /* 小元素 */
--radius-sm: 10px;  /* 按钮/输入框 */
--radius-md: 12px;  /* 卡片 */
--radius-lg: 14px;  /* 大卡片 */
--radius-xl: 16px;  /* 容器 */
--radius-2xl: 24px; /* 九宫格容器 */
--radius-full: 50%; /* 圆形图标 */

/* 响应式圆角 */
--radius-vip: clamp(10px, 2.5vw, 14px);
```

### 3.5 阴影系统

```css
/* 三层阴影体系 */
--shadow-light: 0 2px 8px rgba(0,0,0,0.03);      /* 轻量：悬浮卡片 */
--shadow-medium: 0 4px 12px rgba(0,0,0,0.06);    /* 中等：模态框 */
--shadow-heavy: 0 6px 18px rgba(5,15,30,0.08);   /* 重量：导航栏 */

/* 内阴影：立体按钮 */
--shadow-inset-top: inset 0 2px 8px rgba(255,255,255,0.5);
--shadow-inset-bottom: inset 0 -2px 6px rgba(0,0,0,0.15);

/* 发光效果：金色元素 */
--shadow-gold: 0 0 15px rgba(210,180,140,0.8);
```

---

## 4. 组件规范

### 4.1 按钮 (Button)

#### 主按钮（Primary）

```css
.btn-primary {
  padding: clamp(10px, 2.5vw, 14px) clamp(16px, 4vw, 24px);
  border-radius: var(--radius-sm);
  background: linear-gradient(180deg, #efe7d2, #e2d6b5);
  border: 1px solid rgba(158,138,87,0.45);
  color: #0b1220;
  font-weight: 600;
  font-size: clamp(14px, 3.5vw, 16px);
  box-shadow: var(--shadow-medium);
  transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
}

.btn-primary:active {
  transform: translateY(2px) scale(0.98);
  box-shadow: var(--shadow-light);
}
```

#### 次级按钮（Secondary）

```css
.btn-secondary {
  padding: clamp(8px, 2vw, 12px) clamp(14px, 3.5vw, 20px);
  border-radius: var(--radius-sm);
  background: rgba(255,255,255,0.15);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255,255,255,0.2);
  color: #333;
  font-weight: 600;
  box-shadow: var(--shadow-light);
}
```

#### ⚠️ 禁止项

- ❌ **禁止**使用 `<button>` 标签的浏览器默认样式
- ❌ **禁止**创建超过3种的按钮变体（Primary / Secondary / Text）
- ❌ **禁止**按钮文字少于2个字符（最少"确定"）

### 4.2 卡片 (Card)

#### 浅色卡片（白底）

```css
.card-light {
  background: linear-gradient(180deg, #f7f9fc, #f3f6fa);
  border: 1px solid var(--line-light);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-heavy);
  padding: clamp(14px, 3.5vw, 18px) clamp(16px, 4vw, 20px);
}
```

#### 深色卡片（暗底）

```css
.card-dark {
  background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.03));
  border: 1px solid var(--line-dark);
  border-radius: var(--radius-md);
  padding: clamp(14px, 3.5vw, 18px) clamp(16px, 4vw, 20px);
}
```

#### ⚠️ 禁止项

- ❌ **禁止**卡片嵌套超过2层
- ❌ **禁止**卡片内间距不一致（必须使用统一 padding）
- ❌ **禁止**卡片背景使用纯色（必须使用渐变）

### 4.3 输入框 (Input)

```css
.input-standard {
  width: 100%;
  padding: clamp(10px, 2.5vw, 14px) clamp(12px, 3vw, 16px);
  border-radius: var(--radius-sm);
  border: 1px solid var(--line-light);
  background: #fff;
  font-size: clamp(14px, 3.5vw, 16px);
  color: var(--text);
  transition: all 0.3s ease;
}

.input-standard:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(11,59,94,0.1);
}

/* 错误状态 */
.input-standard.error {
  border-color: var(--danger);
  box-shadow: 0 0 0 3px rgba(255,71,87,0.1);
}
```

#### ⚠️ 禁止项

- ❌ **禁止**使用浏览器默认样式
- ❌ **禁止**输入框高度小于44px（iOS点击区域标准）
- ❌ **禁止**placeholder文字颜色与背景对比度低于4.5:1

### 4.4 数字展示模块

```css
.metric-display {
  display: flex;
  flex-direction: column;
  gap: clamp(4px, 1vw, 6px);
}

.metric-label {
  font-size: clamp(11px, 2.75vw, 12px);
  color: var(--muted);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.metric-value {
  font-size: clamp(20px, 5vw, 28px);
  font-weight: 700;
  color: var(--text);
  font-variant-numeric: tabular-nums; /* 等宽数字 */
  line-height: 1.2;
}

/* 涨跌标识 */
.metric-value.up { color: var(--success); }
.metric-value.up::before { content: '▲ '; }

.metric-value.down { color: var(--danger); }
.metric-value.down::before { content: '▼ '; }
```

#### ⚠️ 禁止项

- ❌ **禁止**数字显示使用非等宽字体
- ❌ **禁止**数字小数点后超过2位（货币）或4位（百分比）
- ❌ **禁止**金额显示不使用千分位分隔符

### 4.5 收益指标模块

```css
.profit-card {
  background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
  border: 1px solid var(--line-light);
  border-radius: var(--radius-md);
  padding: clamp(12px, 3vw, 16px);
  box-shadow: var(--shadow-light);
}

.profit-header {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  margin-bottom: clamp(8px, 2vw, 12px);
}

.profit-title {
  font-size: clamp(13px, 3.25vw, 14px);
  font-weight: 600;
  color: var(--text);
}

.profit-amount {
  font-size: clamp(18px, 4.5vw, 22px);
  font-weight: 700;
  color: var(--success);
  font-variant-numeric: tabular-nums;
}

.profit-rate {
  font-size: clamp(12px, 3vw, 13px);
  color: var(--muted);
}
```

### 4.6 九宫格图标 (Grid Item)

```css
.grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  column-gap: clamp(12px, 4vw, 24px);
  row-gap: clamp(16px, 5vw, 22px);
  padding: clamp(8px, 3vw, 12px);
}

.grid-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  text-decoration: none;
  cursor: pointer;
  -webkit-tap-highlight-color: transparent;
}

.grid-item:active {
  transform: scale(0.95);
  opacity: 0.9;
}

.grid-item .icon {
  width: clamp(38px, 10vw, 48px);
  height: clamp(38px, 10vw, 48px);
  border-radius: 50%;
  background: linear-gradient(135deg, #F8F8F3 0%, #E8E6E1 50%, #DCD9D2 100%);
  border: 1.5px solid #d4af37;
  box-shadow:
    inset 0 2px 8px rgba(255,255,255,0.5),
    inset 0 -2px 6px rgba(0,0,0,0.15),
    0 2px 8px rgba(0,0,0,0.1);
}

.grid-item .icon img {
  width: 160%;
  height: 160%;
  object-fit: cover;
}

.grid-item .text {
  margin-top: clamp(7px, 2vw, 11px);
  font-size: clamp(12px, 3.5vw, 14px);
  font-weight: 600;
  color: #1a2332;
  text-shadow: 0 1px 2px rgba(255,255,255,0.9);
}
```

#### ⚠️ 禁止项

- ❌ **禁止**九宫格超过8个图标（最多2行）
- ❌ **禁止**图标使用非圆形样式
- ❌ **禁止**文字标签超过4个汉字

### 4.7 iOS 风格弹窗

```javascript
// 使用统一弹窗函数
showToast('标题', '消息内容');  // 仅提示
showConfirm('标题', '确认消息'); // 确认对话框
```

```css
.ios-toast {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: linear-gradient(to bottom, #ffffff, #f8f9fa);
  border-radius: 16px;
  width: 280px;
  max-width: 90%;
  box-shadow: 0 12px 40px rgba(0,0,0,0.25);
  z-index: 999999;
}

.ios-toast-title {
  font-size: 17px;
  font-weight: 600;
  color: #000;
  text-align: center;
  padding: 18px 20px 0;
}

.ios-toast-message {
  font-size: 16px;
  color: #1f2937;
  text-align: center;
  padding: 18px 20px 20px;
  white-space: pre-line;
}

.ios-toast-button {
  padding: 14px 16px;
  font-size: 16px;
  color: #007AFF;
  font-weight: 600;
  border-top: 1px solid #e5e7eb;
}
```

#### ⚠️ 禁止项

- ❌ **禁止**使用 `alert()`, `confirm()`, `prompt()` 浏览器原生弹窗
- ❌ **禁止**自定义弹窗样式（必须使用 ios-toast.js）
- ❌ **禁止**弹窗宽度超过屏幕90%

---

## 5. 禁止事项

### 5.1 禁止自定义 class

**原则：复用现有class，不创建野生样式**

```css
/* ❌ 禁止 */
.my-custom-button { ... }
.special-card-v2 { ... }
.temp-fix-style { ... }

/* ✅ 正确 */
.btn-primary { ... }
.card-light { ... }
.grid-item { ... }
```

### 5.2 禁止 Wild Override

```css
/* ❌ 禁止：全局覆盖 */
* { margin: 0 !important; }
button { all: unset !important; }

/* ❌ 禁止：多层 !important */
.vipcard { background: red !important !important; }

/* ✅ 正确：特定性选择器 */
.vipcard[data-vip-level="1"] { ... }
```

### 5.3 禁止不一致的样式

```css
/* ❌ 禁止：随意间距 */
padding: 13px 17px;
margin: 9px 11px;

/* ✅ 正确：使用 Design Tokens */
padding: var(--spacing-md);
margin: var(--spacing-sm);
```

### 5.4 禁止内联样式

```html
<!-- ❌ 禁止 -->
<div style="color: red; padding: 10px;"></div>

<!-- ⚠️ 例外：动态计算的值 -->
<div style="width: ${dynamicWidth}px;"></div>
```

### 5.5 禁止超出规范的字体

```css
/* ❌ 禁止 */
font-family: "Comic Sans MS", cursive;
font-family: "Microsoft YaHei";

/* ✅ 正确：使用统一字体栈 */
font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "PingFang SC", sans-serif;
```

---

## 6. 响应式规则

### 6.1 移动端优先

**设计思路：先移动，后桌面**

```css
/* 默认样式：移动端 */
.container {
  padding: 16px;
  font-size: 14px;
}

/* 桌面端增强 */
@media (min-width: 768px) {
  .container {
    padding: 24px;
    font-size: 16px;
  }
}
```

### 6.2 断点系统

```css
/* 标准断点 */
--breakpoint-xs: 360px;  /* 小屏手机 */
--breakpoint-sm: 480px;  /* 普通手机 */
--breakpoint-md: 768px;  /* 平板 */
--breakpoint-lg: 1024px; /* 小桌面 */
--breakpoint-xl: 1440px; /* 大桌面 */

/* 使用示例 */
@media (max-width: 480px) {
  .vip-card { height: 110px; }
}

@media (min-width: 481px) and (max-width: 768px) {
  .vip-card { height: 120px; }
}

@media (min-width: 769px) {
  .vip-card { height: 140px; }
}
```

### 6.3 使用 clamp() 实现流体排版

```css
/* 推荐：一行代码实现响应式 */
font-size: clamp(最小值, 理想值, 最大值);

/* 示例 */
font-size: clamp(14px, 3.5vw, 18px);
/* 14px (手机) → 按视口缩放 → 18px (桌面) */

padding: clamp(12px, 3vw, 20px);
/* 12px (手机) → 按视口缩放 → 20px (桌面) */
```

### 6.4 禁止固定宽高

```css
/* ❌ 禁止 */
width: 375px;
height: 667px;

/* ✅ 正确：百分比或视口单位 */
width: 100%;
max-width: 1200px;
height: 100vh;

/* ✅ 正确：响应式单位 */
width: clamp(320px, 90vw, 600px);
```

### 6.5 触摸友好

```css
/* 最小点击区域：44x44px (iOS标准) */
.btn, .grid-item, .tab {
  min-height: 44px;
  min-width: 44px;
}

/* 禁用点击高亮 */
.grid-item {
  -webkit-tap-highlight-color: transparent;
}

/* 优化触摸滚动 */
.scrollable {
  -webkit-overflow-scrolling: touch;
  overscroll-behavior: contain;
}
```

---

## 📐 附录：完整 CSS Reset

```css
/* Providence Global Reset */
*, *::before, *::after {
  box-sizing: border-box;
  -webkit-tap-highlight-color: transparent;
}

html, body {
  margin: 0;
  padding: 0;
  width: 100%;
  min-height: 100vh;
  overflow-x: hidden;
}

body {
  background: linear-gradient(180deg, #0e2b44, #091c2e);
  font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "PingFang SC", sans-serif;
  font-size: 14px;
  color: var(--text);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  line-height: 1.6;
}

button, input, textarea, select {
  font: inherit;
  color: inherit;
}

a {
  text-decoration: none;
  color: inherit;
}

img {
  max-width: 100%;
  height: auto;
  display: block;
}

/* 禁用浏览器默认样式 */
button {
  background: none;
  border: none;
  cursor: pointer;
}

input, textarea {
  background: #fff;
  border: 1px solid var(--line-light);
  border-radius: var(--radius-sm);
}
```

---

## 🎯 总结：三大原则

1. **统一性**：使用 Design Tokens，拒绝魔法数字
2. **约束性**：组件规范严格遵守，禁止野生 class
3. **响应性**：移动优先，使用 clamp() 流体布局

---

**文档维护者：** AI Frontend Architect
**审核者：** Providence Team
**下次更新：** 根据用户反馈持续迭代

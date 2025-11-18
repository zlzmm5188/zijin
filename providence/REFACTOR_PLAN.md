# 🎨 PROVIDENCE 全站 Design System 重构计划

## 📋 重构目标

**风格参考：** 支付宝黑金视觉系统
**核心原则：**
1. ✅ 统一 Design Tokens 到 `:root`
2. ✅ 核心数据区使用白色+香槟金边框
3. ✅ 动画仅用 `transform/opacity`
4. ✅ 60fps 移动端优化（iOS Safari）

## 🎯 统一 Design Tokens

```css
:root {
  /* 品牌色 - 深蓝 */
  --navy-deep: #0e2b44;
  --navy-mid: #1a3a5f;
  --navy-light: #2a4a6f;

  /* 品牌色 - 金色 */
  --gold-primary: #c99b2b;
  --gold-light: #d4af37;
  --gold-dark: #8b7044;
  --gold-champagne: #d4b896;

  /* 中性色 */
  --white: #ffffff;
  --black: #000000;
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-400: #9ca3af;
  --gray-500: #6b7280;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-800: #1f2937;
  --gray-900: #111827;

  /* 背景色 */
  --bg-page: #f5f6f8;
  --bg-card: #ffffff;
  --bg-overlay: rgba(0, 0, 0, 0.5);

  /* 边框 */
  --border-light: rgba(201, 155, 43, 0.15);
  --border-normal: rgba(201, 155, 43, 0.25);
  --border-strong: rgba(201, 155, 43, 0.4);

  /* 圆角 */
  --radius-xs: 4px;
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --radius-xl: 20px;
  --radius-2xl: 24px;
  --radius-full: 9999px;

  /* 阴影 */
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
  --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.15);
  --shadow-gold: 0 10px 30px rgba(201, 155, 43, 0.3);

  /* 模糊 */
  --blur-sm: blur(10px);
  --blur-md: blur(20px);
  --blur-lg: blur(40px);

  /* 渐变 */
  --gradient-navy: linear-gradient(135deg, var(--navy-deep) 0%, var(--navy-mid) 50%, var(--navy-deep) 100%);
  --gradient-gold: linear-gradient(135deg, var(--gold-primary) 0%, var(--gold-light) 100%);
  --gradient-black-gold: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);

  /* 字体 */
  --font-size-xs: 11px;
  --font-size-sm: 12px;
  --font-size-base: 14px;
  --font-size-lg: 16px;
  --font-size-xl: 18px;
  --font-size-2xl: 20px;
  --font-size-3xl: 24px;

  /* 行高 */
  --line-height-tight: 1.4;
  --line-height-normal: 1.6;
  --line-height-relaxed: 1.8;

  /* 间距 */
  --space-1: 4px;
  --space-2: 8px;
  --space-3: 12px;
  --space-4: 16px;
  --space-5: 20px;
  --space-6: 24px;
  --space-8: 32px;
  --space-10: 40px;
  --space-12: 48px;

  /* 动画时长 */
  --duration-fast: 0.15s;
  --duration-normal: 0.3s;
  --duration-slow: 0.5s;

  /* 动画曲线 */
  --ease-in: cubic-bezier(0.4, 0, 1, 1);
  --ease-out: cubic-bezier(0, 0, 0.2, 1);
  --ease-in-out: cubic-bezier(0.4, 0, 0.2, 1);
  --ease-smooth: cubic-bezier(0.25, 0.46, 0.45, 0.94);
}
```

## 📝 重构清单

### ✅ 已完成
- [x] education.html - 课堂页面（支付宝深蓝+白色风格）

### 🔄 进行中
- [ ] 1. project-managers.html → brokers.html（项目经理 → 经纪人）

### ⏳ 待重构
- [ ] 2. company-news.html（取消时间线特效）
- [ ] 3. trial-money.html（新手体验金）
- [ ] 4. news-flash.html（财经快讯）
- [ ] 5. lawyer-team.html（法律服务）
- [ ] 6. recharge.html（充值）
- [ ] 7. withdraw.html（提现）
- [ ] 8. points-exchange.html（积分兑换 → 人民币）
- [ ] 9. ribao.html（日报）
- [ ] 10. my-investments.html（我的投资）

## 🎨 视觉规范

### 核心数据卡片
```css
.value-card {
  background: var(--white);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
  padding: var(--space-4);
  box-shadow: var(--shadow-md);
}

.value-card-gold {
  border: 2px solid var(--gold-champagne);
  box-shadow: var(--shadow-gold);
}
```

### 动画规范
```css
/* ✅ 正确：仅使用 transform/opacity */
.card {
  transform: scale(0.95) translateZ(0);
  opacity: 0.8;
  transition: transform var(--duration-normal) var(--ease-smooth),
              opacity var(--duration-normal) var(--ease-smooth);
  will-change: transform, opacity;
}

/* ❌ 禁止：layout 属性 */
.bad {
  width: 100px; /* 禁止动画 */
  height: 100px; /* 禁止动画 */
  margin: 10px; /* 禁止动画 */
}
```

### 移动端优化
```css
/* 滚动性能优化 */
.scroll-container {
  -webkit-overflow-scrolling: touch;
  overscroll-behavior: contain;
}

/* 触摸响应优化 */
button, a {
  -webkit-tap-highlight-color: transparent;
  touch-action: manipulation;
}

/* GPU 加速 */
.animated {
  transform: translateZ(0);
  will-change: transform, opacity;
}
```

## 📦 提交规范

每完成一页重构，执行：
```bash
git add <filename>
git commit -m "refactor: <页面名称> - 统一 Design System"
```

## 🎯 成功标准

每页重构完成后必须检查：
- ✅ 所有颜色/圆角/阴影使用 CSS 变量
- ✅ 核心数据区使用白色+金边框
- ✅ 动画仅用 transform/opacity
- ✅ 移动端滚动 60fps
- ✅ 与首页底部导航一致
- ✅ 响应式适配完善

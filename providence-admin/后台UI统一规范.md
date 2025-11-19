# PROVIDENCE 后台UI统一规范

**版本：v1.0**  
**原则：后台使用前台同一套Design Token**  
**要求：所有视觉、配色、组件必须与前台一致**

---

## 🎨 Design Tokens（从前台提取）

### 颜色系统

```css
/* 主色调 */
--primary: #0b3b5e;           /* 深蓝 */
--primary-600: #0e4a78;       /* 深蓝加深 */
--accent: #c99b2b;            /* 金色（重要元素） */

/* 背景色 */
--bg: #f7f8fa;                /* 页面背景 */
--surface: #fff;              /* 卡片背景 */
--hero-bg: linear-gradient(180deg, #0e2b44, #091c2e);  /* 顶部导航 */

/* 文字色 */
--text: #222;                 /* 正文 */
--muted: #6b7a8a;             /* 次要文字 */

/* 状态色 */
--success: #25d0a6;           /* 成功/通过 */
--warning: #f59e0b;           /* 警告/待审 */
--danger: #ef4444;            /* 危险/拒绝 */
--info: #3b82f6;              /* 信息 */
```

### 圆角系统

```css
--radius-sm: 8px;             /* 小圆角（按钮、输入框） */
--radius-md: 12px;            /* 中圆角（卡片） */
--radius-lg: 16px;            /* 大圆角（模态框） */
--radius-xl: 24px;            /* 超大圆角 */
```

### 阴影系统

```css
--shadow-sm: 0 2px 6px rgba(0,0,0,.06);
--shadow-md: 0 4px 12px rgba(5,15,30,.08);
--shadow-lg: 0 6px 16px rgba(5,15,30,.1);
```

### 间距系统

```css
--spacing-xs: 4px;
--spacing-sm: 8px;
--spacing-md: 16px;
--spacing-lg: 24px;
--spacing-xl: 32px;
```

---

## 🧩 标准组件库

### 1. 按钮组件

**主要按钮（重要操作）**
```html
<button class="btn btn-primary">确认</button>
```
样式：深蓝渐变 + 阴影

**金色按钮（关键操作）**
```html
<button class="btn btn-accent">投资</button>
```
样式：金色渐变

**成功按钮**
```html
<button class="btn btn-success">通过审核</button>
```

**危险按钮**
```html
<button class="btn btn-danger">拒绝</button>
```

**次要按钮**
```html
<button class="btn btn-secondary">取消</button>
```

### 2. 徽章组件

**状态徽章**
```html
<span class="badge badge-success">正常</span>
<span class="badge badge-warning">待审核</span>
<span class="badge badge-danger">已拒绝</span>
<span class="badge badge-primary">进行中</span>
<span class="badge badge-accent">VIP1</span>
```

### 3. 卡片组件

```html
<div class="card">
    <div class="card-header">标题</div>
    <div class="card-body">内容</div>
</div>
```

### 4. 统计卡片

```html
<div class="stat-card">
    <div class="stat-number accent">1,234</div>
    <div class="stat-label">总用户数</div>
</div>
```

### 5. 表单组件

```html
<div class="form-group">
    <label class="form-label">用户名</label>
    <input type="text" class="form-input" placeholder="请输入">
</div>
```

### 6. 表格组件

```html
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>用户名</th>
            <th>操作</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>user123</td>
            <td><button class="btn btn-sm btn-primary">查看</button></td>
        </tr>
    </tbody>
</table>
```

---

## 🔄 页面重构清单

### 已重构
- [x] login.html - 登录页面（SRS设计系统）

### 待重构（20+页面）
- [ ] index.html - 主框架
- [ ] dashboard.html - 数据概览
- [ ] users.html - 用户列表
- [ ] team-tree.html - 推荐分布图
- [ ] vip.html - VIP等级
- [ ] projects.html - 项目管理
- [ ] recharge.html - 充值审核
- [ ] withdraw.html - 提现审核
- [ ] ... 等

---

## 📋 重构步骤

### 每个页面重构流程

1. **引入SRS设计系统CSS**
```html
<link rel="stylesheet" href="../public/css/srs-design-system.css">
```

2. **替换LayUI组件为SRS组件**

**原来（LayUI）：**
```html
<button class="layui-btn">按钮</button>
<span class="layui-badge layui-bg-green">正常</span>
```

**改为（SRS）：**
```html
<button class="btn btn-primary">按钮</button>
<span class="badge badge-success">正常</span>
```

3. **统一颜色使用**

**禁止：**
```css
background: #3498db;  /* 自定义颜色 */
```

**使用：**
```css
background: var(--primary);  /* SRS Token */
color: var(--accent);
```

4. **统一圆角和阴影**

```css
border-radius: var(--radius-md);
box-shadow: var(--shadow-md);
```

---

## 🎯 重构优先级

### Phase 1: 核心页面（本周）
1. login.html ✅
2. index.html（主框架）
3. dashboard.html（数据概览）
4. users.html（用户列表）

### Phase 2: 管理页面（下周）
5. projects.html
6. recharge.html
7. withdraw.html
8. 其他审核页面

### Phase 3: 细节优化
- 动画效果统一
- 响应式适配
- 交互反馈

---

## ⚠️ 禁止事项

1. ❌ 禁止使用LayUI的默认样式
2. ❌ 禁止自定义颜色（必须用Design Token）
3. ❌ 禁止独立的UI风格
4. ❌ 禁止不同的圆角/阴影
5. ❌ 禁止与前台不一致的视觉元素

---

## ✅ 必须执行

1. ✅ 所有页面引入 srs-design-system.css
2. ✅ 所有按钮使用 .btn 类
3. ✅ 所有徽章使用 .badge 类
4. ✅ 所有卡片使用 .card 类
5. ✅ 所有表单使用 .form-* 类
6. ✅ 所有颜色使用 CSS变量

---

**© 2024 PROVIDENCE | UI统一规范**

**后台UI必须与前台视觉完全一致！**

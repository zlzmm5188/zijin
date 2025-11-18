# Cursor 代码库索引配置指南

> **代码库根目录：** `/www/wwwroot/providence/`
> **更新时间：** 2025-11-10

---

## 🎯 在 Cursor 中设置代码库索引

### Step 1: 打开索引设置

1. 点击 Cursor 界面右下角的**齿轮图标⚙️**（或按 `Cmd/Ctrl + ,`）
2. 在搜索框输入：`codebase`
3. 找到 **"Codebase Indexing"** 设置

### Step 2: 启用代码库索引

1. 勾选 ✅ **"Enable Codebase Indexing"**
2. 勾选 ✅ **"Index on startup"**（启动时自动索引）

### Step 3: 设置索引范围

在 **"Indexed Paths"** 中添加：

```
/www/wwwroot/providence
```

或者在 Cursor 中打开文件夹：
- File → Open Folder → 选择 `/www/wwwroot/providence`

### Step 4: 配置忽略文件

Cursor 会自动读取项目根目录的 `.cursorignore` 文件。

我已经创建了 `.cursorignore`，包含：
- ✅ 排除 `node_modules/`
- ✅ 排除 `backups/`
- ✅ 排除图片资源 `img/`
- ✅ 排除字体文件 `*.woff, *.ttf`
- ✅ 排除备份文件 `*.backup, *.bak`

### Step 5: 手动触发索引

如果自动索引没有启动：

1. 按 `Cmd/Ctrl + Shift + P` 打开命令面板
2. 输入：`Codebase: Index Codebase`
3. 按回车执行

或者点击右下角的提示：
```
"Codebase not indexed"
→ 点击 "Start indexing"
```

---

## 📊 索引配置优化

### 建议的索引配置

```json
{
  "codebase.indexing.enabled": true,
  "codebase.indexing.onStartup": true,
  "codebase.indexing.exclude": [
    "**/node_modules/**",
    "**/backups/**",
    "**/img/**/*.{jpg,png,gif,svg}",
    "**/*.backup",
    "**/*.bak",
    "**/*.min.js",
    "**/*.min.css"
  ],
  "codebase.indexing.include": [
    "**/*.js",
    "**/*.html",
    "**/*.css",
    "**/*.md",
    "**/*.json",
    "**/*.php"
  ]
}
```

### 如何设置

1. 打开 Cursor 设置（`Cmd/Ctrl + ,`）
2. 点击右上角的 **"Open Settings (JSON)"** 图标
3. 添加上述配置
4. 保存文件
5. 重启 Cursor

---

## 🚀 索引后的好处

### 启用代码库索引后，Cursor 可以：

✅ **智能代码补全**
- 自动识别项目中的函数、变量、类名
- 提示可用的 API 函数（如 `window.API.user.login()`）

✅ **快速跳转**
- `Cmd/Ctrl + 点击` 函数名 → 跳转到定义
- 查找所有引用位置

✅ **上下文理解**
- AI 能理解整个项目结构
- 代码建议更准确

✅ **符号搜索**
- `Cmd/Ctrl + P` 然后输入 `@` → 搜索符号
- 快速找到函数、类、变量

---

## 📋 Providence 项目推荐索引设置

### 高优先级索引（必须）

```
✅ *.js        - 所有JavaScript文件（40个核心文件）
✅ *.html      - 所有HTML页面（85个）
✅ *.css       - 样式文件（styles.css等）
✅ *.md        - 文档文件（设计规范等）
✅ config.js   - API配置（最重要）
```

### 低优先级（可选）

```
⚪ *.json     - 数据文件
⚪ *.php      - PHP脚本
⚪ *.sh       - Shell脚本
```

### 排除索引（节省时间）

```
❌ img/       - 图片资源（大文件）
❌ backups/   - 备份文件
❌ *.backup   - 备份文件
❌ *.min.*    - 压缩文件
```

---

## ⚡ 快速开始

### 方式1: 使用命令面板（推荐）

```
1. 按 Cmd/Ctrl + Shift + P
2. 输入：index codebase
3. 选择：Codebase: Index Codebase
4. 等待索引完成（约1-2分钟）
```

### 方式2: 点击提示

```
看到 "Codebase not indexed" 提示时
→ 点击 "Start indexing"
→ 等待完成
```

### 方式3: 重启 Cursor

```
关闭 Cursor → 重新打开
→ 自动触发索引
```

---

## 🎯 索引进度查看

### 索引中

```
右下角显示：
"Indexing codebase... 45%"
或
"Indexing: 120/200 files"
```

### 索引完成

```
右下角显示：
"✓ Codebase indexed"
或
提示消失
```

---

## 🔍 验证索引是否成功

### 测试1: 符号跳转

1. 打开 `login.html`
2. 找到 `handleLogin()`
3. 按住 `Cmd/Ctrl` 并点击函数名
4. 应该跳转到 `login.js` 中的函数定义 ✅

### 测试2: 符号搜索

1. 按 `Cmd/Ctrl + P`
2. 输入 `@handleLogin`
3. 应该显示函数位置 ✅

### 测试3: AI 上下文理解

1. 在对话中问："login.js 中的 handleLogin 函数做什么？"
2. AI 应该能准确回答（因为已索引） ✅

---

## ⚠️ 常见问题

### Q1: 索引很慢怎么办？

**A:** 检查 `.cursorignore` 是否正确排除了大文件：
```bash
cd /www/wwwroot/providence
cat .cursorignore
```

### Q2: 索引后还是提示"not indexed"？

**A:** 尝试：
1. 重启 Cursor
2. 删除 `.cursor` 目录（Cursor会重建）
3. 重新索引

### Q3: 索引占用太多空间？

**A:** 清理索引缓存：
```
Cmd/Ctrl + Shift + P
→ 输入：clear index
→ 选择：Codebase: Clear Index
→ 重新索引
```

---

## 📝 索引完成检查清单

- [ ] Cursor 已打开 `/www/wwwroot/providence` 文件夹
- [ ] 右下角没有"Codebase not indexed"提示
- [ ] 能够 Cmd+点击 跳转到函数定义
- [ ] AI 能理解项目上下文（如识别 window.API）

---

## 🎯 立即操作步骤

### 最快的方法：

1. **在 Cursor 中按 `Cmd/Ctrl + Shift + P`**
2. **输入：`index`**
3. **选择：`Codebase: Index Codebase`**
4. **等待 1-2 分钟**
5. **看到 "✓ Codebase indexed" 即完成**

---

**现在请按照上述步骤操作，索引完成后我们继续解决登录问题！** 🚀

补充：索引完成后，我对代码的理解会更准确，可以更好地帮您优化前端！

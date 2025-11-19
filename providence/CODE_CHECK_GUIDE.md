# Providence 代码检查工具使用指南

## 🛠️ 工具列表

### 1. `code-check-all.sh` - 全面代码检查
**功能**：检查所有代码文件的规范性问题

**检查项目**：
- ✓ HTML: DOCTYPE、标签配对、CSS语法
- ✓ CSS: 语法错误、样式统一性
- ✓ JavaScript: 括号配对、alert使用、API错误处理
- ✓ JSON: 语法验证
- ✓ PHP: 语法检查、安全问题

**使用方法**：
```bash
# 检查所有文件
./code-check-all.sh

# 检查特定类型文件
./code-check-all.sh "*.js"
./code-check-all.sh "policy-detail-*.html"
```

### 2. `code-auto-fix.sh` - 自动修复工具
**功能**：自动修复常见的代码规范问题

**自动修复项**：
- ✓ 统一返回按钮class名（back-btn → back-btn-policy）
- ✓ 添加缺失的底部分隔线
- ✓ 替换alert为showToast
- ✓ 更新缓存版本号
- ✓ 清理临时文件

**使用方法**：
```bash
# 运行自动修复
./code-auto-fix.sh

# 备份会自动创建在: backups-YYYYMMDD_HHMMSS/
```

### 3. `STANDARD_MODIFICATION_PROCESS.sh` - 标准流程
**功能**：标准化修改流程检查

**使用方法**：
```bash
./STANDARD_MODIFICATION_PROCESS.sh
```

---

## 📋 标准工作流程

### 步骤1：修改代码
按照需求修改HTML/CSS/JS等文件

### 步骤2：运行检查
```bash
./code-check-all.sh
```

### 步骤3：如果有错误
```bash
./code-auto-fix.sh
```

### 步骤4：验证修复结果
```bash
./code-check-all.sh
```

### 步骤5：测试
- 强制刷新浏览器：`Ctrl+Shift+R`
- 测试所有修改的页面
- 验证功能是否正常

---

## 🎯 Providence项目规范

### HTML规范
- ✅ 必须有`<!DOCTYPE html>`
- ✅ 必须有`charset="utf-8"`
- ✅ 所有标签必须配对
- ✅ 详情页必须有底部分隔线
- ✅ 返回按钮必须用`class="back-btn-policy"`

### CSS规范
- ✅ 分隔线颜色统一：`#0e2b44`
- ✅ 返回按钮样式统一：`padding: 14px 28px; font-size: 15px`
- ✅ 模态框高度：`max-height: 70vh`
- ✅ 不能有孤立的CSS属性（必须有选择器）

### JavaScript规范
- ✅ 禁止使用`alert()`, `confirm()`, `prompt()`
- ✅ 必须使用`showToast()`显示提示
- ✅ API调用必须有错误处理（`.catch()`）
- ✅ 所有括号、花括号必须配对

### PHP规范
- ✅ 必须以`<?php`开头
- ✅ 使用`$_GET`/`$_POST`必须做输入过滤
- ✅ 生产环境不能有`var_dump()`、`print_r()`
- ✅ SQL查询必须使用参数化或转义

---

## 🚨 常见错误及解决

### 错误1：CSS语法错误（孤立属性）
**问题**：CSS属性没有选择器
```css
/* 错误 */
    display: block;
    padding: 10px;

/* 正确 */
.my-class {
    display: block;
    padding: 10px;
}
```
**修复**：删除孤立属性或添加选择器

### 错误2：返回按钮样式不统一
**问题**：使用了不同的class名或padding
**修复**：运行`./code-auto-fix.sh`自动统一

### 错误3：修改后看不到效果
**问题**：CloudFlare缓存
**修复**：
1. 强制刷新：`Ctrl+Shift+R`
2. 清除CF缓存
3. 使用不同子域名访问

---

## 📊 检查工具输出说明

### 符号含义
- ✓ 检查通过
- ⚠ 警告（建议修复但不影响功能）
- ✗ 错误（必须立即修复）

### 颜色含义
- 🟢 绿色：检查通过
- 🟡 黄色：警告
- 🔴 红色：错误

### 退出码
- `0`：所有检查通过
- `1`：发现错误或警告

---

## 💡 最佳实践

1. **每次修改前**：备份文件
2. **修改过程中**：删除旧代码，避免重复
3. **修改完成后**：立即运行`./code-check-all.sh`
4. **提交前**：确保所有检查通过
5. **定期运行**：每天运行一次全面检查

---

## 🔧 自定义检查规则

如需添加自定义检查规则，编辑`code-check-all.sh`：

```bash
# 示例：检查是否使用了硬编码的API地址
if grep -q "https://hardcoded-api" "$file"; then
    echo "⚠ $file: 使用了硬编码API地址"
    ((WARNINGS++))
fi
```

---

## 📞 问题反馈

如果检查工具发现误报或遗漏，请记录并更新工具。

工具版本：v1.0 (2025-11-08)

## 🐛 JavaScript运行时错误检查

### 常见错误类型

1. **SyntaxError（语法错误）**
   - 括号不匹配：`Unexpected token ')'`
   - 模板字符串错误：缺少反引号或变量
   - 对象/数组语法错误
   
   **检查方法**：
   ```bash
   node --check <(sed -n '/<script>/,/<\/script>/p' file.html)
   ```

2. **ReferenceError（引用错误）**
   - 函数未定义：`showManagerDetail is not defined`
   - 变量未声明
   
   **检查方法**：
   - 确保函数在使用前定义
   - 检查变量作用域
   - 使用浏览器F12 Console查看

3. **TypeError（类型错误）**
   - null/undefined调用：`Cannot read property of undefined`
   - 函数调用错误
   
   **检查方法**：
   - 添加空值判断：`if (obj && obj.method)`
   - 使用可选链：`obj?.method?.()`

### 检查流程

**每次修改JavaScript后**：
1. 运行 `./code-check-all.sh`
2. 打开浏览器F12查看Console
3. 测试所有交互功能
4. 检查Network面板API调用

### 工具支持

我们的检查工具现在包括：
- ✓ 花括号配对检查
- ✓ alert/confirm检测
- ✓ API错误处理检查
- ✓ Node.js语法验证（如果安装）


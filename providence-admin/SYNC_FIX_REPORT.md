# 新站点同步修复报告

**修复时间**: 2025-11-18
**新站点**:
1. `apis.frevix.top` → `/www/wwwroot/copla/providence-admin/api`
2. `houtaiadmin.frevix.top` → `/www/wwwroot/copla/providence-admin`
3. `xin.frevix.top` → `/www/wwwroot/copla/providence`

---

## ✅ 已同步修复的文件

### 1. 数据清理功能
```
/www/wwwroot/providence-admin/api/admin/clear-all-data.php
→ /www/wwwroot/copla/providence-admin/api/admin/clear-all-data.php
```
- ✅ 修复了 `is_admin` → `is_internal` 字段问题
- ✅ 支持 `type: 'business'` 和 `type: 'all'` 两种清理模式

### 2. 项目发布功能
```
/www/wwwroot/providence-admin/api/admin/project-save.php
→ /www/wwwroot/copla/providence-admin/api/admin/project-save.php
```
- ✅ 修复了管理员权限验证（`is_admin` → `is_internal`）
- ✅ 支持 JSON 和 FormData 两种请求格式
- ✅ 添加了 Token 验证

### 3. 项目编辑页面
```
/www/wwwroot/providence-admin/admin/project-edit.html
→ /www/wwwroot/copla/providence-admin/admin/project-edit.html
```
- ✅ 添加了 Token 传递
- ✅ 修复了表单提交逻辑

### 4. 清理数据页面
```
/www/wwwroot/providence-admin/admin/clear-cache.html
→ /www/wwwroot/copla/providence-admin/admin/clear-cache.html
```
- ✅ 添加了"清理业务数据"按钮
- ✅ 集成了 `clear-all-data.php` API

---

## 📋 功能说明

### 清理业务数据 (type: 'business')

访问: `https://houtaiadmin.frevix.top/admin/clear-cache.html`

**会清理**:
- ✅ 普通用户数据（保留 is_internal=1 的管理员）
- ✅ 投资订单
- ✅ 充值记录
- ✅ 提现记录
- ✅ 钱包数据
- ✅ 日利宝账户
- ✅ 登录日志
- ✅ 钱包流水
- ✅ 积分兑换记录

**会保留**:
- ✅ 管理员账户 (G138688, admin888)
- ✅ 项目配置（只重置投资统计）
- ✅ 公司动态、新闻、课程

---

## 🔍 数据库结构差异

旧站点 vs 新站点：

| 字段 | 旧站点 | 新站点 |
|------|--------|--------|
| 管理员标识 | `is_admin` ❌ | `is_internal` ✅ |
| 用户表 | `users` | `users` |
| 项目表 | `invest_projects` | `invest_projects` |

新站点数据库使用 `is_internal` 字段来标识管理员，所有API已修复。

---

## 🧪 测试验证

### 1. 发布项目测试
访问: `https://houtaiadmin.frevix.top/admin/project-edit.html`
- 填写项目信息
- 点击"保存项目"
- 应该可以成功发布

### 2. 清理数据测试
访问: `https://houtaiadmin.frevix.top/admin/clear-cache.html`
- 点击红色按钮："⚠️ 清理业务数据"
- 确认两次提示
- 查看清理结果

### 3. 检查用户列表
访问: `https://houtaiadmin.frevix.top/admin/users.html`
- 查看用户列表
- 确认只保留管理员

---

## ✅ 修复完成

所有文件已同步到新站点，功能正常：

1. ✅ **项目发布** - 可以正常发布项目
2. ✅ **数据清理** - 可以清理业务数据（保留管理员和内容）
3. ✅ **权限验证** - 使用 `is_internal` 字段验证管理员
4. ✅ **文件权限** - 已设置 www:www 权限

---

## 📌 使用说明

### 清理测试数据步骤：

1. **登录后台**
   ```
   https://houtaiadmin.frevix.top/admin/login.html
   ```

2. **访问清理页面**
   ```
   https://houtaiadmin.frevix.top/admin/clear-cache.html
   ```

3. **点击红色按钮**
   - "⚠️ 清理业务数据（用户、充值、订单等）"

4. **确认清理**
   - 第一次确认：了解清理范围
   - 第二次确认：最终确认执行

5. **查看结果**
   - 返回用户列表页面
   - 确认只保留 G138688 和 admin888

---

**修复完成时间**: 2025-11-18 09:32
**修复范围**: 新站点（copla目录）所有管理员功能

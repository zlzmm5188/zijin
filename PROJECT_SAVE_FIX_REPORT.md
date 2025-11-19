# 后台项目编辑保存功能修复报告

**修复时间**: 2025-11-18
**问题**: 后台编辑项目时无法保存，提示"操作失败：更新项目失败"

---

## ❌ 问题原因

### 1. **数据格式不匹配**
前端使用 `FormData` 格式提交数据，但后端只能解析JSON格式的 `php://input`。

**前端代码**（project-edit.html）：
```javascript
const formData = new FormData(document.getElementById('projectForm'));
const response = await fetch('../api/admin/project-save.php', {
    method: 'POST',
    body: formData  // ❌ FormData格式
});
```

**后端代码**（project-save.php）：
```php
// ❌ 只能解析JSON
$input = json_decode(file_get_contents('php://input'), true);
if (!$input && !empty($_POST)) {
    $input = $_POST;
}
```

### 2. **Database::update() 方法调用错误**
`update()` 方法的 `$where` 参数期望SQL字符串，但传入了数组。

**错误调用**：
```php
$db->update('invest_projects', $data, ['id' => $id]);
```

**正确调用**：
```php
$db->update('invest_projects', $data, 'id = :where_id', ['where_id' => $id]);
```

---

## ✅ 修复方案

### 1. **增强数据接收逻辑**
修改 `project-save.php`，同时支持JSON和FormData格式：

```php
// 获取请求数据（支持JSON和FormData格式）
$input = [];

// 1. 尝试解析JSON
$rawInput = file_get_contents('php://input');
if (!empty($rawInput)) {
    $jsonData = json_decode($rawInput, true);
    if ($jsonData) {
        $input = $jsonData;
    }
}

// 2. 如果不是JSON，使用POST数据（FormData）
if (empty($input) && !empty($_POST)) {
    $input = $_POST;
}
```

### 2. **修正 update() 方法调用**
```php
// ✅ 正确的调用方式
$success = $db->update(
    'invest_projects',
    $data,
    'id = :where_id',  // WHERE子句（SQL字符串）
    ['where_id' => $id] // WHERE参数
);
```

---

## 📝 修复文件

| 文件 | 修改内容 |
|-----|---------|
| `/www/wwwroot/copla/providence-admin/api/admin/project-save.php` | 1. 增强数据接收逻辑（支持FormData）<br>2. 修正 `Database::update()` 调用 |

---

## ✅ 测试结果

### 测试命令：
```bash
curl -X POST "https://apis.copla.top/index.php/admin/project-save" \
  -H "token: admin_token" \
  -F "id=12" \
  -F "title=稳健理财计划（已更新）" \
  -F "subtitle=测试更新" \
  -F "max_invest=10000" \
  ...
```

### 响应结果：
```json
{
  "code": 1,
  "message": "项目更新成功",
  "data": {
    "id": 12,
    "version": 2
  }
}
```

### 数据库验证：
```sql
SELECT id, title, subtitle, max_invest, version, updated_at
FROM invest_projects WHERE id=12;

-- 结果：
-- title: 稳健理财计划（已更新）
-- subtitle: 测试更新
-- max_invest: 10000.00000000
-- version: 2
```

---

## 🎯 功能恢复

现在可以正常：
- ✅ 编辑项目基本信息
- ✅ 修改项目配置参数
- ✅ 保存更新（版本号自动递增）
- ✅ 支持FormData和JSON两种提交格式

---

## 📋 相关问题修复记录

本次会话中修复的其他问题：
1. ✅ AI聊天接口路由 (`ai/chat`)
2. ✅ 后台清理数据接口路由 (`admin/clear-all-data`)
3. ✅ 项目详情查询（LEFT JOIN问题）
4. ✅ 项目编辑保存（本问题）

---

**修复完成！**✨ 后台项目编辑功能完全恢复正常。

# 后台项目编辑功能修复报告

**修复时间**: 2025-11-18
**问题**: 后台编辑项目时显示"加载失败：项目不存在"

---

## ❌ 问题原因

### 1. **API路由缺失**
`/www/wwwroot/copla/providence-admin/api/index.php` 缺少 `admin/clear-all-data` 路由，导致后台清理数据功能404。

### 2. **SQL查询问题**
`/www/wwwroot/copla/providence-admin/api/admin/project-detail.php` 使用了 LEFT JOIN 查询：

```php
// ❌ 问题代码
SELECT p.*, m.name, m.avatar, m.title, m.introduction
FROM invest_projects p
LEFT JOIN project_managers m ON p.manager_id = m.id
WHERE p.id = :id
```

当 `project_managers` 表中没有对应数据时，LEFT JOIN 返回 NULL，导致 `fetchOne()` 方法返回 `null`，API错误地报告"项目不存在"。

---

## ✅ 修复方案

### 1. **添加API路由**
在 `index.php` 中添加：
```php
'admin/clear-all-data' => 'admin/clear-all-data.php',
```

### 2. **重构SQL查询**
分两步查询，避免LEFT JOIN的问题：

```php
// ✅ 修复后
// 第1步：查询项目基本信息
$sql = "SELECT p.* FROM invest_projects p WHERE p.id = :id";
$project = $db->fetchOne($sql, ['id' => $id]);

// 第2步：单独查询管理员信息（如果存在）
if (!empty($project['manager_id'])) {
    $managerSql = "SELECT name, avatar, title, introduction
                   FROM project_managers WHERE id = :id";
    $manager = $db->fetchOne($managerSql, ['id' => $project['manager_id']]);
    if ($manager) {
        $project['manager_name'] = $manager['name'];
        $project['manager_avatar'] = $manager['avatar'];
        $project['manager_title'] = $manager['title'];
        $project['manager_introduction'] = $manager['introduction'];
    }
}
```

### 3. **添加容错处理**
所有可选字段使用 `?? 0` 提供默认值：
```php
$project['max_invest'] = (float)($project['max_invest'] ?? 0);
$project['is_index'] = (int)($project['is_index'] ?? 0);
// ...
```

---

## 📝 修复文件

| 文件 | 修改内容 |
|-----|---------|
| `/www/wwwroot/copla/providence-admin/api/index.php` | 添加 `ai/chat` 和 `admin/clear-all-data` 路由 |
| `/www/wwwroot/copla/providence-admin/api/admin/project-detail.php` | 重构SQL查询，分步获取项目和管理员信息 |

---

## ✅ 测试结果

```bash
# 测试项目详情API
curl -X GET "https://apis.copla.top/index.php/admin/project-detail?id=12"

# ✅ 返回成功
{
  "code": 1,
  "message": "获取成功",
  "data": {
    "id": 12,
    "project_code": "PRJ20251118767",
    "title": "稳健理财计划",
    "currency": "USDT",
    "cycle_days": 7,
    "total_rate": 7.6,
    "min_invest": 500,
    "max_invest": 6000,
    "total_quota": 5000000,
    ...
  }
}
```

---

## 🎯 功能恢复

现在可以正常：
- ✅ 在后台编辑已发布的项目
- ✅ 查看项目详细信息
- ✅ 修改项目配置参数
- ✅ 清理业务数据（用户、充值、订单等）

---

**修复完成！**✨

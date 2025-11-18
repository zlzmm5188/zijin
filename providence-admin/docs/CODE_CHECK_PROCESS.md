# Providence 后端代码修改检查流程

**版本：v1.0**  
**强制执行：每次修改后端代码必须遵循**

---

## 📋 检查流程（3步骤）

### 1️⃣ 数据库 Schema 一致性检查

**必须检查：**
- [ ] 所有新增字段有完整注释
- [ ] 所有字段有默认值
- [ ] 数据类型正确
- [ ] 创建了迁移文件
- [ ] **资金相关字段必须 `DECIMAL(20,8)`**
- [ ] 索引已添加
- [ ] 外键约束正确

**示例：**
```sql
ALTER TABLE users 
ADD COLUMN balance_usdt DECIMAL(20,8) DEFAULT 0 COMMENT 'USDT余额';
```

### 2️⃣ API接口规范检查

**必须检查：**
- [ ] Response格式遵守 `BACKEND_API_CHECKLIST.md`
- [ ] 响应code只能是 `1` 或 `-1`（不允许其他magic number）
- [ ] 前端禁止计算的字段必须后台计算：
  - `total_rate` - 后台计算
  - `sold` - 后台计算
  - `remain` - 后台计算
  - `profit` - 后台计算
- [ ] CORS头完整
- [ ] 错误信息清晰

**错误示例：**
```php
// ❌ 错误
return ['code' => 200, 'msg' => 'ok'];  // 不规范的code

// ✅ 正确
Response::success($data, '操作成功');  // code = 1
Response::error('操作失败');  // code = -1
```

### 3️⃣ 业务逻辑安全检查

**必须检查：**
- [ ] **币种隔离**：USDT ↔ CNY 禁止跨交易
- [ ] **Token验证**：所有API验证token
- [ ] **Sign验证**：资金类API验证sign
- [ ] **权限检查**：用户只能操作自己的资产
- [ ] **审计日志**：关键操作写入audit_log
- [ ] **幂等性**：资金操作支持Idempotency-Key
- [ ] **事务处理**：资金操作在事务中

**示例检查：**
```php
// ✅ 币种隔离检查
CurrencyMiddleware::validate($userCurrency, $projectCurrency);

// ✅ 权限检查
if ($order->user_id !== $authUser['user_id']) {
    Response::error('无权操作');
}

// ✅ 审计日志
AuditLog::log('finance', '充值审核', 'ADMIN', $adminId, ...);
```

---

## 📝 检查输出格式

### 输出1：检查报告

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
代码检查报告
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. 数据库检查
   ✅ 字段注释完整
   ✅ 默认值已设置
   ❌ 资金字段应为DECIMAL(20,8)
   
2. API接口检查
   ✅ Response格式正确
   ✅ Code值规范
   ❌ 缺少total_rate后端计算
   
3. 安全检查
   ✅ 币种隔离已实施
   ✅ Token验证正常
   ❌ 缺少审计日志
```

### 输出2：修正补丁

```sql
-- 数据库修正
ALTER TABLE wallets 
MODIFY COLUMN balance DECIMAL(20,8) DEFAULT 0;

-- 代码修正
// 添加total_rate计算
$totalRate = $baseRate + $vipRate + $added + $gift;

// 添加审计日志
AuditLog::log(...);
```

### 输出3：最终代码

```php
// 完整的修正后代码
...
```

---

**© 2024 PROVIDENCE | 代码检查流程**

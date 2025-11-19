# Profile页面修复报告

## 修复时间
2025-01-18

## 问题描述
`https://copla.top/profile.html` 页面显示"加载中..."，用户数据无法正常加载。

## 问题原因

### 1. API返回字段与前端期望不匹配
- API返回：`phone`, `cny_balance`, `usdt_balance`, `vip_level`, `total_profit`
- 前端期望：`mobile`, `money`, `ribao`, `usdt_money`, `usdt_ribao`, `level`, `profit`, `profit_cny`, `profit_usdt`

### 2. 缺少日利宝余额数据
- API没有查询和返回日利宝余额（`ribao` 和 `usdt_ribao`）

### 3. 收益数据不完整
- API只返回总收益，没有按币种（CNY/USDT）分类

### 4. CORS配置问题
- `copla.top` 域名未添加到允许列表

### 5. Token验证逻辑冗余
- 同时检查 `$_SERVER['HTTP_TOKEN']` 和 `Auth::user()`，导致验证失败

## 修复内容

### 1. 修复API字段映射 ✅
**文件**: `/www/wwwroot/providence-admin/api/user/info.php`

- 添加日利宝余额查询（从 `ribao_accounts` 表）
- 添加按币种分类的收益查询（从 `invest_orders` 表）
- 添加前端兼容字段映射：
  ```php
  $user['mobile'] = $user['phone'] ?? '';
  $user['money'] = $user['cny_balance'] ?? 0;
  $user['usdt_money'] = $user['usdt_balance'] ?? 0;
  $user['level'] = $user['vip_level'] ?? 1;
  $user['profit'] = $user['profit_cny'] ?? 0;
  $user['tfund'] = $user['profit_cny'] ?? 0;
  $user['ribao'] = $ribaoAccount['cny_balance'] ?? 0;
  $user['usdt_ribao'] = $ribaoAccount['usdt_balance'] ?? 0;
  ```

### 2. 添加CORS支持 ✅
**文件**: `/www/wwwroot/providence-admin/config/bootstrap.php`

- 添加 `https://copla.top` 到允许的域名列表

### 3. 简化Token验证 ✅
**文件**: `/www/wwwroot/providence-admin/api/user/info.php`

- 移除冗余的 `$_SERVER['HTTP_TOKEN']` 检查
- 统一使用 `Auth::user()` 进行验证

## API返回数据格式

修复后的API返回格式：

```json
{
  "code": 1,
  "message": "success",
  "data": {
    "id": 1,
    "uid": 12345678,
    "username": "user123",
    "phone": "13800138000",
    "mobile": "13800138000",  // 兼容字段
    "vip_level": 1,
    "level": 1,  // 兼容字段
    "cny_balance": 1000.00,
    "money": 1000.00,  // 兼容字段
    "usdt_balance": 500.00,
    "usdt_money": 500.00,  // 兼容字段
    "ribao": 200.00,  // 日利宝余额（CNY）
    "usdt_ribao": 100.00,  // 日利宝余额（USDT）
    "profit_cny": 50.00,  // 人民币收益
    "profit_usdt": 25.00,  // USDT收益
    "profit": 50.00,  // 兼容字段
    "tfund": 50.00,  // 兼容字段
    "team_count": 5,
    "invite_code": "12345678",
    "invite": "12345678"  // 兼容字段
  }
}
```

## 测试建议

1. **清除浏览器缓存**，强制刷新页面（Ctrl+Shift+R）
2. **检查控制台**，确认：
   - `[用户数据] ✓ 加载成功`
   - 没有CORS错误
   - Token正确发送
3. **验证数据显示**：
   - 用户名和ID正确显示
   - 余额和收益数据正确
   - 日利宝余额正确显示

## 修复文件清单

1. `/www/wwwroot/providence-admin/api/user/info.php` - API数据映射修复
2. `/www/wwwroot/providence-admin/config/bootstrap.php` - CORS配置更新

## 状态
✅ 修复完成

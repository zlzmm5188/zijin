# Profile页面 USDT卡片修复报告

## 修复时间
2025-11-18

## 问题描述
用户反馈：USDT卡片点击后，无法看到 USDT收益。原因是默认状态设置为"空白"，用户需要点击2次才能看到收益。

## 修复内容

### 1. 修改默认显示状态
**文件**: `/www/wwwroot/copla/providence/profile.html`

**修改前**:
- 默认状态: `empty`（空白）
- 切换逻辑: 空白 → 余额 → 收益 → 空白

**修改后**:
- 默认状态: `balance`（余额）
- 切换逻辑: 余额 → 收益 → 空白 → 余额

### 2. 修改HTML默认显示
**状态1（USDT余额）**:
```html
<div data-usdt-state="balance" style="opacity: 1; pointer-events: auto;">
```

**状态3（空白）**:
```html
<div data-usdt-state="empty" style="opacity: 0; pointer-events: none;">
```

### 3. 修改JavaScript逻辑
```javascript
let currentState = 'balance'; // 默认显示余额

// 点击切换顺序
if (currentState === 'balance') {
    currentState = 'income';
} else if (currentState === 'income') {
    currentState = 'empty';
} else if (currentState === 'empty') {
    currentState = 'balance';
}
```

## 测试验证
1. 访问 https://copla.top/profile.html
2. 页面加载后，USDT卡片默认显示"USDT(余额)"
3. 点击1次：切换到"USDT(收益)" ✅
4. 点击2次：切换到空白
5. 点击3次：切换回"USDT(余额)"

## 数据字段
- USDT余额: `userData.usdt_money + userData.usdt_ribao`
- USDT收益: `userData.profit_usdt`

## 相关文件
- `/www/wwwroot/copla/providence/profile.html` (HTML + JS)
- `/www/wwwroot/copla/providence/profile.js` (数据逻辑)

## 状态说明
✅ 已修复并测试通过

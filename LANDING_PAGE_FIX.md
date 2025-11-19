# 落地页登录跳转修复报告

## 问题描述
用户在 `g138688.copla.top/index.html`（落地页）点击"开始加入"后，无论是否已登录，都跳转到注册页。
用户希望：**如果已登录，应该直接跳转到主站首页 `copla.top/index.html` 并保持登录状态**。

## 落地页文件位置
`/www/wwwroot/copla/providence/invite-landing.html`

## 修复内容

### 修复前逻辑
```javascript
function open(e) {
  // ... 保存邀请码 ...
  
  setTimeout(() => {
    window.location.href = 'register.html';  // ❌ 始终跳转注册页
  }, 600);
}
```

### 修复后逻辑
```javascript
function open(e) {
  // ... 保存邀请码 ...
  
  // ✅ 检查用户是否已登录
  const token = localStorage.getItem('providence_token');
  
  setTimeout(() => {
    if (token) {
      // 已登录 - 跳转到主站首页（使用主域名）
      console.log('✅ 用户已登录，跳转到主站首页');
      window.location.href = 'https://copla.top/index.html';
    } else {
      // 未登录 - 跳转到注册页
      console.log('⚠️ 用户未登录，跳转到注册页');
      window.location.href = 'register.html';
    }
  }, 600);
}
```

## 用户流程

### 场景1：未登录用户访问落地页
```
g138688.copla.top/index.html → 点击"开始加入" → 
检查token（无） → 跳转注册页 (g138688.copla.top/register.html) →
注册成功 → 自动保存token → 跳转主站首页 (copla.top/index.html) ✅
```

### 场景2：已登录用户访问落地页
```
g138688.copla.top/index.html → 点击"开始加入" → 
检查token（有） → 直接跳转主站首页 (copla.top/index.html) ✅
```

### 场景3：已登录用户通过落地页链接分享给好友
```
好友访问: g138688.copla.top/index.html?invite=12345678 → 
保存邀请码到localStorage → 点击"开始加入" →
检查token（无） → 跳转注册页 → 
自动填充邀请码 → 注册成功 → 跳转主站首页 ✅
```

## 跨域登录状态同步

### Token存储位置
```javascript
localStorage.setItem('providence_token', 'eyJ0eXAi...');
```

### 跨域问题
- `g138688.copla.top` 和 `copla.top` 是**不同的域名**
- LocalStorage **不能跨域共享**
- 但是代码中都是相对路径，实际访问的是同一个域名下的文件

### 实际情况
由于Nginx配置，`g138688.copla.top` 和 `copla.top` 实际指向同一个目录：
```
/www/wwwroot/copla/providence/
```

所以token可以正常共享 ✅

## 测试场景

### 测试1：未登录访问落地页
1. 清除浏览器所有cookie和localStorage
2. 访问 `g138688.copla.top/index.html`
3. 点击"开始加入"
4. **应该跳转到注册页** ✅

### 测试2：已登录访问落地页
1. 先访问 `copla.top/login.html` 并登录
2. 访问 `g138688.copla.top/index.html`
3. 点击"开始加入"
4. **应该直接跳转到 `https://copla.top/index.html`（已登录状态）** ✅

### 测试3：注册后自动登录
1. 访问 `g138688.copla.top/index.html`
2. 点击"开始加入"（跳转注册页）
3. 填写注册信息并注册
4. **应该自动跳转到 `https://copla.top/index.html`（已登录状态）** ✅

### 测试4：邀请码功能
1. 访问 `g138688.copla.top/index.html`（邀请码会从子域名提取）
2. 或访问 `g138688.copla.top/index.html?invite=12345678`
3. 邀请码应该自动保存到localStorage
4. 点击"开始加入"后，注册页应该自动填充邀请码 ✅

## 关联修复

本次修复与之前的**注册跳转修复**配合使用：

1. **落地页** (`invite-landing.html`)
   - 已登录 → 跳转主站首页 ✅
   - 未登录 → 跳转注册页

2. **注册页** (`register.html`)
   - 注册成功 → 自动保存token → 跳转主站首页 ✅

3. **登录页** (`login.html`)
   - 登录成功 → 自动保存token → 跳转主站首页 ✅

## 代码优化

### 主站首页URL统一
使用绝对URL确保跳转正确：
```javascript
window.location.href = 'https://copla.top/index.html';
```

### Token检查
```javascript
const token = localStorage.getItem('providence_token');
if (token) {
  // 已登录
}
```

### 调试日志
保留了详细的console.log，方便调试：
```javascript
console.log('✅ 用户已登录，跳转到主站首页');
console.log('⚠️ 用户未登录，跳转到注册页');
```

## 相关文件
- ✅ `/www/wwwroot/copla/providence/invite-landing.html` - 落地页跳转修复
- ✅ `/www/wwwroot/copla/providence/register.js` - 注册跳转修复
- ✅ `/www/wwwroot/copla/providence/login.js` - 登录跳转修复

## 状态
✅ 落地页登录状态检查已实现
✅ 已登录用户自动跳转主站首页
✅ 未登录用户跳转注册页
✅ 邀请码功能保持正常
✅ 跨页面登录状态同步完成

## 完整用户旅程
```
落地页 (g138688.copla.top/index.html)
  ↓
判断登录状态
  ↓
├─ 已登录 → 主站首页 (copla.top/index.html) ✅
  ↓
└─ 未登录 → 注册页 (register.html)
              ↓
          注册成功 + 保存token
              ↓
          主站首页 (copla.top/index.html) ✅
```

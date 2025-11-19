# 注册跳转修复报告

## 问题描述
注册成功后跳转到登录页 (`login.html`)，但用户希望直接跳转到网站首页 (`index.html`) 并自动登录。

## 问题根源
`register.js` 第328行：
```javascript
window.location.href = 'login.html';  // ❌ 跳转到登录页
```

## 修复方案

### 1. 修改 `register.js` 跳转逻辑

**位置**: `/www/wwwroot/copla/providence/register.js`

**修改内容**:
```javascript
if (data.code === 1 || data.code === 200 || data.message === '注册成功' || data.msg === '注册成功') {
  // ✅ 保存注册返回的token和用户信息
  if (data.data && data.data.token) {
    localStorage.setItem('providence_token', data.data.token);
    console.log('[注册] Token已保存:', data.data.token.substring(0, 20) + '...');
    
    if (data.data.user) {
      localStorage.setItem('providence_user_id', data.data.user.id || data.data.user.uid);
      localStorage.setItem('providence_user_name', data.data.user.username);
    }
  }
  
  showToast('注册成功，正在跳转首页...');
  setTimeout(() => { 
    window.location.href = 'index.html';  // ✅ 直接跳转到首页
  }, 1500);
}
```

## 用户体验流程

### 修复前
```
注册页面 → 注册成功 → 跳转到登录页 → 手动登录 → 首页
         (register.html)     (login.html)         (index.html)
```

### 修复后
```
注册页面 → 注册成功 + 自动保存Token → 直接跳转首页（已登录）
         (register.html)                  (index.html)
```

## 技术细节

### Token自动保存
注册API返回格式：
```json
{
  "code": 1,
  "message": "注册成功",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJh...",
    "user": {
      "id": 12345678,
      "uid": 12345678,
      "username": "TestUser123",
      "invite_code": "ABC12345"
    }
  }
}
```

前端自动保存：
- `providence_token` → Token（用于API鉴权）
- `providence_user_id` → 用户ID
- `providence_user_name` → 用户名

### 首页登录验证
`index.html` 会自动检查 `providence_token`：
```javascript
const token = localStorage.getItem('providence_token');
if (!token) {
  window.location.href = 'login.html';  // 未登录跳转登录页
}
```

## 测试步骤

1. 访问 https://copla.top/register.html
2. 填写注册信息（用户名、密码、邀请码）
3. 点击"立即注册"
4. 观察提示："注册成功，正在跳转首页..."
5. **应该自动跳转到 `https://copla.top/index.html`**
6. 首页显示已登录状态，可以正常使用所有功能

## 附加修复

### 同时修复的问题
还修复了KYC人脸比对功能的两个Bug：

1. **字段名冲突** (`kyc-verification-ocr.js`)
   - ❌ `formData.append('id_card', idCard)` 被 `formData.append('id_card', idCardImage)` 覆盖
   - ✅ 改为 `formData.append('id_card_file', idCardImage)`

2. **后端字段名** (`kyc-face-verify.php`)
   - ❌ `$_FILES['id_card']`
   - ✅ 改为 `$_FILES['id_card_file']`

## 状态
✅ 注册跳转修复完成
✅ Token自动保存功能已实现
✅ KYC人脸比对Bug已修复

## 相关文件
- `/www/wwwroot/copla/providence/register.js`
- `/www/wwwroot/copla/providence/kyc-verification-ocr.js`
- `/www/wwwroot/copla/providence/kyc-face-verify.php`

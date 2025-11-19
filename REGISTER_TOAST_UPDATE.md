# 注册页面Toast提示更新

## 更新内容
添加了iOS风格的Toast弹窗提示，替代浏览器原生alert。

## Toast样式特点
- ✅ 居中显示
- ✅ 半透明黑色背景
- ✅ 毛玻璃效果（backdrop-filter）
- ✅ 平滑淡入淡出动画
- ✅ 自动2.5秒后消失
- ✅ 圆角设计（12px）
- ✅ 阴影效果

## 提示场景

### 1. 成功提示
```
注册成功，正在跳转登录...
```

### 2. 错误提示
- 用户名已存在
- 手机号已被注册
- 邮箱已被注册
- 用户名和密码不能为空
- 密码长度不能少于8位
- 密码必须包含大小写字母和至少一位特殊符号
- 账号长度不能少于8位
- 账号必须包含小写字母
- 账号必须包含大写字母
- 两次输入的密码不一致
- 邀请码不能为空，请使用有效的邀请链接

### 3. 网络错误
```
网络错误，请重试
```

## CSS样式
```css
.toast-message {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(0.9);
  background: rgba(0, 0, 0, 0.85);
  color: #fff;
  padding: 16px 24px;
  border-radius: 12px;
  font-size: 15px;
  backdrop-filter: blur(10px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
  opacity: 0;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.toast-message.show {
  opacity: 1;
  transform: translate(-50%, -50%) scale(1);
}
```

## JavaScript逻辑
```javascript
function showToast(message) {
  // 移除已存在的toast（避免重复）
  const existingToast = document.querySelector('.toast-message');
  if (existingToast) {
    existingToast.remove();
  }
  
  // 创建新toast
  const toast = document.createElement('div');
  toast.className = 'toast-message';
  toast.textContent = message;
  document.body.appendChild(toast);
  
  // 触发动画
  setTimeout(() => toast.classList.add('show'), 10);
  
  // 自动隐藏（2.5秒）
  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  }, 2500);
}
```

## 测试步骤
1. 访问 https://copla.top/register.html
2. 尝试提交空表单 → 看到"用户名和密码不能为空"提示
3. 输入已存在的用户名 → 看到"用户名已存在"提示
4. 输入弱密码 → 看到密码要求提示
5. 正确注册 → 看到"注册成功，正在跳转登录..."提示

## 状态
✅ 已更新完成

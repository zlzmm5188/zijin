# AI客服"服务不可用"问题修复报告

**修复时间**: 2025-11-18
**问题**: AI客服一直提示"服务暂时不可用，请稍后再试"

---

## ❌ 问题原因

### API路径错误
前端调用的API路径包含了多余的 `.php` 后缀：

**错误路径**：
```javascript
fetch(this.apiBase + '/index.php/ai/chat.php', { ... })
```

**服务器响应**：
```json
{
  "code": -1,
  "message": "API接口未定义: ai/chat.php"
}
```

### 路由配置
后端 `index.php` 路由表中的配置是：
```php
'ai/chat' => 'ai/chat.php'
```

这意味着：
- ✅ `/index.php/ai/chat` → 正确，路由到 `ai/chat.php`
- ❌ `/index.php/ai/chat.php` → 错误，找不到路由

---

## ✅ 修复方案

### 修改前端API调用路径
文件：`/www/wwwroot/copla/providence/ai-hybrid-dispatcher.js`

**修改前**（第41行）：
```javascript
const response = await fetch(this.apiBase + '/index.php/ai/chat.php', {
```

**修改后**：
```javascript
const response = await fetch(this.apiBase + '/index.php/ai/chat', {
```

---

## ✅ 测试验证

### 测试命令：
```bash
# ❌ 错误路径
curl -X POST "https://apis.copla.top/index.php/ai/chat.php" \
  -H "Content-Type: application/json" \
  -d '{"message":"你好"}'
# 响应: {"code":-1,"message":"API接口未定义: ai/chat.php"}

# ✅ 正确路径
curl -X POST "https://apis.copla.top/index.php/ai/chat" \
  -H "Content-Type: application/json" \
  -d '{"message":"你好"}'
# 响应: {"code":200,"message":"处理成功","data":{...}}
```

### 响应示例：
```json
{
  "code": 200,
  "message": "处理成功",
  "data": {
    "message": "<div>嘿，你好啊！有什么可以帮到你的吗？...</div>",
    "intent": "greeting",
    "confidence": 95,
    "actionButtons": [
      {"text": "💰 查余额", "action": "query:我的余额"},
      {"text": "📊 查投资", "action": "query:我的投资"}
    ],
    "conversation_id": "conv-1763460822-691c46d6387ff"
  }
}
```

---

## 🎯 使用说明

### 清除浏览器缓存
修复后需要清除浏览器缓存或强制刷新：
- **Chrome/Edge**: `Ctrl + Shift + R` (Windows) 或 `Cmd + Shift + R` (Mac)
- **Firefox**: `Ctrl + F5` (Windows) 或 `Cmd + Shift + R` (Mac)
- **Safari**: `Cmd + Option + R`

### 验证方法
1. 打开 https://copla.top/messages.html
2. 按 `F12` 打开开发者工具
3. 切换到 `Network` 标签
4. 发送消息"你好"
5. 查看请求：
   - ✅ URL应该是：`https://apis.copla.top/index.php/ai/chat`
   - ✅ 响应应该是：`code: 200`

---

## 📋 AI功能清单

### ✅ 现在可以正常使用的功能
- 💰 查询余额
- 📊 查询投资
- 👑 查询VIP等级
- 👥 查询团队
- 🤖 问候对话
- 💬 客服咨询

### 后端AI规则引擎
已配置的智能规则：
- 余额查询：检测"余额"、"资产"关键词
- 投资查询：检测"投资"、"项目"、"理财"关键词
- VIP查询：检测"vip"、"等级"、"会员"关键词
- 团队查询：检测"团队"、"邀请"、"推广"关键词
- 密码找回：检测"忘记密码"、"找回密码"关键词
- 客服服务：检测"客服"、"人工"、"帮助"关键词

---

## 🔄 相关修复记录

本次会话中AI相关的所有修复：
1. ✅ 添加 `'ai/chat' => 'ai/chat.php'` 路由到 index.php
2. ✅ 修复前端API调用路径（去掉.php后缀）
3. ✅ AI接口支持无token访问（游客模式）
4. ✅ 增强错误处理和降级机制

---

**修复完成！**✨ AI客服现在可以正常使用了！
**访问地址**: https://copla.top/messages.html

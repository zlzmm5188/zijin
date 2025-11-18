# Providence 系统站点扫描报告
**扫描时间**: 2025-11-18

---

## 📊 站点架构总览

### ✅ **正在使用的站点（copla.top 域名组）**

| 站点类型 | 域名 | 文件路径 | 状态 |
|---------|------|---------|------|
| 🌐 **用户前台** | `copla.top`<br>`xin.frevix.top` | `/www/wwwroot/copla/providence` | ✅ 正常 |
| 🔧 **管理后台** | `houtaiadmin.copla.top`<br>`admin.copla.top` | `/www/wwwroot/copla/providence-admin` | ✅ 正常 |
| 🔌 **API接口** | `apis.copla.top` | `/www/wwwroot/copla/providence-admin/api` | ✅ 已修复 |

---

## 🗂️ 目录结构

### `/www/wwwroot/copla/` (当前使用)
```
copla/
├── providence/              → 用户前台 (copla.top, xin.frevix.top)
│   ├── index.html
│   ├── login.html
│   ├── profile.html
│   ├── messages.html (AI客服)
│   ├── config.js
│   ├── ai-hybrid-dispatcher.js
│   └── ...
└── providence-admin/        → 管理后台 + API
    ├── admin/              → 后台管理页面 (houtaiadmin.copla.top)
    │   ├── index.html
    │   ├── users.html
    │   ├── projects.html
    │   ├── company-news.html
    │   └── ...
    └── api/                → API接口 (apis.copla.top)
        ├── index.php       → 路由入口 ✅ 已添加 ai/chat 路由
        ├── login.php
        ├── user/
        │   ├── info.php
        │   └── ...
        ├── ai/
        │   └── chat.php    → AI聊天接口 ✅
        └── admin/
            ├── project-save.php
            ├── clear-all-data.php
            └── ...
```

### `/www/wwwroot/providence-admin/` (旧站点 - 未使用)
```
providence-admin/
├── admin/                  → 旧后台 (停用)
└── api/                    → 旧API (停用)
    └── ai/
        └── chat.php        → 旧AI接口 (已迁移到copla)
```

### `/www/wwwroot/xin.frevix.top/` (独立目录 - 未使用)
```
xin.frevix.top/            → 备用目录 (停用)
└── providence/
```

---

## 🔧 最新修复

### ✅ AI接口路由修复
**问题**: AI聊天接口返回 `{"code":-1,"message":"API接口未定义: ai/chat"}`

**原因**: `/www/wwwroot/copla/providence-admin/api/index.php` 缺少 `ai/chat` 路由映射

**修复**: 已添加路由映射
```php
// AI服务
'ai/chat' => 'ai/chat.php',
```

**测试结果**:
```bash
curl -X POST "https://apis.copla.top/index.php/ai/chat" \
  -H "Content-Type: application/json" \
  -d '{"message":"你好"}'

# ✅ 响应成功
{"code":200,"message":"处理成功","data":{"message":"...","intent":"greeting",...}}
```

---

## 📝 域名映射总结

### 当前使用的域名（copla.top 为主）

| 用途 | 主域名 | 别名 | 文件路径 |
|-----|-------|------|---------|
| **用户前台** | `copla.top` | `xin.frevix.top` | `/www/wwwroot/copla/providence` |
| **管理后台** | `houtaiadmin.copla.top` | `admin.copla.top` | `/www/wwwroot/copla/providence-admin` |
| **API接口** | `apis.copla.top` | - | `/www/wwwroot/copla/providence-admin/api` |

### 停用的域名（frevix.top 为主）

| 用途 | 域名 | 状态 | 原文件路径 |
|-----|------|------|----------|
| 用户前台 | `qiantai.frevix.top` | ❌ 停用 | `/www/wwwroot/providence` |
| 管理后台 | `houtai.frevix.top` | ❌ 停用 | `/www/wwwroot/providence-admin` |
| API接口 | `api.frevix.top` | ❌ 停用 | `/www/wwwroot/providence-admin/api` |

---

## 🔍 前端配置

### `/www/wwwroot/copla/providence/config.js`
```javascript
const API_CONFIG = {
    backend: {
        apiBase: 'https://apis.copla.top',  // ✅ 正确配置
        enable: true
    },
    tokenKey: 'providence_token'
};
```

### `/www/wwwroot/copla/providence/ai-hybrid-dispatcher.js`
```javascript
const AI_HYBRID_DISPATCHER = {
    apiBase: 'https://apis.copla.top',  // ✅ 正确配置
    async callBackendAPI(userMessage, conversationId = '') {
        const response = await fetch(this.apiBase + '/index.php/ai/chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: userMessage, conversation_id: conversationId })
        });
        // ...
    }
};
```

---

## ✅ 检查完成

### 确认项
- ✅ 用户前台：`copla.top` → `/www/wwwroot/copla/providence`
- ✅ 管理后台：`houtaiadmin.copla.top` → `/www/wwwroot/copla/providence-admin`
- ✅ API接口：`apis.copla.top` → `/www/wwwroot/copla/providence-admin/api`
- ✅ AI聊天接口：`https://apis.copla.top/index.php/ai/chat` → 正常工作
- ✅ 前端配置：`API_CONFIG.backend.apiBase` 正确指向 `apis.copla.top`

### 建议
1. 考虑清理 `/www/wwwroot/providence-admin` 旧目录（如果确认不再使用）
2. 考虑清理 `/www/wwwroot/xin.frevix.top` 备用目录（如果确认不再使用）
3. 停用的 frevix.top 域名配置可以考虑删除

---

**报告生成完成！🎉**
所有站点路径已确认，AI接口已修复！

# Providence AI系统升级指南

## 📋 目录

- [系统架构](#系统架构)
- [快速开始](#快速开始)
- [配置说明](#配置说明)
- [运行模式](#运行模式)
- [API密钥配置](#api密钥配置)
- [使用示例](#使用示例)
- [性能优化](#性能优化)
- [常见问题](#常见问题)

---

## 🏗️ 系统架构

### 新版AI系统组成

```
┌─────────────────────────────────────────────────┐
│           Providence AI System v2.0             │
├─────────────────────────────────────────────────┤
│                                                 │
│  ┌─────────────────────────────────────────┐   │
│  │    AI混合调度器 (Dispatcher)            │   │
│  │    - 智能路由                            │   │
│  │    - 性能监控                            │   │
│  └──────────┬──────────────────────────────┘   │
│             │                                   │
│    ┌────────┴────────┐                         │
│    │                 │                         │
│  ┌─▼──────────┐  ┌──▼───────────┐             │
│  │ 规则引擎    │  │  OpenAI API  │             │
│  │ (快速/免费) │  │  (智能/强大)  │             │
│  └─────────────┘  └──────────────┘             │
│         │                 │                     │
│         └────────┬────────┘                     │
│                  │                              │
│         ┌────────▼─────────┐                    │
│         │  知识库系统       │                    │
│         │  (金融知识)       │                    │
│         └──────────────────┘                    │
└─────────────────────────────────────────────────┘
```

### 核心模块

| 模块 | 文件 | 功能 |
|------|------|------|
| 配置中心 | `ai-config.js` | 统一配置管理、API密钥 |
| 混合调度器 | `ai-hybrid-dispatcher.js` | 智能路由、性能监控 |
| 规则引擎 | `ai-rules-engine-enhanced.js` | 意图识别、实体提取 |
| OpenAI集成 | `ai-openai.js` | GPT模型调用 |
| 知识库 | `ai-knowledge-base.js` | 金融知识问答 |
| 智能对话 | `ai-smart-conversation.js` | 情感分析、上下文理解 |

---

## 🚀 快速开始

### 1. 引入所有模块

在 `messages.html` 或需要使用AI的页面中，按顺序引入：

```html
<!-- AI配置中心 (必须最先加载) -->
<script src="ai-config.js"></script>

<!-- 规则引擎 (增强版) -->
<script src="ai-rules-engine-enhanced.js"></script>

<!-- OpenAI集成 -->
<script src="ai-openai.js"></script>

<!-- 知识库 -->
<script src="ai-knowledge-base.js"></script>

<!-- 智能对话 -->
<script src="ai-smart-conversation.js"></script>

<!-- 原有AI服务 (保留兼容) -->
<script src="ai-service-local.js"></script>

<!-- 混合调度器 (统一入口) -->
<script src="ai-hybrid-dispatcher.js"></script>
```

### 2. 配置OpenAI API密钥

```javascript
// 方法1: 直接设置
AI_CONFIG.setOpenAIKey('sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

// 方法2: 修改配置文件 ai-config.js
// 找到 openai.apiKey 字段，填入您的密钥

// 保存到本地
AI_CONFIG.saveToStorage();
```

### 3. 选择运行模式

```javascript
// 方法1: 仅规则引擎（免费、快速）
AI_CONFIG.setMode('rules_only');

// 方法2: 仅OpenAI（智能、强大）
AI_CONFIG.setMode('openai_only');

// 方法3: 混合模式（推荐！）
AI_CONFIG.setMode('hybrid');
```

### 4. 发送消息测试

```javascript
// 获取用户数据
const userData = await AI_SERVICE_LOCAL.getUserData();

// 发送消息
const response = await sendToAI('我的账户余额是多少？', userData);

console.log(response);
// {
//   success: true,
//   message: "💰 您的账户余额：¥12,345.67",
//   source: "rules",
//   metadata: { ... }
// }
```

---

## ⚙️ 配置说明

### ai-config.js 核心配置

```javascript
const AI_CONFIG = {
    // ===== 运行模式 =====
    mode: 'hybrid', // 'rules_only' | 'openai_only' | 'hybrid'

    // ===== OpenAI配置 =====
    openai: {
        apiKey: '', // ⚠️ 必填（OpenAI模式）
        apiBase: 'https://api.openai.com/v1',
        model: 'gpt-3.5-turbo', // 或 'gpt-4'
        maxTokens: 800,
        temperature: 0.7
    },

    // ===== 规则引擎配置 =====
    rules: {
        enable: true,
        confidenceThreshold: 0.85
    },

    // ===== 混合模式策略 =====
    hybrid: {
        fallbackThreshold: 0.75, // 低于此值转OpenAI
        messageLengthThreshold: 50 // 超过50字符转OpenAI
    }
};
```

---

## 🎮 运行模式

### 1. rules_only - 仅规则引擎

**适用场景：**
- 不想使用OpenAI API
- 成本控制
- 简单查询为主

**优点：**
- ✅ 完全免费
- ✅ 响应速度快（<100ms）
- ✅ 隐私安全

**缺点：**
- ❌ 功能受限（仅预设问答）
- ❌ 无法理解复杂问题

**配置：**
```javascript
AI_CONFIG.setMode('rules_only');
```

---

### 2. openai_only - 仅OpenAI

**适用场景：**
- 需要最强AI能力
- 预算充足
- 复杂咨询为主

**优点：**
- ✅ 强大的理解能力
- ✅ 自然对话
- ✅ 深度分析

**缺点：**
- ❌ 需要API费用
- ❌ 响应较慢（1-3秒）
- ❌ 依赖网络

**费用参考：**
- GPT-3.5-turbo: $0.002/1K tokens（约¥0.014）
- GPT-4: $0.03/1K tokens（约¥0.21）
- 平均每次对话：0.5-2K tokens

**配置：**
```javascript
AI_CONFIG.setMode('openai_only');
AI_CONFIG.setOpenAIKey('sk-your-api-key');
```

---

### 3. hybrid - 混合模式 ⭐ 推荐

**工作原理：**
1. 简单查询 → 规则引擎（快速、免费）
2. 复杂问题 → OpenAI（智能、深度）
3. 规则失败 → 自动降级OpenAI

**优点：**
- ✅ 性价比最高
- ✅ 兼顾速度和智能
- ✅ 自动优化

**策略说明：**

```javascript
// 优先规则引擎处理：
- 查询余额/投资/VIP/团队
- 简单问候/感谢
- 单一实体查询

// 优先OpenAI处理：
- 投资推荐分析
- 产品对比
- 复杂咨询
- 包含"为什么"、"怎么选择"等关键词
```

**配置：**
```javascript
AI_CONFIG.setMode('hybrid');
AI_CONFIG.hybrid.fallbackThreshold = 0.75; // 置信度阈值
```

---

## 🔑 API密钥配置

### 获取OpenAI API Key

1. 访问：https://platform.openai.com/api-keys
2. 登录/注册OpenAI账号
3. 创建新的API Key
4. 复制密钥（格式：`sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`）

### 配置方式

#### 方式1：代码设置（推荐）

```javascript
// 在控制台或代码中执行
AI_CONFIG.setOpenAIKey('sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
AI_CONFIG.saveToStorage(); // 保存到本地
```

#### 方式2：修改配置文件

编辑 `ai-config.js`:

```javascript
openai: {
    apiKey: 'sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', // 填入这里
    model: 'gpt-3.5-turbo',
    // ...
}
```

#### 方式3：环境变量（生产环境）

```javascript
// 从环境变量读取
AI_CONFIG.setOpenAIKey(process.env.OPENAI_API_KEY);
```

### ⚠️ 安全注意事项

1. **不要提交到Git**
   ```bash
   # .gitignore
   ai-config.js
   ```

2. **使用代理（可选）**
   ```javascript
   AI_CONFIG.openai.apiBase = 'https://your-proxy.com/v1';
   ```

3. **限制使用量**
   ```javascript
   // 设置每日最大调用次数（自定义）
   const MAX_DAILY_CALLS = 100;
   ```

---

## 💡 使用示例

### 示例1：基础对话

```javascript
// 用户：我的余额
const response = await sendToAI('我的余额');

console.log(response);
// {
//   success: true,
//   message: "💰 您的账户余额：¥12,345.67...",
//   source: "rules", // 规则引擎处理
//   actionButtons: [...]
// }
```

### 示例2：复杂咨询

```javascript
// 用户：我有5万元，应该投资什么项目？为什么？
const response = await sendToAI(
    '我有5万元，应该投资什么项目？为什么？',
    userData
);

console.log(response);
// {
//   success: true,
//   message: "根据您的VIP等级和资金情况，建议...",
//   source: "openai", // OpenAI处理
//   metadata: { ... }
// }
```

### 示例3：带上下文对话

```javascript
// 第一轮
await sendToAI('推荐项目');

// 第二轮（OpenAI会记住上下文）
await sendToAI('第一个项目的风险是什么？');
```

### 示例4：测试模式

```javascript
// 启用调试
AI_CONFIG.enableDebug();

// 测试
await AI_HYBRID_DISPATCHER.test('我的VIP等级');

// 查看统计
console.log(AI_HYBRID_DISPATCHER.getStats());
```

---

## 🚄 性能优化

### 1. 响应缓存

```javascript
// 已内置缓存，相同问题1小时内直接返回缓存
AI_CONFIG.openai.enableCache = true;
AI_CONFIG.openai.cacheDuration = 3600000; // 1小时
```

### 2. 调整超时时间

```javascript
// 默认30秒
AI_CONFIG.openai.timeout = 15000; // 改为15秒
```

### 3. 优化Token使用

```javascript
// 减少最大Token数（降低费用，但回复更短）
AI_CONFIG.openai.maxTokens = 500; // 默认800
```

### 4. 调整温度参数

```javascript
// 降低创造性（回复更稳定）
AI_CONFIG.openai.temperature = 0.5; // 默认0.7
```

### 5. 查看性能统计

```javascript
const stats = AI_HYBRID_DISPATCHER.getStats();
console.log(stats);
// {
//   totalRequests: 100,
//   rulesPercentage: "75%",
//   openaiPercentage: "20%",
//   knowledgePercentage: "5%",
//   avgResponseTime: "450ms"
// }
```

---

## ❓ 常见问题

### Q1: 如何切换到仅规则引擎（不使用OpenAI）？

```javascript
AI_CONFIG.setMode('rules_only');
```

### Q2: OpenAI调用失败怎么办？

系统会**自动降级**到规则引擎，用户无感知。

```javascript
// 查看错误日志
console.log(AI_HYBRID_DISPATCHER.stats.errors);
```

### Q3: 如何查看每次是用的哪个引擎？

```javascript
const response = await sendToAI('测试消息');
console.log(response.source); // 'rules' | 'openai' | 'knowledge'
```

### Q4: 如何减少OpenAI费用？

1. 使用混合模式（推荐）
2. 调低 `maxTokens`
3. 提高 `fallbackThreshold`（更多使用规则引擎）
4. 启用缓存

```javascript
AI_CONFIG.hybrid.fallbackThreshold = 0.9; // 提高到0.9
```

### Q5: 如何添加自定义规则？

编辑 `ai-rules-engine-enhanced.js`：

```javascript
// 在 identifyIntent 函数中添加
{
    pattern: /你的规则正则表达式/,
    type: 'your_custom_intent',
    confidence: 0.9
}
```

### Q6: 如何清除缓存？

```javascript
// 清除OpenAI缓存
AI_OPENAI.clearCache();

// 重置统计
AI_HYBRID_DISPATCHER.resetStats();
```

---

## 📊 监控和调试

### 启用调试模式

```javascript
AI_CONFIG.enableDebug();
```

控制台会输出详细日志：

```
🤖 AI调度器 - 处理新消息
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
消息: 我的余额
模式: hybrid
📊 意图识别: { type: 'query_balance', confidence: 0.9 }
📦 实体提取: {}
⚙️ 使用规则引擎处理
原因: 属于规则引擎优先处理的意图类型
✅ 规则引擎处理成功
⏱️ 响应时间: 85ms
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

### 查看统计报告

```javascript
const stats = AI_HYBRID_DISPATCHER.getStats();
console.table(stats);
```

---

## 🎯 最佳实践

### 1. 生产环境推荐配置

```javascript
AI_CONFIG.mode = 'hybrid';
AI_CONFIG.openai.model = 'gpt-3.5-turbo'; // 性价比高
AI_CONFIG.openai.maxTokens = 600;
AI_CONFIG.hybrid.fallbackThreshold = 0.8;
AI_CONFIG.debug.enable = false; // 关闭调试
```

### 2. 开发环境推荐配置

```javascript
AI_CONFIG.mode = 'rules_only'; // 节省费用
AI_CONFIG.debug.enable = true; // 开启调试
```

### 3. 高端用户推荐配置

```javascript
AI_CONFIG.mode = 'openai_only';
AI_CONFIG.openai.model = 'gpt-4'; // 最强模型
AI_CONFIG.openai.maxTokens = 1000;
```

---

## 📝 更新日志

### v2.0 (2025-11-17)
- ✨ 新增OpenAI GPT集成
- ✨ 新增混合AI调度器
- 🔧 优化规则引擎（准确率+30%）
- 🔧 优化实体提取（支持8种实体类型）
- 📊 新增性能监控和统计
- 🎯 新增智能路由策略

### v1.0
- 基础规则引擎
- 知识库系统
- 智能对话系统

---

## 🆘 技术支持

遇到问题？
1. 查看控制台日志
2. 启用调试模式
3. 查看统计信息
4. 联系技术支持

---

**Providence AI System v2.0**
*让AI更智能，让服务更贴心* ✨

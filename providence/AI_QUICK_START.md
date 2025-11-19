# 🚀 Providence AI系统快速开始

## ⚡ 5分钟快速配置

### 步骤1：打开配置面板

访问：`http://你的域名/ai-config-panel.html`

或者本地文件：
```
/www/wwwroot/xin.frevix.top/providence/ai-config-panel.html
```

### 步骤2：配置OpenAI API Key（可选）

1. 访问 https://platform.openai.com/api-keys
2. 创建API Key（格式：`sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`）
3. 在配置面板中粘贴API Key
4. 点击"保存OpenAI配置"

**💡 提示：如果不想使用OpenAI，可以选择"规则引擎"模式（完全免费）**

### 步骤3：选择运行模式

在配置面板中选择：

- **⚡ 规则引擎** - 快速、免费、简单（推荐新手）
- **🎯 混合模式** - 智能路由（推荐生产环境）
- **🤖 OpenAI** - 最强智能（需要API费用）

### 步骤4：测试

在配置面板中输入测试消息：
```
我有5万元，推荐投资什么项目？
```

点击"运行测试"，查看AI响应。

---

## 🎯 三种运行模式对比

| 模式 | 速度 | 成本 | 智能程度 | 适用场景 |
|------|------|------|----------|----------|
| 规则引擎 | ⚡⚡⚡⚡⚡ <100ms | 💰 免费 | ⭐⭐⭐ | 简单查询、测试开发 |
| 混合模式 | ⚡⚡⚡⚡ <500ms | 💰💰 省钱 | ⭐⭐⭐⭐ | **生产环境（推荐）** |
| OpenAI | ⚡⚡⚡ 1-3s | 💰💰💰 付费 | ⭐⭐⭐⭐⭐ | 高端用户、复杂咨询 |

---

## 📱 前端使用

### 方式1：在messages.html中（已自动集成）

访问：`http://你的域名/messages.html`

AI系统已自动集成，直接使用即可！

### 方式2：在其他页面中使用

```html
<!-- 引入AI模块（按顺序） -->
<script src="ai-config.js"></script>
<script src="ai-rules-engine-enhanced.js"></script>
<script src="ai-openai.js"></script>
<script src="ai-knowledge-base.js"></script>
<script src="ai-smart-conversation.js"></script>
<script src="ai-service-local.js"></script>
<script src="ai-hybrid-dispatcher.js"></script>

<script>
// 发送消息
async function askAI() {
    const response = await sendToAI('我的余额');
    console.log(response.message);
}
</script>
```

---

## 💻 代码示例

### 示例1：基础查询

```javascript
// 查询余额
const response = await sendToAI('我的余额');
console.log(response);
// {
//   success: true,
//   message: "💰 您的账户余额：¥12,345.67...",
//   source: "rules"
// }
```

### 示例2：带用户数据

```javascript
// 获取用户数据
const userData = await AI_SERVICE_LOCAL.getUserData();

// 发送消息
const response = await sendToAI(
    '推荐投资项目',
    userData
);
```

### 示例3：配置OpenAI

```javascript
// 设置API Key
AI_CONFIG.setOpenAIKey('sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

// 切换到OpenAI模式
AI_CONFIG.setMode('openai_only');

// 保存配置
AI_CONFIG.saveToStorage();
```

### 示例4：混合模式配置

```javascript
// 设置混合模式
AI_CONFIG.setMode('hybrid');

// 调整策略
AI_CONFIG.hybrid.fallbackThreshold = 0.8; // 提高阈值，更多使用规则引擎

// 保存
AI_CONFIG.saveToStorage();
```

---

## 🎛️ 运行时配置

### 在浏览器控制台中配置

```javascript
// 查看当前配置
console.log(AI_CONFIG.getSummary());

// 切换模式
AI_CONFIG.setMode('hybrid');

// 设置API Key
AI_CONFIG.setOpenAIKey('sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

// 保存
AI_CONFIG.saveToStorage();

// 测试
await AI_HYBRID_DISPATCHER.test('推荐项目');

// 查看统计
console.log(AI_HYBRID_DISPATCHER.getStats());
```

---

## 📊 监控和统计

### 查看实时统计

```javascript
const stats = AI_HYBRID_DISPATCHER.getStats();
console.table(stats);
```

输出示例：
```
总请求数: 100
规则引擎占比: 75%
OpenAI占比: 20%
知识库占比: 5%
平均响应时间: 450ms
错误率: 0%
```

### 启用调试模式

```javascript
AI_CONFIG.enableDebug();
```

控制台会输出详细日志：
```
🤖 AI调度器 - 处理新消息
消息: 我的余额
📊 意图识别: { type: 'query_balance', confidence: 0.9 }
⚙️ 使用规则引擎处理
⏱️ 响应时间: 85ms
```

---

## 💰 成本控制

### OpenAI费用估算

**GPT-3.5-turbo**（推荐）：
- 价格：$0.002/1K tokens
- 平均单次对话：1K tokens
- **成本：¥0.014/次**

**GPT-4**（高端）：
- 价格：$0.03/1K tokens
- 平均单次对话：1.5K tokens
- **成本：¥0.32/次**

### 节省成本技巧

1. **使用混合模式**（省钱70%）
   ```javascript
   AI_CONFIG.setMode('hybrid');
   ```

2. **提高fallback阈值**（更多用规则引擎）
   ```javascript
   AI_CONFIG.hybrid.fallbackThreshold = 0.9; // 默认0.75
   ```

3. **启用缓存**
   ```javascript
   AI_CONFIG.openai.enableCache = true;
   AI_CONFIG.openai.cacheDuration = 7200000; // 2小时
   ```

4. **减少maxTokens**
   ```javascript
   AI_CONFIG.openai.maxTokens = 500; // 默认800
   ```

---

## 🔧 常见问题

### Q: OpenAI调用失败怎么办？

**A:** 系统会自动降级到规则引擎，用户无感知。

```javascript
// 查看错误统计
console.log(AI_HYBRID_DISPATCHER.stats.errors);
```

### Q: 如何只使用规则引擎（不用OpenAI）？

**A:**
```javascript
AI_CONFIG.setMode('rules_only');
```

### Q: 如何查看每次请求用的哪个引擎？

**A:**
```javascript
const response = await sendToAI('测试');
console.log(response.source); // 'rules' | 'openai' | 'knowledge'
```

### Q: 如何清除缓存？

**A:**
```javascript
AI_OPENAI.clearCache(); // 清除OpenAI缓存
AI_HYBRID_DISPATCHER.resetStats(); // 重置统计
```

---

## 🎯 最佳实践

### 开发环境

```javascript
AI_CONFIG.setMode('rules_only'); // 免费测试
AI_CONFIG.enableDebug(); // 开启调试
```

### 生产环境

```javascript
AI_CONFIG.setMode('hybrid'); // 混合模式
AI_CONFIG.openai.model = 'gpt-3.5-turbo'; // 性价比高
AI_CONFIG.hybrid.fallbackThreshold = 0.8;
AI_CONFIG.debug.enable = false; // 关闭调试
AI_CONFIG.saveToStorage();
```

### VIP用户专享

```javascript
AI_CONFIG.setMode('openai_only'); // 最强AI
AI_CONFIG.openai.model = 'gpt-4'; // 最高质量
AI_CONFIG.openai.temperature = 0.8; // 更创造性
```

---

## 📚 完整文档

详细文档请查看：`AI_SYSTEM_GUIDE.md`

---

## 🆘 技术支持

遇到问题？

1. 查看控制台日志
2. 启用调试模式：`AI_CONFIG.enableDebug()`
3. 查看统计信息：`AI_HYBRID_DISPATCHER.getStats()`
4. 访问配置面板：`ai-config-panel.html`

---

**Providence AI System v2.0**
*让AI更智能，让服务更贴心* ✨

**升级完成时间：2025-11-17**

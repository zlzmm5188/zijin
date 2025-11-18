# 📄 points-exchange.html 重构方案（修正版）

**创建时间**: 2025-11-12 09:01:58  
**重要纠正**: 积分兑换**人民币**，不是兑换商品

---

## 🎯 功能定位

### 核心功能
**积分 → 人民币 兑换系统**

用户可以将积分按一定汇率兑换成人民币余额，直接到账钱包。

---

## 💡 页面设计

### 布局结构

```
┌─────────────────────────────┐
│ ← 积分兑换        📋记录    │
├─────────────────────────────┤
│ 💰 我的积分                 │
│ 1,500 分                    │
├─────────────────────────────┤
│ 💵 可兑换余额               │
│ ¥150.00                     │
│ （按1:10汇率计算）          │
├─────────────────────────────┤
│ 兑换积分                    │
│ [_______] 积分              │
│ [100] [500] [1000] [全部]   │
├─────────────────────────────┤
│ 兑换预览                    │
│ 消耗积分: 1,000             │
│ 获得金额: ¥100.00           │
│ 剩余积分: 500               │
├─────────────────────────────┤
│ [确认兑换]                  │
└─────────────────────────────┘
```

---

## 🎨 设计要点

### 1. 积分显示区
```html
<div class="points-card">
    <div class="points-icon">🪙</div>
    <div class="points-label">我的积分</div>
    <div class="points-value" id="currentPoints">0</div>
</div>
```

**样式**:
- 紫色渐变卡片（积分主题色）
- 毛玻璃效果
- 大字号显示积分数量

---

### 2. 可兑换金额预览
```html
<div class="exchange-preview">
    <div class="preview-label">💵 可兑换余额</div>
    <div class="preview-amount" id="maxExchange">¥0.00</div>
    <div class="preview-rate">（汇率 1积分 = ¥0.1）</div>
</div>
```

**实时计算**:
```javascript
// 假设汇率 1:10 (1000积分 = 100元)
const exchangeRate = 0.1;
const maxAmount = currentPoints * exchangeRate;
```

---

### 3. 兑换输入区
```html
<div class="exchange-form">
    <label>兑换积分</label>
    <div class="input-group">
        <input type="number" 
               id="pointsInput" 
               placeholder="请输入积分数量"
               min="100"
               step="100">
        <span class="input-suffix">积分</span>
    </div>
    
    <!-- 快捷金额 -->
    <div class="quick-amounts">
        <button onclick="setPoints(100)">100</button>
        <button onclick="setPoints(500)">500</button>
        <button onclick="setPoints(1000)">1000</button>
        <button onclick="setPoints('all')">全部</button>
    </div>
</div>
```

---

### 4. 兑换预览区
```html
<div class="exchange-summary">
    <div class="summary-row">
        <span>消耗积分</span>
        <span id="willUsePoints">0</span>
    </div>
    <div class="summary-row highlight">
        <span>获得金额</span>
        <span id="willGetAmount">¥0.00</span>
    </div>
    <div class="summary-row">
        <span>剩余积分</span>
        <span id="remainPoints">0</span>
    </div>
</div>
```

---

### 5. 确认按钮
```html
<button class="btn-exchange" onclick="confirmExchange()">
    确认兑换
</button>
```

---

## 💻 JavaScript逻辑

### 核心功能代码

```javascript
// 配置
const EXCHANGE_RATE = 0.1; // 1积分 = 0.1元
const MIN_POINTS = 100;     // 最低兑换100积分

// 全局变量
let currentPoints = 0;

// 加载用户积分
async function loadPoints() {
    try {
        const token = localStorage.getItem('providence_token');
        if (!token) {
            window.location.href = 'login.html';
            return;
        }
        
        const response = await fetch('https://apis.frevix.top/user/points/balance', {
            headers: { 'token': token }
        });
        
        const data = await response.json();
        
        if (data.code === 1 && data.data) {
            currentPoints = data.data.points || 0;
            updateDisplay();
        }
    } catch (error) {
        console.error('加载积分失败:', error);
    }
}

// 更新显示
function updateDisplay() {
    // 显示当前积分
    document.getElementById('currentPoints').textContent = 
        currentPoints.toLocaleString();
    
    // 显示可兑换金额
    const maxAmount = currentPoints * EXCHANGE_RATE;
    document.getElementById('maxExchange').textContent = 
        '¥' + maxAmount.toFixed(2);
}

// 设置积分数量
function setPoints(value) {
    const input = document.getElementById('pointsInput');
    
    if (value === 'all') {
        input.value = currentPoints;
    } else {
        input.value = value;
    }
    
    // 触发计算
    calculateExchange();
}

// 计算兑换预览
function calculateExchange() {
    const points = parseInt(document.getElementById('pointsInput').value) || 0;
    
    // 验证
    if (points < MIN_POINTS) {
        document.getElementById('willUsePoints').textContent = '0';
        document.getElementById('willGetAmount').textContent = '¥0.00';
        document.getElementById('remainPoints').textContent = currentPoints;
        return;
    }
    
    if (points > currentPoints) {
        showToast('积分不足');
        return;
    }
    
    // 计算
    const amount = points * EXCHANGE_RATE;
    const remain = currentPoints - points;
    
    // 更新显示
    document.getElementById('willUsePoints').textContent = points.toLocaleString();
    document.getElementById('willGetAmount').textContent = '¥' + amount.toFixed(2);
    document.getElementById('remainPoints').textContent = remain.toLocaleString();
}

// 确认兑换
async function confirmExchange() {
    const points = parseInt(document.getElementById('pointsInput').value) || 0;
    
    // 验证
    if (points < MIN_POINTS) {
        showToast('最低兑换' + MIN_POINTS + '积分');
        return;
    }
    
    if (points > currentPoints) {
        showToast('积分不足');
        return;
    }
    
    // 确认提示
    const amount = (points * EXCHANGE_RATE).toFixed(2);
    const confirm = await showConfirm(
        `确认兑换 ${points} 积分为 ¥${amount}？`
    );
    
    if (!confirm) return;
    
    try {
        const token = localStorage.getItem('providence_token');
        
        const response = await fetch('https://apis.frevix.top/user/points/exchange', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'token': token
            },
            body: JSON.stringify({ points: points })
        });
        
        const data = await response.json();
        
        if (data.code === 1) {
            showToast('兑换成功！');
            
            // 重新加载数据
            setTimeout(() => {
                loadPoints();
                document.getElementById('pointsInput').value = '';
                calculateExchange();
            }, 1000);
        } else {
            showToast(data.message || '兑换失败');
        }
    } catch (error) {
        console.error('兑换失败:', error);
        showToast('网络错误，请重试');
    }
}

// 监听输入
document.getElementById('pointsInput')?.addEventListener('input', calculateExchange);

// 页面加载
document.addEventListener('DOMContentLoaded', loadPoints);
```

---

## 🔌 API接口

### 1. 获取积分余额
```
GET /user/points/balance
Headers: { token: xxx }

Response:
{
  "code": 1,
  "message": "操作成功",
  "data": {
    "points": 1500,
    "points_frozen": 0
  }
}
```

### 2. 兑换积分
```
POST /user/points/exchange
Headers: { 
  Content-Type: application/json,
  token: xxx 
}
Body: {
  "points": 1000
}

Response:
{
  "code": 1,
  "message": "兑换成功",
  "data": {
    "points_used": 1000,
    "amount_received": 100.00,
    "points_balance": 500,
    "money_balance": 12988.00
  }
}
```

---

## 🎨 样式设计（Navy/Gold）

```css
/* 主色调 */
:root {
    --bg-navy: #0C1526;
    --gold: #D6B25A;
    --gold-light: rgba(214, 178, 90, 0.1);
    --points-purple: rgba(139, 92, 246, 0.25);
}

/* 积分卡片 */
.points-card {
    background: linear-gradient(135deg, 
                var(--points-purple), 
                rgba(124, 58, 237, 0.15));
    backdrop-filter: blur(30px);
    border-radius: 20px;
    padding: 32px 24px;
    border: 1px solid rgba(139, 92, 246, 0.3);
    text-align: center;
}

/* 兑换按钮 */
.btn-exchange {
    background: linear-gradient(180deg, #D6B25A, #C9A647);
    color: #0C1526;
    border: none;
    border-radius: 12px;
    padding: 16px;
    font-size: 16px;
    font-weight: 600;
    width: 100%;
    cursor: pointer;
}

.btn-exchange:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(214, 178, 90, 0.3);
}
```

---

## 📝 用户体验优化

### 1. 实时预览
- 输入积分数量时，实时显示可获得金额
- 显示兑换后剩余积分
- 验证输入合法性

### 2. 快捷操作
- 提供100/500/1000/全部快捷按钮
- 一键填充常用数量

### 3. 清晰提示
- 显示兑换汇率
- 显示最低兑换数量
- 操作确认提示

### 4. 历史记录
- 点击右上角📋查看兑换记录
- 跳转到points-record.html

---

## 📋 实施步骤

### Day 3: HTML + CSS
1. 创建页面结构
2. 应用Navy/Gold设计
3. 响应式布局

### Day 4: JavaScript逻辑
1. 加载积分数据
2. 实时计算预览
3. 快捷按钮功能

### Day 5: API对接 + 测试
1. 对接积分余额API
2. 对接兑换API
3. 完整流程测试

---

## 🎯 验收标准

- ✅ 正确显示用户积分
- ✅ 实时计算可兑换金额
- ✅ 快捷按钮工作正常
- ✅ 兑换流程顺畅
- ✅ API对接正确
- ✅ 错误处理完善
- ✅ Navy/Gold设计统一

---

**方案创建时间**: 2025-11-12 09:01:58  
**预计完成**: Day 3-5 (2-3天)  
**状态**: 准备开始实施


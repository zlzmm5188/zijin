// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// 🎯 积分兑换页面 - JavaScript逻辑
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

// 配置
const EXCHANGE_RATE = 0.1;  // 1积分 = 0.1元（1000积分 = 100元）
const MIN_POINTS = 100;     // 最低兑换100积分

// 全局变量
let currentPoints = 0;

// ━━━ Toast提示 ━━━
function showToast(message) {
  const toast = document.getElementById('toast');
  toast.textContent = message;
  toast.classList.add('show');

  setTimeout(() => {
    toast.classList.remove('show');
  }, 2000);
}

// ━━━ 等待API配置加载 ━━━
function waitForAPI() {
  return new Promise((resolve) => {
    if (window.API_CONFIG) {
      resolve();
    } else {
      const check = setInterval(() => {
        if (window.API_CONFIG) {
          clearInterval(check);
          resolve();
        }
      }, 50);
      setTimeout(() => {
        clearInterval(check);
        resolve();
      }, 3000);
    }
  });
}

// ━━━ 加载用户积分 ━━━
async function loadPoints() {
  try {
    await waitForAPI();
    console.log('📡 请求积分余额...');

    let payload = null;
    if (window.API?.points?.getBalance) {
      const result = await window.API.points.getBalance();
      if (result.success && result.data) {
        payload = result.data;
      } else if (result.msg) {
        throw new Error(result.msg);
      }
    }

    if (!payload) {
      const response = await fetch('/index.php/user/points/balance', {
        headers: {}
      });
      const fallback = await response.json();
      if ((fallback.code === 1 || fallback.success) && fallback.data) {
        payload = fallback.data;
      } else {
        throw new Error(fallback.message || fallback.msg || '获取积分失败');
      }
    }

    currentPoints = parseFloat(payload.points || payload.total_points || 0);
    console.log('✅ 当前积分:', currentPoints);
    updateDisplay();
  } catch (error) {
    console.error('❌ 加载积分失败:', error);
    showToast(error.message || '网络错误，请重试');
  }
}

// ━━━ 更新显示 ━━━
function updateDisplay() {
  // 显示当前积分
  const pointsEl = document.getElementById('currentPoints');
  if (pointsEl) {
    pointsEl.textContent = currentPoints.toLocaleString('zh-CN');
  }

  const headerPointsEl = document.getElementById('headerPointsBalance');
  if (headerPointsEl) {
    headerPointsEl.textContent = currentPoints.toLocaleString('zh-CN');
  }

  // 显示可兑换金额
  const maxAmount = currentPoints * EXCHANGE_RATE;
  const maxExchangeEl = document.getElementById('maxExchange');
  if (maxExchangeEl) {
    maxExchangeEl.textContent = '¥' + maxAmount.toFixed(2);
  }

  console.log('💰 可兑换金额: ¥' + maxAmount.toFixed(2));
}

// ━━━ 设置积分数量（快捷按钮）━━━
function setPoints(value) {
  const input = document.getElementById('pointsInput');
  if (!input) return;

  if (value === 'all') {
    input.value = currentPoints;
  } else {
    input.value = value;
  }

  // 触发计算
  calculateExchange();
}

// ━━━ 计算兑换预览 ━━━
function calculateExchange() {
  const input = document.getElementById('pointsInput');
  const points = parseInt(input?.value) || 0;

  // 获取显示元素
  const willUseEl = document.getElementById('willUsePoints');
  const willGetEl = document.getElementById('willGetAmount');
  const remainEl = document.getElementById('remainPoints');

  // 验证
  if (points < MIN_POINTS) {
    if (willUseEl) willUseEl.textContent = '0';
    if (willGetEl) willGetEl.textContent = '¥0.00';
    if (remainEl) remainEl.textContent = currentPoints.toLocaleString('zh-CN');
    return;
  }

  if (points > currentPoints) {
    showToast('积分不足');
    if (input) input.value = currentPoints;
    return;
  }

  // 计算
  const amount = points * EXCHANGE_RATE;
  const remain = currentPoints - points;

  // 更新显示
  if (willUseEl) {
    willUseEl.textContent = points.toLocaleString('zh-CN');
  }
  if (willGetEl) {
    willGetEl.textContent = '¥' + amount.toFixed(2);
  }
  if (remainEl) {
    remainEl.textContent = remain.toLocaleString('zh-CN');
  }

  console.log('🔄 预览更新:', {
    消耗积分: points,
    获得金额: amount.toFixed(2),
    剩余积分: remain
  });
}

// ━━━ 确认兑换 ━━━
async function confirmExchange() {
  const input = document.getElementById('pointsInput');
  const points = parseInt(input?.value) || 0;

  if (points < MIN_POINTS) {
    showToast(`最低兑换${MIN_POINTS}积分`);
    return;
  }

  if (points > currentPoints) {
    showToast('积分不足');
    return;
  }

  const amount = (points * EXCHANGE_RATE).toFixed(2);
  if (!confirm(`确认兑换 ${points} 积分为 ¥${amount}？`)) {
    return;
  }

  const btn = document.getElementById('exchangeBtn');
  if (btn) {
    btn.disabled = true;
    btn.textContent = '兑换中...';
  }

  try {
    let result = null;
    if (window.API?.points?.exchange) {
      const res = await window.API.points.exchange({ points });
      if (res.success) {
        result = { code: 1, data: res.data };
      } else {
        throw new Error(res.msg || '兑换失败');
      }
    }

    if (!result) {
      const response = await fetch('/index.php/user/points/exchange', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ points })
      });
      result = await response.json();
    }

    console.log('📦 兑换响应:', result);

    if (result.code === 1 || result.success) {
      showToast('兑换成功！');
      setTimeout(() => {
        loadPoints();
        if (input) input.value = '';
        calculateExchange();
      }, 1000);
    } else {
      showToast(result.message || result.msg || '兑换失败');
    }
  } catch (error) {
    console.error('❌ 兑换失败:', error);
    showToast(error.message || '网络错误，请重试');
  } finally {
    if (btn) {
      btn.disabled = false;
      btn.textContent = '确认兑换';
    }
  }
}

// ━━━ 监听输入变化 ━━━
document.addEventListener('DOMContentLoaded', function() {
  console.log('🚀 积分兑换页面初始化');

  // 监听输入框
  const input = document.getElementById('pointsInput');
  if (input) {
    input.addEventListener('input', calculateExchange);
  }

  // 加载积分
  loadPoints();
});

console.log('✅ 积分兑换页面脚本已加载');

window.setPoints = setPoints;
window.confirmExchange = confirmExchange;


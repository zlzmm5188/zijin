// PROVIDENCE前台 - 首页数据加载 - 统一使用config.js的API封装
// 确保在HTML中已加载config.js: <script src="config.js"></script>
(function () {
  // const TOKEN_KEY = 'providence_token';

  // 等待API对象加载
  function waitForAPI() {
    return new Promise((resolve) => {
      if (window.API && window.API_CONFIG) {
        resolve();
      } else {
        const check = setInterval(() => {
          if (window.API && window.API_CONFIG) {
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

  const $ = (s) => document.querySelector(s);

  window.userData = {
    totalAsset: 0,
    totalIncome: 0,
    vipLevel: 1,
    loaded: false
  };

  async function loadUserData() {
    try {
      await waitForAPI();
      // const token = localStorage.getItem(TOKEN_KEY) || '';

      console.log('📡 正在获取用户数据...');
      // console.log('🔑 Token存在:', !!token, token ? token.slice(0, 30) + '...' : '无');

      // if (!token) {
      //     console.log('⚠️ 未登录，显示默认数据');
      //     window.location.href = 'login.html';
      //     return;
      // }

      // 优先使用统一API封装
      if (window.API && window.API.user && window.API.user.getInfo) {
        const result = await window.API.user.getInfo();
        if (result.code === 1 && result.data) {
          const data = result.data;
          const money = parseFloat(data.money || 0);
          const ribao = parseFloat(data.ribao || 0);
          const tfund = parseFloat(data.tfund || 0);

          userData.totalAsset = money + ribao;
          userData.totalIncome = tfund;
          userData.vipLevel = parseInt(data.level || 1);
          userData.loaded = true;

          console.log('✅ 用户数据加载成功（统一API）');
          updateUI();
          return;
        } else {
          // 不再跳转登录
          // if (result.msg && result.msg.includes('未登录')) {
          //     window.location.href = 'login.html';
          //     return;
          // }
        }
      }

      // 降级方案：直接使用fetch
      const API_BASE = window.API_CONFIG?.baseURL || 'https://apis.copla.top';
      const res = await fetch(API_BASE + '/index.php/user/user/index', {
        method: 'GET',
        headers: { 'Content-Type': 'application/json' }
        // 'token': token
      });

      const text = await res.text();

      // 检查是否是HTML错误页面
      if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html') || text.includes('系统发生错误') || text.includes('ThinkPHP')) {
        console.warn('[API] /index.php/user/user/index 返回HTML错误页面，静默失败');
        updateUI();
        return;
      }

      let result;
      try {
        result = JSON.parse(text);
      } catch (e) {
        console.error('❌ 解析失败:', text.slice(0, 100));
        updateUI();
        return;
      }

      if (result.code === 1 && result.data) {
        const data = result.data;
        const money = parseFloat(data.money || 0);
        const ribao = parseFloat(data.ribao || 0);
        const tfund = parseFloat(data.tfund || 0);

        userData.totalAsset = money + ribao;
        userData.totalIncome = tfund;
        userData.vipLevel = parseInt(data.level || 1);
        userData.loaded = true;

        console.log('✅ 用户数据加载成功（降级方案）');
        updateUI();
      } else {
        console.warn('⚠️ 获取失败:', result.msg);
        // 不再跳转登录
        // if (result.code === 401 || result.message?.includes('未登录')) {
        //     window.location.href = 'login.html';
        // } else {
        //     updateUI();
        // }
        updateUI();
      }
    } catch (err) {
      console.error('❌ 加载出错:', err);
      updateUI();
    }
  }

  function updateUI() {
    if ($('#totalAsset')) {
      $('#totalAsset').textContent = formatMoney(userData.totalAsset);
      console.log('🖼️ 首页总资产已更新为:', $('#totalAsset').textContent);
    }
    if ($('#totalIncome')) {
      $('#totalIncome').textContent = formatMoney(userData.totalIncome);
      console.log('🖼️ 首页总收益已更新为:', $('#totalIncome').textContent);
    }
    const vipCard = $('.award-badge') || $('.vipcard');
    if (vipCard && userData.vipLevel >= 1) {
      vipCard.setAttribute('data-vip-level', userData.vipLevel);
      console.log('🎯 VIP卡片已更新为:', userData.vipLevel);
    }
  }

  function formatMoney(num) {
    return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  window.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 PROVIDENCE前台启动');
    // const token = localStorage.getItem(TOKEN_KEY);
    // console.log('🔐 当前Token:', token ? '存在(' + token.slice(0, 20) + '...)' : '不存在');
    loadUserData();
  });

  window.toggleAsset = function () {
    const assetEl = $('#totalAsset');
    const incomeEl = $('#totalIncome');
    const eyeEl = $('.eye-toggle');
    if (!assetEl || !incomeEl || !eyeEl) return;

    if (assetEl.textContent.includes('*')) {
      assetEl.textContent = formatMoney(userData.totalAsset);
      incomeEl.textContent = formatMoney(userData.totalIncome);
      eyeEl.textContent = '👁';
    } else {
      assetEl.textContent = '***,***.**';
      incomeEl.textContent = '***,***.**';
      eyeEl.textContent = '🙈';
    }
  };
})();

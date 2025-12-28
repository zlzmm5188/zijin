// =============================================
// 智能拦截器版本: 20250117-FIXED
// =============================================
console.log('%c🚀 Config.js 已加载 [版本: 20250117-FIXED]', 'color: #4CAF50; font-size: 16px; font-weight: bold');
console.log('%c📍 如果看到这条消息，说明config.js成功加载', 'color: #2196F3');
console.log('%c✅ API地址已更新为: agx.bi', 'color: #00FF00; font-weight: bold');

// ===================================
// Providence 前台通用 API 配置
// 重建时间：2025-11-12
// ===================================

const API_BASE_URL = 'https://agx.bi';

const API_CONFIG = {
  SPLASH_DOMAIN: 'https://sen.wyzyrx.cn',
  baseURL: API_BASE_URL,
  adminURL: `${API_BASE_URL}/octohoutai.php`,
  tokenKey: 'providence_token',
  timeout: 15000,
  debug: true // 临时开启调试模式，便于排查问题
};

class HttpClient {
  constructor(config) {
    this.baseURL = config.baseURL;
    this.timeout = config.timeout;
    this.debug = config.debug;
  }

  // getToken() {
  //   try {
  //     return localStorage.getItem(API_CONFIG.tokenKey) || '';
  //   } catch (err) {
  //     console.warn('getToken fail', err);
  //     return '';
  //   }
  // }

  buildHeaders(extra = {}) {
    const headers = {
      'Content-Type': 'application/json',
      ...extra
    };
    // const token = this.getToken();
    // if (token) {
    //   // 同时设置token和Authorization头，兼容不同的后端实现
    //   headers['token'] = token;
    //   headers['Authorization'] = token;
    // }
    return headers;
  }

  async request(method, url, body = null, extraHeaders = {}) {
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), this.timeout);
    const options = {
      method,
      headers: this.buildHeaders(extraHeaders),
      signal: controller.signal
    };
    if (body) options.body = JSON.stringify(body);

    let response;
    let result;
    try {
      if (this.debug) {
        console.log('[HTTP] request', method, url, body);
      }
      response = await fetch(this.baseURL + url, options);
      const text = await response.text();
      try {
        result = text ? JSON.parse(text) : {};
      } catch (err) {
        if (this.debug) {
          console.error('[HTTP] JSON parse error', err, text);
        }
        throw new Error('响应解析失败');
      }
      return {
        status: response.status,
        ok: response.ok,
        data: result
      };
    } finally {
      clearTimeout(timeoutId);
    }
  }

  async get(url, params = {}, extraHeaders = {}) {
    const query = new URLSearchParams(params).toString();
    const fullUrl = query ? `${url}?${query}` : url;
    return this.request('GET', fullUrl, null, extraHeaders);
  }

  async post(url, data = {}, extraHeaders = {}) {
    return this.request('POST', url, data, extraHeaders);
  }
}

const http = new HttpClient(API_CONFIG);

const ApiService = {
  auth: {
    // saveToken(token) {
    //   try {
    //     localStorage.setItem(API_CONFIG.tokenKey, token || '');
    //   } catch (err) {
    //     console.warn('saveToken fail', err);
    //   }
    // },
    // clearToken() {
    //   try {
    //     localStorage.removeItem(API_CONFIG.tokenKey);
    //   } catch (err) {
    //     console.warn('clearToken fail', err);
    //   }
    // },
    // ensureLogin() {
    //   const token = localStorage.getItem(API_CONFIG.tokenKey);
    //   if (!token) {
    //     window.location.href = 'login.html';
    //     return false;
    //   }
    //   return true;
    // }
  },

  ribao: {
    async getInfo() {
      const res = await http.get('/index.php/user/ribao/info');
      return res.data || {};
    },
    async transferIn(payload) {
      const res = await http.post('/index.php/user/ribao/transfer-in', payload);
      return res.data || {};
    },
    async transferOut(payload) {
      const res = await http.post('/index.php/user/ribao/transfer-out', payload);
      return res.data || {};
    },
    async getRecords(params = {}) {
      const res = await http.get('/index.php/user/ribao/records', params);
      return res.data || {};
    }
  },

  points: {
    async getBalance() {
      const res = await http.get('/index.php/user/points/balance');
      return res.data || {};
    },
    async exchange(payload) {
      const res = await http.post('/index.php/user/points/exchange', payload);
      return res.data || {};
    },
    async getLogs(params = {}) {
      const res = await http.get('/index.php/user/points/logs', params);
      return res.data || {};
    }
  },

  finance: {
    async getUserBalance() {
      // 从用户信息接口获取余额
      const res = await http.get('/index.php/user/user/index');
      return res.data || {};
    },
    async recharge(payload) {
      // 使用用户充值接口
      const res = await http.post('/index.php/user/recharge/add', payload);
      return res.data || {};
    },
    async withdraw(payload) {
      const res = await http.post('/index.php/pay/pay/withdraw', payload);
      return res.data || {};
    },
    async getBankList() {
      const res = await http.get('/index.php/pay/bank/list');
      return res.data || {};
    },
    async getUsdtInfo() {
      const res = await http.get('/index.php/pay/us/info');
      return res.data || {};
    }
  }
};

window.ApiService = ApiService;
window.httpClient = http;

// ==========================================

// 页面加载时检查 Token
// Token 失效全局拦截器 - 增强调试版本
// ==========================================
(function() {
  // 保存原始 fetch
  const originalFetch = window.fetch;

  // API白名单 - 这些API的Token错误不会触发强制退出
  const WHITELIST_APIS = [
    '/index.php/finance/ribao-',  // 日利宝相关API
    '/index.php/user/profile.php', // 个人信息
    '/index.php/user/ribao/', // 日利宝相关API（新路径）
    '/index.php/user/sign/', // 签到相关API
    '/index.php/login/', // 登录相关API（忘记密码等）
    '/index.php/user/user/index', // 用户信息API（避免误判）
  ];

  window.fetch = async function(...args) {
    try {
      // 获取请求URL（在调用前获取，以便错误处理）
      const url = typeof args[0] === 'string' ? args[0] : (args[0]?.url || '');

      // 检查 originalFetch 是否存在
      if (!originalFetch) {
        console.error('[拦截器] originalFetch 未定义，无法执行请求');
        throw new Error('Fetch拦截器初始化失败');
      }

      // 只在调试模式下输出日志
      if (API_CONFIG.debug && url) {
        console.log('%c[拦截器] 拦截到请求:', 'color: #2196F3', url);
      }

      const response = await originalFetch.apply(this, args);

      // 检查URL是否有效
      if (!url) {
        console.error('[拦截器] 无法获取请求URL:', args[0]);
        return response;
      }

      // 检查是否在白名单中（URL已在上面获取）
      const isWhitelisted = WHITELIST_APIS.some(pattern => {
        return url.includes(pattern);
      });

      if (API_CONFIG.debug) {
        console.log(`[拦截器] URL白名单状态: ${isWhitelisted ? '✅在白名单中' : '❌不在白名单中'}`);
      }

      // 检查 HTTP 状态码，排除服务器错误（502、503等）
      const httpStatus = response.status;
      if (httpStatus === 502 || httpStatus === 503 || httpStatus === 504) {
        console.error('[拦截器] 服务器错误，不跳转:', {
          url: url,
          status: httpStatus,
          statusText: response.statusText
        });
        return response;
      }

      // 克隆响应以便读取
      const clonedResponse = response.clone();

      try {
        const data = await clonedResponse.json();

        // 只在调试模式下输出日志
        if (API_CONFIG.debug) {
          console.log('[拦截器] API响应:', {
            url: url.substring(url.lastIndexOf('/') + 1),
            code: data.code,
            msg: data.msg
          });
        }

        // 检查 Token 失效 - 已注释：不再检查 token，不再跳转
        // const isAuthError = data.code === 401 || data.code === 501;

        // if (isAuthError) {
        //   if (API_CONFIG.debug) {
        //     console.log('%c[拦截器] 检测到认证错误！', 'color: #FF9800; font-weight: bold');
        //     console.log('[拦截器] 错误详情:', { code: data.code, msg: data.msg, url: url });
        //   }

        //   if (isWhitelisted) {
        //     if (API_CONFIG.debug) {
        //       console.warn('%c[拦截器] ✅ API在白名单中，跳过强制退出', 'color: #4CAF50; font-size: 14px; font-weight: bold');
        //       console.warn('[拦截器] URL:', url);
        //       console.warn('[拦截器] 错误信息:', data.msg);
        //     }
        //   } else {
        //     // 清除本地存储（使用正确的token key）
        //     localStorage.removeItem('providence_token');
        //     localStorage.removeItem('token');
        //     localStorage.removeItem('userInfo');

        //     // 显示提示
        //     if (typeof showToast === 'function') {
        //       showToast('登录已过期，请重新登录');
        //     } else {
        //       alert('登录已过期，请重新登录');
        //     }

        //     // 延迟跳转
        //     setTimeout(() => {
        //       window.location.href = '/login.html';
        //     }, 1500);
        //   }
        // } else if (data.code !== undefined && data.code !== 1) {
        //   // 其他错误（非认证错误），只记录日志，不跳转
        //   console.error('[拦截器] API返回错误（非认证错误），不跳转:', {
        //     url: url,
        //     code: data.code,
        //     msg: data.msg
        //   });
        // }

        // 只记录日志，不跳转
        if (data.code !== undefined && data.code !== 1) {
          console.error('[拦截器] API返回错误，不跳转:', {
            url: url,
            code: data.code,
            msg: data.msg
          });
        }
      } catch (e) {
        // JSON 解析失败（可能是HTML返回或其他非JSON响应），只记录日志，不跳转
        console.error('[拦截器] JSON解析失败（可能是HTML返回或非JSON响应），不跳转:', {
          url: url,
          httpStatus: httpStatus,
          error: e.message
        });
      }

      return response;
    } catch (error) {
      // 获取请求URL用于错误日志
      const url = typeof args[0] === 'string' ? args[0] : (args[0]?.url || '');

      // 如果是网络错误，提供更详细的日志
      if (error.message && (error.message.includes('Failed to fetch') || error.message.includes('NetworkError'))) {
        console.error('[拦截器] 网络请求失败:', {
          url: url,
          error: error.message,
          type: error.name || 'NetworkError',
          hint: '可能是CORS问题、网络连接问题或服务器不可达'
        });
      } else {
        console.error('[拦截器] 请求失败:', {
          url: url,
          error: error.message || error,
          type: error.name || 'UnknownError'
        });
      }

      // 重新抛出错误，让调用者处理
      throw error;
    }
  };

  // 只在调试模式下输出启动日志
  if (API_CONFIG.debug) {
    console.log('%c[拦截器] Token失效拦截器已启动（增强调试模式）', 'color: #4CAF50; font-size: 14px; font-weight: bold');
    console.log('[拦截器] 白名单:', WHITELIST_APIS);
  }
})();
// (function() {
//   // 需要登录的页面列表
//   const requireAuthPages = [
//     'profile.html',
//     'recharge.html',
//     'withdraw.html',
//     'my-investments.html',
//     'ribao.html',
//     'team-rewards.html',
//     'bank-cards.html'
//   ];

//   const currentPage = window.location.pathname.split('/').pop();

//   if (requireAuthPages.includes(currentPage)) {
//     const token = localStorage.getItem('token');

//     if (!token) {
//       console.log('[拦截器] 未登录，跳转登录页');
//       window.location.href = '/login.html';
//     }
//   }
// })();
// APIClient别名，兼容旧代码
class APIClient {
    constructor() {
        this.http = http;
        this.api = ApiService;
    }
    async get(url, params) { return this.http.get(url, params); }
    async post(url, data) { return this.http.post(url, data); }
    // getToken() { return this.http.getToken(); }
}
window.APIClient = APIClient;

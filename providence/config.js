(() => {
  const DEFAULT_API_BASE = 'https://apis.copla.top';
  const DEFAULT_ADMIN_BASE = 'https://houtai.copla.top';
  const DEFAULT_SPLASH_DOMAIN = 'https://sen.wyzyrx.cn';
  const LEGACY_API_HOSTS = [
    'https://apis.copla.top',
    'http://apis.copla.top',
    'https://api.frevix.top',
    'http://api.frevix.top',
    'https://v2.abcmall.one',
    'https://v2api.hemlx.com'
  ];
  const LEGACY_TOKEN_KEYS = ['providence_token', 'token', 'auth_token'];

  console.log('%c🚀 Config.js 已加载 [Unified API Bridge]', 'color: #4CAF50; font-size: 16px; font-weight: bold');

  function sanitizeBase(url) {
    if (!url || typeof url !== 'string') {
      return '';
    }
    return url.trim().replace(/\/$/, '');
  }

  function readMetaContent(name) {
    if (typeof document === 'undefined') {
      return '';
    }
    const tag = document.querySelector(`meta[name="${name}"]`);
    return tag?.content?.trim() || '';
  }

  function readCurrentScriptDataset(key) {
    if (typeof document === 'undefined') {
      return '';
    }
    const currentScript = document.currentScript;
    if (currentScript && currentScript.dataset && currentScript.dataset[key]) {
      return currentScript.dataset[key].trim();
    }
    return '';
  }

  function resolveBaseURL() {
    const candidates = [
      window.API_ENV?.baseURL,
      window.__APP_CONFIG?.apiBase,
      readMetaContent('api-base'),
      readCurrentScriptDataset('apiBase'),
      window.API_BASE,
      DEFAULT_API_BASE
    ];
    for (const candidate of candidates) {
      if (candidate && typeof candidate === 'string') {
        const sanitized = sanitizeBase(candidate);
        if (sanitized) {
          return sanitized;
        }
      }
    }
    return DEFAULT_API_BASE;
  }

  function resolveAdminURL(baseURL) {
    const candidates = [
      window.API_ENV?.adminURL,
      window.__APP_CONFIG?.adminURL,
      readMetaContent('admin-base'),
      readCurrentScriptDataset('adminBase')
    ];
    for (const candidate of candidates) {
      if (candidate && typeof candidate === 'string') {
        const sanitized = sanitizeBase(candidate);
        if (sanitized) {
          return sanitized;
        }
      }
    }
    if (baseURL.includes('api.')) {
      return sanitizeBase(baseURL.replace('api.', 'houtai.'));
    }
    return DEFAULT_ADMIN_BASE;
  }

  function resolveBoolean(value, fallback) {
    if (value === undefined || value === null) {
      return fallback;
    }
    if (typeof value === 'string') {
      return value === 'true' || value === '1';
    }
    return Boolean(value);
  }

  const BASE_URL = resolveBaseURL();

  const API_CONFIG = {
    SPLASH_DOMAIN: DEFAULT_SPLASH_DOMAIN,
    baseURL: BASE_URL,
    adminURL: resolveAdminURL(BASE_URL),
    tokenKey: 'providence_token',
    timeout: Number(window.API_ENV?.timeout || 15000),
    debug: resolveBoolean(window.API_ENV?.debug, true)
  };

  const DEFAULT_AI_CONFIG = {
    mode: 'rules_only',
    backend: { enable: false },
    knowledge: { enable: false, priority: 'low' },
    openai: { apiKey: '', model: 'gpt-4o-mini' }
  };

  const AI_CONFIG = window.AI_CONFIG
    ? { ...DEFAULT_AI_CONFIG, ...window.AI_CONFIG }
    : DEFAULT_AI_CONFIG;
  window.AI_CONFIG = AI_CONFIG;

  console.log('%c📍 API基地址:', 'color: #2196F3', API_CONFIG.baseURL);

  const TokenStorage = {
    get() {
      try {
        const existing = localStorage.getItem(API_CONFIG.tokenKey);
        if (existing) {
          return existing;
        }
        for (const key of LEGACY_TOKEN_KEYS) {
          const legacy = localStorage.getItem(key);
          if (legacy) {
            localStorage.setItem(API_CONFIG.tokenKey, legacy);
            return legacy;
          }
        }
      } catch (err) {
        console.warn('[TokenStorage] 读取失败:', err.message);
      }
      return '';
    },
    set(token) {
      if (!token) {
        return;
      }
      try {
        localStorage.setItem(API_CONFIG.tokenKey, token);
        for (const key of LEGACY_TOKEN_KEYS) {
          localStorage.setItem(key, token);
        }
      } catch (err) {
        console.warn('[TokenStorage] 保存失败:', err.message);
      }
    },
    clear() {
      try {
        localStorage.removeItem(API_CONFIG.tokenKey);
        for (const key of LEGACY_TOKEN_KEYS) {
          localStorage.removeItem(key);
        }
      } catch (err) {
        console.warn('[TokenStorage] 清除失败:', err.message);
      }
    }
  };

  class HttpClient {
    constructor(config) {
      this.baseURL = sanitizeBase(config.baseURL || DEFAULT_API_BASE);
      this.timeout = config.timeout || 15000;
      this.debug = config.debug;
    }

    getToken() {
      return TokenStorage.get();
    }

    setToken(token) {
      TokenStorage.set(token);
    }

    clearToken() {
      TokenStorage.clear();
    }

    buildHeaders(extraHeaders = {}, skipAuth = false) {
      const headers = new Headers({ 'Content-Type': 'application/json' });
      const source = extraHeaders instanceof Headers ? extraHeaders : new Headers(extraHeaders);
      source.forEach((value, key) => headers.set(key, value));
      if (!skipAuth && !headers.has('token')) {
        const token = this.getToken();
        if (token) {
          headers.set('token', token);
          headers.set('Authorization', token);
        }
      }
      return headers;
    }

    resolveUrl(url) {
      if (!url) {
        return this.baseURL;
      }
      if (/^https?:/i.test(url)) {
        return normalizeApiUrl(url);
      }
      if (url.startsWith('/')) {
        return `${this.baseURL}${url}`;
      }
      if (url.startsWith('index.php')) {
        return `${this.baseURL}/${url}`;
      }
      return `${this.baseURL}/${url.replace(/^\.\//, '')}`;
    }

    async request(method, url, body = null, headers = {}, options = {}) {
      const skipAuth = Boolean(options.skipAuth);
      const endpoint = this.resolveUrl(url);
      const finalHeaders = this.buildHeaders(headers, skipAuth);
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), this.timeout);
      const fetchOptions = {
        method,
        headers: finalHeaders,
        signal: controller.signal,
        mode: 'cors',
        credentials: 'include'
      };

      if (body && method !== 'GET' && method !== 'HEAD') {
        const contentType = finalHeaders.get('Content-Type') || '';
        fetchOptions.body = contentType.includes('application/json') && typeof body !== 'string'
          ? JSON.stringify(body)
          : body;
      }

      if (this.debug) {
        console.log('[HTTP]', method, endpoint, body);
      }

      try {
        const response = await fetch(endpoint, fetchOptions);
        const text = await response.text();
        let parsed = null;
        if (text) {
          try {
            parsed = JSON.parse(text);
          } catch (err) {
            console.error('[HTTP] JSON解析失败:', err.message, text.substring(0, 200));
          }
        }

        const payload = parsed || {};
        const success = (
          response.ok && (payload.code === 1 || payload.code === 200)
        ) || payload.success === true;
        const message = payload.msg || payload.message || (success ? 'success' : `HTTP ${response.status}`);
        const data = payload.data ?? payload.result ?? null;

        return {
          success,
          status: response.status,
          data,
          msg: message,
          raw: payload
        };
      } catch (error) {
        console.error('[HTTP] 请求失败:', error);
        return {
          success: false,
          status: 0,
          data: null,
          msg: error.message || '网络错误',
          error
        };
      } finally {
        clearTimeout(timeoutId);
      }
    }

    async get(url, params = {}, options = {}) {
      const query = new URLSearchParams(params).toString();
      const finalUrl = query ? `${url}?${query}` : url;
      return this.request('GET', finalUrl, null, options.headers || {}, options);
    }

    async post(url, data = {}, options = {}) {
      return this.request('POST', url, data, options.headers || {}, options);
    }

    async put(url, data = {}, options = {}) {
      return this.request('PUT', url, data, options.headers || {}, options);
    }

    async delete(url, options = {}) {
      return this.request('DELETE', url, null, options.headers || {}, options);
    }
  }

  const http = new HttpClient(API_CONFIG);

  const API_ENDPOINTS = {
    auth: {
      login: '/index.php/login/account',
      legacyLogin: '/index.php/login/login/account',
      register: '/index.php/login/reg/account'
    },
    user: {
      info: '/index.php/user/user/index',
      invite: '/index.php/user/user/invite'
    },
    fund: {
      list: '/index.php/fund/project/all',
      detail: '/index.php/fund/project/detail',
      invest: '/index.php/fund/project/add'
    },
    order: {
      list: '/index.php/user/order/list'
    },
    finance: {
      recharge: '/index.php/user/recharge/add',
      withdraw: '/index.php/pay/pay/withdraw',
      bankCards: '/index.php/pay/bank/list',
      usdtInfo: '/index.php/pay/us/info'
    },
    points: {
      balance: '/index.php/user/points/balance',
      exchange: '/index.php/user/points/exchange',
      logs: '/index.php/user/points/logs'
    },
    ribao: {
      info: '/index.php/user/ribao/info',
      transferIn: '/index.php/user/ribao/transfer-in',
      transferOut: '/index.php/user/ribao/transfer-out',
      records: '/index.php/user/ribao/records'
    },
    team: {
      info: '/index.php/user/team/team',
      rewardsStatus: '/index.php/user/team/rewards_status',
      claimReward: '/index.php/user/team/claim_reward'
    },
    trial: {
      claim: '/index.php/user/trial/claim'
    },
    sign: {
      info: '/index.php/user/sign/info',
      sign: '/index.php/user/sign/sign'
    },
    ai: {
      chat: '/index.php/ai/chat'
    },
    pay: {
      bankList: '/index.php/pay/bank/list',
      usdtCheck: '/index.php/pay/usdt/check',
      currencyExchange: '/index.php/pay/currency-exchange'
    }
  };

  const API = {
    user: {
      getInfo() {
        return http.get(API_ENDPOINTS.user.info);
      },
      login(username, password) {
        return http.post(API_ENDPOINTS.auth.login, { username, password }, { skipAuth: true });
      },
      register(payload) {
        return http.post(API_ENDPOINTS.auth.register, payload, { skipAuth: true });
      },
      invite() {
        return http.get(API_ENDPOINTS.user.invite);
      }
    },
    fund: {
      getList(params = {}) {
        return http.get(API_ENDPOINTS.fund.list, params);
      },
      getDetail(id) {
        return http.get(`${API_ENDPOINTS.fund.detail}?id=${encodeURIComponent(id ?? '')}`);
      },
      invest(payload) {
        return http.post(API_ENDPOINTS.fund.invest, payload);
      }
    },
    order: {
      getList(params = {}) {
        return http.get(API_ENDPOINTS.order.list, params);
      }
    },
    finance: {
      recharge(payload) {
        return http.post(API_ENDPOINTS.finance.recharge, payload);
      },
      withdraw(payload) {
        return http.post(API_ENDPOINTS.finance.withdraw, payload);
      },
      getBankCards() {
        return http.get(API_ENDPOINTS.finance.bankCards);
      },
      getUsdtInfo() {
        return http.get(API_ENDPOINTS.finance.usdtInfo);
      }
    },
    points: {
      getBalance() {
        return http.get(API_ENDPOINTS.points.balance);
      },
      exchange(payload) {
        return http.post(API_ENDPOINTS.points.exchange, payload);
      },
      getLogs(params = {}) {
        return http.get(API_ENDPOINTS.points.logs, params);
      }
    },
    ribao: {
      getInfo() {
        return http.get(API_ENDPOINTS.ribao.info);
      },
      getRecords(params = {}) {
        return http.get(API_ENDPOINTS.ribao.records, params);
      },
      transferIn(payload) {
        return http.post(API_ENDPOINTS.ribao.transferIn, payload);
      },
      transferOut(payload) {
        return http.post(API_ENDPOINTS.ribao.transferOut, payload);
      }
    },
    team: {
      getInfo() {
        return http.get(API_ENDPOINTS.team.info);
      },
      getRewardsStatus() {
        return http.get(API_ENDPOINTS.team.rewardsStatus);
      },
      claimReward(payload = {}) {
        return http.post(API_ENDPOINTS.team.claimReward, payload);
      }
    },
    trial: {
      claim(payload = {}) {
        return http.post(API_ENDPOINTS.trial.claim, payload);
      }
    },
    sign: {
      info() {
        return http.get(API_ENDPOINTS.sign.info);
      },
      sign() {
        return http.post(API_ENDPOINTS.sign.sign, {});
      }
    },
    ai: {
      chat(payload) {
        return http.post(API_ENDPOINTS.ai.chat, payload);
      }
    }
  };

  const ApiService = {
    ribao: {
      async getInfo() {
        const res = await API.ribao.getInfo();
        if (!res.success) {
          throw new Error(res.msg || '获取日利宝信息失败');
        }
        return res.data || {};
      },
      async transferIn(payload) {
        const res = await API.ribao.transferIn(payload);
        if (!res.success) {
          throw new Error(res.msg || '转入失败');
        }
        return res.data || {};
      },
      async transferOut(payload) {
        const res = await API.ribao.transferOut(payload);
        if (!res.success) {
          throw new Error(res.msg || '转出失败');
        }
        return res.data || {};
      },
      async getRecords(params = {}) {
        const res = await API.ribao.getRecords(params);
        if (!res.success) {
          throw new Error(res.msg || '获取记录失败');
        }
        return res.data || {};
      }
    },
    points: {
      async getBalance() {
        const res = await API.points.getBalance();
        if (!res.success) {
          throw new Error(res.msg || '获取积分失败');
        }
        return res.data || {};
      },
      async exchange(payload) {
        const res = await API.points.exchange(payload);
        if (!res.success) {
          throw new Error(res.msg || '兑换失败');
        }
        return res.data || {};
      },
      async getLogs(params = {}) {
        const res = await API.points.getLogs(params);
        if (!res.success) {
          throw new Error(res.msg || '获取积分日志失败');
        }
        return res.data || {};
      }
    },
    finance: {
      async getUserBalance() {
        const res = await API.user.getInfo();
        if (!res.success) {
          throw new Error(res.msg || '获取余额失败');
        }
        return res.data || {};
      },
      async recharge(payload) {
        return API.finance.recharge(payload);
      },
      async withdraw(payload) {
        return API.finance.withdraw(payload);
      },
      async getBankList() {
        return API.finance.getBankCards();
      },
      async getUsdtInfo() {
        return API.finance.getUsdtInfo();
      }
    }
  };

  function normalizeApiUrl(url) {
    if (!url) {
      return url;
    }
    const trimmed = url.trim();
    if (trimmed.startsWith('/index.php/')) {
      return `${API_CONFIG.baseURL}${trimmed}`;
    }
    if (trimmed.startsWith('index.php/')) {
      return `${API_CONFIG.baseURL}/${trimmed}`;
    }
    const legacyHost = LEGACY_API_HOSTS.find((host) => trimmed.startsWith(host));
    if (legacyHost) {
      return `${API_CONFIG.baseURL}${trimmed.substring(legacyHost.length)}`;
    }
    return trimmed;
  }

  function shouldAttachToken(url) {
    if (!url) {
      return false;
    }
    if (url.includes('/login/') || url.includes('/register/')) {
      return false;
    }
    return url.startsWith(API_CONFIG.baseURL);
  }

  function normalizeHeaders(headersInput) {
    if (!headersInput) {
      return new Headers();
    }
    if (headersInput instanceof Headers) {
      return new Headers(headersInput);
    }
    return new Headers(headersInput);
  }

  function setupFetchInterceptor() {
    if (typeof window === 'undefined' || typeof window.fetch !== 'function') {
      return;
    }
    const originalFetch = window.fetch.bind(window);
    window.fetch = function(input, init = {}) {
      if (typeof input !== 'string') {
        return originalFetch(input, init);
      }
      const normalizedInit = { ...init };
      const skipAuth = Boolean(normalizedInit.skipAuth);
      if ('skipAuth' in normalizedInit) {
        delete normalizedInit.skipAuth;
      }
      const headers = normalizeHeaders(normalizedInit.headers);
      const rewrittenUrl = normalizeApiUrl(input);
      if (shouldAttachToken(rewrittenUrl) && !skipAuth) {
        if (!headers.has('token')) {
          const token = TokenStorage.get();
          if (token) {
            headers.set('token', token);
            headers.set('Authorization', token);
          }
        }
      }
      normalizedInit.headers = headers;
      return originalFetch(rewrittenUrl, normalizedInit);
    };
    console.log('%c🔒 Fetch 拦截器已启用', 'color: #9C27B0');
  }

  setupFetchInterceptor();

  class APIClient {
    constructor() {
      this.http = http;
      this.api = API;
    }

    async get(url, params) {
      return this.http.get(url, params);
    }

    async post(url, data) {
      return this.http.post(url, data);
    }

    getToken() {
      return this.http.getToken();
    }
  }

  window.API_CONFIG = API_CONFIG;
  window.API_ENDPOINTS = API_ENDPOINTS;
  window.httpClient = http;
  window.API = API;
  window.ApiService = ApiService;
  window.APIClient = APIClient;
  window.getApiBaseURL = () => API_CONFIG.baseURL;
  window.getAuthToken = () => TokenStorage.get();
  window.setAuthToken = (token) => TokenStorage.set(token);
  window.clearAuthToken = () => TokenStorage.clear();
})();

// 登录页面 - 统一使用config.js的API封装
// 确保在HTML中已加载config.js: <script src="config.js"></script>
// 等待 config.js 加载完成
function waitForConfig() {
    return new Promise((resolve) => {
        // 检查 window.API_CONFIG 是否存在且包含 baseURL
        if (window.API_CONFIG && window.API_CONFIG.baseURL) {
            console.log('[登录] API_CONFIG 已加载:', window.API_CONFIG.baseURL);
            resolve();
            return;
        }

        // 每 50ms 检查一次
        const check = setInterval(() => {
            if (window.API_CONFIG && window.API_CONFIG.baseURL) {
                clearInterval(check);
                console.log('[登录] API_CONFIG 已加载:', window.API_CONFIG.baseURL);
                resolve();
            }
        }, 50);

        // 3 秒后超时，仍然允许继续，避免死循环
        setTimeout(() => {
            clearInterval(check);
            if (window.API_CONFIG && window.API_CONFIG.baseURL) {
                console.log('[登录] API_CONFIG 已加载（超时后）:', window.API_CONFIG.baseURL);
            } else {
                console.warn('[登录] API_CONFIG 加载超时，使用默认配置');
            }
            resolve();
        }, 3000);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initPage();
    loadSavedCredentials();
});

function loadSavedCredentials() {
    const savedUsername = localStorage.getItem('saved_username');
    const savedPassword = localStorage.getItem('saved_password');
    const rememberMe = localStorage.getItem('remember_me') === 'true';

    if (savedUsername) {
        const usernameEl = document.getElementById('loginUsername');
        if (usernameEl) usernameEl.value = savedUsername;
    }

    if (rememberMe && savedPassword) {
        const passwordEl = document.getElementById('loginPassword');
        if (passwordEl) passwordEl.value = savedPassword;
        console.log('[登录] 已自动填充保存的账号密码');
    }
}

function saveCredentials(username, password, remember) {
    if (remember) {
        localStorage.setItem('saved_username', username);
        localStorage.setItem('saved_password', password);
        localStorage.setItem('remember_me', 'true');
        console.log('[登录] 已保存账号密码');
    } else {
        localStorage.removeItem('saved_password');
        localStorage.setItem('remember_me', 'false');
        console.log('[登录] 已清除保存的密码');
    }
}

function initPage() {
    // 回车登录
    document.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            handleLogin();
        }
    });
}

// 账号登录
async function handleLogin() {
    // 等待 config.js 完全加载后再执行登录流程
    await waitForConfig();

    if (window.__loginLoading) return;
    window.__loginLoading = true;
    const btn = document.getElementById('btnLogin');
    if (btn) { btn.classList.add('btn-loading'); btn.disabled = true; }

    const usernameEl = document.getElementById('loginUsername');
    const passwordEl = document.getElementById('loginPassword');
    const rememberEl = document.getElementById('rememberPassword');

    // 清除HTML5验证错误
    if (usernameEl) {
        usernameEl.setCustomValidity('');
        usernameEl.reportValidity();
    }
    if (passwordEl) {
        passwordEl.setCustomValidity('');
        passwordEl.reportValidity();
    }

    const username = usernameEl ? usernameEl.value.trim() : '';
    const password = passwordEl ? passwordEl.value.trim() : '';
    const remember = rememberEl ? rememberEl.checked : false;

    if (!username) {
        showToast('请输入账号');
        if (btn) { btn.classList.remove('btn-loading'); btn.disabled = false; }
        window.__loginLoading = false;
        return;
    }

    if (!password) {
        showToast('请输入密码');
        if (btn) { btn.classList.remove('btn-loading'); btn.disabled = false; }
        window.__loginLoading = false;
        return;
    }

    try {
        // 直接使用fetch（稳定可靠）
        // 修复：使用正确的 API 域名和路径
        const API_BASE = window.API_CONFIG?.baseURL || 'https://apis.copla.top';
        const apiUrl = API_BASE + '/index.php/login/account';

        console.log('[登录] API地址:', apiUrl);
        console.log('[登录] API_CONFIG状态:', typeof window.API_CONFIG !== 'undefined' ? '已加载' : '未加载');

        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });

        let text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('登录返回(非JSON):', text.substring(0, 200));
            // 检查是否是HTML错误页面
            if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                showToast('服务器返回错误页面，请检查网络或联系管理员');
            } else {
                showToast('服务器响应格式错误，请稍后再试');
            }
            return;
        }

        // 统一格式：code: 1 或 200 表示成功
        if ((data.code === 1 || data.code === 200) && data.data) {
            // 保存 token 和用户信息
            try {
                // API返回格式：{ code: 1, data: { token: '...', user: { id: ..., username: ... } } }
                const token = data.data.token || data.data.access_token;
                if (token) {
                    localStorage.setItem('providence_token', token);
                    console.log('[登录] Token已保存:', token.substring(0, 20) + '...');
                } else {
                    console.warn('[登录] API响应中未找到token字段');
                }

                // 保存用户信息
                const userId = data.data.user?.id || data.data.user_id || data.data.id;
                if (userId) {
                    localStorage.setItem('providence_user_id', userId);
                }

                const userName = data.data.user?.username || data.data.username || username;
                if (userName) {
                    localStorage.setItem('providence_user_name', userName);
                }

                console.log('[登录] 用户信息已保存:', { userId, userName });
            } catch (e) {
                console.error('[登录] 保存登录信息失败:', e);
            }

            // 保存账号密码（如果勾选了记住密码）
            if (remember) {
                localStorage.setItem('saved_username', username);
                localStorage.setItem('saved_password', password);
                localStorage.setItem('remember_password', 'true');
            } else {
                localStorage.removeItem('saved_username');
                localStorage.removeItem('saved_password');
                localStorage.removeItem('remember_password');
            }

            // 显示成功提示
            showToast('登录成功');

            // 延迟跳转，确保token已保存
            setTimeout(() => {
                console.log('[登录] 跳转到首页，Token:', localStorage.getItem('providence_token') ? '已保存' : '未保存');
                window.location.href = 'index.html';
            }, 300);
        } else {
            showToast(data.message || data.msg || '账号或密码错误');
        }
    } catch (error) {
        console.error('登录错误:', error);
        showToast('网络错误，请重试');
    } finally {
        if (btn) { btn.classList.remove('btn-loading'); btn.disabled = false; }
        window.__loginLoading = false;
    }
}

// showToast函数已移到ios-toast.js，使用iOS风格弹窗

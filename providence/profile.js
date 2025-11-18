// 个人中心页面 - 统一使用config.js的API封装
// 确保在HTML中已加载config.js: <script src="config.js"></script>

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

let userData = {
    token: localStorage.getItem('providence_token') || '',
    id: 0,
    username: '',
    mobile: '',
    realname: '',
    money: 0,
    ribao: 0,
    profit: 0,
    recharges: 0,
    withdraws: 0,
    level: 1,
    invite_code: '',
    projects_count: 0,
    usdt_money: 0,
    usdt_ribao: 0
};

// 页面加载
document.addEventListener('DOMContentLoaded', function () {
    // 重新读取token（防止页面加载时token还未保存）
    // 多次尝试读取，确保能获取到token
    let token = localStorage.getItem('providence_token') || localStorage.getItem('token') || '';
    if (!token) {
        // 如果第一次读取失败，延迟再试一次（可能是从登录页刚跳转过来）
        setTimeout(() => {
            token = localStorage.getItem('providence_token') || localStorage.getItem('token') || '';
            userData.token = token;
            if (token) {
                console.log('[页面加载] ✓ Token延迟加载成功');
            } else {
                console.log('[页面加载] ⚠️ Token未找到，尝试加载用户数据验证');
            }
            initPage();
            // 即使token为空，也尝试加载用户数据，让API验证
            loadUserData().catch(err => {
                console.error('[页面加载] 加载用户数据失败:', err);
                // 只有在明确没有token时才跳转，有token时不跳转（可能是网络问题）
                if (!userData.token) {
                    console.log('[页面加载] ⚠️ 无Token，延迟检查');
                    setTimeout(() => {
                        if (!userData.token) {
                            window.location.href = 'login.html';
                        }
                    }, 3000);
                } else {
                    console.log('[页面加载] ⚠️ 有Token但加载失败，可能是网络问题，不跳转');
                }
            });
            bindEvents();
        }, 300);
    } else {
        userData.token = token;
        console.log('[页面加载] ✓ Token已加载');
        initPage();
        loadUserData();
        bindEvents();
    }
});

// 初始化
function initPage() {
    // 再次检查token（双重保障）
    // 延迟检查，给token保存留出时间（从其他页面跳转过来时）
    // 不在这里跳转，让loadUserData来处理（如果API返回未登录错误再跳转）
    setTimeout(() => {
        const token = localStorage.getItem('providence_token') || localStorage.getItem('token') || '';
        if (token) {
            // 更新userData中的token
            userData.token = token;
            console.log('[初始化] ✓ Token已加载:', token.substring(0, 20) + '...');
        } else {
            console.log('[初始化] ⚠️ Token未找到，等待loadUserData验证');
            // 不立即跳转，让loadUserData通过API验证后再决定
        }
    }, 200);
}

// 加载用户数据
async function loadUserData() {
    console.log('[用户数据] 开始加载...');
    try {
        await waitForAPI();

        // 优先使用统一API封装（ApiService）
        let response = null;
        if (window.ApiService && window.ApiService.finance && window.ApiService.finance.getUserBalance) {
            try {
                const result = await window.ApiService.finance.getUserBalance();
                if (result && (result.money !== undefined || result.balance !== undefined)) {
                    // ApiService返回的是数据对象，需要包装成标准格式
                    response = { code: 1, msg: 'ok', data: result };
                }
            } catch (e) {
                console.warn('[用户数据] ApiService调用失败，使用降级方案:', e);
            }
        }

        // 降级方案：使用httpClient
        if (!response && window.httpClient) {
            try {
                const result = await window.httpClient.get('/index.php/user/user/index');
                if (result && result.data) {
                    // httpClient返回格式: {status, ok, data: {code, msg, data}}
                    if (result.data.code === 1 && result.data.data) {
                        response = result.data; // {code: 1, msg: 'ok', data: {...}}
                    } else {
                        response = result.data; // 可能是错误响应
                    }
                }
            } catch (e) {
                console.warn('[用户数据] httpClient调用失败，使用fetch:', e);
            }
        }

        // 降级方案：直接使用fetch
        if (!response) {
            const API_BASE = window.API_CONFIG?.baseURL || 'https://apis.copla.top';
            const res = await fetch(API_BASE + '/index.php/user/user/index', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json', 'token': userData.token }
            });
            const text = await res.text();
            try {
                response = JSON.parse(text);
            } catch (e) {
                console.error('[用户数据] ✗ 解析失败:', text.substring(0, 100));
                response = { code: 0, msg: '网络错误', data: null };
            }
        }

        console.log('[用户数据] API响应:', response);

        if (response.code === 1 && response.data) {
            const data = response.data;
            console.log('[用户数据] 原始数据:', data);
            userData = {
                ...userData,
                id: data.uid || data.id || data.user_id || 0, // 优先使用uid（8位数字ID）
                uid: data.uid || data.id || data.user_id || 0, // 保存uid字段
                username: data.username || data.mobile || data.phone || data.realname || 'User',
                mobile: data.mobile || data.phone || '',
                realname: data.realname || data.username || data.mobile || 'Providence',
                money: parseFloat(data.money || 0),
                ribao: parseFloat(data.ribao || 0),
                profit: parseFloat(data.tfund || 0),
                profit_cny: parseFloat(data.profit_cny || data.profit || data.tfund || 0), // 人民币收益
                profit_usdt: parseFloat(data.profit_usdt || data.usdt_profit || 0), // USDT收益
                recharges: parseFloat(data.recharges || 0),
                withdraws: parseFloat(data.withdraws || 0),
                level: parseInt(data.level || 1),
                invite_code: data.invite || '',
                projects_count: parseInt(data.projects_count || 0),
                avatar: data.avatar || data.headimgurl || '',
                usdt_money: parseFloat(data.usdt_money || data.usdt || data.usdt_balance || 0),
                usdt_ribao: parseFloat(data.usdt_ribao || data.usdt_ribao_balance || 0),
                team_count: parseInt(data.team_count || data.team_members || data.team_num || 0), // 团队人数
                team_total_invest: parseFloat(data.team_total_invest || data.team_invest || data.team_investment || 0) // 团队总投资
            };

            console.log('[用户数据] ✓ 加载成功:', userData);
            updateUI();
            // updateUpgradeProgress 现在是异步函数，需要await或单独调用
            updateUpgradeProgress().catch(err => {
                console.error('[用户数据] VIP进度加载失败:', err);
            });
            loadProjectsCount();
        } else {
            console.error('[用户数据] ✗ API返回格式错误:', response);

            // 只有在明确是认证错误且没有token时才跳转
            // 如果有token但返回错误，可能是网络问题或服务器问题，不跳转
            const isAuthError = response.code === 501 ||
                response.code === -1 ||
                response.msg === 'Token验证失败' ||
                response.msg === '请先登录' ||
                response.msg === '未登录' ||
                response.msg === 'token无效' ||
                response.msg === 'token过期';

            if (isAuthError && !userData.token) {
                // 没有token且明确是认证错误，才跳转
                console.log('[用户数据] ⚠️ 无Token且认证失败，跳转到登录页');
                localStorage.removeItem('providence_token');
                localStorage.removeItem('token');
                showToast('登录已过期，请重新登录');
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 1500);
                return;
            } else if (isAuthError && userData.token) {
                // 有token但认证失败，可能是token过期，清除token但不立即跳转，让用户继续使用
                console.warn('[用户数据] ⚠️ Token可能已过期，但保留当前状态');
                // 不清除token，不跳转，只显示提示
                showToast('登录状态异常，请稍后重试');
                updateUI();
                return;
            }

            // 如果只是其他错误，不跳转，显示错误提示
            showToast(response.msg || '加载用户数据失败');
            updateUI();
        }
    } catch (error) {
        console.error('[用户数据] ✗ 加载失败:', error);

        // 网络错误或API错误，不跳转，只显示提示
        // 只有在明确是认证错误且没有token时才跳转
        const errorMsg = error.message || error.toString() || '';
        const isAuthError = errorMsg.includes('401') ||
            errorMsg.includes('403') ||
            errorMsg.includes('Token验证失败') ||
            errorMsg.includes('未登录') ||
            errorMsg.includes('请先登录');

        // 只有在没有token且明确是认证错误时才跳转
        if (!userData.token && isAuthError) {
            console.log('[用户数据] ⚠️ 无Token且认证失败，跳转到登录页');
            setTimeout(() => {
                if (!userData.token) {
                    window.location.href = 'login.html';
                }
            }, 2000);
            return;
        }

        // 其他情况（有token但网络错误、有token但API错误等）都不跳转
        // 只显示提示，让用户继续使用
        if (userData.token) {
            console.log('[用户数据] ⚠️ 有Token但加载失败，可能是网络问题，不跳转');
            showToast('网络连接失败，请重试');
            updateUI();
        } else {
            // 没有token且不是明确的认证错误，延迟检查
            console.log('[用户数据] ⚠️ 无Token，延迟检查');
            setTimeout(() => {
                if (!userData.token) {
                    window.location.href = 'login.html';
                }
            }, 3000);
        }
    }
}

// 获取投资项目数量（完全静默失败）
async function loadProjectsCount() {
    // 检查用户是否登录
    if (!userData.token) {
        userData.projects_count = 0;
        return;
    }

    try {
        await waitForAPI();

        // 优先使用统一API封装
        let response = null;
        if (window.API && window.API.order && window.API.order.getList) {
            const result = await window.API.order.getList({ status: 0, page: 1, limit: 100 });
            if (result.success && result.data) {
                response = { code: 200, msg: 'ok', data: result.data };
            }
        }

        // 降级方案
        if (!response) {
            const API_BASE = window.API_CONFIG?.baseURL || 'https://apis.copla.top';
            const params = new URLSearchParams({ status: 0, page: 1, limit: 100 });
            try {
                const res = await fetch(API_BASE + '/index.php/fund/project/all?' + params.toString(), {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json', 'token': userData.token }
                });

                console.log('[项目列表] 请求状态:', res.status, res.statusText);

                // 先读取响应文本（响应体只能读取一次）
                const text = await res.text();

                // 检查HTTP状态码
                if (!res.ok) {
                    console.warn('[API] /index.php/user/project/list HTTP错误:', res.status);
                    console.warn('[API] 错误响应内容:', text.substring(0, 500));

                    // 尝试解析JSON错误响应
                    try {
                        const errorData = JSON.parse(text);
                        if (errorData.msg) {
                            console.warn('[API] 错误消息:', errorData.msg);
                            // 如果是SQL错误，静默处理（后端问题）
                            if (errorData.msg.includes('SQLSTATE') || errorData.msg.includes('ambiguous') || errorData.msg.includes('Column')) {
                                console.warn('[API] SQL错误，静默失败（后端需要修复）');
                            }
                        }
                    } catch (e) {
                        // 不是JSON，检查是否是HTML错误页面
                        if (text.includes('系统发生错误') || text.includes('ThinkPHP') || text.includes('Exception')) {
                            console.warn('[API] /index.php/user/project/list 返回HTML错误页面，静默失败');
                        }
                    }
                    userData.projects_count = 0;
                    return;
                }

                // 检查是否是HTML错误页面
                if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html') || text.includes('系统发生错误')) {
                    console.warn('[API] /index.php/user/project/list 返回HTML错误页面，静默失败');
                    userData.projects_count = 0;
                    return;
                }

                try {
                    response = JSON.parse(text);
                } catch (e) {
                    console.warn('[API] /index.php/user/project/list JSON解析失败，静默失败');
                    userData.projects_count = 0;
                    return;
                }
            } catch (error) {
                console.warn('[API] /index.php/user/project/list 请求失败:', error.message);
                userData.projects_count = 0;
                return;
            }
        }

        if (response.code === 1 && response.data) {
            // 从API获取实际项目数量
            if (response.data.list && Array.isArray(response.data.list)) {
                userData.projects_count = response.data.list.length;
            } else if (response.data.total !== undefined) {
                userData.projects_count = parseInt(response.data.total || 0);
            } else if (response.data.count !== undefined) {
                userData.projects_count = parseInt(response.data.count || 0);
            } else {
                userData.projects_count = 0;
            }

            // 更新UI显示
            const investSubEl = document.getElementById('investSub');
            if (investSubEl && userData.projects_count > 0) {
                investSubEl.textContent = userData.projects_count + '个项目进行中';
            }

            console.log('[项目数量] ✓ 获取成功:', userData.projects_count);
        }
    } catch (error) {
        // 完全静默失败，不输出错误，不影响用户体验
        userData.projects_count = 0;
    }
}

// 更新UI
function updateUI() {
    // 更新头部信息（使用首页样式）
    const userNameBrand = document.getElementById('userNameBrand');
    const userIdBrand = document.getElementById('userIdBrand');
    const userVipBadge = document.getElementById('userVipBadge');
    const avatarLarge = document.querySelector('.user-avatar-large');
    const avatarText = document.querySelector('.avatar-text-large');

    if (userNameBrand) {
        const name = userData.realname || userData.nickname || userData.username || 'PROVIDENCE';
        userNameBrand.textContent = name;
        userNameBrand.dataset.value = name;
        // 添加隐藏功能
        userNameBrand.dataset.visible = 'true';
    }

    if (userIdBrand) {
        const id = userData.id || '10847392';
        userIdBrand.textContent = `ID: ${id}`;
        userIdBrand.dataset.value = `ID: ${id}`;
        userIdBrand.dataset.idValue = id;
        // 添加隐藏功能
        userIdBrand.dataset.visible = 'true';
    }

    // 更新账号和ID显示（新增）- 确保数据正确显示
    const userNameDisplay = document.getElementById('userNameDisplay');
    const userIdDisplay = document.getElementById('userIdDisplay');

    if (userNameDisplay) {
        // 优先使用username，然后是mobile，最后是realname，确保有值
        let name = userData.username || userData.mobile || userData.realname || '';
        // 如果所有字段都为空，使用默认值
        if (!name || name === 'User' || name === 'Providence') {
            // 尝试从localStorage获取
            const storedName = localStorage.getItem('providence_user_name');
            if (storedName) {
                name = storedName;
            } else {
                // 最后使用uid作为显示
                name = userData.uid ? `用户${userData.uid}` : '用户';
            }
        }
        console.log('[更新UI] 账号显示:', name, 'userData:', { username: userData.username, mobile: userData.mobile, realname: userData.realname, uid: userData.uid });
        userNameDisplay.textContent = name;
        userNameDisplay.dataset.value = name;
    } else {
        console.warn('[更新UI] userNameDisplay元素未找到');
    }

    if (userIdDisplay) {
        // 优先使用uid（8位数字ID），如果没有则使用id
        const id = userData.uid || userData.id || '--';
        console.log('[更新UI] ID显示:', id, 'userData:', { uid: userData.uid, id: userData.id });
        userIdDisplay.textContent = id;
        userIdDisplay.dataset.value = id;
        userIdDisplay.dataset.idValue = id;
    } else {
        console.warn('[更新UI] userIdDisplay元素未找到');
    }

    if (userVipBadge && userData.level >= 1 && userData.level <= 8) {
        userVipBadge.setAttribute('data-vip', userData.level);
    }

    // 如果有头像URL则显示，否则用默认头像
    if (avatarLarge && userData.avatar && userData.avatar.trim()) {
        const avatarUrl = userData.avatar.startsWith('http') ? userData.avatar : (window.location.origin + userData.avatar);
        avatarLarge.style.backgroundImage = `url('${avatarUrl}')`;
        if (avatarText) avatarText.style.display = 'none';
    }

    // 更新VIP等级
    const vipCard = document.getElementById('vipCard');
    if (vipCard) {
        const level = userData.level || 1;
        vipCard.setAttribute('data-vip-level', level);
        // VIP图标已移除，不再更新
    }

    // 更新资产栏
    const totalAssetEl = document.getElementById('totalAsset');
    const totalIncomeCnyEl = document.getElementById('totalIncomeCny');
    const totalIncomeUsdtEl = document.getElementById('totalIncomeUsdt');
    const totalUsdtAssetEl = document.getElementById('totalUsdtAsset');
    const teamMembersEl = document.getElementById('teamMembers');
    const teamTotalInvestEl = document.getElementById('teamTotalInvest');

    if (totalAssetEl) {
        const totalAsset = (userData.money || 0) + (userData.ribao || 0);
        totalAssetEl.textContent = totalAsset.toFixed(2);
        totalAssetEl.dataset.value = totalAsset.toFixed(2);
        // 如果金额为0，使用白色；否则使用渐变色
        if (totalAsset === 0) {
            totalAssetEl.style.background = 'none';
            totalAssetEl.style.color = '#ffffff';
            totalAssetEl.style.webkitTextFillColor = '#ffffff';
        } else {
            totalAssetEl.style.background = 'linear-gradient(135deg, #fff 0%, rgba(201, 169, 97, 0.9) 100%)';
            totalAssetEl.style.webkitBackgroundClip = 'text';
            totalAssetEl.style.webkitTextFillColor = 'transparent';
            totalAssetEl.style.backgroundClip = 'text';
            totalAssetEl.style.color = 'transparent';
        }
    }

    // 人民币收益（从userData.profit_cny或profit获取）
    if (totalIncomeCnyEl) {
        const profitCny = parseFloat(userData.profit_cny || userData.profit || 0);
        totalIncomeCnyEl.textContent = profitCny.toFixed(2);
        totalIncomeCnyEl.dataset.value = profitCny.toFixed(2);
        // 如果金额为0，使用白色；否则使用渐变色
        if (profitCny === 0) {
            totalIncomeCnyEl.style.background = 'none';
            totalIncomeCnyEl.style.color = '#ffffff';
            totalIncomeCnyEl.style.webkitTextFillColor = '#ffffff';
        } else {
            totalIncomeCnyEl.style.background = 'linear-gradient(135deg, #fff 0%, rgba(201, 169, 97, 0.9) 100%)';
            totalIncomeCnyEl.style.webkitBackgroundClip = 'text';
            totalIncomeCnyEl.style.webkitTextFillColor = 'transparent';
            totalIncomeCnyEl.style.backgroundClip = 'text';
            totalIncomeCnyEl.style.color = 'transparent';
        }
    }

    // USDT收益（从userData.profit_usdt获取）
    if (totalIncomeUsdtEl) {
        const profitUsdt = parseFloat(userData.profit_usdt || 0);
        totalIncomeUsdtEl.textContent = profitUsdt.toFixed(2);
        totalIncomeUsdtEl.dataset.value = profitUsdt.toFixed(2);
    }

    // USDT卡片中的收益显示（totalUsdtIncome）
    const totalUsdtIncomeEl = document.getElementById('totalUsdtIncome');
    if (totalUsdtIncomeEl) {
        const profitUsdt = parseFloat(userData.profit_usdt || 0);
        totalUsdtIncomeEl.textContent = profitUsdt.toFixed(2);
        totalUsdtIncomeEl.dataset.value = profitUsdt.toFixed(2);
        // 如果金额为0，使用白色；否则使用绿色
        if (profitUsdt === 0) {
            totalUsdtIncomeEl.style.background = 'none';
            totalUsdtIncomeEl.style.color = '#ffffff';
            totalUsdtIncomeEl.style.webkitTextFillColor = '#ffffff';
        } else {
            totalUsdtIncomeEl.style.background = 'none';
            totalUsdtIncomeEl.style.color = '#4ade80';
            totalUsdtIncomeEl.style.webkitTextFillColor = '#4ade80';
        }
    }

    if (totalUsdtAssetEl) {
        const totalUsdtAsset = parseFloat((userData.usdt_money || 0) + (userData.usdt_ribao || 0));
        totalUsdtAssetEl.textContent = totalUsdtAsset.toFixed(2);
        totalUsdtAssetEl.dataset.value = totalUsdtAsset.toFixed(2);
        // 如果金额为0，使用白色；否则使用绿色
        if (totalUsdtAsset === 0) {
            totalUsdtAssetEl.style.background = 'none';
            totalUsdtAssetEl.style.color = '#ffffff';
            totalUsdtAssetEl.style.webkitTextFillColor = '#ffffff';
        } else {
            totalUsdtAssetEl.style.background = 'none';
            totalUsdtAssetEl.style.color = '#4ade80';
            totalUsdtAssetEl.style.webkitTextFillColor = '#4ade80';
        }
    }

    // 团队人数（从userData.team_count或team_members获取）
    if (teamMembersEl) {
        const teamMembers = userData.team_count || userData.team_members || 0;
        teamMembersEl.textContent = teamMembers.toString();
        teamMembersEl.dataset.value = teamMembers.toString();
    }

    // 团队总投资（从userData.team_total_invest或team_invest获取）
    if (teamTotalInvestEl) {
        const teamTotalInvest = (userData.team_total_invest || userData.team_invest || 0).toFixed(2);
        teamTotalInvestEl.textContent = teamTotalInvest;
        teamTotalInvestEl.dataset.value = teamTotalInvest;
    }

    // 更新升级进度（异步调用，已在loadUserData中调用）
}

// 更新升级进度（从后端接口获取真实数据）
async function updateUpgradeProgress() {
    if (!userData.token) {
        console.warn('[VIP进度] 未登录，跳过加载');
        return;
    }

    try {
        await waitForAPI();
        console.log('[VIP进度] 开始加载...');

        // 优先使用统一API封装
        let response = null;
        if (window.API && window.API.vip && window.API.vip.getInfo) {
            const result = await window.API.vip.getInfo();
            if (result.success && result.data) {
                // 转换格式以兼容现有代码
                response = { code: 200, msg: 'ok', data: result.data };
            }
        }

        // 降级方案
        if (!response) {
            const API_BASE = window.API_CONFIG?.baseURL || 'https://apis.copla.top';
            const res = await fetch(API_BASE + '/index.php/user/vip/progress', {
                method: 'GET',
                headers: { 'Content-Type': 'application/json', 'token': userData.token }
            });
            const text = await res.text();
            try {
                response = JSON.parse(text);
            } catch (e) {
                setDefaultProgress();
                return;
            }
        }

        console.log('[VIP进度] API响应:', response);

        // 兼容多种返回格式
        let data = null;

        // 格式1: {code: 200, data: {...}}
        if (response.code === 1 && response.data) {
            data = response.data;
        }
        // 格式2: {msg: 'ok', data: {...}}
        else if (response.msg === 'ok' && response.data) {
            data = response.data;
        }
        // 格式3: 直接返回数据对象 {current_level: 1, next_level: 2, ...}
        else if (response.current_level !== undefined || response.next_level !== undefined) {
            data = response;
        }

        if (data) {
            // 获取当前等级和下一等级
            const currentLevel = parseInt(data.current_level || data.level || userData.level || 1);
            const nextLevel = parseInt(data.next_level || (currentLevel >= 8 ? 8 : currentLevel + 1));

            // 获取升级所需金额（兼容多种字段名）
            const needInvest = parseFloat(data.need_invest || data.need_amount || data.needInvest || 0);

            // 获取当前投资额
            const currentInvest = parseFloat(data.current_invest || data.current_amount || data.recharges || userData.recharges || 0);

            // 计算进度百分比
            let progress = 0;
            if (data.progress_percent !== undefined) {
                progress = parseFloat(data.progress_percent);
            } else if (data.progress !== undefined) {
                progress = parseFloat(data.progress);
            } else if (needInvest > 0) {
                // 自己计算进度
                progress = Math.min((currentInvest / (currentInvest + needInvest)) * 100, 100);
            }

            // 计算最终显示的进度百分比（至少5%，除非已达最高等级）
            const progressPercent = currentLevel >= 8 ? 100 : Math.max(parseFloat(progress), 5);

            // 更新VIP卡片中的进度条
            const nextVipLevelEl = document.getElementById('nextVipLevel');
            const needInvestAmountEl = document.getElementById('needInvestAmount');
            const vipProgressFillEl = document.getElementById('vipProgressFill');

            if (nextVipLevelEl) {
                if (currentLevel >= 8) {
                    nextVipLevelEl.textContent = '最高等级';
                } else {
                    nextVipLevelEl.textContent = `VIP${nextLevel}`;
                }
            }

            if (needInvestAmountEl) {
                needInvestAmountEl.textContent = formatMoney(needInvest);
            }

            if (vipProgressFillEl) {
                vipProgressFillEl.style.width = progressPercent + '%';
            }

            // 更新进度百分比显示
            const vipProgressPercentEl = document.getElementById('vipProgressPercent');
            if (vipProgressPercentEl) {
                vipProgressPercentEl.textContent = Math.round(progressPercent) + '%';
            }

            // 确保进度条显示
            const vipProgressBar = document.querySelector('.vip-progress-bar');
            if (vipProgressBar) {
                vipProgressBar.style.display = 'block';
                vipProgressBar.style.visibility = 'visible';
            }

            console.log('[VIP进度] ✅ 更新完成', {
                currentLevel,
                nextLevel,
                needInvest,
                currentInvest,
                progress: progressPercent
            });
        } else {
            console.warn('[VIP进度] ⚠️ 响应格式不正确，使用默认值:', response);
            // 如果接口失败，使用默认值
            setDefaultProgress();
        }
    } catch (error) {
        console.error('[VIP进度] ❌ 加载失败:', error);
        // 如果接口失败，使用默认值
        setDefaultProgress();
    }
}

// 设置默认进度值（接口失败时使用）
function setDefaultProgress() {
    const currentLevel = userData.level || 1;
    const nextLevel = currentLevel >= 8 ? 8 : currentLevel + 1;
    const needInvest = 0;

    const nextVipLevelEl = document.getElementById('nextVipLevel');
    const needInvestAmountEl = document.getElementById('needInvestAmount');
    const vipProgressFillEl = document.getElementById('vipProgressFill');

    if (nextVipLevelEl) {
        nextVipLevelEl.textContent = `VIP${nextLevel}`;
    }

    if (needInvestAmountEl) {
        needInvestAmountEl.textContent = formatMoney(needInvest);
    }

    if (vipProgressFillEl) {
        vipProgressFillEl.style.width = '5%';
    }
}

// 绑定事件
function bindEvents() {
    // 退出登录
    const logoutBtn = document.querySelector('.btn-logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }

    // 设置图标点击
    const settingsIcon = document.querySelector('.settings-icon');
    if (settingsIcon) {
        settingsIcon.addEventListener('click', function () {
            showToast('提示', '设置功能开发中');
        });
    }
}

// 退出登录
async function handleLogout() {
    const confirmed = await showConfirm('确认退出', '确定要退出登录吗？');
    if (confirmed) {
        localStorage.removeItem('providence_token');
        await showToast('提示', '已退出登录');
        setTimeout(() => {
            window.location.href = 'login.html';
        }, 1000);
    }
}

// API请求（已废弃，统一使用config.js的API封装）
// 保留此函数仅用于向后兼容，新代码应直接使用window.API.*
async function apiRequest(endpoint, data = {}, method = 'GET') {
    await waitForAPI();
    const API_BASE = window.API_CONFIG?.baseURL || 'https://apis.copla.top';
    const url = API_BASE + endpoint;

    const headers = {
        'token': userData.token
    };

    if (method !== 'GET') {
        headers['Content-Type'] = 'application/json';
    }

    const options = {
        method: method,
        headers: headers
    };

    try {
        if (method === 'GET') {
            const params = new URLSearchParams(data);
            const fullUrl = params.toString() ? `${url}?${params}` : url;

            const response = await fetch(fullUrl, options);

            if (!response.ok) {
                if (response.status >= 500) {
                    console.warn(`[API] ${endpoint} 服务器错误 ${response.status}，静默失败`);
                    return { code: 500, msg: '服务器错误', data: null };
                }
                throw new Error(`HTTP ${response.status}`);
            }

            const text = await response.text();

            if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                console.warn(`[API] ${endpoint} 返回HTML错误页面`);
                return { code: 500, msg: '服务器错误', data: null };
            }

            const result = JSON.parse(text);
            return result;
        } else {
            options.body = JSON.stringify(data);

            const response = await fetch(url, options);

            if (!response.ok) {
                if (response.status >= 500) {
                    console.warn(`[API] ${endpoint} 服务器错误 ${response.status}，静默失败`);
                    return { code: 500, msg: '服务器错误', data: null };
                }
                throw new Error(`HTTP ${response.status}`);
            }

            const text = await response.text();

            if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html')) {
                console.warn(`[API] ${endpoint} 返回HTML错误页面`);
                return { code: 500, msg: '服务器错误', data: null };
            }

            const result = JSON.parse(text);
            return result;
        }
    } catch (error) {
        console.warn(`[API] ${endpoint} 请求失败:`, error.message);
        return { code: 0, msg: '网络错误', data: null };
    }
}

// 格式化金额
function formatMoney(amount) {
    return parseFloat(amount || 0).toLocaleString('zh-CN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// 使用全局iOS弹窗组件（通过ios-toast.js）
// showToast函数已在ios-toast.js中定义
function _old_showToast(message, duration = 2000) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, duration);
}

// Providence 项目详情页 - 完全重构版
// 目标：简单直接，确保数据能正常显示

console.log('📜 项目详情脚本加载');

// 获取URL参数
function getUrlParam(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
}

// 等待API加载
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
            setTimeout(() => { clearInterval(check); resolve(); }, 3000);
        }
    });
}

// 初始化
async function init() {
    console.log('🚀 项目详情页初始化');
    
    const projectId = getUrlParam('id');
    if (!projectId) {
        alert('缺少项目ID参数');
        return;
    }
    
    console.log('📋 项目ID:', projectId);
    await waitForAPI();
    await loadProject(projectId);
}

// 加载项目数据
async function loadProject(id) {
    try {
        const API_BASE = window.API_CONFIG?.baseURL || 'https://apis.copla.top';
        const token = localStorage.getItem('providence_token') || localStorage.getItem('token') || '';
        
        // 添加超时控制（8秒）
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 8000);
        
        let data = null;
        
        // 优先使用统一API封装
        if (window.API && window.API.fund && window.API.fund.getDetail) {
            try {
                const apiPromise = window.API.fund.getDetail(id);
                const timeoutPromise = new Promise((_, reject) => 
                    setTimeout(() => reject(new Error('API调用超时')), 8000)
                );
                const result = await Promise.race([apiPromise, timeoutPromise]);
                
                if (result && result.success && result.data) {
                    data = { code: 1, data: result.data, msg: "ok" };
                    console.log("[Project Detail] ✓ 使用统一API封装");
                }
            } catch (apiError) {
                console.warn("[Project Detail] 统一API封装调用失败:", apiError.message);
            }
        }
        
        // 降级方案：直接调用API
        if (!data) {
            try {
                const url = `${API_BASE}/index.php/fund/project/detail?id=${id}`;
                console.log('📡 请求:', url);
                
                const res = await fetch(url, {
                    headers: {
                        "Accept": "application/json",
                        "Content-Type": "application/json",
                        "token": token
                    },
                    signal: controller.signal
                });
                clearTimeout(timeoutId);
                
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                }
                
                const text = await res.text();
                try {
                    data = JSON.parse(text);
                    console.log("[Project Detail] ✓ 使用直接API调用");
                } catch (parseError) {
                    console.error("[Project Detail] JSON解析失败:", text.substring(0, 100));
                    throw new Error("服务器返回格式错误");
                }
            } catch (fetchError) {
                clearTimeout(timeoutId);
                if (fetchError.name === 'AbortError') {
                    throw new Error('请求超时，请稍后重试');
                }
                throw fetchError;
            }
        }
        
        console.log('📦 响应:', data);
        
        if ((data.code === 1 || data.code === 200) && data.data) {
            const project = data.data;
            console.log('✅ 项目数据:', project);
            
            // 更新页面显示
            updatePage(project);
            await loadUserBalance(); // 加载用户余额
        } else {
            console.error('❌ API错误:', data.msg || data.message);
            showError(data.msg || data.message || '加载失败');
        }
    } catch (err) {
        console.error('❌ 加载失败:', err);
        showError(err.message || '网络错误');
    }
}

// 更新页面显示
function updatePage(p) {
    console.log('🎨 更新页面');
    
    // 项目名称和副标题
    const projName = document.getElementById('projName');
    if (projName) projName.textContent = p.name || p.title || '投资项目';
    
    const projSub = document.getElementById('projSub');
    if (projSub) {
        const category = p.category || p.categoryName || '固收优选';
        const desc = p.description || p.desc || p.intro || '';
        projSub.textContent = desc ? `${category} · ${desc.substring(0, 30)}${desc.length > 30 ? '...' : ''}` : category;
    }
    
    // 四个关键指标
    const cycle = document.getElementById('cycle');
    if (cycle) {
        const days = parseInt(p.total_days || p.cycle_days || p.day || 0);
        if (days > 0) {
            if (days < 30) {
                cycle.textContent = `${days} 天`;
            } else if (days < 365) {
                const months = Math.floor(days / 30);
                cycle.textContent = `${months} 个月`;
            } else {
                const years = Math.floor(days / 365);
                cycle.textContent = `${years} 年`;
            }
        } else {
            cycle.textContent = '灵活';
        }
    }
    
    const rateVip = document.getElementById('rateVip');
    if (rateVip) {
        const vipRate = parseFloat(p.added_rate || p.vip_rate || 0);
        rateVip.textContent = vipRate > 0 ? `+${vipRate.toFixed(2)}%` : '—';
    }
    
    const maxAmt = document.getElementById('maxAmt');
    if (maxAmt) {
        const max = parseFloat(p.max_invest || p.max || 0);
        maxAmt.textContent = max > 0 ? `¥${max.toLocaleString('zh-CN')}` : '无限制';
    }
    
    const minAmt = document.getElementById('minAmt');
    if (minAmt) {
        const min = parseFloat(p.min_invest || p.min || 0);
        minAmt.textContent = min > 0 ? `¥${min.toLocaleString('zh-CN')}` : '—';
    }
    
    // 募集进度
    const prog = document.getElementById('prog');
    if (prog) {
        const total = parseFloat(p.total_amount || p.total || 0);
        const sold = parseFloat(p.sold_amount || p.sold || 0);
        const progress = total > 0 ? Math.min((sold / total) * 100, 100) : 0;
        prog.style.width = progress + '%';
        prog.textContent = progress.toFixed(1) + '%';
    }
    
    // 募集信息
    const soldInfo = document.getElementById('soldInfo');
    if (soldInfo) {
        const sold = parseFloat(p.sold_amount || p.sold || 0);
        soldInfo.textContent = `已募集: ¥${sold.toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }
    
    const totalInfo = document.getElementById('totalInfo');
    if (totalInfo) {
        const total = parseFloat(p.total_amount || p.total || 0);
        totalInfo.textContent = `总额度: ¥${total > 0 ? total.toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '无限制'}`;
    }
    
    // 项目详情
    const projectDesc = document.getElementById('projectDesc');
    if (projectDesc) {
        const desc = p.description || p.desc || p.intro || p.content || '';
        if (desc) {
            projectDesc.innerHTML = `<p style="margin: 0; white-space: pre-wrap;">${desc.replace(/\n/g, '<br>')}</p>`;
        } else {
            projectDesc.innerHTML = '<p style="margin: 0; color: #8a95a6;">暂无项目详情</p>';
        }
    }
    
    // 收益说明
    const baseRate = document.getElementById('baseRate');
    if (baseRate) {
        const rate = parseFloat(p.daily_rate || p.base_rate || p.rate || 0);
        baseRate.textContent = rate > 0 ? `${rate.toFixed(2)}%` : '—';
    }
    
    const vipRateText = document.getElementById('vipRateText');
    if (vipRateText) {
        const vipRate = parseFloat(p.added_rate || p.vip_rate || 0);
        vipRateText.textContent = vipRate > 0 ? `+${vipRate.toFixed(2)}%` : '无';
    }
    
    const addedRateText = document.getElementById('addedRateText');
    if (addedRateText) {
        const added = parseFloat(p.added_rate || p.added || 0);
        addedRateText.textContent = added > 0 ? `${added.toFixed(2)}%` : '无';
    }
    
    const giftRateText = document.getElementById('giftRateText');
    if (giftRateText) {
        const gift = parseFloat(p.gift_rate || p.gift || 0);
        giftRateText.textContent = gift > 0 ? `${gift.toFixed(2)}%` : '无';
    }
    
    // 项目经理信息
    const pmInfo = document.getElementById('pmInfo');
    if (pmInfo) {
        const managerName = p.manager_name || p.company_name || 'PROVIDENCE';
        const managerAvatar = p.manager_avatar || p.company_image || 'img/user-avatar-default.svg';
        const managerTitle = p.manager_title || '项目负责人';
        
        pmInfo.innerHTML = `
            <img src="${managerAvatar}" alt="${managerName}" onerror="this.src='img/user-avatar-default.svg'">
            <div>
                <b>${managerName}</b>
                <div><small>${managerTitle}</small></div>
            </div>
        `;
    }
    
    // 隐藏所有加载中...文本
    hideAllLoading();
    
    console.log('✅ 页面更新完成');
}

// 显示项目经理详情（占位函数）
function showManagerDetail() {
    if (typeof showToast === 'function') {
        showToast('项目经理详情功能开发中', 2000);
    } else {
        alert('项目经理详情功能开发中');
    }
}

// 加载用户余额
async function loadUserBalance() {
    try {
        const API_BASE = window.API_CONFIG?.baseURL || 'https://apis.copla.top';
        const token = localStorage.getItem('providence_token') || localStorage.getItem('token') || '';
        
        if (!token) {
            const balanceEl = document.getElementById('balance');
            if (balanceEl) balanceEl.textContent = '请先登录';
            return;
        }
        
        // 优先使用统一API封装
        let balance = null;
        if (window.ApiService && window.ApiService.finance && window.ApiService.finance.getUserBalance) {
            try {
                const result = await window.ApiService.finance.getUserBalance();
                if (result && (result.money !== undefined || result.balance !== undefined)) {
                    balance = parseFloat(result.money || result.balance || 0);
                    console.log("[Balance] ✓ 使用ApiService获取余额:", balance);
                }
            } catch (e) {
                console.warn("[Balance] ApiService调用失败:", e);
            }
        }
        
        // 降级方案
        if (balance === null) {
            const response = await fetch(API_BASE + "/index.php/user/user/index", {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'token': token
                }
            });
            
            if (response.ok) {
                const text = await response.text();
                try {
                    const data = JSON.parse(text);
                    if (data.code === 1 && data.data) {
                        balance = parseFloat(data.data.money || 0);
                        console.log("[Balance] ✓ 使用直接API获取余额:", balance);
                    }
                } catch (e) {
                    console.warn("[Balance] JSON解析失败:", e);
                }
            }
        }
        
        const balanceEl = document.getElementById('balance');
        if (balanceEl) {
            balanceEl.textContent = balance !== null ? `¥${balance.toLocaleString('zh-CN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` : '加载中…';
        }
    } catch (err) {
        console.warn("[Balance] 加载余额失败:", err);
        const balanceEl = document.getElementById('balance');
        if (balanceEl) balanceEl.textContent = '加载失败';
    }
}

// 隐藏所有加载中
function hideAllLoading() {
    const loadingEls = document.querySelectorAll('.loading, [data-loading]');
    loadingEls.forEach(el => el.style.display = 'none');
    
    // 移除加载中...文本
    document.body.innerHTML = document.body.innerHTML.replace(/加载中\.\.\./g, '');
    document.body.innerHTML = document.body.innerHTML.replace(/正在加载项目信息/g, '');
    document.body.innerHTML = document.body.innerHTML.replace(/正在加载/g, '');
}

// 显示错误
function showError(msg) {
    const projName = document.getElementById('projName');
    if (projName) projName.textContent = '加载失败: ' + msg;
    
    if (typeof showToast === 'function') {
        showToast(msg, 3000);
    } else {
        alert(msg);
    }
}

// 显示项目经理详情（占位函数）
function showManagerDetail() {
    if (typeof showToast === 'function') {
        showToast('项目经理详情功能开发中', 2000);
    } else {
        alert('项目经理详情功能开发中');
    }
}

// 页面加载完成
document.addEventListener('DOMContentLoaded', () => {
    init();
    
    // 绑定申请认购按钮
    const applyBtn = document.getElementById('applyBtn');
    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            const projectId = getUrlParam('id');
            if (!projectId) {
                if (typeof showToast === 'function') {
                    showToast('项目ID不存在', 2000);
                } else {
                    alert('项目ID不存在');
                }
                return;
            }
            
            // 检查是否登录
            const token = localStorage.getItem('providence_token') || localStorage.getItem('token') || '';
            // if (!token) {
            //     if (typeof showToast === 'function') {
            //         showToast('请先登录', 2000);
            //     } else {
            //         alert('请先登录');
            //     }
            //     setTimeout(() => {
            //         window.location.href = 'login.html';
            //     }, 1500);
            //     return;
            // }
            
            // 跳转到认购页面（如果存在）或显示提示
            if (typeof showToast === 'function') {
                showToast('认购功能开发中', 2000);
            } else {
                alert('认购功能开发中');
            }
        });
    }
});

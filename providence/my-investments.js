// 我的投资 - 统一使用config.js的API封装
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
    // token: localStorage.getItem('providence_token') || '',
    projects: [],
    totalInvest: 0,
    totalProfit: 0,
    avgRate: 0
};

let currentStatus = 0; // 0=持有中, 1=已结束

// 页面加载
document.addEventListener('DOMContentLoaded', function() {
    initPage();
    loadInvestments();
    bindEvents();
});

// 初始化页面
function initPage() {
    // if (!userData.token) {
    //     showToast('提示', '请先登录').then(() => {
    //     setTimeout(() => {
    //         window.location.href = 'login.html';
    //         }, 500);
    //     });
    //     return;
    // }
}

// 加载投资数据
async function loadInvestments() {
    try {
        // 直接加载项目列表，接口会返回统计数据
        await loadProjectList();
    } catch (error) {
        console.error('加载投资数据失败:', error);
        showEmpty();
    }
}

// 加载项目列表
async function loadProjectList() {
    const container = document.getElementById('investmentsList');
    container.innerHTML = '<div class="loading-state"><div class="loading-spinner"></div><div class="loading-text">加载中...</div></div>';

    try {
        await waitForAPI();

        // 优先使用统一API封装
        let response = null;
        if (window.API && window.API.order && window.API.order.getList) {
            const result = await window.API.order.getList({ status: currentStatus, page: 1, limit: 50 });
            if (result.success && result.data) {
                response = { code: 200, msg: 'ok', data: result.data };
            }
        }

        // 降级方案
        if (!response) {
            response = await apiRequest('/index.php/user/project/list', {
                status: currentStatus,
                page: 1,
                limit: 50
            }, 'GET');
        }

        console.log('[我的投资] API响应:', response);

        // 处理不同的响应格式
        let responseData = null;
        if (response.code === 200 && response.data) {
            responseData = response.data;
        } else if (response.msg === 'ok' && response.data) {
            responseData = response.data;
        } else {
            console.warn('[我的投资] 响应格式不正确:', response);
            showEmpty();
            return;
        }

            // 更新统计数据
        if (responseData.total_invest !== undefined) {
            userData.totalInvest = parseFloat(responseData.total_invest || 0);
        } else {
            // 如果没有统计数据，从项目列表计算
            const allProjects = responseData.list || [];
            userData.totalInvest = allProjects.reduce((sum, p) => sum + parseFloat(p.money || 0), 0);
        }

        if (responseData.total_profit !== undefined) {
            userData.totalProfit = parseFloat(responseData.total_profit || 0);
        } else {
            // 如果没有统计数据，从已结束项目计算
            const completedProjects = (responseData.list || []).filter(p => p.status === 1);
            userData.totalProfit = completedProjects.reduce((sum, p) => sum + parseFloat(p.profit || p.total_profit || 0), 0);
            }

        if (responseData.avg_rate !== undefined) {
            userData.avgRate = parseFloat(responseData.avg_rate || 0);
        } else {
            // 计算平均收益率
            const allProjects = responseData.list || [];
            if (allProjects.length > 0 && userData.totalInvest > 0) {
                userData.avgRate = (userData.totalProfit / userData.totalInvest * 100).toFixed(2);
            } else {
                userData.avgRate = 0;
            }
        }

            updateSummary();

            // 更新项目列表
        userData.projects = responseData.list || [];

            if (userData.projects.length === 0) {
                showEmpty();
            } else {
                renderProjects();
        }
    } catch (error) {
        console.error('[我的投资] 加载项目列表失败:', error);
        showEmpty();
    }
}

// 更新概览数据
function updateSummary() {
    document.getElementById('totalInvest').textContent = formatMoney(userData.totalInvest);
    document.getElementById('totalProfit').textContent = formatMoney(userData.totalProfit);
    document.getElementById('avgRate').textContent = userData.avgRate + '%';
}

// 渲染投资项目
function renderProjects() {
    const container = document.getElementById('investmentsList');

    if (userData.projects.length === 0) {
        showEmpty();
        return;
    }

    const projectsHtml = userData.projects.map(project => {
        const progress = Math.min(parseFloat(project.progress || 0), 100);
        const statusClass = project.status === 0 ? 'running' : 'completed';
        const statusText = project.status === 0 ? '进行中' : '已结束';
        const profitRate = parseFloat(project.profit_rate || project.total_rate || 0);
        const totalProfit = parseFloat(project.total_profit || project.profit || 0);
        const investmentAmount = parseFloat(project.money || 0);
        const remainDays = project.remain_days !== undefined ? project.remain_days : 0;
        const elapsedDays = project.elapsed_days !== undefined ? project.elapsed_days : 0;
        const totalDays = parseInt(project.day || 0);

        return `
            <div class="inv-card" data-id="${project.id}">
                <div class="inv-header">
                    <div class="inv-name">${project.title || '投资项目'}</div>
                    <div class="inv-status ${statusClass}">${statusText}</div>
                </div>
                <div class="inv-details">
                    <div class="inv-row">
                        <span class="label">投资金额</span>
                        <span class="value">¥${formatMoney(investmentAmount)}</span>
                    </div>
                    <div class="inv-row">
                        <span class="label">${project.status === 0 ? '预期收益' : '实际收益'}</span>
                        <span class="value profit">+¥${formatMoney(totalProfit)}</span>
                    </div>
                    <div class="inv-row">
                        <span class="label">收益率</span>
                        <span class="value profit">+${profitRate.toFixed(2)}%</span>
                    </div>
                    <div class="inv-row">
                        <span class="label">${project.status === 0 ? '剩余天数' : '结束日期'}</span>
                        <span class="value">${project.status === 0 ? (remainDays >= 0 ? remainDays : 0) + '天' : (project.end_date || '')}</span>
                    </div>
                </div>
                ${project.status === 0 && totalDays > 0 ? `
                <div class="inv-progress">
                    <div class="progress-bar-inv">
                        <div class="progress-fill-inv" style="width:${progress}%"></div>
                    </div>
                    <div class="progress-text">${elapsedDays}/${totalDays}天</div>
                </div>
                ` : ''}
                <div class="inv-footer">
                    <button class="inv-btn-detail" data-id="${project.id}">查看详情</button>
                </div>
            </div>
        `;
    }).join('');

    container.innerHTML = projectsHtml;

    // 绑定详情按钮事件
    container.querySelectorAll('.inv-btn-detail').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const projectId = this.getAttribute('data-id');
            viewDetail(projectId);
        });
    });
}

// 显示空状态
function showEmpty() {
    const container = document.getElementById('investmentsList');
    container.innerHTML = `
        <div class="empty-state">
            <div class="empty-icon">📊</div>
            <div class="empty-text">暂无投资记录</div>
            <a href="projects.html" class="empty-btn">去投资</a>
        </div>
    `;
}

// 查看详情
function viewDetail(projectId) {
    // 跳转到项目详情页
    window.location.href = `project-detail-new.html?id=${projectId}`;
}

// 绑定事件
function bindEvents() {
    // 标签切换
    document.querySelectorAll('.inv-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.inv-tab-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentStatus = parseInt(this.getAttribute('data-status'));
            // 切换标签时重新加载数据
            loadProjectList();
        });
    });
}

// API请求（降级方案）
async function apiRequest(endpoint, data = {}, method = 'GET') {
    await waitForAPI();
    const API_BASE = window.API_CONFIG?.baseURL || 'https://apis.copla.top';
    const url = API_BASE + endpoint;
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
            // 'token': userData.token
        }
    };

    try {
        let response;
        if (method === 'GET') {
            const params = new URLSearchParams(data);
            const fullUrl = params.toString() ? `${url}?${params}` : url;
            response = await fetch(fullUrl, options);
        } else {
            options.body = JSON.stringify(data);
            response = await fetch(url, options);
        }

        const text = await response.text();

        // 检查是否是HTML错误页面
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html') || text.includes('系统发生错误') || text.includes('ThinkPHP')) {
            console.warn(`[API] ${endpoint} 返回HTML错误页面`);
            return { code: 500, msg: '服务器错误', data: null };
        }

        try {
            return JSON.parse(text);
        } catch (e) {
            console.warn(`[API] ${endpoint} JSON解析失败:`, text.substring(0, 200));
            return { code: 500, msg: '响应格式错误', data: null };
        }
    } catch (error) {
        console.warn(`[API] ${endpoint} 请求失败:`, error.message);
        return { code: 0, msg: '网络错误', data: null };
    }
}

// 格式化金额
function formatMoney(amount) {
    return parseFloat(amount || 0).toFixed(2);
}

// 格式化日期
function formatDate(timestamp) {
    const date = new Date(timestamp * 1000);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

// 使用全局iOS弹窗组件（通过ios-toast.js）
// showToast函数已在ios-toast.js中定义

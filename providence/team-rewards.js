// 团队管理奖页面 - 统一使用config.js的API封装
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
    teamCount: 0,
    teamInvest: 0,
    rules: []
};

document.addEventListener('DOMContentLoaded', function() {
    initPage();
});

function initPage() {
    // if (!userData.token) {
    //     showToast('请先登录');
    //     setTimeout(() => {
    //         window.location.href = 'login.html';
    //     }, 1500);
    //     return;
    // }

    loadRewardsData();
}

async function loadRewardsData() {
    try {
        await waitForAPI();
        const API_BASE = window.API_CONFIG?.baseURL || 'https://apis.copla.top';
        const response = await fetch(API_BASE + '/index.php/user/team/rewards_status', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
                // 'token': userData.token
            }
        });

        const text = await response.text();

        // 检查是否是HTML错误页面
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html') || text.includes('系统发生错误') || text.includes('ThinkPHP')) {
            console.warn('[API] /index.php/user/team/rewards_status 返回HTML错误页面');
            showToast('加载失败，请重试');
            return;
        }

        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('解析团队奖励数据失败:', text.substring(0, 100));
            showToast('加载失败，请重试');
            return;
        }

        console.log('团队奖励数据:', data);

        if (data.code === 200 && data.data) {
            userData.teamCount = data.data.team_count;
            userData.teamInvest = data.data.team_invest;
            userData.rules = data.data.rules;

            // 更新UI
            document.getElementById('teamCount').textContent = userData.teamCount + '人';
            document.getElementById('teamInvest').textContent = '¥' + formatMoney(userData.teamInvest);

            renderRules();
        } else {
            showToast(data.msg || '加载失败');
        }
    } catch (error) {
        console.error('加载团队奖励数据失败:', error);
        showToast('加载失败，请重试');
    }
}

function renderRules() {
    const container = document.getElementById('rewardsList');

    if (userData.rules.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-icon">💎</div>
                <div class="empty-text">暂无奖励规则</div>
            </div>
        `;
        return;
    }

    const rulesHtml = userData.rules.map(rule => {
        let badgeClass = 'badge-unclaimed';
        let badgeText = '未达标';
        let btnClass = 'claim-btn-disabled';
        let btnText = '未达标';
        let btnDisabled = 'disabled';

        if (rule.is_claimed) {
            badgeClass = 'badge-claimed';
            badgeText = '已领取';
            btnText = '已领取';
        } else if (rule.can_claim) {
            badgeClass = 'badge-can-claim';
            badgeText = '可领取';
            btnClass = 'claim-btn-active';
            btnText = '立即领取';
            btnDisabled = '';
        }

        const cardClass = rule.is_claimed ? 'reward-card claimed' : (rule.can_claim ? 'reward-card can-claim' : 'reward-card');

        return `
            <div class="${cardClass}">
                <div class="reward-header">
                    <div class="reward-points">${formatNumber(rule.reward_points)} 积分</div>
                    <div class="reward-badge ${badgeClass}">${badgeText}</div>
                </div>
                <div class="reward-requirements">
                    <div class="requirement-item">
                        <div class="requirement-label">团队人数</div>
                        <div class="requirement-value">${rule.member_count}人</div>
                    </div>
                    <div class="requirement-item">
                        <div class="requirement-label">累计投资</div>
                        <div class="requirement-value">¥${formatMoney(rule.invest_amount)}</div>
                    </div>
                </div>
                <button class="claim-btn ${btnClass}"
                        onclick="claimReward(${rule.id})"
                        ${btnDisabled}>
                    ${btnText}
                </button>
            </div>
        `;
    }).join('');

    container.innerHTML = rulesHtml;
}

async function claimReward(rewardId) {
    try {
        await waitForAPI();
        const API_BASE = window.API_CONFIG?.baseURL || 'https://apis.copla.top';
        const response = await fetch(API_BASE + '/index.php/user/team/claim_reward', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
                // 'token': userData.token
            },
            body: JSON.stringify({ reward_id: rewardId })
        });

        const text = await response.text();

        // 检查是否是HTML错误页面
        if (text.trim().startsWith('<!DOCTYPE') || text.trim().startsWith('<html') || text.includes('系统发生错误') || text.includes('ThinkPHP')) {
            console.warn('[API] /index.php/user/team/claim_reward 返回HTML错误页面');
            showToast('领取失败，请重试');
            return;
        }

        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('解析领取奖励响应失败:', text.substring(0, 100));
            showToast('领取失败，请重试');
            return;
        }

        if (data.code === 200) {
            showToast(data.msg || '领取成功！');
            // 延迟刷新页面
            setTimeout(() => {
                loadRewardsData();
            }, 1500);
        } else {
            showToast(data.msg || '领取失败');
        }
    } catch (error) {
        console.error('领取奖励失败:', error);
        showToast('领取失败，请重试');
    }
}

function formatMoney(value) {
    const num = parseFloat(value) || 0;
    if (num >= 10000) {
        return (num / 10000).toFixed(2) + '万';
    }
    return num.toLocaleString('zh-CN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function formatNumber(value) {
    return parseInt(value || 0).toLocaleString('zh-CN');
}

function showToast(message) {
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
    }, 2000);
}

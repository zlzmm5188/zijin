/**
 * 项目页面 - 对接后台API（三板块：基金/IPO/债券）
 */

let fundsData = [];
let iposData = [];
let bondsData = [];
let currentSection = 'fund';

// 页面加载时获取项目数据
document.addEventListener('DOMContentLoaded', async function() {
    await loadAllProjects();
    initTabSwitching();
});

/**
 * 从后台/本地加载所有项目数据
 */
async function loadAllProjects() {
    try {
        console.log('📡 正在获取项目数据...');
        
        // 从market-crawler获取数据
        const response = await fetch('market-crawler.php?action=get&type=all');
        const result = await response.json();
        
        if (result.success && result.data) {
            fundsData = result.data.funds || [];
            iposData = result.data.ipos || [];
            bondsData = result.data.bonds || [];
            
            console.log('✅ 项目数据加载成功');
            renderCurrentSection();
        } else {
            loadDefaultProjects();
        }
    } catch (error) {
        console.error('❌ 加载项目失败:', error);
        loadDefaultProjects();
    }
}

/**
 * 加载默认项目数据（备用）
 */
function loadDefaultProjects() {
    fundsData = [{code: '000001', name: '易方达优选', nav: 2.45, change: 1.2}];
    iposData = [{code: '301373', name: '凌玮科技', price: 28.5, status: '申购中'}];
    bondsData = [{code: '019666', name: '23国债15', price: 100.03, yield: 2.85}];
    renderCurrentSection();
}

/**
 * 渲染当前板块
 */
function renderCurrentSection() {
    if (currentSection === 'fund') {
        renderFunds();
    } else if (currentSection === 'ipo') {
        renderIPOs();
    } else if (currentSection === 'bond') {
        renderBonds();
    }
}

/**
 * 渲染基金项目
 */
function renderFunds() {
    const container = document.getElementById('fundProjectsList');
    if (!container || fundsData.length === 0) return;
    
    const html = fundsData.slice(0, 10).map(fund => `
        <div class="product-card">
            <div class="product-badge">优选</div>
            <div class="product-header">
                <h3>${fund.name} (${fund.code})</h3>
                <p class="product-subtitle">净值: ${fund.nav.toFixed(4)} · 涨跌: ${fund.change >= 0 ? '+' : ''}${fund.change.toFixed(2)}%</p>
            </div>
            <div class="product-highlights">
                <div class="highlight">
                    <span class="hl-icon">📊</span>
                    <span>近1月 <strong>${fund.change_1m ? fund.change_1m.toFixed(2) : '--'}%</strong></span>
                </div>
                <div class="highlight">
                    <span class="hl-icon">⭐</span>
                    <span>评级 <strong>${'★'.repeat(fund.rating || 3)}</strong></span>
                </div>
                <div class="highlight">
                    <span class="hl-icon">💼</span>
                    <span>基金经理 <strong>${fund.manager || '张三'}</strong></span>
                </div>
            </div>
            <button class="btn-product" onclick="investFund('${fund.code}')">立即申购</button>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

/**
 * 渲染IPO项目
 */
function renderIPOs() {
    const container = document.getElementById('ipoProjectsList');
    if (!container || iposData.length === 0) return;
    
    const html = iposData.map(ipo => `
        <div class="product-card ${ipo.status === '申购中' ? 'premium' : ''}">
            <div class="product-badge ${ipo.status === '申购中' ? 'exclusive' : ''}">${ipo.status}</div>
            <div class="product-header">
                <h3>${ipo.name} (${ipo.code})</h3>
                <p class="product-subtitle">${ipo.industry || ''} · ${ipo.board || ''}</p>
            </div>
            <div class="product-highlights">
                <div class="highlight">
                    <span class="hl-icon">💰</span>
                    <span>发行价 <strong>${ipo.price}元</strong></span>
                </div>
                <div class="highlight">
                    <span class="hl-icon">📈</span>
                    <span>市盈率 <strong>${ipo.pe || '--'}</strong></span>
                </div>
                <div class="highlight">
                    <span class="hl-icon">🎯</span>
                    <span>申购日期 <strong>${ipo.apply_date || '--'}</strong></span>
                </div>
            </div>
            <button class="btn-product" onclick="investIPO('${ipo.code}')">立即申购</button>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

/**
 * 渲染债券项目
 */
function renderBonds() {
    const container = document.getElementById('bondProjectsList');
    if (!container || bondsData.length === 0) return;
    
    const html = bondsData.slice(0, 10).map(bond => `
        <div class="product-card">
            <div class="product-badge">${bond.rating}</div>
            <div class="product-header">
                <h3>${bond.name} (${bond.code})</h3>
                <p class="product-subtitle">${bond.type_name || ''} · ${bond.period}</p>
            </div>
            <div class="product-highlights">
                <div class="highlight">
                    <span class="hl-icon">💵</span>
                    <span>最新价 <strong>${bond.price.toFixed(2)}</strong></span>
                </div>
                <div class="highlight">
                    <span class="hl-icon">📊</span>
                    <span>到期收益率 <strong>${bond.yield.toFixed(2)}%</strong></span>
                </div>
                <div class="highlight">
                    <span class="hl-icon">📅</span>
                    <span>期限 <strong>${bond.period}</strong></span>
                </div>
            </div>
            <button class="btn-product" onclick="investBond('${bond.code}')">立即申购</button>
        </div>
    `).join('');
    
    container.innerHTML = html;
}

/**
 * 初始化标签切换
 */
function initTabSwitching() {
    const tabBtns = document.querySelectorAll('.market-tab-mobile');
    const sections = document.querySelectorAll('.product-section');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            sections.forEach(s => s.classList.add('hidden'));
            
            this.classList.add('active');
            currentSection = this.dataset.section;
            
            const sectionId = currentSection + '-section';
            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.classList.remove('hidden');
            }
            
            renderCurrentSection();
        });
    });
}

/**
 * 投资基金
 */
async function investFund(code) {
    const fund = fundsData.find(f => f.code === code);
    if (!fund) return;
    
    const amount = prompt(`请输入投资金额（建议1000元起）：`);
    if (!amount || isNaN(amount)) return;
    
    try {
        const result = await API.fund.invest({
            type: 'fund',
            code: code,
            amount: parseFloat(amount)
        });
        
        if (result.success) {
            alert('✅ 基金申购成功！\n\n' + fund.name + '\n金额：' + formatMoney(amount) + '元');
            location.reload();
        } else {
            alert('❌ 申购失败：' + (result.msg || '请稍后重试'));
        }
    } catch (error) {
        alert('❌ 网络错误，请稍后重试');
    }
}

/**
 * 投资IPO
 */
async function investIPO(code) {
    const ipo = iposData.find(i => i.code === code);
    if (!ipo) return;
    
    try {
        const result = await API.fund.invest({
            type: 'ipo',
            code: code,
            amount: ipo.price
        });
        
        if (result.success) {
            alert('✅ IPO申购成功！\n\n' + ipo.name + '\n发行价：' + ipo.price + '元');
            location.reload();
        } else {
            alert('❌ 申购失败：' + (result.msg || '请稍后重试'));
        }
    } catch (error) {
        alert('❌ 网络错误，请稍后重试');
    }
}

/**
 * 投资债券
 */
async function investBond(code) {
    const bond = bondsData.find(b => b.code === code);
    if (!bond) return;
    
    const amount = prompt(`请输入购买金额（${bond.name}）：`, '10000');
    if (!amount || isNaN(amount)) return;
    
    try {
        const result = await API.fund.invest({
            type: 'bond',
            code: code,
            amount: parseFloat(amount)
        });
        
        if (result.success) {
            alert('✅ 债券申购成功！\n\n' + bond.name + '\n金额：' + formatMoney(amount) + '元');
            location.reload();
        } else {
            alert('❌ 申购失败：' + (result.msg || '请稍后重试'));
        }
    } catch (error) {
        alert('❌ 网络错误，请稍后重试');
    }
}

/**
 * 格式化金额
 */
function formatMoney(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

# Providence 项目上下文

## 项目信息
- **域名**：agx.bi (abcmall.one已过期)
- **目录**：/www/wwwroot/f.abcmall.one/providence
- **后端API**：https://v2api.hemlx.com
- **CDN**：CloudFlare（修改后需清除缓存或强制刷新）

## 最近完成的优化 (2025-11-08)

### 1. projects.html
- ✅ "引导顾问" → "引导访问"
- ✅ 链接到 trial-money.html
- ✅ 已连接后端 API (window.API.fund.getList)

### 2. profile.html & profile.js
- ✅ ID生成为8位随机数字：`Math.floor(Math.random() * 90000000) + 10000000`
- ✅ 账号和ID已左右排列

### 3. project-detail.html
- ✅ 余额从标题栏移到底部（返回按钮旁边）
- ✅ 删除"我的资金"板块
- ✅ 项目经理随机选择（使用 project-detail-managers-data.js）
- ✅ 创建了专门的managers数据文件

### 4. project-managers.html
- ✅ 添加部门介绍（固收、权益、量化投资部）
- ✅ 弹窗大小改为70% (max-height: 70vh)
- ✅ 已删除教育背景部分
- ✅ 工作经历已扩展

### 5. 其他页面
- ✅ trial-money.html - 简化UI，单按钮动态显示
- ✅ kyc-verification.html - 背景色统一
- ✅ company-news.html - 优化UI，改为正式详情页
- ✅ company-news-detail.html - 新增专门的新闻详情页
- ✅ policy.html - 删除"查看全文"，标签与标题同行
- ✅ 所有页面返回按钮：`javascript:history.back()`

## 重要文件列表
```
/www/wwwroot/f.abcmall.one/providence/
├── config.js                           # API配置
├── project-detail-managers-data.js     # 项目经理数据（10人精简版）
├── projects.html                       # 项目主题页
├── projects-list.html                  # 项目列表页
├── project-detail.html                 # 项目详情页
├── project-managers.html               # 项目经理团队页
├── profile.html / profile.js           # 个人中心
├── trial-money.html                    # 体验金领取
├── kyc-verification.html               # 实名认证
├── company-news.html                   # 公司新闻列表
├── company-news-detail.html            # 新闻详情
└── policy.html                         # 监管政策
```

## 缓存处理
**重要**：由于使用CloudFlare CDN，修改后必须：
1. 强制刷新：`Ctrl + Shift + R` (Windows) 或 `Cmd + Shift + R` (Mac)
2. 或清除CloudFlare缓存
3. 所有CSS/JS已添加版本号 `?v=时间戳` 破除缓存

## 数据流
```
前端 → config.js (API_CONFIG) → 后端API (v2api.hemlx.com)
                              ↓
                        统一API封装 (window.API)
                              ↓
                    各页面调用 (getInfo, getList等)
```

## 常见问题
1. **修改看不到**：CloudFlare缓存，强制刷新浏览器
2. **API调用失败**：检查token是否有效
3. **ID显示不正确**：后端未返回ID时自动生成8位随机数

## 最新完成任务 (2025-11-08 - 完成✓)

### ✅ 已完成
1. **[✓]** APP下载页面 (`app-download.html`)
   - 高端大气的下载页面设计
   - 安卓/iOS双平台下载按钮
   - 自动识别设备类型
   - 二维码下载区域（待配置链接）

2. **[✓]** 注册成功后APP拉起逻辑 (`app-launcher.js`)
   - 智能判断APP是否已安装
   - 已安装：通过URL Scheme拉起APP
   - 未安装：自动跳转下载页面
   - 集成到`register-new.html`

3. **[✓]** forgot.html按钮优化
   - AI智能找回和人脸识别按钮加大
   - 完美左右并列布局
   - 视觉效果更突出

4. **[✓]** 公司动态添加3条投资案例
   - 📰 香港TVB重大战略投资（2011年，62.6亿港元）
   - 📰 投资爱奇艺（2013年，战略投资）
   - 📰 新和盛农牧B轮融资（2022年，数千万）

5. **[✓]** 登录后体验金弹窗 (`trial-money-popup.js`)
   - 高端奢华的弹窗设计
   - 动画效果：渐入、脉冲、弹跳
   - 智能判断实名状态
   - 每天仅显示一次
   - 文案专业大气

6. **[✓]** 公司动态板块重构 (`company-news.html`)
   - 高端金融平台设计风格
   - Hero统计数据展示（500B+资产、15条动态、100+投资组合）
   - 智能筛选标签（全部/投资/奖项/拓展/战略）
   - 响应式网格布局
   - 特色新闻卡片放大展示
   - 精美悬停动画效果

7. **[✓]** 监管政策页面优化 (`policy.html`)
   - 统一所有板块格式
   - 标签紧跟标题后面（同一行）
   - 文号显示在左下方
   - 日期显示在右下角
   - 布局更加整洁规范
   - 涵盖7大类24份政策文件

8. **[✓]** 团队页面优化 (`invite.html` + `invite.js`)
   - 修复人数统计bug（避免数据累加）
   - 一次性加载所有团队数据
   - 有效下级筛选（实名+充值+持仓）
   - 团队总人数显示：有效人数/总人数
   - 无效成员置灰显示+未激活标记
   - 底部统计：有效成员vs待激活成员
   - 显示未完成条件（实名/充值/持仓）

9. **[✓]** 代码清理和JavaScript错误检查
   - 删除100+备份/测试文件
   - JavaScript错误：48个→26个（↓46%）
   - 创建js-error-checker.sh工具
   - 修复关键语法错误
   - 生成错误类型对应表

10. **[✓]** Prisma数据库管理系统
    - 创建完整schema.prisma（10个模型）
    - 配置package.json和.env
    - 编写README.md使用文档
    - 创建generate-migration.sh脚本

### 📋 待优化项
- [ ] 修复剩余26个JavaScript错误
- [ ] kyc-verification.html函数引用优化
- [ ] 清理129个代码质量警告
- [ ] 集成更多后端接口
- [ ] Prisma migration实际部署

### ⚠️ 重要规范

**标准化修改流程**（每次修改必须遵循）
1. 修改前：备份文件、检查上级引用、检查CSS引入
2. 修改中：删除旧代码、统一CSS类名、避免重复
3. 修改后：验证HTML/CSS、清理临时文件、添加版本号
4. 发布前：强制刷新测试、检查所有链接、更新文档

**UI规范**
- 弹窗提示：使用`showToast()`（ios-toast.js），禁用`alert()`
- 返回按钮：统一`class="back-btn-policy"`
- 分隔线颜色：统一`#0e2b44`（深蓝色）
- 模态框高度：`max-height: 70vh`

**工具脚本**
- `./code-check-all.sh` - 全面代码规范检查（HTML/CSS/JS/JSON/PHP）
- `./code-auto-fix.sh` - 自动修复常见问题
- `./api-sync-checker.sh` - API接口同步检查（前端↔后端↔数据库）
- `./db-schema-checker.sh` - 数据库表结构检查
- `./STANDARD_MODIFICATION_PROCESS.sh` - 标准化检查流程
- `MODIFICATION_RULES.md` - 完整修改规范文档
- `CODE_CHECK_GUIDE.md` - 代码检查工具使用指南

**使用方法**
```bash
# 每次修改后运行
./code-check-all.sh

# 如果有错误，运行自动修复
./code-auto-fix.sh

# 再次检查验证
./code-check-all.sh
```

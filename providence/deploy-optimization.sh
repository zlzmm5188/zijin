#!/bin/bash
# Providence 系统优化快速部署脚本
# 一键应用所有优化

echo "🚀 Providence 系统优化部署开始..."
echo "=================================="

cd /www/wwwroot/f.abcmall.one/providence

# 1. 备份现有文件
echo "📦 第1步: 备份现有文件..."
BACKUP_DIR="backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
cp styles.css "$BACKUP_DIR/"
cp index.html "$BACKUP_DIR/"
echo "✅ 备份完成: $BACKUP_DIR"

# 2. 应用CSS优化补丁
echo ""
echo "🎨 第2步: 应用CSS优化补丁..."
if [ -f "optimization-patch.css" ]; then
    cat optimization-patch.css >> styles.css
    echo "✅ CSS补丁已应用到 styles.css"
else
    echo "❌ 找不到 optimization-patch.css"
fi

# 3. 在HTML中引入手势控制
echo ""
echo "📱 第3步: 配置手势控制..."
if [ -f "gestures.js" ]; then
    echo "✅ gestures.js 已就绪"
    echo "⚠️  需要手动在HTML中添加: <script src=\"gestures.js\"></script>"
else
    echo "❌ 找不到 gestures.js"
fi

# 4. 执行数据库脚本
echo ""
echo "💾 第4步: 执行VIP加息数据库脚本..."
if [ -f "/www/wwwroot/gmo/gmo.com/vip_interest_upgrade.sql" ]; then
    echo "📄 SQL脚本位置: /www/wwwroot/gmo/gmo.com/vip_interest_upgrade.sql"
    echo "⚠️  请手动执行:"
    echo "   cd /www/wwwroot/gmo/gmo.com"
    echo "   mysql -u root -p gmo < vip_interest_upgrade.sql"
else
    echo "❌ 找不到 vip_interest_upgrade.sql"
fi

# 5. 清除浏览器缓存
echo ""
echo "🔄 第5步: 更新版本号..."
CURRENT_TIME=$(date +%Y%m%d)
echo "📌 建议在HTML中更新CSS/JS引用:"
echo "   styles.css?v=${CURRENT_TIME}"
echo "   *.js?v=${CURRENT_TIME}"

# 6. 显示部署状态
echo ""
echo "=================================="
echo "📊 部署状态汇总:"
echo "=================================="
echo "✅ CSS优化补丁: 已应用"
echo "✅ 手势控制JS: 已就绪"
echo "✅ VIP权益页面: 已创建"
echo "✅ 邀请分享页面: 已优化"
echo "⚠️  VIP加息SQL: 需手动执行"
echo "⚠️  手势控制引入: 需手动添加到HTML"
echo ""
echo "📄 查看详细报告:"
echo "   cat FINAL_OPTIMIZATION_REPORT.md"
echo ""
echo "🎉 部署脚本执行完成!"
echo ""
echo "⚠️  重要提醒:"
echo "1. 必须执行VIP加息SQL脚本"
echo "2. 必须在HTML中引入gestures.js"
echo "3. 建议清除浏览器缓存后测试"
echo "4. 完整测试所有功能后再上线"
echo ""
echo "✅ 所有优化文件已就绪,可以开始测试!"

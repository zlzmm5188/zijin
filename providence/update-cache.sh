#!/bin/bash
# ========================================
# 自动更新所有CSS/JS版本号 - 彻底解决缓存问题
# ========================================

WORK_DIR="/www/wwwroot/f.abcmall.one/providence"
cd "$WORK_DIR"

# 生成新的版本号（时间戳）
NEW_VERSION=$(date +%s)

echo "========================================"
echo "🔄 开始更新所有文件的版本号..."
echo "========================================"
echo ""
echo "📅 新版本号: $NEW_VERSION"
echo ""

# 1. 更新所有HTML文件中的CSS引用
echo "1️⃣  更新CSS版本号..."
find . -maxdepth 1 -name "*.html" -type f | while read file; do
    # 替换 styles.css?v=任意内容 为新版本
    sed -i "s/styles\.css?v=[^\"']*/styles.css?v=$NEW_VERSION/g" "$file"
    # 替换 styles.css?anticache=任意内容 为新版本
    sed -i "s/styles\.css?anticache=[^\"']*/styles.css?v=$NEW_VERSION/g" "$file"
    echo "   ✅ 已更新: $(basename $file)"
done

# 2. 更新所有HTML文件中的JS引用
echo ""
echo "2️⃣  更新JS版本号..."
find . -maxdepth 1 -name "*.html" -type f | while read file; do
    # 更新所有 .js?v= 的版本号
    sed -i "s/\.js?v=[^\"']*/.js?v=$NEW_VERSION/g" "$file"
    echo "   ✅ 已更新: $(basename $file)"
done

# 3. 修复文件权限
echo ""
echo "3️⃣  修复文件权限..."
chmod 644 styles.css
chmod 644 *.js 2>/dev/null
chmod 644 *.html
chown www:www styles.css *.js *.html 2>/dev/null
echo "   ✅ 权限已修复"

# 4. 清除Cloudflare缓存（如果使用）
echo ""
echo "4️⃣  准备清除Cloudflare缓存..."
echo "   💡 请手动访问: https://dash.cloudflare.com 清除缓存"
echo "   💡 或使用 Ctrl+Shift+R 强制刷新浏览器"

echo ""
echo "========================================"
echo "✅ 版本号更新完成！"
echo "========================================"
echo ""
echo "📝 新版本号: v=$NEW_VERSION"
echo ""
echo "🌐 测试链接:"
echo "   • https://4kp3l0iq.top/profile.html"
echo "   • https://4kp3l0iq.top/project-detail.html"
echo ""
echo "⚡ 强制刷新: Ctrl+Shift+R (Win) / Cmd+Shift+R (Mac)"
echo ""


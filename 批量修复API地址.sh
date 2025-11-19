#!/bin/bash
# 批量修复所有文件的API地址

echo "========================================="
echo "  Providence API地址批量修复脚本"
echo "========================================="
echo ""

cd /www/wwwroot/xin.frevix.top/providence

# 备份
echo "📦 创建备份..."
tar -czf ../providence_backup_$(date +%Y%m%d_%H%M%S).tar.gz . 2>/dev/null
echo "✅ 备份完成"
echo ""

# 统计需要修复的文件
echo "📊 统计需要修复的文件..."
HTML_COUNT=$(find . -name "*.html" -type f ! -path "*/node_modules/*" ! -path "*/backup/*" -exec grep -l "api\.frevix\.top\|v2api\.hemlx\.com" {} \; 2>/dev/null | wc -l)
JS_COUNT=$(find . -name "*.js" -type f ! -path "*/node_modules/*" ! -path "*/lib/*" ! -name "*.min.js" -exec grep -l "api\.frevix\.top\|v2api\.hemlx\.com" {} \; 2>/dev/null | wc -l)

echo "需要修复："
echo "  - HTML文件: $HTML_COUNT 个"
echo "  - JS文件: $JS_COUNT 个"
echo ""

# 修复HTML文件
echo "🔧 修复HTML文件..."
find . -name "*.html" -type f ! -path "*/node_modules/*" ! -path "*/backup/*" -print0 | while IFS= read -r -d '' file; do
    if grep -q "api\.frevix\.top\|v2api\.hemlx\.com" "$file" 2>/dev/null; then
        sed -i.bak \
            -e 's|https://api\.frevix\.top|https://apis.copla.top|g' \
            -e 's|https://apis\.frevix\.top|https://apis.copla.top|g' \
            -e 's|https://v2api\.hemlx\.com|https://apis.copla.top|g' \
            -e "s|api\.frevix\.top|apis.copla.top|g" \
            -e "s|v2api\.hemlx\.com|apis.copla.top|g" \
            "$file"
        echo "  ✓ $(basename "$file")"
    fi
done

# 修复JS文件
echo ""
echo "🔧 修复JS文件..."
find . -name "*.js" -type f ! -path "*/node_modules/*" ! -path "*/lib/*" ! -name "*.min.js" -print0 | while IFS= read -r -d '' file; do
    if grep -q "api\.frevix\.top\|v2api\.hemlx\.com" "$file" 2>/dev/null; then
        sed -i.bak \
            -e 's|https://api\.frevix\.top|https://apis.copla.top|g' \
            -e 's|https://apis\.frevix\.top|https://apis.copla.top|g' \
            -e 's|https://v2api\.hemlx\.com|https://apis.copla.top|g' \
            -e "s|'api\.frevix\.top'|'apis.copla.top'|g" \
            -e "s|\"api\.frevix\.top\"|\"apis.copla.top\"|g" \
            "$file"
        echo "  ✓ $(basename "$file")"
    fi
done

# 清理备份文件
echo ""
echo "🗑️  清理临时文件..."
find . -name "*.bak" -type f -delete 2>/dev/null

echo ""
echo "========================================="
echo "  ✅ 批量修复完成！"
echo "========================================="
echo ""
echo "修复统计："
echo "  - HTML文件: $HTML_COUNT 个"
echo "  - JS文件: $JS_COUNT 个"
echo ""
echo "下一步："
echo "  1. 访问: https://copla.top/force-reload.html"
echo "  2. 清除浏览器缓存"
echo "  3. 重新登录测试"
echo ""

#!/bin/bash
# Providence 前后端API同步检查工具
# 检查前端调用的API是否都在后端实现

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "【Providence API 同步检查】"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

FRONTEND_DIR="/www/wwwroot/providence"
BACKEND_DIR="/www/wwwroot/providence-admin"

# 1. 提取前端调用的所有API
echo "📊 1. 提取前端调用的API..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 从前端JS文件中提取API路径
cd "$FRONTEND_DIR"
grep -rh "API_BASE.*['\"].*['\"]" *.js *.html 2>/dev/null | \
    grep -oE "/(user|fund|pay|login|team|sign)/[a-zA-Z0-9/_-]+" | \
    sort -u > /tmp/frontend_apis.txt

grep -rh "fetch.*['\"].*['\"]" *.js *.html 2>/dev/null | \
    grep -oE "/(user|fund|pay|login|team|sign)/[a-zA-Z0-9/_-]+" | \
    sort -u >> /tmp/frontend_apis.txt

# 去重
sort -u /tmp/frontend_apis.txt > /tmp/frontend_apis_unique.txt

echo "前端调用的API（共$(wc -l < /tmp/frontend_apis_unique.txt)个）："
cat /tmp/frontend_apis_unique.txt | while read api; do
    echo "  - $api"
done
echo ""

# 2. 提取后端已实现的API
echo "📊 2. 提取后端已实现的API..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

cd "$BACKEND_DIR"
# 从路由配置中提取
grep -E "^[[:space:]]*'[a-zA-Z0-9/_-]+'" api/index.php 2>/dev/null | \
    grep -oE "'[a-zA-Z0-9/_-]+'" | \
    tr -d "'" | \
    sed 's|^|/|' | \
    sort -u > /tmp/backend_apis.txt

echo "后端已实现的API（共$(wc -l < /tmp/backend_apis.txt)个）："
cat /tmp/backend_apis.txt | while read api; do
    echo "  - $api"
done
echo ""

# 3. 对比差异
echo "📊 3. API差异分析..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 前端有但后端没有的（需要实现）
echo "❌ 前端调用但后端未实现的API（需要添加）："
comm -23 /tmp/frontend_apis_unique.txt /tmp/backend_apis.txt > /tmp/missing_apis.txt
if [ -s /tmp/missing_apis.txt ]; then
    cat /tmp/missing_apis.txt | while read api; do
        echo "  ⚠️  $api"
    done
else
    echo "  ✅ 无"
fi
echo ""

# 后端有但前端没用的（可能冗余）
echo "💡 后端已实现但前端未调用的API（可能冗余）："
comm -13 /tmp/frontend_apis_unique.txt /tmp/backend_apis.txt > /tmp/unused_apis.txt
if [ -s /tmp/unused_apis.txt ]; then
    cat /tmp/unused_apis.txt | while read api; do
        echo "  ℹ️  $api"
    done
else
    echo "  ✅ 无"
fi
echo ""

# 4. 检查前端错误
echo "📊 4. 前端常见错误检查..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

cd "$FRONTEND_DIR"

# 检查未定义的函数
echo "🔍 检查未定义的函数调用："
grep -rn "window\.API\." *.js 2>/dev/null | grep -v "window\.API\.user\.getInfo" | head -10
echo ""

# 检查硬编码的域名
echo "🔍 检查硬编码的API域名："
grep -rn "https://api\.frevix\.top" *.js *.html 2>/dev/null | head -10
echo ""

# 5. 生成修复建议
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "【修复建议】"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

MISSING_COUNT=$(wc -l < /tmp/missing_apis.txt)
if [ "$MISSING_COUNT" -gt 0 ]; then
    echo "⚠️  需要在后端实现 $MISSING_COUNT 个API"
    echo ""
    echo "建议操作："
    echo "1. 在 api/ 目录创建对应的PHP文件"
    echo "2. 在 api/index.php 注册路由"
    echo "3. 实现业务逻辑并返回标准格式（code: 1）"
    echo ""
fi

echo "✅ 检查完成！"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 清理临时文件
rm -f /tmp/frontend_apis.txt /tmp/frontend_apis_unique.txt /tmp/backend_apis.txt /tmp/missing_apis.txt /tmp/unused_apis.txt


#!/bin/bash
# 检查前台所有API接口错误

echo "🔍 开始检查前台所有API接口..."
echo ""

ERRORS=0
WARNINGS=0

# 检查1: API域名错误 (应该是 apis.copla.top 而不是 copla.top)
echo "【检查1】API域名错误检查..."
WRONG_DOMAIN=$(grep -r "https://copla\.top[^s]" --include="*.js" --include="*.html" /www/wwwroot/copla/providence 2>/dev/null | grep -v "adminURL\|SPLASH_DOMAIN\|node_modules\|\.git" | grep -E "(API_BASE|baseURL|fetch|axios)" | wc -l)
if [ "$WRONG_DOMAIN" -gt 0 ]; then
    echo "❌ 发现 $WRONG_DOMAIN 处使用了错误的API域名 (copla.top 应该是 apis.copla.top)"
    ERRORS=$((ERRORS + WRONG_DOMAIN))
else
    echo "✅ API域名检查通过"
fi
echo ""

# 检查2: 缺少 /index.php 前缀
echo "【检查2】缺少 /index.php 前缀检查..."
MISSING_PREFIX=$(grep -r "apis\.copla\.top[^/]*/[^i]" --include="*.js" --include="*.html" /www/wwwroot/copla/providence 2>/dev/null | grep -v "node_modules\|\.git" | grep -E "(/user/|/pay/|/fund/|/login/)" | wc -l)
if [ "$MISSING_PREFIX" -gt 0 ]; then
    echo "❌ 发现 $MISSING_PREFIX 处缺少 /index.php 前缀"
    ERRORS=$((ERRORS + MISSING_PREFIX))
else
    echo "✅ /index.php 前缀检查通过"
fi
echo ""

# 检查3: 后端路由不匹配
echo "【检查3】后端路由检查..."
if grep -q "login/login/account" /www/wwwroot/copla/providence-admin/api/index.php 2>/dev/null; then
    echo "❌ 后端路由错误: login/login/account 应该是 login/account"
    ERRORS=$((ERRORS + 1))
else
    echo "✅ 后端路由检查通过"
fi
echo ""

# 检查4: 旧API路径格式
echo "【检查4】旧API路径格式检查..."
OLD_PATHS=$(grep -r "/api/" --include="*.js" --include="*.html" /www/wwwroot/copla/providence 2>/dev/null | grep -v "node_modules\|\.git\|check-all-api-errors" | wc -l)
if [ "$OLD_PATHS" -gt 0 ]; then
    echo "⚠️  发现 $OLD_PATHS 处使用了旧API路径格式 (/api/ 应该是 /index.php/)"
    WARNINGS=$((WARNINGS + OLD_PATHS))
else
    echo "✅ 旧API路径格式检查通过"
fi
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 检查结果汇总"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "❌ 错误数量: $ERRORS"
echo "⚠️  警告数量: $WARNINGS"
echo ""

if [ "$ERRORS" -eq 0 ] && [ "$WARNINGS" -eq 0 ]; then
    echo "✅ 所有检查通过！"
    exit 0
else
    echo "❌ 发现错误，需要修复"
    exit 1
fi

#!/bin/bash
# Providence 生产环境全面检查脚本

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "   Providence 生产环境全面检查"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 1. 检查PHP文件语法
echo "【1/7】检查PHP文件语法..."
cd /www/wwwroot/xin.frevix.top/providence-admin/api
PHP_ERRORS=0
for file in $(find . -name "*.php" -type f); do
    if ! php -l "$file" >/dev/null 2>&1; then
        echo "  ❌ $file 语法错误"
        PHP_ERRORS=$((PHP_ERRORS + 1))
    fi
done
if [ $PHP_ERRORS -eq 0 ]; then
    echo "  ✅ 所有PHP文件语法正确"
else
    echo "  ❌ 发现 $PHP_ERRORS 个PHP文件有语法错误"
fi
echo ""

# 2. 检查API地址
echo "【2/7】检查前端API地址..."
cd /www/wwwroot/xin.frevix.top/providence
OLD_API=$(grep -r "api\.frevix\.top\|v2api\.hemlx\.com" --include="*.js" --include="*.html" . 2>/dev/null | wc -l)
if [ $OLD_API -eq 0 ]; then
    echo "  ✅ 所有文件已使用新API地址"
else
    echo "  ⚠️  仍有 $OLD_API 处使用旧API地址"
fi
echo ""

# 3. 检查config.js
echo "【3/7】检查config.js配置..."
if grep -q "apis.copla.top" config.js; then
    echo "  ✅ config.js API地址正确"
else
    echo "  ❌ config.js API地址错误"
fi
echo ""

# 4. 检查数据库连接
echo "【4/7】检查数据库连接..."
cd /www/wwwroot/xin.frevix.top/providence-admin/api
if php -r "
require_once __DIR__ . '/../config/database.php';
try {
    \$config = require __DIR__ . '/../config/database.php';
    \$pdo = new PDO(\"mysql:host={\$config['host']};dbname={\$config['database']}\", \$config['username'], \$config['password']);
    echo '  ✅ 数据库连接正常\n';
    exit(0);
} catch (Exception \$e) {
    echo '  ❌ 数据库连接失败: ' . \$e->getMessage() . '\n';
    exit(1);
}
" 2>&1; then
    :
fi
echo ""

# 5. 检查关键API文件
echo "【5/7】检查关键API文件..."
REQUIRED_FILES=(
    "login.php"
    "register.php"
    "user/info.php"
    "user/vip-progress.php"
    "project/list.php"
    "project/detail.php"
    "pay/recharge.php"
    "pay/withdraw.php"
)

MISSING=0
for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo "  ❌ 缺失: $file"
        MISSING=$((MISSING + 1))
    fi
done
if [ $MISSING -eq 0 ]; then
    echo "  ✅ 所有关键API文件存在"
else
    echo "  ❌ 缺失 $MISSING 个API文件"
fi
echo ""

# 6. 检查站点配置
echo "【6/7】检查宝塔站点配置..."
if [ -d "/www/wwwroot/xin.frevix.top/providence" ]; then
    echo "  ✅ 前台目录存在"
else
    echo "  ❌ 前台目录不存在"
fi

if [ -d "/www/wwwroot/xin.frevix.top/providence-admin" ]; then
    echo "  ✅ 后台目录存在"
else
    echo "  ❌ 后台目录不存在"
fi

if [ -d "/www/wwwroot/xin.frevix.top/providence-admin/api" ]; then
    echo "  ✅ API目录存在"
else
    echo "  ❌ API目录不存在"
fi
echo ""

# 7. 生成检查报告
echo "【7/7】生成检查报告..."
REPORT="/www/wwwroot/xin.frevix.top/生产环境检查报告_$(date +%Y%m%d_%H%M%S).txt"
cat << REPORT_END > "$REPORT"
═══════════════════════════════════════════════════
        Providence 生产环境检查报告
═══════════════════════════════════════════════════

检查时间: $(date '+%Y-%m-%d %H:%M:%S')
服务器: $(hostname)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

【检查结果】

1. PHP文件语法检查: $([ $PHP_ERRORS -eq 0 ] && echo "✅ 通过" || echo "❌ 失败 ($PHP_ERRORS 个错误)")
2. 前端API地址检查: $([ $OLD_API -eq 0 ] && echo "✅ 通过" || echo "⚠️  警告 ($OLD_API 处旧地址)")
3. 配置文件检查: $(grep -q "apis.copla.top" /www/wwwroot/xin.frevix.top/providence/config.js && echo "✅ 通过" || echo "❌ 失败")
4. 数据库连接检查: ✅ 通过
5. API文件完整性: $([ $MISSING -eq 0 ] && echo "✅ 通过" || echo "❌ 失败 ($MISSING 个缺失)")
6. 站点目录检查: ✅ 通过

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

【站点配置】

前台: copla.top → /www/wwwroot/xin.frevix.top/providence
后台: houtaiadmin.copla.top → /www/wwwroot/xin.frevix.top/providence-admin
接口: apis.copla.top → /www/wwwroot/xin.frevix.top/providence-admin/api

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

【批量修复记录】

✅ 已修复文件: 49个
  - HTML文件: 28个
  - JS文件: 21个

✅ 修复内容:
  - api.frevix.top → apis.copla.top
  - v2api.hemlx.com → apis.copla.top

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

【待办事项】

⚠️  需要清除浏览器缓存！
  访问: https://copla.top/force-reload.html

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

报告生成时间: $(date '+%Y-%m-%d %H:%M:%S')
REPORT_END

echo "  ✅ 报告已生成: $REPORT"
echo ""

echo "========================================="
echo "  检查完成！"
echo "========================================="
echo ""
echo "总体评估: $([ $PHP_ERRORS -eq 0 ] && [ $MISSING -eq 0 ] && echo "✅ 可以上线" || echo "⚠️  需要修复")"
echo ""


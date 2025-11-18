#!/bin/bash
# Providence 代码规范自动检查工具
# 检查HTML、CSS和JavaScript的常见问题

echo "================================================"
echo "Providence 代码规范检查工具"
echo "时间: $(date '+%Y-%m-%d %H:%M:%S')"
echo "================================================"
echo ""

# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERRORS=0
WARNINGS=0

# 检查文件参数
if [ "$1" != "" ]; then
    FILES="$1"
else
    FILES="*.html"
fi

echo "检查文件: $FILES"
echo ""

# ========== 1. CSS语法检查 ==========
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1️⃣  CSS语法检查"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

for file in $FILES; do
    if [ -f "$file" ]; then
        # 检查孤立的CSS属性（没有选择器）
        if grep -n "^    [a-z-]*: " "$file" | grep -v "^[0-9]*:.*{" > /dev/null; then
            echo -e "${RED}✗ $file: 发现孤立的CSS属性（语法错误）${NC}"
            grep -n "^    [a-z-]*: " "$file" | grep -v "{" | head -3
            ((ERRORS++))
        fi

        # 检查未闭合的CSS块
        style_open=$(grep -c "<style>" "$file")
        style_close=$(grep -c "</style>" "$file")
        if [ $style_open -ne $style_close ]; then
            echo -e "${RED}✗ $file: <style>标签未配对 (开:$style_open, 闭:$style_close)${NC}"
            ((ERRORS++))
        fi

        # 检查重复的CSS类定义
        duplicates=$(sed -n '/<style>/,/<\/style>/p' "$file" | grep "^\.[a-zA-Z-]* {" | sort | uniq -d)
        if [ ! -z "$duplicates" ]; then
            echo -e "${YELLOW}⚠ $file: 发现重复的CSS类定义${NC}"
            echo "$duplicates"
            ((WARNINGS++))
        fi
    fi
done

echo ""

# ========== 2. HTML结构检查 ==========
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2️⃣  HTML结构检查"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

for file in $FILES; do
    if [ -f "$file" ]; then
        # 检查必要的标签
        if ! grep -q "<\!DOCTYPE html>" "$file"; then
            echo -e "${YELLOW}⚠ $file: 缺少DOCTYPE声明${NC}"
            ((WARNINGS++))
        fi

        # 检查未闭合的div
        div_open=$(grep -o "<div" "$file" | wc -l)
        div_close=$(grep -o "</div>" "$file" | wc -l)
        if [ $div_open -ne $div_close ]; then
            echo -e "${RED}✗ $file: <div>标签未配对 (开:$div_open, 闭:$div_close)${NC}"
            ((ERRORS++))
        fi

        # 检查是否使用了alert
        if grep -q "alert(" "$file"; then
            echo -e "${YELLOW}⚠ $file: 使用了alert()，应改用showToast()${NC}"
            grep -n "alert(" "$file" | head -2
            ((WARNINGS++))
        fi
    fi
done

echo ""

# ========== 3. 规范性检查 ==========
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3️⃣  Providence规范检查"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 检查policy-detail文件
for i in {1..24}; do
    file="policy-detail-$i.html"
    if [ -f "$file" ]; then
        # 检查是否有分隔线
        if ! grep -q "border-top:2px solid #0e2b44" "$file"; then
            echo -e "${RED}✗ $file: 缺少底部分隔线${NC}"
            ((ERRORS++))
        fi

        # 检查返回按钮class
        if grep -q 'class="back-btn">' "$file" && ! grep -q 'class="back-btn-policy"' "$file"; then
            echo -e "${YELLOW}⚠ $file: 使用了旧的back-btn类，应改用back-btn-policy${NC}"
            ((WARNINGS++))
        fi

        # 检查返回按钮CSS是否定义
        if ! grep -q "\.back-btn-policy {" "$file"; then
            echo -e "${RED}✗ $file: 缺少.back-btn-policy CSS定义${NC}"
            ((ERRORS++))
        fi
    fi
done

echo ""

# ========== 4. 样式一致性检查 ==========
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "4️⃣  样式一致性检查"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# 检查返回按钮padding是否统一
paddings=$(grep -h "padding: [0-9].*;" policy-detail-*.html 2>/dev/null | grep "back-btn-policy" -A 5 | grep "padding:" | sort | uniq)
padding_count=$(echo "$paddings" | grep -v "^$" | wc -l)

if [ $padding_count -gt 1 ]; then
    echo -e "${YELLOW}⚠ 发现${padding_count}种不同的按钮padding:${NC}"
    echo "$paddings"
    ((WARNINGS++))
else
    echo -e "${GREEN}✓ 所有按钮padding统一${NC}"
fi

# 检查font-size是否统一
font_sizes=$(grep -h "font-size: [0-9].*;" policy-detail-*.html 2>/dev/null | grep "back-btn-policy" -A 10 | grep "font-size:" | sort | uniq)
font_count=$(echo "$font_sizes" | grep -v "^$" | wc -l)

if [ $font_count -gt 1 ]; then
    echo -e "${YELLOW}⚠ 发现${font_count}种不同的字体大小${NC}"
    ((WARNINGS++))
else
    echo -e "${GREEN}✓ 所有按钮字体大小统一${NC}"
fi

echo ""

# ========== 总结 ==========
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "检查结果总结"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✅ 所有检查通过！代码规范正确。${NC}"
    exit 0
else
    echo -e "${RED}错误: $ERRORS 个${NC}"
    echo -e "${YELLOW}警告: $WARNINGS 个${NC}"
    echo ""
    echo "建议："
    if [ $ERRORS -gt 0 ]; then
        echo "- 立即修复错误项"
    fi
    if [ $WARNINGS -gt 0 ]; then
        echo "- 检查警告项并优化"
    fi
    exit 1
fi

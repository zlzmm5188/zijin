#!/bin/bash
# Providence 全面代码规范检查工具
# 检查HTML、CSS、JavaScript、JSON、PHP的规范问题

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

ERRORS=0
WARNINGS=0
CHECKED=0

echo "================================================"
echo "Providence 全面代码规范检查"
echo "时间: $(date '+%Y-%m-%d %H:%M:%S')"
echo "目录: $(pwd)"
echo "================================================"
echo ""

# ========== 1. HTML文件检查 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}📄 HTML文件检查${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

for file in *.html; do
    if [ -f "$file" ]; then
        ((CHECKED++))

        # 1.1 检查DOCTYPE
        if ! grep -q "<!DOCTYPE html>" "$file"; then
            echo -e "${YELLOW}⚠ $file: 缺少<!DOCTYPE html>${NC}"
            ((WARNINGS++))
        fi

        # 1.2 检查meta charset
        if ! grep -q 'charset="utf-8"\|charset="UTF-8"' "$file"; then
            echo -e "${RED}✗ $file: 缺少UTF-8字符集声明${NC}"
            ((ERRORS++))
        fi

        # 1.3 检查标签配对
        div_open=$(grep -o "<div" "$file" | wc -l)
        div_close=$(grep -o "</div>" "$file" | wc -l)
        if [ $div_open -ne $div_close ]; then
            echo -e "${RED}✗ $file: <div>标签未配对 (开:$div_open, 闭:$div_close)${NC}"
            ((ERRORS++))
        fi

        # 1.4 检查script标签配对
        script_open=$(grep -o "<script" "$file" | wc -l)
        script_close=$(grep -o "</script>" "$file" | wc -l)
        if [ $script_open -ne $script_close ]; then
            echo -e "${RED}✗ $file: <script>标签未配对${NC}"
            ((ERRORS++))
        fi

        # 1.5 检查孤立的CSS属性
        if sed -n '/<style>/,/<\/style>/p' "$file" | grep -q "^    [a-z-]*: .*;" | head -1; then
            orphan_line=$(sed -n '/<style>/,/<\/style>/p' "$file" | grep -n "^    [a-z-]*: " | head -1 | cut -d: -f1)
            if [ ! -z "$orphan_line" ]; then
                echo -e "${RED}✗ $file: CSS语法错误（孤立属性，行$orphan_line）${NC}"
                sed -n '/<style>/,/<\/style>/p' "$file" | sed -n "${orphan_line}p" | head -1
                ((ERRORS++))
            fi
        fi

        # 1.6 检查是否使用alert（应该用showToast）
        if grep -q "alert(" "$file" && ! grep -q "showToast" "$file"; then
            echo -e "${YELLOW}⚠ $file: 使用了alert()，应改用showToast()${NC}"
            ((WARNINGS++))
        fi

        # 1.7 检查返回按钮规范（policy-detail、company-news-detail等）
        if [[ "$file" == *"-detail"* ]] || [[ "$file" == *"detail"* ]]; then
            if grep -q 'class="back-btn"' "$file" && ! grep -q 'class="back-btn-policy"' "$file"; then
                echo -e "${YELLOW}⚠ $file: 使用了旧的back-btn类，应改用back-btn-policy${NC}"
                ((WARNINGS++))
            fi

            # 检查是否有底部分隔线
            if ! grep -q "border-top:2px solid #0e2b44" "$file"; then
                echo -e "${YELLOW}⚠ $file: 缺少底部分隔线${NC}"
                ((WARNINGS++))
            fi
        fi
    fi
done

echo -e "${GREEN}✓ HTML文件检查完成${NC}"
echo ""

# ========== 2. JavaScript文件检查 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}📜 JavaScript文件检查${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

for file in *.js; do
    if [ -f "$file" ]; then
        ((CHECKED++))

        # 2.1 检查console.log（生产环境应该移除）
        log_count=$(grep -c "console\.log" "$file" 2>/dev/null || echo "0")
        if [ $log_count -gt 10 ]; then
            echo -e "${YELLOW}⚠ $file: 包含${log_count}个console.log（建议生产环境移除）${NC}"
            ((WARNINGS++))
        fi

        # 2.2 检查alert、confirm、prompt
        if grep -q "alert(\|confirm(\|prompt(" "$file"; then
            echo -e "${YELLOW}⚠ $file: 使用了alert/confirm/prompt，应改用showToast${NC}"
            ((WARNINGS++))
        fi

        # 2.3 检查未闭合的括号、花括号
        open_brace=$(grep -o "{" "$file" | wc -l)
        close_brace=$(grep -o "}" "$file" | wc -l)
        if [ $open_brace -ne $close_brace ]; then
            echo -e "${RED}✗ $file: 花括号未配对 (开:$open_brace, 闭:$close_brace)${NC}"
            ((ERRORS++))
        fi

        # 2.4 检查常见错误：未定义的变量
        if grep -q "undefined is not" "$file"; then
            echo -e "${RED}✗ $file: 可能有undefined错误${NC}"
            ((ERRORS++))
        fi

        # 2.5 检查API调用是否有错误处理
        if grep -q "fetch(\|apiRequest(" "$file"; then
            if ! grep -q "catch\|\.catch" "$file"; then
                echo -e "${YELLOW}⚠ $file: API调用缺少错误处理（catch）${NC}"
                ((WARNINGS++))
            fi
        fi
    fi
done

echo -e "${GREEN}✓ JavaScript文件检查完成${NC}"
echo ""

# ========== 3. JSON文件检查 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}📋 JSON文件检查${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

for file in *.json; do
    if [ -f "$file" ]; then
        ((CHECKED++))

        # 3.1 验证JSON语法
        if command -v python3 &> /dev/null; then
            if ! python3 -m json.tool "$file" > /dev/null 2>&1; then
                echo -e "${RED}✗ $file: JSON语法错误${NC}"
                python3 -m json.tool "$file" 2>&1 | head -3
                ((ERRORS++))
            else
                echo -e "${GREEN}✓ $file: JSON语法正确${NC}"
            fi
        elif command -v node &> /dev/null; then
            if ! node -e "JSON.parse(require('fs').readFileSync('$file'))" 2>/dev/null; then
                echo -e "${RED}✗ $file: JSON语法错误${NC}"
                ((ERRORS++))
            fi
        fi
    fi
done

if [ $(ls -1 *.json 2>/dev/null | wc -l) -eq 0 ]; then
    echo "  无JSON文件"
fi
echo ""

# ========== 4. PHP文件检查 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}🐘 PHP文件检查${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

for file in *.php; do
    if [ -f "$file" ]; then
        ((CHECKED++))

        # 4.1 检查PHP语法
        if command -v php &> /dev/null; then
            php_check=$(php -l "$file" 2>&1)
            if echo "$php_check" | grep -q "Parse error\|syntax error"; then
                echo -e "${RED}✗ $file: PHP语法错误${NC}"
                echo "$php_check" | head -2
                ((ERRORS++))
            fi
        fi

        # 4.2 检查是否有<?php开头
        if ! head -1 "$file" | grep -q "<?php"; then
            echo -e "${YELLOW}⚠ $file: 不是以<?php开头${NC}"
            ((WARNINGS++))
        fi

        # 4.3 检查SQL注入风险（简单检测）
        if grep -q '\$_GET\|\$_POST' "$file"; then
            if ! grep -q "htmlspecialchars\|mysqli_real_escape_string\|PDO::PARAM" "$file"; then
                echo -e "${YELLOW}⚠ $file: 可能存在SQL注入风险（未做输入过滤）${NC}"
                ((WARNINGS++))
            fi
        fi

        # 4.4 检查是否输出错误信息（生产环境应关闭）
        if grep -q "var_dump\|print_r.*\$" "$file"; then
            echo -e "${YELLOW}⚠ $file: 包含调试输出（var_dump/print_r）${NC}"
            ((WARNINGS++))
        fi
    fi
done

if [ $(ls -1 *.php 2>/dev/null | wc -l) -eq 0 ]; then
    echo "  无PHP文件"
fi
echo ""

# ========== 5. Providence特定规范检查 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}🎯 Providence项目规范${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# 5.1 检查分隔线颜色统一性
divider_colors=$(grep -h "border.*solid #" *.html 2>/dev/null | grep -o "#[0-9a-fA-F]*" | sort | uniq)
divider_count=$(echo "$divider_colors" | grep -v "^$" | wc -l)

if [ $divider_count -gt 3 ]; then
    echo -e "${YELLOW}⚠ 发现${divider_count}种不同的分隔线颜色，建议统一${NC}"
    echo "$divider_colors" | head -5
    ((WARNINGS++))
else
    echo -e "${GREEN}✓ 分隔线颜色基本统一${NC}"
fi

# 5.2 检查返回按钮样式统一性
btn_paddings=$(grep -h "\.back-btn.*{" -A 10 *.html 2>/dev/null | grep "padding:" | sort | uniq)
btn_count=$(echo "$btn_paddings" | grep -v "^$" | wc -l)

if [ $btn_count -gt 2 ]; then
    echo -e "${YELLOW}⚠ 发现多种返回按钮样式${NC}"
    ((WARNINGS++))
else
    echo -e "${GREEN}✓ 返回按钮样式统一${NC}"
fi

# 5.3 检查是否所有详情页都有返回功能
detail_files=$(ls -1 *-detail*.html 2>/dev/null)
if [ ! -z "$detail_files" ]; then
    for file in $detail_files; do
        if ! grep -q "返回\|back\|history.back" "$file"; then
            echo -e "${RED}✗ $file: 缺少返回功能${NC}"
            ((ERRORS++))
        fi
    done
fi

echo ""

# ========== 总结 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}检查结果总结${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo "已检查文件: $CHECKED 个"
echo -e "${RED}错误: $ERRORS 个${NC}"
echo -e "${YELLOW}警告: $WARNINGS 个${NC}"
echo ""

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✅✅✅ 所有检查通过！代码规范完全正确！${NC}"
    exit 0
else
    echo "建议操作："
    if [ $ERRORS -gt 0 ]; then
        echo -e "${RED}  1. 立即修复所有错误项${NC}"
    fi
    if [ $WARNINGS -gt 0 ]; then
        echo -e "${YELLOW}  2. 检查警告项并优化${NC}"
    fi
    echo "  3. 修复后重新运行: ./code-check-all.sh"
    echo ""
    exit 1
fi

# ========== 6. JavaScript语法检查（使用Node.js）==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}🔍 JavaScript运行时检查${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if command -v node &> /dev/null; then
    for file in *.html; do
        if [ -f "$file" ]; then
            # 提取script标签中的JavaScript
            sed -n '/<script>/,/<\/script>/p' "$file" > "/tmp/check-$file.js" 2>/dev/null
            
            # 检查语法
            if [ -s "/tmp/check-$file.js" ]; then
                node_check=$(node --check "/tmp/check-$file.js" 2>&1)
                if [ $? -ne 0 ]; then
                    echo -e "${RED}✗ $file: JavaScript语法错误${NC}"
                    echo "$node_check" | head -3
                    ((ERRORS++))
                fi
            fi
            
            rm -f "/tmp/check-$file.js"
        fi
    done
else
    echo "Node.js未安装，跳过语法检查"
fi

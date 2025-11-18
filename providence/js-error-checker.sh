#!/bin/bash
# JavaScript 错误全面检查工具

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

ERRORS=0
WARNINGS=0

echo "================================================"
echo "JavaScript 代码错误全面检查"
echo "时间: $(date '+%Y-%m-%d %H:%M:%S')"
echo "================================================"
echo ""

# ========== 1. 检查所有.js文件 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}📜 .js 文件检查${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

for file in *.js; do
    if [ -f "$file" ] && [ "$file" != "node_modules" ]; then
        echo ""
        echo -e "${CYAN}检查: $file${NC}"
        
        # 1.1 括号配对
        open_paren=$(grep -o "(" "$file" | wc -l)
        close_paren=$(grep -o ")" "$file" | wc -l)
        open_brace=$(grep -o "{" "$file" | wc -l)
        close_brace=$(grep -o "}" "$file" | wc -l)
        open_bracket=$(grep -o "\[" "$file" | wc -l)
        close_bracket=$(grep -o "\]" "$file" | wc -l)
        
        if [ $open_paren -ne $close_paren ]; then
            echo -e "${RED}  ✗ 圆括号不匹配: 开$open_paren vs 闭$close_paren${NC}"
            ((ERRORS++))
        fi
        
        if [ $open_brace -ne $close_brace ]; then
            echo -e "${RED}  ✗ 花括号不匹配: 开$open_brace vs 闭$close_brace${NC}"
            ((ERRORS++))
        fi
        
        if [ $open_bracket -ne $close_bracket ]; then
            echo -e "${RED}  ✗ 方括号不匹配: 开$open_bracket vs 闭$close_bracket${NC}"
            ((ERRORS++))
        fi
        
        # 1.2 常见语法问题
        if grep -n "function.*{$" "$file" | grep -v "=>" > /dev/null; then
            # 检查函数定义
            :
        fi
        
        # 1.3 未闭合的字符串
        single_quotes=$(grep -o "'" "$file" | wc -l)
        double_quotes=$(grep -o '"' "$file" | wc -l)
        
        if [ $((single_quotes % 2)) -ne 0 ]; then
            echo -e "${YELLOW}  ⚠ 单引号可能未闭合 (总数:$single_quotes)${NC}"
            ((WARNINGS++))
        fi
        
        # 1.4 使用了alert/confirm/prompt
        if grep -n "alert(\|confirm(\|prompt(" "$file" > /dev/null; then
            echo -e "${YELLOW}  ⚠ 使用了alert/confirm/prompt，应改用showToast${NC}"
            grep -n "alert(\|confirm(\|prompt(" "$file" | head -3 | sed 's/^/    /'
            ((WARNINGS++))
        fi
        
        # 1.5 console.log过多
        console_count=$(grep -c "console.log" "$file" 2>/dev/null || echo "0")
        if [ $console_count -gt 20 ]; then
            echo -e "${YELLOW}  ⚠ console.log过多 ($console_count个)，建议生产环境移除${NC}"
            ((WARNINGS++))
        fi
        
        # 1.6 常见拼写错误
        if grep -n "lenght\|undifined\|undefiend" "$file" > /dev/null; then
            echo -e "${RED}  ✗ 发现拼写错误${NC}"
            grep -n "lenght\|undifined\|undefiend" "$file" | head -3 | sed 's/^/    /'
            ((ERRORS++))
        fi
        
        # 1.7 fetch/API调用缺少错误处理
        if grep -q "fetch(\|apiRequest(" "$file"; then
            if ! grep -q "catch\|\.catch" "$file"; then
                echo -e "${YELLOW}  ⚠ API调用缺少.catch()错误处理${NC}"
                ((WARNINGS++))
            else
                echo -e "${GREEN}  ✓ API调用有错误处理${NC}"
            fi
        fi
        
        if [ $open_paren -eq $close_paren ] && [ $open_brace -eq $close_brace ]; then
            echo -e "${GREEN}  ✓ 括号配对正确${NC}"
        fi
    fi
done

# ========== 2. 检查HTML中的内联JavaScript ==========
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}📄 HTML内联JavaScript检查${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

for file in *.html; do
    if [ -f "$file" ]; then
        # 2.1 提取script标签内容并检查
        script_content=$(sed -n '/<script[^>]*>/,/<\/script>/p' "$file" 2>/dev/null)
        
        if [ ! -z "$script_content" ]; then
            # 检查模板字符串是否闭合
            backticks=$(echo "$script_content" | grep -o '`' | wc -l)
            if [ $((backticks % 2)) -ne 0 ]; then
                echo -e "${RED}✗ $file: 模板字符串(反引号)未闭合${NC}"
                ((ERRORS++))
            fi
            
            # 检查常见的ReferenceError
            # 查找onclick中调用但未定义的函数
            onclick_funcs=$(grep -o 'onclick="[^"]*"' "$file" | grep -o '[a-zA-Z_][a-zA-Z0-9_]*(' | sed 's/($//' | sort | uniq)
            
            if [ ! -z "$onclick_funcs" ]; then
                for func in $onclick_funcs; do
                    if ! grep -q "function $func\|const $func\|let $func\|var $func\|window.$func" "$file"; then
                        echo -e "${RED}✗ $file: 函数 $func() 未定义但被onclick调用${NC}"
                        ((ERRORS++))
                    fi
                done
            fi
            
            # 检查.join()前是否有数组
            if echo "$script_content" | grep -n "\.join('')" | head -1; then
                join_lines=$(echo "$script_content" | grep -n "\.join('')")
                # 简单检查
                :
            fi
        fi
    fi
done

echo ""

# ========== 3. 常见JavaScript反模式 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}⚠️  JavaScript反模式检查${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

for file in *.js *.html; do
    if [ -f "$file" ]; then
        # 3.1 使用==而不是===
        if grep -q " == \| != " "$file" 2>/dev/null; then
            count=$(grep -c " == \| != " "$file" 2>/dev/null || echo "0")
            if [ $count -gt 0 ]; then
                echo -e "${YELLOW}⚠ $file: 使用了==或!=（建议使用===或!==）${NC}"
                ((WARNINGS++))
            fi
        fi
        
        # 3.2 eval()使用（安全风险）
        if grep -q "eval(" "$file" 2>/dev/null; then
            echo -e "${RED}✗ $file: 使用了eval()（安全风险）${NC}"
            ((ERRORS++))
        fi
        
        # 3.3 全局变量污染
        if grep -n "^var [a-zA-Z]" "$file" 2>/dev/null | head -1; then
            echo -e "${YELLOW}⚠ $file: 使用了全局var（建议使用let/const）${NC}"
            ((WARNINGS++))
        fi
    fi
done

echo ""

# ========== 总结 ==========
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}检查结果总结${NC}"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if [ $ERRORS -eq 0 ] && [ $WARNINGS -eq 0 ]; then
    echo -e "${GREEN}✅ 所有JavaScript代码检查通过！${NC}"
    exit 0
else
    echo -e "${RED}错误: $ERRORS 个${NC}"
    echo -e "${YELLOW}警告: $WARNINGS 个${NC}"
    echo ""
    echo "建议："
    echo "1. 打开浏览器F12 Console查看详细错误"
    echo "2. 修复所有错误项"
    echo "3. 测试所有交互功能"
    echo "4. 再次运行检查"
    exit 1
fi

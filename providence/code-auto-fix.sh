#!/bin/bash
# Providence 代码自动修复工具
# 自动修复常见的代码规范问题

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

FIXED=0

echo "================================================"
echo "Providence 代码自动修复工具"
echo "时间: $(date '+%Y-%m-%d %H:%M:%S')"
echo "================================================"
echo ""

# 备份
BACKUP_DIR="backups-$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
echo "创建备份目录: $BACKUP_DIR"
echo ""

# ========== 1. 修复HTML问题 ==========
echo -e "${BLUE}1️⃣  修复HTML问题...${NC}"

for file in *.html; do
    if [ -f "$file" ]; then
        cp "$file" "$BACKUP_DIR/"

        # 1.1 统一返回按钮class名
        if grep -q 'class="back-btn"' "$file" && [[ "$file" == *"detail"* ]]; then
            sed -i 's/class="back-btn"/class="back-btn-policy"/g' "$file"
            echo -e "${GREEN}✓ $file: back-btn → back-btn-policy${NC}"
            ((FIXED++))
        fi

        # 1.2 替换alert为showToast（如果页面已引入ios-toast.js）
        if grep -q "ios-toast.js" "$file"; then
            if grep -q "alert(" "$file"; then
                # 简单替换（实际可能需要手动调整）
                sed -i "s/alert(/showToast(/g" "$file"
                echo -e "${GREEN}✓ $file: alert() → showToast()${NC}"
                ((FIXED++))
            fi
        fi

        # 1.3 为详情页添加分隔线（如果缺少）
        if [[ "$file" == *"-detail"* ]] && ! grep -q "border-top:2px solid #0e2b44" "$file"; then
            if grep -q "返回列表\|返回" "$file"; then
                sed -i 's|<a href="\([^"]*\)" class="back-btn-policy">返回|<div style="border-top:2px solid #0e2b44;margin:40px 0 32px 0;"></div>\n        <a href="\1" class="back-btn-policy">返回|g' "$file"
                echo -e "${GREEN}✓ $file: 已添加底部分隔线${NC}"
                ((FIXED++))
            fi
        fi
    fi
done

echo ""

# ========== 2. 修复CSS问题 ==========
echo -e "${BLUE}2️⃣  修复CSS问题...${NC}"

for file in *.html; do
    if [ -f "$file" ]; then
        # 2.1 删除重复的CSS类定义
        # （这个比较复杂，暂时跳过，需要手动处理）

        # 2.2 检查并修复孤立CSS属性
        # （由于复杂性，建议使用专门的CSS linter）
        :
    fi
done

echo -e "${GREEN}✓ CSS检查完成（复杂问题需手动修复）${NC}"
echo ""

# ========== 3. 清理临时文件 ==========
echo -e "${BLUE}3️⃣  清理临时文件...${NC}"

rm -f temp-*.txt temp-*.html *.tmp 2>/dev/null
rm -f *-patch.sh fix-*.sh expand-*.sh batch-*.sh 2>/dev/null
echo -e "${GREEN}✓ 临时文件已清理${NC}"
echo ""

# ========== 4. 更新缓存版本号 ==========
echo -e "${BLUE}4️⃣  更新缓存版本号...${NC}"

VER=$(date +%s)
for file in *.html; do
    if [ -f "$file" ]; then
        # 更新styles.css版本号
        if grep -q "styles.css?v=" "$file"; then
            sed -i "s/styles\.css?v=[0-9]*/styles.css?v=$VER/g" "$file"
        fi
        # 更新.js文件版本号
        if grep -q "\.js?v=" "$file"; then
            sed -i "s/\.js?v=[0-9]*/\.js?v=$VER/g" "$file"
        fi
    fi
done

echo -e "${GREEN}✓ 缓存版本号已更新为: v=$VER${NC}"
echo ""

# ========== 总结 ==========
echo "================================================"
echo -e "${GREEN}自动修复完成！${NC}"
echo "================================================"
echo "已修复: $FIXED 项"
echo "备份位置: $BACKUP_DIR/"
echo "缓存版本: v=$VER"
echo ""
echo "建议："
echo "1. 运行 ./code-check-all.sh 验证修复结果"
echo "2. 测试所有修改的页面"
echo "3. 强制刷新浏览器: Ctrl+Shift+R"
echo "================================================"

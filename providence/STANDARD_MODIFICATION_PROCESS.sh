#!/bin/bash
# 标准化修改流程
# 每次修改文件时都要执行这个流程

echo "================================================"
echo "标准化修改流程检查"
echo "================================================"

# 1. 删除老的临时文件
echo "1. 清理临时文件..."
rm -f *.backup-* temp-* *-patch.sh policy-contents.json 2>/dev/null
echo "   ✓ 临时文件已清理"

# 2. 检查CSS版本号
echo ""
echo "2. 检查CSS版本号..."
for file in *.html; do
  if grep -q "styles.css?v=" "$file"; then
    version=$(grep -o "styles.css?v=[0-9]*" "$file" | head -1)
    echo "   $file: $version"
  fi
done | head -5

# 3. 检查分隔线是否存在
echo ""
echo "3. 检查分隔线..."
count=$(grep -l "border-top:2px solid #0e2b44" policy-detail-*.html 2>/dev/null | wc -l)
echo "   找到 $count 个文件包含分隔线"

# 4. 检查返回按钮
echo ""
echo "4. 检查返回按钮..."
count=$(grep -l "back-btn-policy\|返回列表" policy-detail-*.html 2>/dev/null | wc -l)
echo "   找到 $count 个文件包含返回按钮"

# 5. 添加缓存破除版本号
echo ""
echo "5. 添加缓存版本号..."
VER=$(date +%s)
sed -i "s/styles\.css?v=[0-9]*/styles.css?v=$VER/g" policy.html
echo "   ✓ policy.html 已更新为 v=$VER"

echo ""
echo "================================================"
echo "检查完成！版本号: v=$VER"
echo "================================================"

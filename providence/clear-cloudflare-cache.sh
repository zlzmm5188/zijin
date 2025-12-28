#!/bin/bash
# Cloudflare缓存清除脚本
# 用于快速清除agx.bi域名的CDN缓存

echo "🌐 Cloudflare缓存清除工具"
echo "================================"
echo ""

# Cloudflare配置
CF_EMAIL="l09680550871@gmail.com"
CF_API_KEY="85832aec51094ffd765682f3d18a9465f2893"

# 需要先获取Zone ID
echo "📋 步骤1：获取Zone ID"
echo "访问 https://dash.cloudflare.com/"
echo "选择域名 agx.bi"
echo "在右侧找到 Zone ID 并复制"
echo ""
read -p "请输入Zone ID: " CF_ZONE_ID

if [ -z "$CF_ZONE_ID" ]; then
    echo "❌ Zone ID不能为空"
    exit 1
fi

echo ""
echo "🧹 步骤2：清除缓存..."
echo ""

# 清除所有缓存
RESPONSE=$(curl -s -X POST "https://api.cloudflare.com/client/v4/zones/${CF_ZONE_ID}/purge_cache" \
  -H "X-Auth-Email: ${CF_EMAIL}" \
  -H "X-Auth-Key: ${CF_API_KEY}" \
  -H "Content-Type: application/json" \
  --data '{"purge_everything":true}')

# 检查结果
SUCCESS=$(echo "$RESPONSE" | grep -o '"success":true' | wc -l)

if [ "$SUCCESS" -eq 1 ]; then
    echo "✅ 缓存清除成功！"
    echo ""
    echo "📊 清除详情:"
    echo "$RESPONSE" | jq '.' 2>/dev/null || echo "$RESPONSE"
    echo ""
    echo "⏱ 预计30秒-3分钟后全球生效"
    echo ""
    echo "🎯 现在可以访问网站查看更新："
    echo "   https://agx.bi/"
else
    echo "❌ 缓存清除失败！"
    echo ""
    echo "错误信息:"
    echo "$RESPONSE" | jq '.' 2>/dev/null || echo "$RESPONSE"
    echo ""
    echo "💡 可能的原因："
    echo "   1. Zone ID错误"
    echo "   2. API Key错误"
    echo "   3. 权限不足"
    echo ""
    echo "📞 请手动登录Cloudflare控制台清除："
    echo "   https://dash.cloudflare.com/"
fi

echo ""
echo "================================"

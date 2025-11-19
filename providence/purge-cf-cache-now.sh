#!/bin/bash
# 立即清除 CloudFlare 缓存 - 自动化脚本
# 针对 qiantai.frevix.top 域名

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🌐 CloudFlare 缓存清除工具"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# CloudFlare 配置
CF_EMAIL="l09680550871@gmail.com"
CF_API_KEY="85832aec51094ffd765682f3d18a9465f2893"
DOMAIN="qiantai.frevix.top"

echo "📧 邮箱: $CF_EMAIL"
echo "🌐 域名: $DOMAIN"
echo ""

# 步骤1: 获取 Zone ID
echo "📡 正在获取 Zone ID..."
ZONE_RESPONSE=$(curl -s -X GET "https://api.cloudflare.com/client/v4/zones?name=$DOMAIN" \
  -H "X-Auth-Email: $CF_EMAIL" \
  -H "X-Auth-Key: $CF_API_KEY" \
  -H "Content-Type: application/json")

ZONE_ID=$(echo $ZONE_RESPONSE | grep -o '"id":"[^"]*"' | head -1 | cut -d'"' -f4)

if [ -z "$ZONE_ID" ]; then
  echo "❌ 无法获取 Zone ID"
  echo "响应: $ZONE_RESPONSE"
  exit 1
fi

echo "✅ Zone ID: $ZONE_ID"
echo ""

# 步骤2: 清除指定文件的缓存
echo "🧹 清除关键文件缓存..."
echo ""

FILES=(
  "https://qiantai.frevix.top/config.js"
  "https://qiantai.frevix.top/login.js"
  "https://qiantai.frevix.top/api-utils.js"
  "https://qiantai.frevix.top/project-detail.js"
  "https://qiantai.frevix.top/login.html"
  "https://qiantai.frevix.top/register.html"
  "https://qiantai.frevix.top/index.html"
  "https://qiantai.frevix.top/profile.html"
  "https://qiantai.frevix.top/projects.html"
  "https://qiantai.frevix.top/project-detail.html"
)

# 构建 JSON 数组
JSON_FILES="["
for file in "${FILES[@]}"; do
  JSON_FILES+="\"$file\","
done
JSON_FILES="${JSON_FILES%,}]"  # 移除最后的逗号

echo "📋 清除文件列表:"
for file in "${FILES[@]}"; do
  echo "  - $file"
done
echo ""

# 发送清除请求
PURGE_RESPONSE=$(curl -s -X POST "https://api.cloudflare.com/client/v4/zones/$ZONE_ID/purge_cache" \
  -H "X-Auth-Email: $CF_EMAIL" \
  -H "X-Auth-Key: $CF_API_KEY" \
  -H "Content-Type: application/json" \
  --data "{\"files\":$JSON_FILES}")

echo "📡 API响应:"
echo "$PURGE_RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$PURGE_RESPONSE"
echo ""

# 检查结果
if echo "$PURGE_RESPONSE" | grep -q '"success":true'; then
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "✅ CloudFlare 缓存清除成功！"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  echo "🎯 下一步："
  echo "1. 等待 30 秒让 CDN 更新"
  echo "2. 访问：https://qiantai.frevix.top/login-standalone.html"
  echo "3. 测试登录功能"
  echo ""
else
  echo "❌ 缓存清除失败"
  echo ""
  echo "请手动清除："
  echo "1. 访问 CloudFlare Dashboard"
  echo "2. 选择域名 $DOMAIN"
  echo "3. 点击 Caching → Purge Cache → Purge Everything"
fi

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

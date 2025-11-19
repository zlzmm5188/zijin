#!/bin/bash
echo "完整测试登录流程..."
echo ""

echo "请求:"
echo "POST https://api.frevix.top/login/login/account"
echo '{"username":"Qq123456","password":"Qq123456"}'
echo ""

curl -s -X POST "https://api.frevix.top/login/login/account" \
  -H "Content-Type: application/json" \
  -d '{"username":"Qq123456","password":"Qq123456"}' | jq '.'

echo ""
echo "如果code=1并且有token，说明登录成功"
echo "如果code=-1，查看message"

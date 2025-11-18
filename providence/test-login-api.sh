#!/bin/bash
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "测试登录API - 多种请求方式"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# 测试1: POST + JSON
echo "测试1: POST + JSON"
curl -X POST "https://api.frevix.top/login/login/account" \
  -H "Content-Type: application/json" \
  -d '{"username":"test","password":"123456","system":1}' \
  -s | jq -r '.message // .msg // .'
echo ""

# 测试2: POST + Form Data
echo "测试2: POST + Form Data"
curl -X POST "https://api.frevix.top/login/login/account" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "username=test&password=123456&system=1" \
  -s | jq -r '.message // .msg // .'
echo ""

# 测试3: GET + Query String
echo "测试3: GET + Query String"
curl -X GET "https://api.frevix.top/login/login/account?username=test&password=123456&system=1" \
  -s | jq -r '.message // .msg // .'
echo ""

# 测试4: POST + 不同参数名
echo "测试4: POST + 不同参数名（account/pass）"
curl -X POST "https://api.frevix.top/login/login/account" \
  -H "Content-Type: application/json" \
  -d '{"account":"test","pass":"123456","system":1}' \
  -s | jq -r '.message // .msg // .'
echo ""

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

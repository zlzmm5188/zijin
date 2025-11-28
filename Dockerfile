# 使用官方 node 镜像
FROM node:18-alpine

# 创建工作目录
WORKDIR /app

# 复制依赖清单并安装生产依赖
COPY package*.json ./

# 使用 npm install --omit=dev 替代 npm ci，以避免 package-lock.json 不匹配的问题
# 如果 package-lock.json 存在且匹配，也可以使用 npm ci
RUN npm install --omit=dev

# 复制源码
COPY . .

# 默认端口
ENV PORT=1337
EXPOSE 1337

# 启动命令：使用 server.js 启动静态文件服务器
CMD ["npm", "start"]
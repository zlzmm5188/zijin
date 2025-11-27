# 使用官方 node 镜像
FROM node:18-alpine

# 创建工作目录
WORKDIR /app

# 复制依赖清单并安装生产依赖
COPY package*.json ./
RUN npm install --omit=dev

# 复制源码
COPY . .

# 默认端口（Parse 常用 1337；如不同请修改）
ENV PORT=1337
EXPOSE 1337

# 启动命令：依赖于 package.json 中的 start 脚本，例如 "start": "node index.js"
CMD ["npm", "start"]
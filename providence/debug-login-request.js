// 调试登录请求 - 查看实际发送的数据
console.log('=== 登录请求调试 ===');

const testData = {
  username: 'testuser',
  password: 'testpass',
  system: 1
};

console.log('准备发送的数据:', JSON.stringify(testData, null, 2));
console.log('Content-Type: application/json');
console.log('Method: POST');
console.log('URL: https://apis.copla.top/index.php/login/account');

fetch('https://apis.copla.top/index.php/login/account', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(testData)
})
.then(r => r.json())
.then(data => {
  console.log('后端响应:', data);
})
.catch(err => {
  console.error('请求失败:', err);
});

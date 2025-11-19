-- 创建测试账号
USE providence;

-- 生成密码（Qq123456）
-- md5(md5('Qq123456') + 'Providence_Password_Salt_2024')

INSERT INTO `users` (
  `uid`,
  `username`, 
  `password`,
  `phone`,
  `vip_level`,
  `balance`,
  `balance_cny`,
  `balance_usdt`,
  `status`,
  `invite_code`,
  `created_at`
) VALUES (
  '12345678',
  'Qq123456',
  'a1b2c3d4e5f6',  -- 临时密码，需要用Auth::hashPassword('Qq123456')生成
  '13800138000',
  0,
  1000.00,
  1000.00,
  0,
  1,
  '12345678',
  NOW()
);

SELECT '✅ 测试账号创建完成！' as result;

# SRS_CORE_RULES（系统硬性规则）

**Providence 后台系统永不可变规则**  
**所有逻辑的最上层约束**  
**版本：v1.0**  
**创建时间：2024-11-10**

---

## 🪙 【币种隔离原则】

### 规则定义

- **CNY 与 USDT 两个资金体系完全隔离**
- **CNY 只能购买 CNY 项目，USDT 只能购买 USDT 项目**
- **提现只能提到用户绑定的该币种的提现方式**
  - CNY → 银行卡
  - USDT → TRC20地址
- **修改提现方式必须管理员审核 + 人脸验证**

### 实施要求

```php
// 强制检查
CurrencyMiddleware::validate($walletCurrency, $projectCurrency);

// 禁止
USDT钱包购买CNY项目 ❌
CNY余额提现到USDT地址 ❌
跨币种转账 ❌
```

### 数据库字段

```
users表：balance_cny / balance_usdt / frozen_cny / frozen_usdt
projects表：currency (CNY/USDT)
orders表：currency
```

---

## 💰 【收益计算统一后端执行】

### 核心原则

**前端不做数学计算**  
**收益、周期收益、VIP加息全部由后端返回最终数值**

### 返回字段统一命名

```json
{
  "actual_rate": 0.0025,        // 最终日利率
  "expected_profit": 1250.50,   // 预计收益
  "principal": 50000            // 本金
}
```

### 唯一计算入口

```php
// ✅ 唯一正确方式
EarningsService::calculateEarnings($amount, $baseRate, $vipRate, $days);

// ❌ 禁止
前端JS计算收益 ❌
Controller中直接计算 ❌
前端展示页面中计算 ❌
```

### 公式（仅后端使用）

```
每日收益 = 投资金额 × (基础利率 + VIP额外加息) / 100
总收益 = 每日收益 × 天数周期
```

---

## ⭐ 【VIP加息规则】

### 规则定义

- **VIP额外加息为固定百分比（如 +0.3%）**
- **VIP等级由系统自动根据累计有效投资升级**
- **不允许人工修改 VIP 等级**

### 升级条件

```
累计投资额 >= VIP要求 → 自动升级（只升不降）
```

### 触发时机

```
每次投资成功后 → VipService::checkAndUpgrade($userId)
```

### 强制约束

```php
// ✅ 自动升级
VipService::checkAndUpgrade($userId);

// ❌ 禁止
手动修改VIP等级 ❌
允许VIP降级 ❌
不记录升级日志 ❌
```

### 管理员例外

- 管理员可override一次
- 必须记录完整audit_log
- 必须说明原因

---

## 🔒 【订单执行规则】

### 订单状态枚举

```php
pending   => 0   // 待确认
running   => 1   // 运行中（锁定）
finished  => 2   // 已完成
refund    => 3   // 已退款
rejected  => 4   // 已拒绝
```

### 强制约束

- **订单购买后为锁定状态，不可提前退出**
- **周期结束后才触发返利与结算**
- **运行中的订单不可提前赎回**

### 状态转换规则

```
pending → running   (支付成功)
running → finished  (到达end_date，自动转换)
running → refund    (仅管理员可操作，需审计)
```

### 防套利机制

```php
// 强约束检查
if ($order->status == OrderStatus::RUNNING && date('Y-m-d') < $order->end_date) {
    throw new Exception('订单运行中，不可提前退出');
}
```

---

## 🎁 【邀请奖励规则】

### 邀请层级

**两级邀请制**

```
A → B (一级)
B → C (二级，A也能获得C的返利)
```

### 有效用户条件

```
1. 下级必须完成实名认证
2. 下级必须完成真实投资订单
3. 下级的投资订单必须完全结束（finished状态）
```

### 返利发放规则

- **事件驱动，订单完成才计算邀请奖励**
- **邀请奖励实时结算，不可提前预发**
- **返利币种必须与投资币种一致**

### 触发时机

```php
// 订单完成时触发
if ($order->status == OrderStatus::FINISHED) {
    ReferralService::processReward($orderId);
}

// ❌ 禁止
投资时立即发放返利 ❌
订单运行中发放 ❌
```

### 返利比例

从 `vip_interest_rules` 表读取：
- `level1_percent` - 一级返利%
- `level2_percent` - 二级返利%

---

## 🔢 【用户ID生成】

### 邀请码规则

**注册邀请码为随机8位数字，禁止递增编号**

```php
// ✅ 正确
$inviteCode = random_int(10000000, 99999999);

// ❌ 禁止
$inviteCode = $userId; // 使用用户ID
$inviteCode = str_pad($userId, 8, '0'); // 递增编号
```

### 唯一性检查

```php
do {
    $code = random_int(10000000, 99999999);
} while (inviteCodeExists($code));
```

---

## 📝 【审计规范】

### 必须记录的操作

**所有关键行为必须记录 audit_log**：

**资金类（Critical）**
- ✅ 充值申请/审核/入账
- ✅ 提现申请/审核/打款
- ✅ 投资认购
- ✅ 收益发放
- ✅ 邀请返利
- ✅ 团队奖励
- ✅ 修改提现方式

**用户类（Important）**
- ✅ 用户注册/登录
- ✅ 实名认证提交/审核
- ✅ VIP等级变化
- ✅ 修改密码/交易密码

**管理类（Important）**
- ✅ 管理员所有操作
- ✅ 规则配置修改
- ✅ 项目发布/审核

### 审计日志格式

```php
audit_log (
    user_id,        // 用户ID
    ip,             // IP地址
    action_type,    // 动作类型
    before_value,   // 变化前数值
    after_value,    // 变化后数值
    timestamp,      // 时间戳
    sign            // 校验签名
)
```

### 实施方式

```php
// 所有关键操作
AuditLog::log(
    $module,       // member/project/finance/risk/promotion
    $action,       // 操作名称
    $operatorType, // user/admin/system
    $operatorId,   // 操作者ID
    $targetType,   // 目标类型
    $targetId,     // 目标ID
    $beforeData,   // 变更前数据
    $afterData     // 变更后数据
);
```

---

## 🔐 【接口安全规范】

### 双重验证机制

**所有 API 请求必须通过 Token + Sign 双重验证**

### Token验证

```
Header: token: xxx
用途：身份认证
```

### Sign签名验证（资金类API必须）

```
Header: sign: xxx
Header: timestamp: xxx

算法：
sign = md5(参数排序 + timestamp + secret_key)

有效期：5分钟
```

### 实施要求

```php
// 所有API
Auth::user(); // Token验证

// 资金类API（充值/提现/投资）
SignValidator::validate(); // Token + Sign双重验证
```

---

## 🎯 【前后端职责划分】

### 前端职责

- ✅ **只负责输入**（收集用户输入）
- ✅ **只负责展示**（显示后端返回的结果）
- ❌ **不改业务含义**
- ❌ **不改逻辑**
- ❌ **不改规则**
- ❌ **不做任何数学运算**

### 后端职责

- ✅ 所有业务逻辑计算
- ✅ 所有规则验证
- ✅ 所有数据校验
- ✅ 所有状态管理
- ✅ 所有事件触发

### 示例

```javascript
// ❌ 前端禁止
const profit = amount * rate * days; // 计算收益
const newBalance = balance - amount; // 计算余额

// ✅ 前端正确
<span>{{ earnings.expected_profit }}</span> // 只显示
<input v-model="amount" /> // 只输入
```

---

## 📋 【订单周期不可变】

### 规则

- 订单购买后，周期天数锁定
- 不允许提前结束（除非退款）
- 必须到达 `end_date` 才能完成

### 强制检查

```php
if ($order->status == OrderStatus::RUNNING) {
    if (date('Y-m-d') < $order->end_date) {
        throw new Exception('订单未到期，不可操作');
    }
}
```

---

## 🚫 【绝对禁止事项】

### 禁止列表

1. ❌ 前端计算收益、利息、余额
2. ❌ Controller中硬编码业务规则
3. ❌ 跨币种购买、转账、提现
4. ❌ 订单运行中提前退出
5. ❌ 手动随意修改VIP等级
6. ❌ 邀请返利在投资时立即发放
7. ❌ 使用用户ID作为邀请码
8. ❌ 资金操作不记录audit_log
9. ❌ API只用Token不用Sign
10. ❌ 修改核心规则不记录

---

## ✅ 【强制执行事项】

### 必须执行

1. ✅ 收益计算使用 `EarningsService::calculateEarnings()`
2. ✅ VIP操作使用 `VipService::checkAndUpgrade()`
3. ✅ 返利使用 `ReferralService::processReward()`
4. ✅ 币种检查使用 `CurrencyMiddleware::validate()`
5. ✅ 所有关键操作调用 `AuditLog::log()`
6. ✅ 资金类API调用 `SignValidator::validate()`
7. ✅ 邀请码使用 `random_int(10000000, 99999999)`

---

## 📐 【核心计算公式】

### 收益计算（唯一公式）

```
每日收益 = 投资金额 × (基础利率 + VIP额外加息) / 100
总收益 = 每日收益 × 天数周期

注意：
- 基础利率：从项目表读取
- VIP额外加息：从vip_interest_rules表读取
- 天数周期：按实际天数，不按年化
```

### VIP升级条件（唯一标准）

```
用户累计有效投资额 >= VIP等级要求 → 自动升级
```

### 邀请返利计算（唯一公式）

```
返利金额 = 下级投资金额 × 返利百分比
返利时机 = 下级订单完成时（finished状态）
```

---

## 🔄 【事件驱动机制】

### 关键事件

```
OrderCreated事件    → VIP升级检查
OrderFinished事件   → 邀请返利发放
OrderFinished事件   → 团队奖励检查
UserRegistered事件  → 发放新人体验金
```

### 实施方式

```php
// 订单完成时
if ($order->status == OrderStatus::FINISHED) {
    // 触发返利事件
    ReferralService::processReward($orderId);
    
    // 记录审计
    AuditLog::log(...);
}
```

---

## 📊 【数据表规则】

### 规则表（可动态调整）

```
prov_vip_interest_rules      - VIP加息规则
prov_team_reward_rules       - 团队奖励规则
prov_project_scripts_library - 项目文案库（≥100条）
```

**这些表的数据可以调整，但必须：**
- 通过管理后台修改
- 记录 audit_log
- 通知相关开发者

### 核心业务表（不可随意改）

```
prov_users               - 用户表
prov_invest_orders       - 投资订单
prov_wallet_logs         - 钱包流水
prov_audit_log           - 审计日志
```

---

## 🎯 【API统一规范】

### 返回格式

```json
{
  "code": 1,           // 1成功 -1失败
  "message": "成功",   // 提示信息
  "data": {}           // 返回数据
}
```

### 请求头规范

```
// 所有API
Authorization: Bearer {token}
或
token: {token}

// 资金类API（额外）
sign: {md5签名}
timestamp: {时间戳}
```

### 时间格式

```
统一使用：Y-m-d H:i:s
周期单位：天
不使用年化计算
```

---

## ⚡ 【性能与安全】

### 缓存策略

- VIP规则表可缓存（1小时）
- 项目列表可缓存（5分钟）
- 用户余额实时查询（不缓存）

### 防刷机制

- 登录失败5次锁定30分钟
- API请求频率限制（100次/分钟）
- 同一IP注册限制（5个/天）

### 敏感操作

需要额外验证：
- 修改提现方式 → 人脸验证 + 管理员审核
- 大额提现(>10000) → 人脸验证
- 修改交易密码 → 原密码 + 短信验证码

---

## 🔍 【审计与风控】

### 审计日志必须包含

```
module          // 模块
action          // 操作
operator_type   // 操作者类型
operator_id     // 操作者ID
target_type     // 目标类型
target_id       // 目标ID
before_data     // 变更前（JSON）
after_data      // 变更后（JSON）
ip              // IP地址
user_agent      // 设备信息
created_at      // 时间戳
```

### 风控检查点

- 异常IP（多账号同IP）
- 异常设备（多账号同设备）
- 异常金额（单次大额）
- 异常频率（短时多次）

---

## 🏗️ 【开发7步骤流程】

**任何新模块必须完成7步骤，否则视为未完成**

```
Step 1: DB表结构设计
Step 2: Model + Service层
Step 3: Controller API
Step 4: Test + Validate
Step 5: 前端UI页面对接
Step 6: 日志与审计表写入
Step 7: 风控规则检查
```

---

## 📌 【Cursor AI开发约束】

### AI必须遵守

1. ✅ 修改代码前先查看此文档
2. ✅ 使用正确的Service类
3. ✅ 不在前端写计算逻辑
4. ✅ 所有资金操作记录audit_log
5. ✅ 遵循7步骤流程
6. ✅ 使用事件驱动模式

### AI禁止行为

1. ❌ 在Controller中直接计算收益
2. ❌ 在前端JavaScript中计算任何金额
3. ❌ 硬编码业务规则
4. ❌ 破坏币种隔离
5. ❌ 允许订单提前退出
6. ❌ 不记录审计日志

---

## 🎓 【代码示例】

### ✅ 正确示例

```php
// 投资API（符合SRS规范）
public function invest($userId, $projectId, $amount, $currency) {
    // 1. 币种检查
    CurrencyMiddleware::validate($userCurrency, $projectCurrency);
    
    // 2. 获取VIP加息
    $vipRate = VipService::getExtraRate($userId);
    
    // 3. 计算收益（唯一入口）
    $earnings = EarningsService::calculateEarnings($amount, $baseRate, $vipRate, $days);
    
    // 4. 创建订单
    $orderId = createOrder(...);
    
    // 5. VIP升级检查
    VipService::checkAndUpgrade($userId);
    
    // 6. 审计日志
    AuditLog::log(...);
    
    return $earnings;
}
```

### ❌ 错误示例

```php
// 直接在Controller计算 ❌
$earning = $amount * $rate;

// 前端计算 ❌
const profit = amount * rate * days;

// 不验证币种 ❌
if ($currency == 'USDT') { ... }

// 不记录审计 ❌
updateBalance($userId, $amount); // 没有audit_log
```

---

## 🔧 【核心类使用规范】

### VipService（VIP升级唯一入口）

```php
// ✅ 正确
VipService::checkAndUpgrade($userId);

// ❌ 错误
UPDATE users SET vip_level = 5 WHERE id = ?;
```

### EarningsService（收益计算唯一入口）

```php
// ✅ 正确
$earnings = EarningsService::calculateEarnings($amount, $baseRate, $vipRate, $days);

// ❌ 错误
$earning = $amount * ($baseRate + $vipRate) * $days;
```

### ReferralService（返利事件驱动）

```php
// ✅ 正确（订单完成时）
if ($order->status == OrderStatus::FINISHED) {
    ReferralService::processReward($orderId);
}

// ❌ 错误（投资时立即发放）
ReferralService::processReward($orderId); // 时机错误
```

### CurrencyMiddleware（币种强约束）

```php
// ✅ 正确
CurrencyMiddleware::validate($walletCurrency, $projectCurrency);

// ❌ 错误
跳过币种检查直接购买
```

---

## 📖 【文档更新规则】

### 修改SRS规则时

1. 必须团队讨论
2. 必须更新此文档
3. 必须通知所有开发者
4. 必须记录修改原因
5. 必须版本号升级

### 版本号规则

```
v1.0 - 初始版本
v1.1 - 小幅调整
v2.0 - 重大变更
```

---

**© 2024 PROVIDENCE | 核心规则文档 v1.0**

**⚠️ 此文档定义系统核心规则，任何修改必须经过架构评审**  
**⚠️ 所有开发人员（包括AI）必须严格遵守此规范**  
**⚠️ 违反规则的代码将被拒绝合并**

**最后更新：2024-11-10**

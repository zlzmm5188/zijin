<?php
/**
 * 为转入转出API添加日志记录功能
 */

// 转入API的日志SQL
$transferInLogSQL = '
    // 记录到ribao_logs表
    $logStmt = $pdo->prepare("
        INSERT INTO ribao_logs 
        (user_id, type, amount, before_balance, after_balance, status, remark, created_at) 
        VALUES 
        (:user_id, \'in\', :amount, :before_balance, :after_balance, 1, \'转入日利宝\', NOW())
    ");
    $logStmt->execute([
        \':user_id\' => $userId,
        \':amount\' => $amount,
        \':before_balance\' => $wallet[\'ribao_balance\'],
        \':after_balance\' => $newRibaoBalance
    ]);
';

// 转出API的日志SQL  
$transferOutLogSQL = '
    // 记录到ribao_logs表
    $logStmt = $pdo->prepare("
        INSERT INTO ribao_logs 
        (user_id, type, amount, before_balance, after_balance, status, remark, created_at) 
        VALUES 
        (:user_id, \'out\', :amount, :before_balance, :after_balance, 1, \'从日利宝转出\', NOW())
    ");
    $logStmt->execute([
        \':user_id\' => $userId,
        \':amount\' => $amount,
        \':before_balance\' => $wallet[\'ribao_balance\'],
        \':after_balance\' => $newRibaoBalance
    ]);
';

echo "日志记录代码已准备\n";
echo "需要手动添加到以下位置：\n";
echo "1. ribao-transfer-in.php: 在UPDATE wallets成功后\n";
echo "2. ribao-transfer-out.php: 在UPDATE wallets成功后\n";

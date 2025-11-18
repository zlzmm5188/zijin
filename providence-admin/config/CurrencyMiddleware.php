<?php
/**
 * 币种隔离中间件
 * 
 * ⚠️ 强约束：禁止跨币种操作
 */
class CurrencyMiddleware {
    /**
     * 验证币种匹配
     * 
     * @throws Exception 币种不匹配时抛出异常
     */
    public static function validate($walletCurrency, $projectCurrency) {
        if ($walletCurrency !== $projectCurrency) {
            throw new Exception("币种不匹配：钱包币种({$walletCurrency})与项目币种({$projectCurrency})不一致");
        }
        
        return true;
    }
    
    /**
     * 验证币种是否有效
     */
    public static function isValid($currency) {
        return Currency::isValid($currency);
    }
}

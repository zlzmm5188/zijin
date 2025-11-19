<?php
/**
 * 验证器类
 * 用于统一数据验证规则
 */
class Validator {

    /**
     * 验证密码强度
     * 必须包含：大写字母、小写字母、数字、特殊符号
     *
     * @param string $password 待验证的密码
     * @return true|string true表示验证通过，字符串表示错误信息
     */
    public static function validatePasswordStrength($password) {
        // 密码长度至少8位
        if (strlen($password) < 8) {
            return '密码长度至少8位';
        }

        // 必须包含大写字母
        if (!preg_match('/[A-Z]/', $password)) {
            return '密码必须包含大写字母';
        }

        // 必须包含小写字母
        if (!preg_match('/[a-z]/', $password)) {
            return '密码必须包含小写字母';
        }

        // 必须包含数字
        if (!preg_match('/[0-9]/', $password)) {
            return '密码必须包含数字';
        }

        // 必须包含特殊符号
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\\\/~`]/', $password)) {
            return '密码必须包含特殊符号 (!@#$%^&*等)';
        }

        return true;
    }

    /**
     * 验证手机号格式
     *
     * @param string $phone
     * @return bool
     */
    public static function validatePhone($phone) {
        return preg_match('/^1[3-9]\d{9}$/', $phone);
    }

    /**
     * 验证邮箱格式
     *
     * @param string $email
     * @return bool
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * 验证用户名格式
     * 只允许字母、数字、下划线，4-20位
     *
     * @param string $username
     * @return true|string
     */
    public static function validateUsername($username) {
        if (strlen($username) < 4 || strlen($username) > 20) {
            return '用户名长度必须为4-20位';
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return '用户名只能包含字母、数字和下划线';
        }

        return true;
    }

    /**
     * 验证金额格式
     *
     * @param mixed $amount
     * @return bool
     */
    public static function validateAmount($amount) {
        return is_numeric($amount) && $amount > 0;
    }

    /**
     * 验证币种
     *
     * @param string $currency
     * @return bool
     */
    public static function validateCurrency($currency) {
        return in_array(strtoupper($currency), ['CNY', 'USDT']);
    }
}

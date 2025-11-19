<?php
/**
 * Providence API 全局辅助函数
 */

if (!function_exists('dd')) {
    /**
     * 调试输出并终止
     */
    function dd(...$vars) {
        foreach ($vars as $var) {
            dump($var);
        }
        die(1);
    }
}

if (!function_exists('dump')) {
    /**
     * 调试输出
     */
    function dump($var) {
        if (class_exists('Symfony\Component\VarDumper\VarDumper')) {
            \Symfony\Component\VarDumper\VarDumper::dump($var);
        } else {
            var_dump($var);
        }
    }
}

if (!function_exists('env')) {
    /**
     * 获取环境变量
     */
    function env($key, $default = null) {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('uuid')) {
    /**
     * 生成UUID
     */
    function uuid() {
        if (class_exists('Ramsey\Uuid\Uuid')) {
            return \Ramsey\Uuid\Uuid::uuid4()->toString();
        }
        return uniqid('', true);
    }
}

if (!function_exists('log_info')) {
    /**
     * 记录信息日志
     */
    function log_info($message, $context = []) {
        if (class_exists('Monolog\Logger')) {
            $logger = new \Monolog\Logger('api');
            $logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/../logs/api.log', \Monolog\Logger::INFO));
            $logger->info($message, $context);
        }
    }
}

if (!function_exists('log_error')) {
    /**
     * 记录错误日志
     */
    function log_error($message, $context = []) {
        if (class_exists('Monolog\Logger')) {
            $logger = new \Monolog\Logger('api');
            $logger->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__ . '/../logs/api.log', \Monolog\Logger::ERROR));
            $logger->error($message, $context);
        }
    }
}

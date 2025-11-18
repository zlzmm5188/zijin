<?php
/**
 * 系统常量定义
 */

// 币种枚举
class Currency {
    const CNY = 'CNY';
    const USDT = 'USDT';
    
    const NAMES = [
        self::CNY => '人民币',
        self::USDT => 'USDT(TRC20)'
    ];
    
    public static function isValid($currency) {
        return in_array($currency, [self::CNY, self::USDT]);
    }
}

// 订单状态枚举
class OrderStatus {
    const PENDING = 0;    // 待确认
    const RUNNING = 1;    // 运行中
    const FINISHED = 2;   // 已完成
    const REFUND = 3;     // 已退款
    const REJECTED = 4;   // 已拒绝
    
    const NAMES = [
        self::PENDING => '待确认',
        self::RUNNING => '运行中',
        self::FINISHED => '已完成',
        self::REFUND => '已退款',
        self::REJECTED => '已拒绝'
    ];
}

// 项目类型枚举
class ProjectType {
    const IPO = 'IPO';
    const FUND = 'FUND';
    const BOND = 'BOND';
    const FIXED = 'FIXED';
    const INVEST = 'INVEST';
    
    const NAMES = [
        self::IPO => 'IPO配售',
        self::FUND => '私募基金',
        self::BOND => '债券投资',
        self::FIXED => '固定收益',
        self::INVEST => '定期定投'
    ];
}

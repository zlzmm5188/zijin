<?php
/**
 * 订单服务类
 * 处理订单状态验证和转换
 */
class OrderService {
    /**
     * 验证订单状态
     * 强约束：运行中的订单不可提前退出
     */
    public static function validateStatus($orderId, $requiredStatus) {
        $db = Database::getInstance();
        
        $order = $db->fetchOne("SELECT status, end_date FROM " . $db->getPrefix() . "invest_orders WHERE id = :id", 
            ['id' => $orderId]);
        
        if (!$order) {
            throw new Exception('订单不存在');
        }
        
        if ($order['status'] != $requiredStatus) {
            throw new Exception('订单状态不符合要求');
        }
        
        return true;
    }
    
    /**
     * 检查订单是否可以提取
     * 强约束：必须到达end_date并且状态为finished
     */
    public static function canWithdraw($orderId) {
        $db = Database::getInstance();
        
        $order = $db->fetchOne("SELECT status, end_date FROM " . $db->getPrefix() . "invest_orders WHERE id = :id", 
            ['id' => $orderId]);
        
        if (!$order) {
            return false;
        }
        
        // 必须是finished状态
        if ($order['status'] != OrderStatus::FINISHED) {
            return false;
        }
        
        // 必须到达结束日期
        if (strtotime($order['end_date']) > time()) {
            return false;
        }
        
        return true;
    }
}

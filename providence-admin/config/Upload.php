<?php
/**
 * 文件上传服务类
 */
class Upload {
    private static $uploadPath = __DIR__ . '/../uploads/';
    private static $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    private static $maxSize = 5 * 1024 * 1024; // 5MB
    
    /**
     * 上传单个文件
     */
    public static function uploadFile($file, $subDir = 'images') {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['success' => false, 'msg' => '没有文件上传'];
        }
        
        // 检查文件大小
        if ($file['size'] > self::$maxSize) {
            return ['success' => false, 'msg' => '文件大小不能超过5MB'];
        }
        
        // 检查文件类型
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::$allowedTypes)) {
            return ['success' => false, 'msg' => '不支持的文件类型'];
        }
        
        // 创建目录
        $targetDir = self::$uploadPath . $subDir . '/' . date('Ymd') . '/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // 生成文件名
        $filename = uniqid() . '_' . time() . '.' . $ext;
        $targetFile = $targetDir . $filename;
        
        // 移动文件
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            $url = '/uploads/' . $subDir . '/' . date('Ymd') . '/' . $filename;
            return ['success' => true, 'url' => $url, 'filename' => $filename];
        } else {
            return ['success' => false, 'msg' => '文件上传失败'];
        }
    }
    
    /**
     * 上传多个文件
     */
    public static function uploadMultiple($files, $subDir = 'images') {
        $results = [];
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            $result = self::uploadFile($file, $subDir);
            if ($result['success']) {
                $results[] = $result['url'];
            }
        }
        
        return $results;
    }
}

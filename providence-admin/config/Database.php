<?php
/**
 * 数据库操作类
 */
class Database {
    private static $instance = null;
    private $pdo;
    private $config;
    
    private function __construct() {
        $this->config = require __DIR__ . '/database.php';
        $this->connect();
    }
    
    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['database']};charset={$this->config['charset']}";
            $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("数据库连接失败: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getPrefix() {
        return $this->config['prefix'];
    }
    
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("SQL Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }
    
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : null;
    }
    
    public function insert($table, $data) {
        $table = $this->config['prefix'] . $table;
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);
        
        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->query($sql, $data);
        
        return $stmt ? $this->pdo->lastInsertId() : false;
    }
    
    public function update($table, $data, $where, $whereParams = []) {
        $table = $this->config['prefix'] . $table;
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "`{$key}` = :{$key}";
        }
        $setStr = implode(', ', $set);
        
        $sql = "UPDATE `{$table}` SET {$setStr} WHERE {$where}";
        $params = array_merge($data, $whereParams);
        
        return $this->query($sql, $params) !== false;
    }
    
    public function delete($table, $where, $params = []) {
        $table = $this->config['prefix'] . $table;
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        return $this->query($sql, $params) !== false;
    }
    
    public function count($table, $where = '1=1', $params = []) {
        $table = $this->config['prefix'] . $table;
        $sql = "SELECT COUNT(*) as count FROM `{$table}` WHERE {$where}";
        $result = $this->fetchOne($sql, $params);
        return $result ? (int)$result['count'] : 0;
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollBack() {
        return $this->pdo->rollBack();
    }
}

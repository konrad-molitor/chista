<?php

declare(strict_types=1);

namespace Chista\Database;

use PDO;
use PDOException;

class Connection
{
    private static ?PDO $instance = null;
    
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = self::createConnection();
        }
        
        return self::$instance;
    }
    
    public function getConnection(): PDO
    {
        return self::getInstance();
    }
    
    private static function createConnection(): PDO
    {
        $host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? 'localhost');
        $port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? '3306');
        $database = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? 'chista');
        $username = getenv('DB_USER') ?: getenv('DB_USERNAME') ?: ($_ENV['DB_USER'] ?? $_ENV['DB_USERNAME'] ?? 'root');
        $password = getenv('DB_PASSWORD') ?: ($_ENV['DB_PASSWORD'] ?? '');
        
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        try {
            $pdo = new PDO($dsn, $username, $password, $options);
            return $pdo;
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            
            if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
                throw new PDOException("Database connection failed: " . $e->getMessage());
            } else {
                throw new PDOException("Database connection failed");
            }
        }
    }
    
    public static function testConnection(): bool
    {
        try {
            $pdo = self::getInstance();
            $pdo->query('SELECT 1');
            return true;
        } catch (PDOException $e) {
            error_log("Database test failed: " . $e->getMessage());
            return false;
        }
    }
    
    private function __clone() {}
    public function __wakeup() {}
} 
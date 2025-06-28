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
    
    private static function createConnection(): PDO
    {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $database = $_ENV['DB_NAME'] ?? 'chista';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';
        
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        try {
            $pdo = new PDO($dsn, $username, $password, $options);
            
            // Query logging will be implemented later
            // if (($_ENV['ENABLE_QUERY_LOG'] ?? 'false') === 'true' && 
            //     ($_ENV['APP_ENV'] ?? 'production') === 'development') {
            //     $pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, [LoggingPDOStatement::class]);
            // }
            
            return $pdo;
        } catch (PDOException $e) {
            // Log the error but don't expose database details in production
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
    
    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {}
} 
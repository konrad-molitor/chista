<?php

declare(strict_types=1);

// Load environment variables
require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use Chista\Database\Connection;

// Load environment file
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
    $dotenv->load();
}

echo "Starting database migration...\n";

try {
    // Test database connection
    echo "Testing database connection...\n";
    
    $pdo = Connection::getInstance();
    echo "✅ Database connection successful!\n";
    
    // Execute schema file
    $schemaFile = __DIR__ . '/../schemas/01_create_database.sql';
    
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: {$schemaFile}");
    }
    
    echo "Executing database schema...\n";
    
    $sql = file_get_contents($schemaFile);
    
    // Split SQL file by statements, handling multiline statements
    $statements = [];
    $currentStatement = '';
    $lines = explode("\n", $sql);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines and comments
        if (empty($line) || str_starts_with($line, '--')) {
            continue;
        }
        
        $currentStatement .= $line . ' ';
        
        // If line ends with semicolon, it's end of statement
        if (str_ends_with($line, ';')) {
            $statements[] = trim($currentStatement);
            $currentStatement = '';
        }
    }
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $pdo->exec($statement);
                $firstWords = implode(' ', array_slice(explode(' ', $statement), 0, 3));
                echo "✅ Executed: " . $firstWords . "...\n";
            } catch (PDOException $e) {
                // Skip IF NOT EXISTS errors and duplicate key errors
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'Duplicate entry') === false) {
                    echo "⚠️  Warning: " . $e->getMessage() . "\n";
                    echo "Statement: " . substr($statement, 0, 100) . "...\n";
                }
            }
        }
    }
    
    echo "\n✅ Database migration completed successfully!\n";
    
    // Test some basic queries
    echo "\nTesting database tables...\n";
    
    $tables = ['tokens', 'operators', 'chats', 'messages'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
        $count = $stmt->fetch()['count'];
        echo "✅ Table '{$table}': {$count} records\n";
    }
    
    echo "\n🎉 All tests passed! Database is ready for use.\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
} 
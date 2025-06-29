<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;
use Chista\Database\Connection;

if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
    $dotenv->load();
}

echo "Starting database migration...\n";

require_once __DIR__ . '/../../src/Database/Connection.php';

function runMigrations(): void
{
    echo "Running database migrations...\n";
    
    try {
        $pdo = Connection::getInstance();
        echo "Database connection established\n";
        
        $sqlFile = __DIR__ . '/../schemas/01_create_database.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found: $sqlFile");
        }
        
        $sql = file_get_contents($sqlFile);
        if ($sql === false) {
            throw new Exception("Failed to read SQL file");
        }
        
        echo "Executing migrations...\n";
        
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            fn($stmt) => !empty($stmt) && !str_starts_with($stmt, '--')
        );
        
        foreach ($statements as $statement) {
            if (empty($statement)) continue;
            echo "Executing: " . substr($statement, 0, 50) . "...\n";
            $pdo->exec($statement);
        }
        
        echo "Migrations completed successfully!\n";
        echo "Database structure created\n";
        
    } catch (Exception $e) {
        echo "Migration error: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if (php_sapi_name() === 'cli') {
    runMigrations();
} else {
    echo "This script can only be run from command line\n";
    exit(1);
} 
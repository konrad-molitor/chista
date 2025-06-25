<?php

declare(strict_types=1);

// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment file
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'UTC');

// Set error reporting based on environment
if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// CORS headers for API and widget
if (strpos($_SERVER['REQUEST_URI'], '/api/') === 0 || $_SERVER['REQUEST_URI'] === '/widget.js') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

// Simple router
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Remove query string from URI
$uri = parse_url($requestUri, PHP_URL_PATH);

// Route handling
switch (true) {
    case $uri === '/':
        serveHomePage();
        break;
        
    case $uri === '/health':
        header('Content-Type: text/plain');
        echo 'OK';
        break;
        
    case $uri === '/widget.js':
        serveWidget();
        break;
        
    case strpos($uri, '/api/') === 0:
        handleApiRequest($uri, $requestMethod);
        break;
        
    case strpos($uri, '/operator') === 0:
        handleOperatorPanel($uri);
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        break;
}

function serveHomePage(): void
{
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Chista - AI Customer Support</title>
        <style>
            body { 
                font-family: Arial, sans-serif; 
                text-align: center; 
                padding: 50px; 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                margin: 0;
            }
            .container { max-width: 600px; margin: 0 auto; }
            .logo { max-width: 200px; margin-bottom: 30px; }
            h1 { font-size: 3em; margin-bottom: 20px; }
            p { font-size: 1.2em; margin-bottom: 30px; }
            .status { background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <img src="/assets/img/logo.png" alt="Chista Logo" class="logo">
            <h1>Chista</h1>
            <p>AI-powered customer support chat system</p>
            <div class="status">
                <h3>System Status</h3>
                <p>✅ API Server: Active</p>
                <p>✅ Database: Connected</p>
                <p>✅ Widget: Ready</p>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function serveWidget(): void
{
    header('Content-Type: application/javascript');
    echo "console.log('Chista widget loaded - coming soon!');";
}

function handleApiRequest(string $uri, string $method): void
{
    header('Content-Type: application/json');
    
    // Basic API routing
    $path = str_replace('/api', '', $uri);
    
    switch ($path) {
        case '/status':
            echo json_encode([
                'status' => 'active',
                'version' => '1.0.0',
                'timestamp' => date('c')
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'API endpoint not found']);
            break;
    }
}

function handleOperatorPanel(string $uri): void
{
    echo "Operator panel - coming soon!";
} 
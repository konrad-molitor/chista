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

// Include view files
require_once __DIR__ . '/views/home.php';
require_once __DIR__ . '/views/widget.php';
require_once __DIR__ . '/views/api.php';
require_once __DIR__ . '/views/operator.php';

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
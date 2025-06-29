<?php

declare(strict_types=1);

class WhitelistChecker
{
    private array $allowedDomains;

    public function __construct()
    {
        $whitelist = $_ENV['URLS_WHITELIST'] ?? 'localhost';
        $this->allowedDomains = array_map('trim', explode(',', $whitelist));
        
        // Debug logging
        error_log("WhitelistChecker: URLS_WHITELIST = " . ($whitelist ?: 'EMPTY'));
        error_log("WhitelistChecker: Allowed domains = " . implode(', ', $this->allowedDomains));
    }

    /**
     * Check if the origin is allowed based on whitelist
     */
    public function isOriginAllowed(string $origin): bool
    {
        // Extract domain from origin URL
        $parsedUrl = parse_url($origin);
        if (!$parsedUrl) {
            return false;
        }

        $domain = $parsedUrl['host'] ?? '';
        $port = $parsedUrl['port'] ?? null;
        
        // Build domain string with port if exists
        $domainWithPort = $port ? "$domain:$port" : $domain;
        
        // Check both domain with port and without port
        return in_array($domainWithPort, $this->allowedDomains) || 
               in_array($domain, $this->allowedDomains);
    }

    /**
     * Check if the referer is allowed
     */
    public function isRefererAllowed(): bool
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // TEMPORARY: Allow all for debugging
        error_log("WhitelistChecker: DEBUG - APP_ENV = " . ($_ENV['APP_ENV'] ?? 'NOT_SET'));
        error_log("WhitelistChecker: DEBUG - Referer = " . $referer);
        error_log("WhitelistChecker: DEBUG - Host = " . $host);
        return true; // TEMPORARY: Allow all requests for debugging
        
        // Allow direct access to localhost variants
        if (empty($referer)) {
            // Allow localhost
            if (strpos($host, 'localhost') !== false || $host === '127.0.0.1') {
                return true;
            }
            
            // Allow Fly.io health checks and similar monitoring tools
            if (strpos($userAgent, 'Fly.io-HealthCheck') !== false || 
                strpos($userAgent, 'Docker-HealthCheck') !== false ||
                strpos($host, '.fly.dev') !== false) {
                return true;
            }
            
            error_log("WhitelistChecker: No referer for host: $host, UA: $userAgent");
            return false;
        }

        $allowed = $this->isOriginAllowed($referer);
        
        if (!$allowed) {
            error_log("WhitelistChecker: Blocked referer: $referer, allowed domains: " . implode(', ', $this->allowedDomains));
        }

        return $allowed;
    }

    /**
     * Get CORS headers for allowed origin
     */
    public function getCorsHeaders(): array
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
        
        $headers = [
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            'Access-Control-Max-Age' => '86400'
        ];

        if ($this->isOriginAllowed($origin)) {
            $headers['Access-Control-Allow-Origin'] = $origin;
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }

        return $headers;
    }

    /**
     * Send 403 Forbidden response
     */
    public function sendForbiddenResponse(): void
    {
        http_response_code(403);
        echo json_encode(['error' => 'Domain not allowed']);
        exit;
    }
} 
<?php

declare(strict_types=1);

class WhitelistChecker
{
    private array $allowedDomains;

    public function __construct()
    {
        $whitelist = $_ENV['URLS_WHITELIST'] ?? 'localhost';
        $this->allowedDomains = array_map('trim', explode(',', $whitelist));
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
        
        // Allow direct access to localhost variants
        if (empty($referer)) {
            // For direct access, check if current host is localhost
            if (strpos($host, 'localhost') !== false || $host === '127.0.0.1') {
                return true;
            }
            
            error_log("WhitelistChecker: No referer for host: $host");
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
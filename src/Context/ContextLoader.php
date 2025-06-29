<?php

declare(strict_types=1);

class ContextLoader
{
    private string $contextDir;

    public function __construct()
    {
        $this->contextDir = __DIR__ . '/../../public/context/';
        
        // Create context directory if it doesn't exist
        if (!is_dir($this->contextDir)) {
            mkdir($this->contextDir, 0755, true);
        }
    }

    /**
     * Load context from a file or URL
     */
    public function loadContext(string $contextSrc): ?string
    {
                if (empty($contextSrc)) {
            return null;
        }

        if (filter_var($contextSrc, FILTER_VALIDATE_URL)) {
            return $this->loadContextFromUrl($contextSrc);
        }
        
        if (strpos($contextSrc, '/') === 0) {
            $referer = $_SERVER['HTTP_REFERER'] ?? null;
            if ($referer) {
                $parsed = parse_url($referer);
                if ($parsed && isset($parsed['scheme']) && isset($parsed['host'])) {
                    $host = $parsed['host'];
                    $baseUrl = $parsed['scheme'] . '://' . $host;
                    if (isset($parsed['port'])) {
                        $baseUrl .= ':' . $parsed['port'];
                    }
                    $fullUrl = $baseUrl . $contextSrc;
                    return $this->loadContextFromUrl($fullUrl);
                }
            }
            return null;
        }

        return $this->loadContextFromFile($contextSrc);
    }

    /**
     * Load context from URL
     */
    private function loadContextFromUrl(string $url): ?string
    {
        try {
            if (!extension_loaded('curl') || !function_exists('curl_init')) {
                return $this->loadContextFromUrlFallback($url);
            }
            
            $parsed = parse_url($url);
            if (!$parsed || !in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
                return null;
            }

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'Chista-ContextLoader/1.0'
            ]);

            $content = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                error_log("cURL error loading context from URL $url: " . curl_error($ch));
                curl_close($ch);
                return null;
            }
            
            curl_close($ch);

            if ($httpCode !== 200) {
                error_log("HTTP error $httpCode loading context from URL: $url");
                return null;
            }

            if ($content === false) {
                error_log("Failed to load context from URL: $url");
                return null;
            }

            return trim((string)$content);

        } catch (Exception $e) {
            error_log("Exception loading context from URL $url: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Load context from local file
     */
    private function loadContextFromFile(string $contextSrc): ?string
    {
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $contextSrc);
        $filePath = $this->contextDir . $filename;

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        return $content === false ? null : trim($content);
    }


    
    /**
     * Fallback method to load context from URL using file_get_contents
     */
    private function loadContextFromUrlFallback(string $url): ?string
    {
        try {
            $parsed = parse_url($url);
            $originalHost = $parsed['host'] ?? 'localhost';
            $port = $parsed['port'] ?? null;
            $hostHeader = $originalHost . ($port ? ":$port" : '');
            
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 10,
                    'user_agent' => 'Mozilla/5.0 (compatible; Chista/1.0)',
                    'header' => "Host: $hostHeader\r\n",
                    'ignore_errors' => true
                ]
            ]);
            
            $content = file_get_contents($url, false, $context);
            
            if ($content === false) {
                return null;
            }
            
            // Check HTTP response headers
            if (isset($http_response_header)) {
                $httpCode = 0;
                if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches)) {
                    $httpCode = (int)$matches[1];
                }
                
                if ($httpCode !== 200) {
                    error_log("ContextLoader: HTTP error $httpCode loading context from URL using fallback: $url");
                    return null;
                }
            }
            
            return trim($content);
            
        } catch (Exception $e) {
            error_log("ContextLoader: Exception loading context from URL using fallback $url: " . $e->getMessage());
            return null;
        }
    }
} 
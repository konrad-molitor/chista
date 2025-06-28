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
    // Check system status
    $dbStatus = '❌ Database: Disconnected';
    $apiStatus = '✅ API Server: Active';
    $widgetStatus = '✅ Widget: Ready';
    
    try {
        require_once __DIR__ . '/../src/Database/Connection.php';
        
        if (\Chista\Database\Connection::testConnection()) {
            $dbStatus = '✅ Database: Connected';
        }
    } catch (Exception $e) {
        error_log("Database check failed: " . $e->getMessage());
    }
    
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
                min-height: 100vh;
            }
            .container { max-width: 600px; margin: 0 auto; }
            .logo { 
                max-width: 200px; 
                margin-bottom: 30px; 
                background: rgba(255,255,255,0.9);
                padding: 20px;
                border-radius: 15px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.2);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.3);
            }
            h1 { font-size: 3em; margin-bottom: 20px; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
            p { font-size: 1.2em; margin-bottom: 30px; }
            .status { 
                background: rgba(255,255,255,0.1); 
                padding: 20px; 
                border-radius: 10px; 
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.2);
            }
            .status p { margin: 10px 0; font-family: monospace; }
            .quote { 
                margin-top: 30px; 
                font-style: italic; 
                opacity: 0.8; 
                font-size: 1.1em;
            }
            .integration-section {
                margin-top: 40px;
                padding: 30px;
                background: rgba(255,255,255,0.15);
                border-radius: 15px;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.2);
            }
            .integration-section h2 {
                color: white;
                margin-bottom: 15px;
                font-size: 2em;
            }
            .integration-section h3 {
                color: white;
                margin: 25px 0 15px 0;
            }
            .integration-section p {
                margin-bottom: 20px;
                font-size: 1.1em;
            }
            .code-container {
                position: relative;
                background: rgba(0,0,0,0.3);
                padding: 15px 50px 15px 15px;
                border-radius: 8px;
                margin: 20px 0;
                font-family: 'Monaco', 'Menlo', monospace;
                border: 1px solid rgba(255,255,255,0.2);
            }
            .code-container code {
                color: #e2e8f0;
                font-size: 14px;
                word-break: break-all;
            }
            .copy-btn {
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(255,255,255,0.2);
                border: none;
                color: white;
                padding: 8px 12px;
                border-radius: 5px;
                cursor: pointer;
                transition: all 0.3s ease;
                font-size: 16px;
            }
            .copy-btn:hover {
                background: rgba(255,255,255,0.3);
                transform: translateY(-50%) scale(1.1);
            }
            .steps {
                margin: 25px 0;
            }
            .steps ol {
                padding-left: 20px;
            }
            .steps li {
                margin-bottom: 10px;
                font-size: 1.05em;
                line-height: 1.6;
            }
            .steps code {
                background: rgba(0,0,0,0.2);
                padding: 2px 6px;
                border-radius: 3px;
                font-family: 'Monaco', 'Menlo', monospace;
                font-size: 0.9em;
            }
            .features-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin: 30px 0;
            }
            .feature {
                background: rgba(255,255,255,0.1);
                padding: 20px;
                border-radius: 10px;
                border: 1px solid rgba(255,255,255,0.2);
                backdrop-filter: blur(5px);
            }
            .feature h4 {
                color: white;
                margin: 0 0 10px 0;
                font-size: 1.2em;
            }
            .feature p {
                margin: 0;
                opacity: 0.9;
                line-height: 1.5;
            }
            .demo-notice {
                background: rgba(255,193,7,0.2);
                border: 2px solid rgba(255,193,7,0.5);
                padding: 20px;
                border-radius: 10px;
                margin-top: 30px;
                text-align: center;
            }
            .demo-notice h3 {
                color: #ffc107;
                margin-top: 0;
            }
            .demo-notice p {
                margin-bottom: 0;
                font-size: 1.1em;
            }
            .react-integration {
                background: rgba(97, 218, 251, 0.15);
                border: 2px solid rgba(97, 218, 251, 0.3);
                padding: 20px;
                border-radius: 10px;
                margin-top: 20px;
            }
            .react-integration h3 {
                color: #61dafb;
                margin-top: 0;
            }
            .react-integration .code-container {
                background: rgba(0,0,0,0.4);
                font-size: 12px;
                line-height: 1.4;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <img src="/assets/img/logo.png" alt="Chista Logo" class="logo">
            <h1>Chista</h1>
            <p>AI-powered customer support chat system</p>
            <div class="status">
                <h3>System Status</h3>
                <p><?= $apiStatus ?></p>
                <p><?= $dbStatus ?></p>
                <p><?= $widgetStatus ?></p>
            </div>
            <div class="quote">
                <p>"Chista illuminates the path to knowledge through conversation"</p>
            </div>
            
            <!-- Integration Section -->
            <div class="integration-section">
                <h2>🔧 Widget Integration</h2>
                <p>Add chat support to your website with just one line of code:</p>
                
                <div class="code-container">
                    <code id="integration-code">&lt;script src="<?= $_ENV['APP_URL'] ?? 'http://localhost:8080' ?>/widget.js"&gt;&lt;/script&gt;</code>
                    <button onclick="copyCode()" class="copy-btn" title="Копировать код">📋</button>
                </div>
                
                <div class="steps">
                    <h3>Integration Steps:</h3>
                    <ol>
                        <li>Copy the code above</li>
                        <li>Paste it before the closing <code>&lt;/body&gt;</code> tag on your website</li>
                        <li>The widget will appear automatically in the bottom right corner</li>
                        <li>Done! Users can start chatting</li>
                    </ol>
                </div>
                
                <div class="features-grid">
                    <div class="feature">
                        <h4>🎨 Modern Design</h4>
                        <p>Responsive interface that fits any website</p>
                    </div>
                    <div class="feature">
                        <h4>⚡ Fast Loading</h4>
                        <p>Optimized code won't slow down your site</p>
                    </div>
                    <div class="feature">
                        <h4>📱 Mobile Ready</h4>
                        <p>Works perfectly on all devices</p>
                    </div>
                </div>
                
                <div class="demo-notice">
                    <h3>🎯 Live Demo</h3>
                    <p>The widget is already connected on this page! Click the chat button in the bottom right corner to try it out.</p>
                </div>
                
                <div class="react-integration">
                    <h3>⚛️ React Integration</h3>
                    <p>For React applications, use the enhanced integration method:</p>
                    
                    <div class="code-container">
                        <code>
// React Hook for Chista Widget<br/>
useEffect(() => {<br/>
&nbsp;&nbsp;const script = document.createElement('script');<br/>
&nbsp;&nbsp;script.src = '<?= $_ENV['APP_URL'] ?? 'http://localhost:8080' ?>/widget.js';<br/>
&nbsp;&nbsp;document.body.appendChild(script);<br/>
<br/>
&nbsp;&nbsp;return () => {<br/>
&nbsp;&nbsp;&nbsp;&nbsp;if (window.ChistaWidget) {<br/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;window.ChistaWidget.destroy();<br/>
&nbsp;&nbsp;&nbsp;&nbsp;}<br/>
&nbsp;&nbsp;&nbsp;&nbsp;document.body.removeChild(script);<br/>
&nbsp;&nbsp;};<br/>
}, []);
                        </code>
                    </div>
                    
                    <p><strong>Manual control:</strong> Use <code>window.ChistaWidget</code> API for advanced integration.</p>
                </div>
            </div>
        </div>
        
        <script>
            function copyCode() {
                const code = document.getElementById('integration-code').textContent;
                navigator.clipboard.writeText(code).then(() => {
                    const btn = document.querySelector('.copy-btn');
                    const originalText = btn.textContent;
                    btn.textContent = '✅';
                    btn.style.background = '#4caf50';
                    setTimeout(() => {
                        btn.textContent = originalText;
                        btn.style.background = '';
                    }, 2000);
                }).catch(() => {
                    alert('Code copied: ' + code);
                });
            }
        </script>
        
        <!-- Load Chista Widget for Demo -->
        <script src="/widget.js"></script>
    </body>
    </html>
    <?php
}

function serveWidget(): void
{
    header('Content-Type: application/javascript');
    ?>
(function() {
    'use strict';
    
    // Configuration
    const CHISTA_API_BASE = '<?= $_ENV['CHISTA_API_BASE'] ?? 'http://localhost:8080' ?>';
    
    // Create widget styles
    const styles = `
        #chista-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 2147483647;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        #chista-chat-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        #chista-chat-button:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.6);
        }
        
        #chista-chat-button svg {
            width: 24px;
            height: 24px;
            fill: white;
        }
        
        #chista-chat-window {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 2147483646;
        }
        
        #chista-chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        #chista-chat-title {
            font-weight: 600;
            font-size: 16px;
        }
        
        #chista-chat-close {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        #chista-chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }
        
        .chista-message {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        
        .chista-message.user {
            justify-content: flex-end;
        }
        
        .chista-message-content {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.4;
            text-align: left;
        }
        
        .chista-message.bot .chista-message-content {
            background: white;
            color: #333;
            border: 1px solid #e1e5e9;
            border-radius: 18px 18px 18px 4px;
        }
        
        .chista-message.user .chista-message-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 18px 18px 4px 18px;
        }
        
        #chista-chat-input-container {
            padding: 15px;
            background: white;
            border-top: 1px solid #e1e5e9;
            display: flex;
            gap: 10px;
        }
        
        #chista-chat-input {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #e1e5e9;
            border-radius: 20px;
            font-size: 14px;
            outline: none;
            resize: none;
            font-family: inherit;
        }
        
        #chista-chat-send {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s ease;
        }
        
        #chista-chat-send:hover {
            transform: scale(1.1);
        }
        
        #chista-chat-send:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .chista-typing {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 10px 15px;
            background: white;
            border: 1px solid #e1e5e9;
            border-radius: 18px 18px 18px 4px;
            max-width: 80%;
        }
        
        .chista-typing-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #667eea;
            animation: chista-typing 1.4s infinite ease-in-out;
        }
        
        .chista-typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .chista-typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes chista-typing {
            0%, 60%, 100% {
                transform: translateY(0);
                opacity: 0.5;
            }
            30% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }
        
        @media (max-width: 480px) {
            #chista-chat-window {
                width: calc(100vw - 40px);
                height: calc(100vh - 40px);
                bottom: 20px;
                right: 20px;
                left: 20px;
                top: 20px;
            }
        }
    `;
    
    // Add styles to page
    const styleSheet = document.createElement('style');
    styleSheet.textContent = styles;
    document.head.appendChild(styleSheet);
    
    // Create widget HTML
    const widgetHTML = `
        <div id="chista-widget">
            <button id="chista-chat-button" aria-label="Abrir chat de soporte">
                <svg viewBox="0 0 24 24">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h4l4 4 4-4h4c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-2 12H6v-2h12v2zm0-3H6V9h12v2zm0-3H6V6h12v2z"/>
                </svg>
            </button>
            
            <div id="chista-chat-window">
                <div id="chista-chat-header">
                    <div id="chista-chat-title">Soporte Chista</div>
                    <button id="chista-chat-close" aria-label="Cerrar chat">&times;</button>
                </div>
                
                <div id="chista-chat-messages">
                    <div class="chista-message bot">
                        <div class="chista-message-content">
                            ¡Hola! Soy Chista, tu asistente de soporte. ¿En qué puedo ayudarte hoy?
                        </div>
                    </div>
                </div>
                
                <div id="chista-chat-input-container">
                    <textarea id="chista-chat-input" placeholder="Escribe tu mensaje..." rows="1"></textarea>
                    <button id="chista-chat-send" aria-label="Enviar mensaje">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    // Check if widget is already initialized
    function isWidgetInitialized() {
        return !!document.getElementById('chista-widget');
    }
    
    // Attach event listeners with protection against re-attachment
    function attachEventListeners() {
        const chatButton = document.getElementById('chista-chat-button');
        const chatWindow = document.getElementById('chista-chat-window');
        const chatClose = document.getElementById('chista-chat-close');
        const chatInput = document.getElementById('chista-chat-input');
        const chatSend = document.getElementById('chista-chat-send');
        const chatMessages = document.getElementById('chista-chat-messages');
        
        if (!chatButton || !chatWindow || !chatInput || !chatSend || !chatMessages) {
            console.warn('Chista: Widget elements not found, retrying...');
            return false;
        }
        
        // Check if already initialized
        if (chatButton.hasAttribute('data-chista-initialized')) {
            return true;
        }
        
        let isOpen = false;
        
        // Toggle chat window
        function toggleChat() {
            isOpen = !isOpen;
            chatWindow.style.display = isOpen ? 'flex' : 'none';
            if (isOpen) {
                chatInput.focus();
            }
        }
        
        // Add message to chat
        function addMessage(content, isUser = false) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chista-message ${isUser ? 'user' : 'bot'}`;
            messageDiv.innerHTML = `<div class="chista-message-content">${content}</div>`;
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Show typing indicator
        function showTyping() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'chista-message bot';
            typingDiv.id = 'chista-typing-indicator';
            typingDiv.innerHTML = `
                <div class="chista-typing">
                    <div class="chista-typing-dot"></div>
                    <div class="chista-typing-dot"></div>
                    <div class="chista-typing-dot"></div>
                </div>
            `;
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Hide typing indicator
        function hideTyping() {
            const typingIndicator = document.getElementById('chista-typing-indicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }
        
        // Send message to API
        async function sendMessage(message) {
            try {
                const response = await fetch(`${CHISTA_API_BASE}/api/chat`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message: message,
                        timestamp: new Date().toISOString()
                    })
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                return data.response || 'Lo siento, no pude procesar tu mensaje. Inténtalo de nuevo.';
            } catch (error) {
                console.error('Chista API Error:', error);
                return 'Lo siento, hay un problema con la conexión. Inténtalo más tarde.';
            }
        }
        
        // Handle message submission
        async function handleSubmit() {
            const message = chatInput.value.trim();
            if (!message) return;
            
            // Add user message
            addMessage(message, true);
            chatInput.value = '';
            chatSend.disabled = true;
            
            // Show typing indicator
            showTyping();
            
            // Send to API and get response
            const response = await sendMessage(message);
            
            // Hide typing and show response
            hideTyping();
            addMessage(response);
            chatSend.disabled = false;
            chatInput.focus();
        }
        
        // Auto-resize textarea
        function autoResize() {
            chatInput.style.height = 'auto';
            chatInput.style.height = Math.min(chatInput.scrollHeight, 100) + 'px';
        }
        
        // Event listeners
        chatButton.addEventListener('click', toggleChat);
        chatClose.addEventListener('click', toggleChat);
        chatSend.addEventListener('click', handleSubmit);
        
        chatInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                handleSubmit();
            }
        });
        
        chatInput.addEventListener('input', autoResize);
        
        // Close chat when clicking outside
        document.addEventListener('click', (e) => {
            if (isOpen && !chatWindow.contains(e.target) && !chatButton.contains(e.target)) {
                toggleChat();
            }
        });
        
        // Mark as initialized
        chatButton.setAttribute('data-chista-initialized', 'true');
        chatClose.setAttribute('data-chista-initialized', 'true');
        chatSend.setAttribute('data-chista-initialized', 'true');
        
        console.log('Chista widget loaded successfully!');
        return true;
    }
    
    // Initialize widget with React/SPA compatibility
    function init() {
        // Prevent multiple initializations
        if (isWidgetInitialized()) {
            console.log('Chista widget already initialized');
            return;
        }
        
        // Add widget to page
        document.body.insertAdjacentHTML('beforeend', widgetHTML);
        
        // Try to attach event listeners
        const success = attachEventListeners();
        
        // If failed, retry after short delay (for React)
        if (!success) {
            setTimeout(() => {
                if (!attachEventListeners()) {
                    console.error('Chista: Failed to initialize widget after retry');
                }
            }, 100);
        }
    }
    
    // Enhanced initialization for SPA frameworks
    function initWidget() {
        // Check if already exists
        if (isWidgetInitialized()) {
            return;
        }
        
        // Detect framework environment
        const isReact = !!(window.React || document.querySelector('[data-reactroot]') || document.querySelector('[data-react-app]'));
        const isVue = !!(window.Vue);
        const isAngular = !!(window.ng);
        
        if (isReact || isVue || isAngular) {
            console.log('Chista: SPA framework detected, using enhanced initialization');
            // Delay for SPA frameworks
            setTimeout(() => {
                init();
                // Additional verification after delay
                setTimeout(() => {
                    const button = document.getElementById('chista-chat-button');
                    if (button && !button.hasAttribute('data-chista-initialized')) {
                        console.warn('Chista: Re-initializing event listeners');
                        attachEventListeners();
                    }
                }, 200);
            }, 150);
        } else {
            // Regular initialization for traditional websites
            init();
        }
    }
    
    // Destroy widget function for cleanup
    function destroyWidget() {
        const widget = document.getElementById('chista-widget');
        if (widget) {
            widget.remove();
            console.log('Chista widget destroyed');
        }
    }
    
    // Export API for React/SPA integration
    window.ChistaWidget = {
        init: initWidget,
        destroy: destroyWidget,
        isInitialized: isWidgetInitialized,
        version: '1.0.0'
    };
    
    // Enhanced initialization logic
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWidget);
    } else if (document.readyState === 'interactive' || document.readyState === 'complete') {
        // Additional delay for SPA frameworks
        setTimeout(initWidget, 50);
    }
})();
    <?php
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
            
        case '/chat':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $message = $input['message'] ?? '';
            
            if (empty($message)) {
                http_response_code(400);
                echo json_encode(['error' => 'Message is required']);
                break;
            }
            
            // Simulate AI response (later will be replaced with actual AI processing)
            $responses = [
                'Hola' => '¡Hola! ¿En qué puedo ayudarte hoy?',
                'Ayuda' => 'Estoy aquí para ayudarte. Puedes preguntarme sobre nuestros productos y servicios.',
                'Precio' => 'Para información sobre precios, por favor contacta con nuestro equipo de ventas.',
                'Soporte' => 'Te estoy ayudando ahora mismo. ¿Cuál es tu consulta específica?',
                'Gracias' => '¡De nada! Estoy aquí para ayudarte cuando lo necesites.',
                'Adiós' => '¡Hasta pronto! No dudes en contactarnos si necesitas más ayuda.'
            ];
            
            $response = 'Gracias por tu mensaje. Nuestro equipo revisará tu consulta y te responderá pronto.';
            
            // Simple keyword matching
            foreach ($responses as $keyword => $reply) {
                if (stripos($message, $keyword) !== false) {
                    $response = $reply;
                    break;
                }
            }
            
            echo json_encode([
                'response' => $response,
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
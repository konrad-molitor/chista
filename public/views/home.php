<?php

declare(strict_types=1);

function serveHomePage(): void
{
    // Check system status
    $dbStatus = '❌ Database: Disconnected';
    $apiStatus = '✅ API Server: Active';
    $widgetStatus = '✅ Widget: Ready';
    
    try {
        require_once __DIR__ . '/../../src/Database/Connection.php';
        
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
            .container { max-width: 700px; margin: 0 auto; }
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
                text-align: left;
            }
            .integration-section h2 {
                color: white;
                margin-bottom: 15px;
                font-size: 2em;
                text-align: center;
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
            .feature-highlight {
                background: rgba(76, 175, 80, 0.2);
                border: 1px solid rgba(76, 175, 80, 0.4);
                padding: 15px;
                border-radius: 8px;
                margin: 20px 0;
            }
            .feature-highlight h4 {
                color: #81c784;
                margin-top: 0;
            }
        </style>
        <script>
            function copyToClipboard(text) {
                navigator.clipboard.writeText(text).then(function() {
                    console.log('Code copied to clipboard');
                }, function(err) {
                    console.error('Failed to copy: ', err);
                });
            }
        </script>
    </head>
    <body>
        <div class="container">
            <img src="assets/img/logo.png" alt="Chista Logo" class="logo">
            <h1>Chista</h1>
            <p>Sistema de soporte al cliente con IA</p>
            
            <div class="status">
                <h3>Estado del Sistema</h3>
                <p><?= $dbStatus ?></p>
                <p><?= $apiStatus ?></p>
                <p><?= $widgetStatus ?></p>
            </div>
            
            <div class="integration-section">
                <h2>Integración del Widget</h2>
                <p>Para integrar el widget de chat en tu sitio web, añade el siguiente código antes del cierre de la etiqueta <code>&lt;/body&gt;</code>:</p>
                
                <h3>Método Básico (sin contexto personalizado)</h3>
                <div class="code-container">
                    <button class="copy-btn" onclick="copyToClipboard('<script src=&quot;<?= $_SERVER['HTTP_HOST'] ?? 'localhost' ?>/widget.js&quot;></script>')">Copiar</button>
                    <code>
&lt;script src="<?= $_SERVER['HTTP_HOST'] ?? 'localhost' ?>/widget.js"&gt;&lt;/script&gt;
                    </code>
                </div>
                
                <div class="feature-highlight">
                    <h4>¡Nuevo! Contexto Personalizado</h4>
                    <p>Ahora puedes proporcionar información específica sobre tu negocio para que el AI responda con conocimiento contextual:</p>
                </div>
                
                <h3>Con Contexto Personalizado (Recomendado)</h3>
                <div class="code-container">
                    <button class="copy-btn" onclick="copyToClipboard('<script src=&quot;<?= $_SERVER['HTTP_HOST'] ?? 'localhost' ?>/widget.js&quot; data-context-src=&quot;mi-negocio.md&quot; data-title=&quot;Mi Asistente&quot;></script>')">Copiar</button>
                    <code>
&lt;script <br>
&nbsp;&nbsp;src="<?= $_SERVER['HTTP_HOST'] ?? 'localhost' ?>/widget.js" <br>
&nbsp;&nbsp;data-context-src="mi-negocio.md"<br>
&nbsp;&nbsp;data-title="Mi Asistente"<br>
&gt;&lt;/script&gt;
                    </code>
                </div>
                
                <h3>Usando URL Externa</h3>
                <div class="code-container">
                    <button class="copy-btn" onclick="copyToClipboard('<script src=&quot;<?= $_SERVER['HTTP_HOST'] ?? 'localhost' ?>/widget.js&quot; data-context-src=&quot;https://mi-sitio.com/contexto.md&quot; data-title=&quot;Mi Asistente&quot;></script>')">Copiar</button>
                    <code>
&lt;script <br>
&nbsp;&nbsp;src="<?= $_SERVER['HTTP_HOST'] ?? 'localhost' ?>/widget.js" <br>
&nbsp;&nbsp;data-context-src="https://mi-sitio.com/contexto.md"<br>
&nbsp;&nbsp;data-title="Mi Asistente"<br>
&gt;&lt;/script&gt;
                    </code>
                </div>
                
                <div class="steps">
                    <h4>Parámetros disponibles:</h4>
                    <ul>
                        <li><strong>data-context-src</strong>: Archivo local (.md/.txt) o URL con información de tu negocio</li>
                        <li><strong>data-title</strong>: Título personalizado para el chat</li>
                    </ul>
                </div>
                
                <div class="feature-highlight">
                    <h4>Sistema de Whitelist</h4>
                    <p>Solo los dominios autorizados pueden usar el widget. Configuración automática de CORS y protección 403 para dominios no permitidos.</p>
                </div>
                
                <h3>Para React/Vue/Angular:</h3>
                <div class="steps">
                    <ol>
                        <li>Añade el script con los parámetros de contexto</li>
                        <li>El widget se inicializa automáticamente</li>
                        <li>Usa <code>window.ChistaWidget</code> para control programático si es necesario</li>
                    </ol>
                </div>
            </div>
            
            <div class="quote">
                <p>"Chista ilumina el camino hacia el conocimiento a través de la conversación"</p>
            </div>
        </div>
        
        <!-- Load Chista Widget for Demo with Context -->
        <script 
            src="/widget.js" 
            data-context-src="chista.md"
            data-title="Asistente Chista"
        ></script>
    </body>
    </html>
    <?php
} 
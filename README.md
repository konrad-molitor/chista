<div align="center">
  <img src="public/assets/img/logo.png" alt="Chista Logo" width="200"/>
  
  # Chista
  
  Sistema de soporte al cliente con IA, nombrado en honor a la deidad zoroástrica de la sabiduría y el conocimiento.
</div>

## Descripción

Chista es un widget de chat de soporte al cliente embebible que utiliza un agente de IA para respuestas automáticas y permite que operadores humanos se unan a las conversaciones cuando sea necesario.

### Características Principales

- 🤖 **Agente AI**: Respuestas automáticas vía OpenRouter API
- 💬 **Operadores en Vivo**: Operadores humanos pueden unirse a conversaciones
- 🔧 **Widget Embebible**: Fácil integración en cualquier sitio web
- 🗄️ **Historial de Mensajes**: Todos los chats guardados en MySQL
- 🔐 **Seguridad**: Autenticación basada en tokens con protección CORS
- 📊 **Panel de Operador**: Interfaz conveniente para gestión de chats
- 🎯 **Contexto Personalizado**: Soporte para información específica del negocio

## Tecnologías

- **Backend**: PHP 8.1+ con FPM
- **Frontend**: JavaScript vanilla + CSS moderno
- **Base de Datos**: MySQL 8.0
- **IA**: OpenRouter API (Mistral)
- **Infraestructura**: Fly.io

## Acceso en Línea

- **Página Principal**: https://chista.ivaliev.dev/
- **Panel de Operador**: https://chista.ivaliev.dev/operator/
- **API Status**: https://chista.ivaliev.dev/api/status
- **Widget de Chat**: https://chista.ivaliev.dev/widget.js

## Integración

### Método Básico

Para embeber el widget en tu sitio web, añade antes del cierre de `</body>`:

```html
<script src="https://chista.ivaliev.dev/widget.js"></script>
```

### Con Contexto Personalizado (Recomendado)

```html
<script 
  src="https://chista.ivaliev.dev/widget.js"
  data-context-src="mi-negocio.md"
  data-title="Mi Asistente"
></script>
```

### Usando URL Externa

```html
<script 
  src="https://chista.ivaliev.dev/widget.js"
  data-context-src="https://mi-sitio.com/contexto.md"
  data-title="Mi Asistente"
></script>
```

### Parámetros Disponibles

- `data-context-src`: Archivo local (.md/.txt) o URL con información de tu negocio
- `data-title`: Título personalizado para el chat

## API Endpoints

### Endpoints Principales

- `GET /api/status` - Estado del sistema
- `POST /api/chat` - Enviar mensaje al chat
- `GET /api/chat/{chatId}/history` - Obtener historial de chat
- `POST /api/chat/{chatId}/request-human` - Solicitar operador humano
- `GET /api/operator/chats` - Lista de chats para operador

### Ejemplo de Uso

```javascript
// Enviar mensaje
const response = await fetch('https://chista.ivaliev.dev/api/chat', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    message: 'Hola, necesito ayuda',
    chat_id: 1
  })
});

const data = await response.json();
```

## Estructura del Proyecto

```
chista/
├── public/
│   ├── api/             # API endpoints
│   ├── views/           # Páginas HTML
│   ├── assets/          # CSS, JS, imágenes
│   └── index.php        # Punto de entrada
├── src/
│   ├── Database/        # Conexión a BD
│   ├── Security/        # Autenticación y CORS
│   └── Context/         # Carga de contexto
├── database/
│   └── migrations/      # Migraciones de BD
└── docker/              # Configuración Docker
```

## Características Técnicas

### Base de Datos

- **MySQL 8.0** en Fly.io
- Tablas: `chats`, `messages`, `operators`, `tokens`, `rate_limits`
- Conexión segura vía red interna

### IA y Procesamiento

- **OpenRouter API** con modelo Mistral
- Contexto personalizable por negocio
- Rate limiting por IP
- Respuestas en español

### Seguridad

- Headers CORS configurados automáticamente
- Protección contra spam con rate limiting
- Validación de entrada sanitizada
- Logs de seguridad

## Estado del Sistema

Sistema desplegado y operativo en Fly.io:

- ✅ **Aplicación**: https://chista.ivaliev.dev/
- ✅ **Base de Datos**: MySQL conectada
- ✅ **IA**: OpenRouter configurado
- ✅ **API**: Todos los endpoints operativos
- ✅ **SSL**: Certificado Let's Encrypt activo

## Monitoreo

El sistema incluye:

- Endpoint de salud en `/api/status`
- Logs automáticos de errores
- Monitoreo de conexión a BD
- Verificación de API de IA

## Licencia

MIT License

## Soporte

Para preguntas y soporte, por favor crea un issue en este repositorio.

---

*"Chista ilumina el camino hacia el conocimiento a través de la conversación"* 
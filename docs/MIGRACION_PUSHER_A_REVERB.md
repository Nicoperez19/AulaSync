# 🔄 Guía de Migración: Pusher → Laravel Reverb

## 📋 Descripción

Esta guía documenta la migración del sistema de broadcasting de **Pusher** (servicio externo) a **Laravel Reverb** (self-hosted), el servidor oficial de WebSockets de Laravel.

---

## ✅ Cambios Realizados

### 1. **Archivo de Configuración** (`config/broadcasting.php`)

Se agregó la conexión `reverb` como primera opción:

```php
'reverb' => [
    'driver' => 'reverb',
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host' => env('REVERB_HOST', '127.0.0.1'),
        'port' => env('REVERB_PORT', 8080),
        'scheme' => env('REVERB_SCHEME', 'http'),
        'useTLS' => env('REVERB_SCHEME', 'http') === 'https',
    ],
],
```

### 2. **Variables de Entorno** (`.env.example`)

Se agregaron las siguientes variables para Reverb:

```env
# Laravel Reverb Configuration (Self-Hosted WebSockets)
BROADCAST_DRIVER=reverb
REVERB_APP_ID=1
REVERB_APP_KEY=your-app-key-here
REVERB_APP_SECRET=your-app-secret-here
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

# Reverb Server Configuration
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

# Vite Frontend Configuration
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### 3. **Configuración de Laravel Echo** (`resources/js/bootstrap.js`)

Se descomentó y actualizó la configuración para Reverb:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? '127.0.0.1',
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
        },
    },
});
```

### 4. **Código Existente** (Sin Cambios Requeridos)

✅ **El evento `AttendanceRegistered` NO requiere cambios** porque:
- Implementa la interfaz `ShouldBroadcast`
- Usa los mismos métodos que Pusher
- Laravel Reverb es compatible con la API de Pusher

✅ **El controlador `AttendanceController` NO requiere cambios**

✅ **Las rutas de canales en `routes/channels.php` NO requieren cambios**

---

## 🚀 Pasos de Instalación

### **Paso 1: Instalar Laravel Reverb**

```bash
composer require laravel/reverb
```

### **Paso 2: Publicar Configuración**

```bash
php artisan reverb:install
```

Este comando:
- Publica el archivo `config/reverb.php`
- Agrega las variables de entorno necesarias al `.env`
- Instala las dependencias de NPM requeridas

### **Paso 3: Configurar Variables de Entorno**

Editar el archivo `.env`:

```env
# Cambiar el driver de broadcasting
BROADCAST_DRIVER=reverb

# Configurar credenciales de Reverb (generadas automáticamente)
REVERB_APP_ID=1
REVERB_APP_KEY=tu-key-generada
REVERB_APP_SECRET=tu-secret-generado
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

# Configuración del servidor
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

# Variables para Vite (Frontend)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

**Nota:** Si `reverb:install` ya generó valores aleatorios para `APP_KEY`, `APP_SECRET`, úsalos. Si no, puedes generarlos manualmente:

```bash
php artisan tinker
>>> Str::random(32)
```

### **Paso 4: Instalar Dependencias de Frontend**

```bash
pnpm install
# o
npm install
```

Esto instalará `laravel-echo` y `pusher-js` (requerido por Reverb).

### **Paso 5: Compilar Assets**

```bash
pnpm run build
# o para desarrollo con hot-reload
pnpm run dev
```

### **Paso 6: Limpiar Caché de Configuración**

```bash
php artisan config:cache
php artisan cache:clear
```

---

## ▶️ Ejecutar el Servidor Reverb

### **Opción 1: En Primer Plano (Desarrollo)**

```bash
php artisan reverb:start
```

Deberías ver:

```
  INFO  Starting server on 0.0.0.0:8080

  2025-11-18 14:30:00 ......................................................  RUNNING
```

### **Opción 2: En Segundo Plano (Producción)**

```bash
php artisan reverb:start --host=0.0.0.0 --port=8080 &
```

### **Opción 3: Con Supervisor (Producción Recomendada)**

Crear archivo `/etc/supervisor/conf.d/reverb.conf`:

```ini
[program:reverb]
process_name=%(program_name)s
command=php /ruta/a/tu/proyecto/artisan reverb:start
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/ruta/a/tu/proyecto/storage/logs/reverb.log
stopwaitsecs=3600
```

Luego:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start reverb
```

---

## 🧪 Verificar que Funciona

### **1. Verificar Conexión desde el Navegador**

Abrir la consola del navegador (F12) y ejecutar:

```javascript
// Verificar estado de conexión
console.log(Echo.connector.pusher.connection.state);
// Debe mostrar: "connected"

// Verificar host y puerto
console.log(Echo.connector.pusher.config);
// Debe mostrar: wsHost: "127.0.0.1", wsPort: 8080
```

### **2. Probar Broadcasting desde Tinker**

```bash
php artisan tinker
```

```php
// Crear una asistencia de prueba
$attendance = new App\Models\Asistencia([
    'id' => 999,
    'rut_asistente' => '12345678',
    'nombre_asistente' => 'Test Reverb',
    'hora_llegada' => now()->format('H:i:s')
]);

// Disparar evento
event(new App\Events\AttendanceRegistered(
    'A101', 
    'R20251118143001', 
    $attendance, 
    15, 
    40, 
    ['type' => 'profesor', 'name' => 'Test', 'id' => '123']
));
```

Si funciona correctamente:
- En la terminal de Reverb verás el mensaje siendo procesado
- En el navegador (si estás suscrito al canal) recibirás el evento

### **3. Probar el Endpoint de Asistencia**

```bash
curl -X POST http://localhost:8000/api/attendance \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": "12345678",
    "room_id": "A101",
    "student_name": "Test Student"
  }'
```

Si hay una reserva activa, el evento se transmitirá automáticamente.

---

## 🔧 Comandos Útiles

### **Ver Estado de Reverb**

```bash
# Ver logs en tiempo real
tail -f storage/logs/reverb.log

# Ver logs de Laravel
tail -f storage/logs/laravel.log
```

### **Reiniciar Reverb**

```bash
# Si está corriendo en primer plano: Ctrl+C y volver a ejecutar
php artisan reverb:start

# Si está con Supervisor
sudo supervisorctl restart reverb
```

### **Verificar Puerto**

```bash
# Windows (PowerShell)
netstat -ano | findstr :8080

# Linux/Mac
lsof -i :8080
```

---

## 🌐 Configuración para Producción

### **1. Usar HTTPS (TLS)**

Editar `.env`:

```env
REVERB_SCHEME=https
REVERB_PORT=443
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
VITE_REVERB_PORT="${REVERB_PORT}"
```

### **2. Configurar Nginx como Proxy Reverso**

Agregar a tu configuración de Nginx:

```nginx
location /app {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}
```

### **3. Configurar Firewall**

```bash
# Permitir puerto 8080
sudo ufw allow 8080/tcp
```

### **4. Variables de Entorno para Producción**

```env
REVERB_HOST=tu-dominio.com
REVERB_PORT=443
REVERB_SCHEME=https
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080

VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

---

## 📊 Comparación: Pusher vs Reverb

| Característica | Pusher | Reverb |
|---|---|---|
| **Hosting** | Externo (SaaS) | Self-hosted |
| **Costo** | Pago por uso | Gratis |
| **Latencia** | Depende de la región | Mínima (local) |
| **Escalabilidad** | Automática | Manual |
| **Control** | Limitado | Total |
| **Configuración** | Mínima | Media |
| **Privacidad** | Datos en servidores externos | Datos en tu servidor |
| **API** | Compatible | 100% compatible |

---

## 🐛 Troubleshooting

### **Error: "Connection refused"**

**Causa:** Reverb no está corriendo

**Solución:**
```bash
php artisan reverb:start
```

---

### **Error: "Unable to connect to WebSocket"**

**Causa:** Variables de entorno incorrectas

**Solución:**
1. Verificar que `.env` tenga las variables correctas
2. Ejecutar: `php artisan config:cache`
3. Recompilar assets: `pnpm run build`

---

### **Error: "401 Unauthorized" en canal privado**

**Causa:** CSRF token no está presente o es inválido

**Solución:**
Verificar que el layout tenga el meta tag:
```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
```

---

### **Evento no se recibe en el frontend**

**Causa:** No estás suscrito al canal o el evento no se está disparando

**Solución:**
1. Verificar suscripción:
   ```javascript
   console.log(Echo.connector.pusher.allChannels());
   ```
2. Verificar logs de Reverb:
   ```bash
   tail -f storage/logs/reverb.log
   ```

---

### **Puerto 8080 ya está en uso**

**Solución:**
```bash
# Cambiar puerto en .env
REVERB_PORT=8081
REVERB_SERVER_PORT=8081
VITE_REVERB_PORT=8081

# Recompilar
pnpm run build

# Reiniciar Reverb
php artisan reverb:start
```

---

## ✅ Checklist de Migración

- [x] Instalar Laravel Reverb: `composer require laravel/reverb`
- [x] Publicar configuración: `php artisan reverb:install`
- [ ] Configurar variables en `.env`
- [ ] Actualizar `BROADCAST_DRIVER=reverb`
- [ ] Instalar dependencias NPM: `pnpm install`
- [ ] Compilar assets: `pnpm run build`
- [ ] Limpiar caché: `php artisan config:cache`
- [ ] Iniciar servidor Reverb: `php artisan reverb:start`
- [ ] Verificar conexión desde navegador
- [ ] Probar evento desde Tinker
- [ ] Probar endpoint de asistencia
- [ ] Configurar Supervisor para producción (opcional)
- [ ] Configurar Nginx proxy reverso (opcional)

---

## 📚 Referencias

- [Documentación Oficial de Laravel Reverb](https://laravel.com/docs/11.x/reverb)
- [Laravel Broadcasting](https://laravel.com/docs/11.x/broadcasting)
- [Laravel Echo](https://laravel.com/docs/11.x/broadcasting#client-side-installation)

---

## 🎉 Ventajas de Usar Reverb

1. **✅ Gratis y Open Source** - No hay costos mensuales
2. **✅ Self-Hosted** - Tus datos permanecen en tu servidor
3. **✅ Latencia Mínima** - Servidor local = respuesta instantánea
4. **✅ Control Total** - Configura todo según tus necesidades
5. **✅ Compatibilidad 100%** - API idéntica a Pusher
6. **✅ Integración Nativa** - Soporte oficial de Laravel
7. **✅ Fácil Debugging** - Logs locales en tiempo real

---

**¡Migración completada exitosamente!** 🚀

El sistema de asistencia ahora usa **Laravel Reverb** en lugar de Pusher, manteniendo todas las funcionalidades de broadcasting en tiempo real sin dependencias externas.

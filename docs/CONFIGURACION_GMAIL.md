# 📧 Configuración de Gmail para Envío de Correos

## 🚀 Activación del Envío Real de Correos

El sistema ahora está configurado para enviar correos reales a través de Gmail SMTP.

---

## ⚙️ Configuración Requerida

### 1. Obtener Contraseña de Aplicación de Gmail

**Importante:** No uses tu contraseña normal de Gmail. Necesitas una "Contraseña de Aplicación".

#### Pasos:

1. Ve a tu cuenta de Google: https://myaccount.google.com/
2. Click en **Seguridad** (en el menú izquierdo)
3. Activa la **Verificación en dos pasos** (si no está activada)
4. Busca **Contraseñas de aplicaciones**
5. Selecciona **Correo** y **Otro (nombre personalizado)**
6. Escribe: "AulaSync"
7. Click en **Generar**
8. **Copia la contraseña de 16 caracteres** (espacios incluidos o sin espacios)

### 2. Configurar `.env`

Abre el archivo `.env` en la raíz del proyecto y configura:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-contraseña-de-aplicacion-de-16-caracteres
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tu-email@gmail.com
MAIL_FROM_NAME="AulaSync"
```

**Ejemplo:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=soporteaulasync@gmail.com
MAIL_PASSWORD=abcd efgh ijkl mnop
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=soporteaulasync@gmail.com
MAIL_FROM_NAME="AulaSync"
```

### 3. Limpiar Caché

Después de modificar `.env`, ejecuta:

```bash
php artisan config:clear
php artisan cache:clear
```

---

## 🧪 Prueba de Envío

### Opción 1: Comando de Prueba

Ejecuta el siguiente comando para enviar un correo de prueba:

```bash
php artisan correo:test tu-email@gmail.com --nombre="Tu Nombre"
```

**Ejemplo:**
```bash
php artisan correo:test juan.perez@gmail.com --nombre="Juan Pérez"
```

Si todo funciona, verás:
```
✅ Correo enviado exitosamente a juan.perez@gmail.com
```

### Opción 2: Usar la Interfaz

1. Ve a **Correos Masivos > Enviar Correos**
2. Selecciona una plantilla
3. Click en **"Cargar"**
4. Selecciona destinatarios o agrega un email externo
5. Click en **"Enviar Correos"**

---

## ❌ Solución de Problemas

### Error: "Authentication failed"

**Causa:** Contraseña incorrecta o no es una contraseña de aplicación.

**Solución:**
- Asegúrate de usar una **Contraseña de Aplicación**, no tu contraseña normal
- Verifica que no haya espacios extra en el `.env`
- Regenera una nueva contraseña de aplicación

### Error: "Connection timeout"

**Causa:** Puerto o servidor incorrecto.

**Solución:**
- Verifica que `MAIL_PORT=587`
- Verifica que `MAIL_ENCRYPTION=tls`
- Verifica tu conexión a internet

### Error: "Could not connect to host"

**Causa:** Firewall o antivirus bloqueando la conexión.

**Solución:**
- Verifica que tu firewall permita conexiones al puerto 587
- Temporalmente desactiva el antivirus para probar

### Los correos llegan a SPAM

**Solución:**
- Esto es normal en las primeras pruebas
- Marca el correo como "No es spam"
- Con el tiempo, Gmail aprenderá que tus correos no son spam

### Error: "SMTP Error: Data not accepted"

**Causa:** Contenido del correo bloqueado por Gmail.

**Solución:**
- Evita usar muchas imágenes grandes
- Evita palabras spam como "GRATIS", "OFERTA", etc.
- Reduce la cantidad de enlaces

---

## 📝 Verificar Configuración Actual

Para verificar la configuración actual de correo, ejecuta:

```bash
php artisan tinker
```

Y luego:
```php
config('mail.mailers.smtp')
```

Deberías ver algo como:
```php
[
  "transport" => "smtp",
  "host" => "smtp.gmail.com",
  "port" => 587,
  "encryption" => "tls",
  "username" => "tu-email@gmail.com",
  "password" => "************",
]
```

---

## 🔒 Seguridad

**IMPORTANTE:**
- ❌ **NUNCA** compartas tu contraseña de aplicación
- ❌ **NUNCA** subas el archivo `.env` a Git (ya está en `.gitignore`)
- ✅ Usa contraseñas de aplicación, no contraseñas normales
- ✅ Revoca las contraseñas de aplicación que no uses

---

## 📊 Logs

Los errores de envío se registran en:
```
storage/logs/laravel.log
```

Para ver los últimos errores:
```bash
tail -f storage/logs/laravel.log
```

---

## 🎯 Límites de Gmail

Gmail tiene límites de envío:
- **500 correos por día** para cuentas gratuitas
- **2000 correos por día** para cuentas Google Workspace

Si necesitas enviar más correos, considera:
- SendGrid
- Amazon SES
- Mailgun
- Postmark

---

## ✅ Checklist de Configuración

- [ ] Verificación en dos pasos activada en Gmail
- [ ] Contraseña de aplicación generada
- [ ] `.env` configurado correctamente
- [ ] Caché limpiada (`php artisan config:clear`)
- [ ] Prueba de envío exitosa (`php artisan correo:test`)
- [ ] Correo de prueba recibido (revisar spam también)

---

## 📞 Soporte

Si después de seguir todos los pasos aún tienes problemas:

1. Revisa los logs: `storage/logs/laravel.log`
2. Ejecuta el comando de prueba con el flag `-v` para más detalles
3. Verifica que tu cuenta de Gmail no tenga restricciones
4. Intenta con otra cuenta de Gmail para descartar problemas de cuenta

---

**Fecha:** 14 de Octubre de 2025  
**Versión:** 1.0

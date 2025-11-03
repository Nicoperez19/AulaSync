# Sistema de Baneos de Reservas

## Descripción
Este sistema permite a los administradores banear usuarios temporalmente para que no puedan realizar reservas de salas. El baneo incluye una razón y una duración.

## Características

### 1. Gestión de Baneos
- **Crear Baneo**: Permite banear a un usuario especificando:
  - RUN del usuario
  - Razón del baneo (máximo 500 caracteres)
  - Fecha y hora hasta cuando estará baneado
  
- **Listar Baneos**: Muestra todos los baneos con:
  - Información del usuario
  - Estado (Activo/Expirado)
  - Tiempo restante
  
- **Editar Baneo**: Permite modificar la razón y duración del baneo

- **Eliminar Baneo**: Permite eliminar un baneo antes de que expire

### 2. Validación en Reservas
El sistema verifica automáticamente si un usuario está baneado cuando intenta:
- Crear una reserva manual desde el panel de administración
- Crear una reserva desde el plano digital (escaneando QR)

### 3. Notificación de Baneo
Cuando un usuario baneado intenta hacer una reserva, se muestra:
- Modal con fondo negro (según requisito)
- Razón del baneo
- Fecha de expiración
- Tiempo restante del baneo

## Uso

### Acceso
El sistema de baneos está disponible en el menú lateral bajo:
- **Mantenedores** → **Baneos**

### Permisos Requeridos
Se requiere el permiso `mantenedor de reservas` para acceder al sistema de baneos.

### Crear un Baneo
1. Ir a **Baneos** en el menú lateral
2. Hacer clic en **Nuevo Baneo**
3. Ingresar el RUN del usuario (debe existir como Profesor, Solicitante o Usuario)
4. Escribir la razón del baneo (será visible para el usuario)
5. Seleccionar fecha y hora de expiración
6. Hacer clic en **Crear Baneo**

### Editar un Baneo
1. Ir a **Baneos** en el menú lateral
2. Hacer clic en **Editar** en el baneo deseado
3. Modificar la razón o fecha de expiración
4. Hacer clic en **Actualizar Baneo**

### Eliminar un Baneo
1. Ir a **Baneos** en el menú lateral
2. Hacer clic en **Eliminar** en el baneo deseado
3. Confirmar la eliminación

## Consideraciones Técnicas

### Base de Datos
Los baneos se almacenan en la tabla `student_bans` con:
- `id`: ID autoincremental
- `run`: RUN del usuario baneado
- `reason`: Razón del baneo
- `banned_until`: Fecha y hora de expiración
- `created_at`, `updated_at`: Timestamps

### Limpieza Automática
El sistema incluye un método `cleanExpiredBans()` en el modelo `StudentBan` que puede ser usado en un job programado para limpiar baneos expirados:

```php
// En un comando o job programado
StudentBan::cleanExpiredBans();
```

### API Endpoints
- `POST /api/crear-reserva-profesor` - Valida baneo al crear reserva de profesor
- `POST /api/crear-reserva-solicitante` - Valida baneo al crear reserva de solicitante
- `GET /api/bans/check/{run}` - Verifica si un usuario está baneado

### Validación
- El RUN debe existir en las tablas `profesors`, `solicitantes` o `users`
- La fecha de expiración debe ser futura
- La razón no puede exceder 500 caracteres

## Tipos de Usuario Soportados
El sistema funciona con todos los tipos de usuario:
- **Profesores** (tabla `profesors`)
- **Solicitantes** (tabla `solicitantes`)
- **Usuarios** (tabla `users`)

## Integración con Sistema Existente
- Se integra con el sistema de permisos existente
- Usa las mismas vistas y componentes de la aplicación
- Sigue los patrones de diseño existentes

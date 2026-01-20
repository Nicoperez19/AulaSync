# Guía de Instalación Gestor Aulas
1.- Clonar repositorio:  git clone https://github.com/Nicoperez19/AulaSync

2.- Copiar .env desde .env.example:  cp .env.example .env

3.- Crear carpetas necesarias
mkdir storage\framework\cache
mkdir storage\framework\sessions
mkdir storage\framework\views
mkdir bootstrap\cache

4.- Generar clave de app: php artisan key:generate
5.- Ejecutar migraciones base de datos central:
php artisan migrate --path=database/migrations/central

6.- Ejecutar seeders base de datos central:
php artisan db:seed --class=CentralDatabaseSeeder

7.- Ejecutar migraciones y seeders base de datos tenants:
 php artisan tenants:setup --fresh --seed

8.- Compilar frontend o ejecutar frontend de desarrollo:
npm run build / npm run dev

9.- Ejecutar servidor de desarrollo laravel:
php artisan serve 

10.- Navegar a la ip asignada.

11.- Ingresar con las credenciales
usuario: 19716146
contraseña: password

12.- Seleccionar sede.

13.- Inicializar Wizard con la contraseña del .env

14.- Completar la información.

15.- Sede y aplicación lista para utilizarse.




# CONFIGURACION DEL .ENV

## 1. Configuración de Base de Datos

### Conexión Principal (Central Database)
```env
# Tipo de conexión de base de datos
DB_CONNECTION=mysql

# Host donde corre MySQL (127.0.0.1 para localhost, o IP del servidor)
DB_HOST=127.0.0.1

# Puerto de MySQL (3306 es el puerto por defecto)
DB_PORT=3306

# Nombre de la base de datos central
# Esta base de datos CENTRAL almacena: usuarios, sedes, universidades, regiones, roles, permisos
DB_DATABASE=gestor

# Credenciales del usuario MySQL
DB_USERNAME=root
DB_PASSWORD=

# Credenciales root para operaciones administrativas
# Se usa para crear automáticamente bases de datos de tenants
# Requiere permisos: CREATE DATABASE, DROP DATABASE, ALTER
DB_ROOT_USERNAME=root
DB_ROOT_PASSWORD=
```

**Notas importantes:**
- `DB_DATABASE=gestor` es la **base de datos central** compartida por todos los tenants
- Contiene tablas del sistema: `users`, `roles`, `permissions`, `sedes`, `tenants`, etc.
- Los `DB_ROOT_*` deben tener permisos de administrador para crear/borrar BDs de tenants

---

## 2. Configuración Multi-Tenancy

### Habilitación del Sistema Multi-Tenant
```env
# Habilita o deshabilita el sistema multi-tenant
MULTITENANCY_ENABLED=true

# Usar bases de datos separadas para cada tenant
# Si es 'false': todos los tenants usan la misma BD (filtrados por tenant_id)
# Si es 'true': cada tenant tiene su propia BD (RECOMENDADO)
MULTITENANCY_SEPARATE_DATABASES=false

# Nombre de la BD base para tenants (solo si SEPARATE_DATABASES=false)
DB_TENANT_DATABASE=${DB_DATABASE}
```

### ¿Cómo funciona la multi-tenancy?

**Arquitectura actual de AulaSync:**

```
┌─────────────────────────────────────────┐
│      BASE DE DATOS CENTRAL (gestor)     │
│  ────────────────────────────────────   │
│  • users (todos los usuarios del app)   │
│  • roles, permissions (permisos)        │
│  • sedes (sedes de universidades)       │
│  • tenants (info de tenants)            │
│  • universidades, regiones, etc.        │
└─────────────────────────────────────────┘

        TENANT 1            TENANT 2
        ────────           ────────
    (Sede IT A) (Sede IT B)
    
    • espacios          • espacios
    • reservas          • reservas
    • profesores        • profesores
    • asignaturas       • asignaturas
    • asistencias       • asistencias
    • etc.              • etc.
```

**Método de Identificación:** `session`
- El tenant se identifica por la sesión del usuario
- Cuando un usuario inicia sesión, se establece el `tenant_id` en la sesión
- Todas las consultas se filtran automáticamente por `tenant_id`

---

## 3. Configuración de Sesión y Cache

```env
# Driver de sesión (file = guardado en storage/framework/sessions)
SESSION_DRIVER=file

# Tiempo de vida de la sesión en minutos (120 = 2 horas)
SESSION_LIFETIME=120

# Driver de cache (file = archivos temporales)
CACHE_DRIVER=file

# Driver de cola de trabajos
QUEUE_CONNECTION=sync
```

---

## 4. Configuración de Email (Para notificaciones)

```env
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=soportegestoraulasit@gmail.com
MAIL_PASSWORD=atqrzexhcbqokyuk      # Contraseña de App Gmail
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Correos de administradores para alertas del sistema
ADMIN_EMAIL_1=admin1@example.com
ADMIN_EMAIL_2=admin2@example.com
```

---

## 5. Configuración del Wizard de Inicialización

```env
# Contraseña requerida para acceder al wizard de configuración inicial
# Se usa en el paso 0 del wizard (configuración de sede)
TENANT_INIT_PASSWORD=gestoraulasit2024
```

**Uso:** 
- Cuando un tenant nuevo accede por primera vez, el wizard solicita esta contraseña
- La contraseña es configurada por el administrador del sistema
- Se debe cambiar antes de ir a producción

---

## Ejemplo de Configuración Completa para Desarrollo Local

```env
# APP
APP_NAME=AulaSync
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# DATABASE
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestor
DB_USERNAME=root
DB_PASSWORD=

# ROOT CREDENTIALS (para crear BDs de tenants)
DB_ROOT_USERNAME=root
DB_ROOT_PASSWORD=

# MULTI-TENANCY
MULTITENANCY_ENABLED=true
MULTITENANCY_SEPARATE_DATABASES=false
DB_TENANT_DATABASE=gestor

# SESSION & CACHE
SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_DRIVER=file

# TENANT INITIALIZATION
TENANT_INIT_PASSWORD=gestoraulasit2024

# MAIL (opcional para desarrollo)
MAIL_DRIVER=log
```

---

## Flujo de Creación de Tenant

1. **Administrador crea una sede** (desde panel de admin)
2. **Sistema crea automáticamente:**
   - Nueva BD: `aulasync_{nombre_sede}` (si SEPARATE_DATABASES=true)
   - Registro en tabla `tenants`
   - Enlace con `sedes`
3. **Usuario accede a la sede**
4. **Se establece `tenant_id` en sesión**
5. **Todos los modelos se filtran automáticamente por `tenant_id`**

---

## Troubleshooting

### Error: "Table not found: roles"
- Ejecutar: `php artisan migrate --path=database/migrations/central`

### Tenants no se crean automáticamente
- Verificar: `MULTITENANCY_ENABLED=true`
- Verificar: `DB_ROOT_USERNAME` y `DB_ROOT_PASSWORD` tienen permisos

### Usuario ve datos de otro tenant
- Verificar el middleware `EnsureTenantIsSet` en rutas
- Revisar que los modelos tengan el trait `BelongsToTenant`

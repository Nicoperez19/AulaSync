# AulaSync

Sistema de gestión de aulas y reservas multi-tenant para instituciones educativas.

## 📋 Descripción

AulaSync es una plataforma web que permite a universidades e instituciones educativas gestionar:

- **Espacios físicos**: Salas de clases, laboratorios, auditorios
- **Reservas**: Programación de uso de espacios por horarios
- **Asistencia**: Registro de asistencia de estudiantes con soporte QR
- **Planificación académica**: Asignaturas, profesores, horarios semanales
- **Multi-tenancy**: Cada sede/campus opera de forma independiente con su propia base de datos

### Stack Tecnológico

| Componente | Tecnología |
|------------|------------|
| Backend | Laravel 10.48, PHP 8.1+ |
| Frontend | Livewire 3, Alpine.js, Tailwind CSS |
| Base de datos | MariaDB 10.11 / MySQL 8 |
| Build | Vite, pnpm |
| Multi-tenancy | Spatie Multitenancy (session-based) |

---

## 🚀 Instalación Rápida

### Prerrequisitos

- PHP 8.1+
- Composer
- Node.js 18+ con pnpm (`npm install -g pnpm`)
- Docker (recomendado para base de datos)

### 1. Clonar y configurar

```bash
git clone https://github.com/Nicoperez19/AulaSync
cd AulaSync

# Instalar dependencias
composer install
pnpm install

# Copiar configuración
cp .env.example .env
php artisan key:generate
```

### 2. Base de datos con Docker (recomendado)

```bash
# Iniciar MariaDB con todas las bases de datos pre-creadas
docker-compose up -d

# Verificar que está corriendo
docker ps
```

Esto crea automáticamente:
- `aulamanager_central` - Base de datos central (usuarios, roles, sedes)
- `aulamanager_th`, `aulamanager_ch`, `aulamanager_la`, `aulamanager_ct`, `aulamanager_ccp` - Bases de datos por tenant

### 3. Migraciones y seeders

```bash
# Migrar base de datos central
php artisan migrate --path=database/migrations/central

# Seeders centrales (usuarios, roles, sedes, tenants)
php artisan db:seed --class=CentralDatabaseSeeder

# Configurar todos los tenants (migraciones + seeders)
php artisan tenants:setup --fresh --seed

# O configurar un tenant específico
php artisan tenant:setup LA --fresh --seed
```

### 4. Compilar frontend y ejecutar

```bash
# Desarrollo (con hot reload)
pnpm run dev

# En otra terminal
php artisan serve
```

### 5. Acceder al sistema

1. Navegar a `http://localhost:8000`
2. **Credenciales por defecto:**
   - Usuario: `19716146`
   - Contraseña: `password`
3. Seleccionar una sede
4. Completar el wizard inicial (contraseña: ver `TENANT_INIT_PASSWORD` en `.env`)

---

## 🗄️ Arquitectura de Base de Datos

### Modelo Multi-Tenant

AulaSync utiliza **bases de datos separadas por tenant** (cada sede tiene su propia BD):

```
┌─────────────────────────────────────────────┐
│     BASE DE DATOS CENTRAL                   │
│     (aulamanager_central)                   │
├─────────────────────────────────────────────┤
│  • users          (todos los usuarios)      │
│  • roles          (roles del sistema)       │
│  • permissions    (permisos)                │
│  • sedes          (sedes registradas)       │
│  • tenants        (config por tenant)       │
│  • universidades  (instituciones)           │
│  • regiones       (ubicaciones)             │
└─────────────────────────────────────────────┘
           │
           ▼
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ aulamanager  │ │ aulamanager  │ │ aulamanager  │
│     _th      │ │     _ch      │ │     _la      │
├──────────────┤ ├──────────────┤ ├──────────────┤
│ • espacios   │ │ • espacios   │ │ • espacios   │
│ • reservas   │ │ • reservas   │ │ • reservas   │
│ • profesores │ │ • profesores │ │ • profesores │
│ • asignaturas│ │ • asignaturas│ │ • asignaturas│
│ • asistencias│ │ • asistencias│ │ • asistencias│
│ • horarios   │ │ • horarios   │ │ • horarios   │
└──────────────┘ └──────────────┘ └──────────────┘
   Sede Talca      Sede Chillán     Sede Los Ángeles
```

### Sedes disponibles

| Código | Sede | Base de datos |
|--------|------|---------------|
| TH | Talca | `aulamanager_th` |
| CH | Chillán | `aulamanager_ch` |
| LA | Los Ángeles | `aulamanager_la` |
| CT | Curicó | `aulamanager_ct` |
| CCP | Concepción | `aulamanager_ccp` |

---

## ⚙️ Configuración del .env

### Variables principales

```env
# === Aplicación ===
APP_NAME=AulaSync
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# === Base de datos ===
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aulamanager_central
DB_USERNAME=aulamanager
DB_PASSWORD=aulamanager_secret

# === Multi-tenancy ===
MULTITENANCY_ENABLED=true
MULTITENANCY_SEPARATE_DATABASES=true
MULTITENANCY_IDENTIFICATION=session

# === Wizard de inicialización ===
TENANT_INIT_PASSWORD=gestoraulasit2024
```

### Configuración de correo (opcional)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-correo@gmail.com
MAIL_PASSWORD=tu-app-password
MAIL_ENCRYPTION=tls
```

---

## 🛠️ Comandos Útiles

### Gestión de tenants

```bash
# Listar todos los tenants
php artisan tenants:list

# Configurar todos los tenants
php artisan tenants:setup --fresh --seed

# Configurar un tenant específico
php artisan tenant:setup LA --seed
php artisan tenant:setup TH --fresh --seed  # fresh = elimina y recrea tablas
```

### Desarrollo

```bash
# Frontend en modo watch
pnpm run dev

# Build de producción
pnpm run build

# Limpiar cachés
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Ejecutar tests
php artisan test
```

### Base de datos

```bash
# Migraciones centrales
php artisan migrate --path=database/migrations/central

# Rollback central
php artisan migrate:rollback --path=database/migrations/central

# Tinker (consola interactiva)
php artisan tinker
```

---

## 📁 Estructura del Proyecto

```
AulaSync/
├── app/
│   ├── Console/Commands/     # Comandos artisan personalizados
│   ├── Http/
│   │   ├── Controllers/      # Controladores web
│   │   │   └── Api/          # Controladores API REST
│   │   ├── Middleware/       # Middleware (auth, tenant)
│   │   └── Requests/         # Form requests (validación)
│   ├── Livewire/             # Componentes Livewire
│   ├── Models/               # Modelos Eloquent
│   ├── Services/             # Lógica de negocio
│   └── Traits/               # Traits reutilizables
├── config/
│   ├── database.php          # Configuración de conexiones
│   └── multitenancy.php      # Configuración multi-tenant
├── database/
│   ├── migrations/
│   │   ├── central/          # Migraciones BD central
│   │   └── tenant/           # Migraciones BDs tenant
│   └── seeders/
│       ├── Data/             # Datos por sede (espacios, pisos, etc.)
│       ├── CentralDatabaseSeeder.php
│       └── TenantDatabaseSeeder.php
├── docker/
│   └── mysql/init.sql        # Script inicialización Docker
├── docs/                     # Documentación adicional
├── resources/
│   └── views/
│       └── livewire/         # Vistas de componentes Livewire
├── routes/
│   ├── api.php               # Rutas API
│   └── web.php               # Rutas web
└── docker-compose.yml        # Configuración Docker
```

---

## 🔌 API REST

AulaSync expone endpoints REST para integración con aplicaciones móviles o nativas, pensado para el módulo de asistencia, por ejemplo.

### Autenticación

La API usa Laravel Sanctum con tokens Bearer:

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "usuario@example.com", "password": "password"}'

# Usar token en requests
curl http://localhost:8000/api/espacios \
  -H "Authorization: Bearer {token}"
```

### Endpoints principales

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/espacios` | Listar espacios |
| GET | `/api/espacios/{id}` | Detalle de espacio |
| GET | `/api/programacion-semanal` | Programación de la semana |
| POST | `/api/attendance` | Registrar asistencia |
| POST | `/api/student-qr-attendance` | Asistencia por QR |

Ver [docs/API_ESPACIOS_Y_TIPOS.md](docs/API_ESPACIOS_Y_TIPOS.md) para documentación completa.

---

## 🧪 Testing

```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos
php artisan test --filter=AttendanceTest

# Con coverage (requiere Xdebug)
php artisan test --coverage
```

---

## 🐛 Troubleshooting

### Error: "SQLSTATE[HY000] [1049] Unknown database"

Las bases de datos no están creadas. Ejecuta:
```bash
docker-compose up -d  # Crea las BDs automáticamente
```

### Error: "Table 'roles' doesn't exist"

Faltan las migraciones centrales:
```bash
php artisan migrate --path=database/migrations/central
```

### Seeders fallan con "tenant connection"

Los seeders de tenant requieren que el tenant esté activo:
```bash
php artisan tenant:setup LA --seed
```

### Assets no cargan (404 en JS/CSS)

```bash
pnpm run build
# O en desarrollo:
pnpm run dev
```

### Docker: puerto 3306 ocupado

```bash
# Ver qué usa el puerto
netstat -ano | findstr :3306

# O cambiar el puerto en docker-compose.yml
ports:
  - "3307:3306"
```

---

## 📚 Documentación Adicional (ALGUNOS ARCHIVOS NECESITAN REVISIÓN Y ACTUALIZARLOS)

| Documento | Descripción |
|-----------|-------------|
| [docs/README.md](docs/README.md) | Índice de documentación |
| [docs/API_ESPACIOS_Y_TIPOS.md](docs/API_ESPACIOS_Y_TIPOS.md) | API de espacios |
| [docs/API_REGISTRO_ASISTENCIA.md](docs/API_REGISTRO_ASISTENCIA.md) | API de asistencia |
| [docs/MULTITENANCY_QUICK_REFERENCE.md](DEBUG/MULTITENANCY_QUICK_REFERENCE.md) | Referencia multi-tenant |
| [.github/copilot-instructions.md](.github/copilot-instructions.md) | Guía para desarrollo con IA |

---

## 👥 Contribuir

1. Fork del repositorio
2. Crear rama feature: `git checkout -b feature/nueva-funcionalidad`
3. Commit cambios: `git commit -m 'Agregar nueva funcionalidad'`
4. Push a la rama: `git push origin feature/nueva-funcionalidad`
5. Crear Pull Request

---

## 📄 Licencia

Este proyecto es software propietario. Todos los derechos reservados.

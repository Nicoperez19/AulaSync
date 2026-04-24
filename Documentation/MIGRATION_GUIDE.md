# Guía de Migración y Configuración Inicial (Desde Cero)

Esta guía detalla los pasos necesarios para inicializar el sistema AulaSync en un entorno nuevo o después de un reset completo.

## Requisitos Previos

1.  Asegurarse de que el archivo `.env` tenga las credenciales correctas para MySQL/MariaDB.
2.  Configurar `DB_ROOT_PASSWORD` en el `.env` (puede ser vacío si no hay contraseña de root localmente) para que el script pueda crear las bases de datos de los tenants.

## Proceso de Inicialización

### 1. Limpieza y Migración Central
Este paso elimina todas las tablas de la base de datos central (`aulamanager_central`) y las recrea.

```bash
php artisan migrate:central --fresh
```

### 2. Poblado de Datos Centrales (Seeders)
Es fundamental poblar los datos básicos que definen las sedes y los tenants antes de configurar estos últimos. El seeder `CentralDatabaseSeeder` ya tiene el orden correcto de dependencias.

```bash
php artisan db:seed --class=CentralDatabaseSeeder
```

*Nota: Este comando creará Universidades, Sedes, Campuses, Roles, Usuarios base y los registros de los 5 Tenants.*

### 3. Configuración de Bases de Datos de Tenants
Este comando automatiza la creación de las bases de datos individuales (`aulasync_ccp`, `aulasync_ch`, etc.), ejecuta sus migraciones y puebla sus datos específicos.

```bash
php artisan tenants:setup --fresh --seed
```

*Nota: Responde `yes` cuando el comando solicite confirmación para eliminar bases de datos existentes.*

---

> [!WARNING]
> **NO utilices scripts manuales** como `seed_ct.php` o similares que se encuentren en la raíz del proyecto. Estos scripts son obsoletos y pueden causar inconsistencias. Utiliza siempre el flujo estándar descrito arriba.

---

## Mantenimiento Diario

El sistema cuenta con tareas programadas (Schedule) para mantener la integridad de los datos:

- **Reset de las 00:00**: El comando `php artisan espacios:liberar` se ejecuta automáticamente cada medianoche para dejar todos los espacios disponibles y finalizar reservas pendientes, dejando evidencia de no uso en las observaciones.
- **Detección de Inasistencias**: El comando `php artisan clases:detectar-no-realizadas` corre cada 5 minutos durante la jornada académica para registrar profesores que no asistieron a sus clases.

## Estructura de Migraciones

Para mantener el orden, no agregues migraciones en la raíz de `database/migrations`. Utiliza las subcarpetas:

- `database/migrations/central/`: Tablas globales (Usuarios, Sedes, Tenants).
- `database/migrations/tenant/`: Tablas específicas de cada sede (Espacios, Reservas, Planificaciones).

# Refactorización de EspacioSeeder y Migraciones de Tenant

## Resumen
Se ha completado la reestructuración del proceso de seeding de espacios y la implementación de herramientas para la gestión de migraciones por tenant.

## Cambios Realizados

### 1. Refactorización de EspacioSeeder
El seeder monolítico `EspacioSeeder.php` ha sido refactorizado para cargar datos dinámicamente según el tenant activo.

- **Ubicación de Datos**: Los arrays de datos crudos se han movido a `database/seeders/Data/Espacios/`.
  - `TH.php`: Talcahuano
  - `CT.php`: Cañete
  - `CH.php`: Chillán
  - `LA.php`: Los Ángeles
- **Lógica Dinámica**: `EspacioSeeder` ahora detecta el tenant actual (`Tenant::current()`), determina la sede (`sede_id`), y carga el archivo correspondiente.
- **Mapeo de Pisos**: Se implementó `buildPisoMap()` para resolver dinámicamente las relaciones (`piso_id`) entre los datos estáticos y los IDs reales en la base de datos del tenant.

### 2. Herramienta de Migración de Tenants
Se creó el comando `tenants:migrate` para facilitar el mantenimiento.

- **Comando**: `php artisan tenants:migrate {tenant?} {--fresh} {--seed}`
- **Funcionalidad**: Itera sobre los tenants activos y ejecuta migraciones en sus bases de datos específicas.
- **Uso**: 
  - `php artisan tenants:migrate`: Migrar todos.
  - `php artisan tenants:migrate aulasync_th`: Migrar solo TH.

### 3. Resultados de Pruebas
Se verificó el funcionamiento con el tenant `aulasync_th` (Talcahuano).
- Limpieza de tablas exitosa.
- `PisoSeeder` generó los pisos base.
- `EspacioSeeder` leyó `TH.php`, mapeó los pisos y creó 29 espacios correctamente.

## Siguiente Paso Recomendado
Ejecutar el seeder en todos los tenants para asegurar consistencia:
```bash
php artisan tenants:migrate --seed
```
O usar el comando legacy si se prefiere no re-migrar.

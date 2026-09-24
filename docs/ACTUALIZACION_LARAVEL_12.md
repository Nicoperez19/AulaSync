# Guía de Migración y Actualización: Laravel 11 a Laravel 12

Este documento detalla el procedimiento técnico, la justificación arquitectónica y las verificaciones ejecutadas para actualizar **AulaSync** desde **Laravel 11.56.1** hacia **Laravel 12.69.2** en la rama de trabajo `UpdateFramework`.

---

## 1. ¿Por Qué se Realiza la Actualización?

1. **Adopción de la Versión Estable Moderna de Laravel (Laravel 12):**
   * Laravel 12 consolida las mejoras del skeleton simplificado, optimiza el rendimiento del contenedor de inyección de dependencias (`Container`) y optimiza el consumo de memoria en consultas masivas de Eloquent.
2. **Actualización Mayor de Multitenancy (`spatie/laravel-multitenancy` v4):**
   * La versión `3.2.0` limitaba el framework a Laravel 11. Al actualizar a la versión `^4.2` (`4.2.1`), el paquete no solo soporta Laravel 12 sino que ya incluye compatibilidad directa para **Laravel 13**, facilitando el salto final.
3. **Paso Previo e Indispensable para Laravel 13:**
   * Tener el proyecto validado y funcionando en Laravel 12 garantiza que el 100% de los breaking changes acumulados de dependencias intermedias están resueltos y aislados antes de abordar el cambio de versión de PHP necesario para Laravel 13.

---

## 2. Requisitos del Entorno

* **Rama Git:** `UpdateFramework`
* **Versión de PHP:** `PHP 8.2.12` (XAMPP) — Cumple con el requisito de Laravel 12 (`PHP >= 8.2.0`).
* **Gestor de Paquetes:** `Composer 2.9.7`.

---

## 3. ¿Cómo se Hizo? (Paso a Paso Técnico)

### Paso 1: Diagnóstico de Bloqueos con Composer
Se analizó la compatibilidad directa con Laravel 12:
```bash
composer prohibits laravel/framework 12.0.0
```
**Bloqueos identificados:**
1. `spatie/laravel-multitenancy (3.2.0)` requería `illuminate/support ^10.0|^11.0`.
2. `nunomaduro/collision (v8.5.0)` tenía conflicto con `laravel/framework >=12.0.0`.
3. `phpunit/phpunit` requería ascender a la serie `11.x`.

---

### Paso 2: Actualización de `composer.json`
Se actualizaron las siguientes dependencias a sus versiones compatibles con Laravel 12:

```json
"require": {
    "php": "^8.2",
    "barryvdh/laravel-dompdf": "^3.1",
    "blade-ui-kit/blade-heroicons": "^1.5",
    "endroid/qr-code": "^6.0",
    "guzzlehttp/guzzle": "^7.2",
    "laravel/framework": "^12.0",
    "laravel/reverb": "^1.6",
    "laravel/sanctum": "^4.0",
    "laravel/tinker": "^2.9",
    "livewire/livewire": "3.6.4",
    "maatwebsite/excel": "^3.1.55",
    "spatie/laravel-multitenancy": "^4.2",
    "spatie/laravel-permission": "^6.16"
},
"require-dev": {
    "beyondcode/laravel-er-diagram-generator": "^4.0",
    "kamona/kui-laravel-breeze": "^0.5.1",
    "laravel/breeze": "^2.0",
    "laravel/pint": "^1.0",
    "laravel/sail": "^1.26",
    "mockery/mockery": "^1.6",
    "fakerphp/faker": "^1.24",
    "nunomaduro/collision": "^8.9",
    "phpunit/phpunit": "^11.5",
    "spatie/laravel-ignition": "^2.4"
}
```

---

### Paso 3: Simulación en Modo Seco (Dry Run)
Se probó la resolución de Composer:
```bash
composer update --dry-run
```
**Resultado:** Resolución limpia con **0 errores** (2 instalaciones nuevas de polyfills compatibles, 24 actualizaciones de paquetes y 0 remociones).

---

### Paso 4: Ejecución de la Instalación
Se aplicó la actualización a las dependencias:
```bash
composer update
```
**Paquetes clave actualizados:**
* `laravel/framework`: **`v11.56.1` ➔ `v12.69.2`**
* `spatie/laravel-multitenancy`: **`3.2.0` ➔ `4.2.1`**
* `nunomaduro/collision`: **`v8.5.0` ➔ `v8.9.5`**
* `phpunit/phpunit`: **`10.5.65` ➔ `11.5.56`**

El comando `package:discover` auto-registró todos los paquetes de Laravel 12 sin advertencias.

---

## 4. Verificación y Pruebas del Sistema

### 1. Comprobación de Versión del Framework
```bash
php artisan --version
# Salida: Laravel Framework 12.69.2
```

### 2. Comprobación del Mapa de Rutas
Se verificó que los controladores, Livewire y middlewares carguen sin conflictos:
```bash
php artisan route:list
# Salida: 344 rutas registradas y operativas sin advertencias.
```

### 3. Prueba de Conexión a Base de Datos Central
```bash
php artisan tinker --execute="echo 'Tenants: ' . \App\Models\Tenant::count() . ' | Universidades: ' . \App\Models\Universidad::count();"
# Salida: Tenants: 5 | Universidades: 1
```

### 4. Prueba de Conexión Multitenancy (Base de Datos Tenant)
Se verificó el cambio dinámico de conexión tenant sobre el nuevo motor de Spatie Multitenancy v4:
```bash
php artisan tinker --execute="\$t = \App\Models\Tenant::find(5); \$t->makeCurrent(); echo 'Tenant 5 Espacios: ' . \App\Models\Espacio::count() . ' | Planificaciones: ' . \App\Models\Planificacion_Asignatura::count();"
# Salida: Tenant 5 Espacios: 31 | Planificaciones: 459
```

### 5. Limpieza de Cachés
```bash
php artisan view:clear
php artisan route:clear
php artisan config:clear
# Salida: Todas las cachés limpiadas con éxito.
```

---

## 5. Hoja de Ruta para el Salto Final: Laravel 13

* **Requisito Fundamental:** Laravel 13 exige estrictamente **PHP >= 8.3**.
* **Procedimiento Recomendado:**
  1. Actualizar el binario de PHP en XAMPP (o configurar un ejecutable de PHP 8.3 / 8.4 en el `PATH`).
  2. Ajustar `"php": "^8.3"` y `"laravel/framework": "^13.0"` en `composer.json`.
  3. Ejecutar `composer update` para completar la migración definitiva.

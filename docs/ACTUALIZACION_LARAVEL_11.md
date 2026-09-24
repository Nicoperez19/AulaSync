# Guía de Migración y Actualización: Laravel 10 a Laravel 11

Este documento detalla el procedimiento técnico, la justificación arquitectónica y las verificaciones ejecutadas para actualizar **AulaSync** desde **Laravel 10.48.29** hacia **Laravel 11.56.1** en la rama de trabajo `UpdateFramework`.

---

## 1. ¿Por Qué se Realiza la Actualización?

1. **Fin de Soporte Activo de Laravel 10:**
   * Laravel 10 concluyó su ciclo de soporte de correcciones de errores en febrero de 2024 y soporte de seguridad en 2025. Laravel 11 cuenta con soporte activo y recepción continua de parches de estabilidad y seguridad.
2. **Evolución del Stack de Paquetes:**
   * Los paquetes centrales del sistema (`spatie/laravel-multitenancy`, `spatie/laravel-permission`, `livewire/livewire` v3, `maatwebsite/excel` y `laravel/reverb`) reciben sus principales optimizaciones de rendimiento y compatibilidad sobre el motor de Laravel 11.
3. **Mejoras de Rendimiento en el Núcleo:**
   * Actualización del motor subyacente a **Symfony 7** y **Carbon 3**, mejorando la velocidad de resolución de rutas, manejo de fechas y consumo de memoria.
4. **Camino Obligatorio hacia Laravel 12 y Laravel 13:**
   * En Laravel no es técnicamente viable saltar directamente de Laravel 10 a Laravel 13 sin pasar por las versiones intermedias, ya que cada versión introduce cambios mayores en dependencias y convenciones de framework.

---

## 2. Requisitos Previos del Entorno

* **Rama Git:** `UpdateFramework`
* **Versión de PHP:** `PHP 8.2.12` (CLI / XAMPP) — Cumple con el requisito mínimo de Laravel 11 (`PHP >= 8.2.0`).
* **Gestor de Paquetes:** `Composer 2.9.7`.

---

## 3. ¿Cómo se Hizo? (Paso a Paso Técnico)

### Paso 1: Diagnóstico de Conflictos con Composer
Antes de alterar paquetes a ciegas, se analizó qué dependencias bloqueaban a Laravel 11 mediante el comando:
```bash
composer prohibits laravel/framework 11.0.0
```
**Hallazgos detectados:**
* `laravel/sanctum` estaba en `v3.3` (bloqueaba Laravel 11, requiere `^4.0`).
* `maatwebsite/excel` estaba anclado estrictamente a `3.1.48` (requiere `^3.1.55` para soportar Laravel 11).
* `nunomaduro/collision` estaba en `v7.0` (requiere `^8.1`).
* `laravel/breeze` estaba en `v1.29` (requiere `^2.0`).
* `mockery/mockery` requería actualización a `^1.6`.

---

### Paso 2: Actualización de Restricciones en `composer.json`
Se actualizaron las versiones mínimas requeridas:

```json
"require": {
    "php": "^8.2",
    "barryvdh/laravel-dompdf": "^3.1",
    "blade-ui-kit/blade-heroicons": "^1.5",
    "endroid/qr-code": "^6.0",
    "guzzlehttp/guzzle": "^7.2",
    "laravel/framework": "^11.0",
    "laravel/reverb": "^1.6",
    "laravel/sanctum": "^4.0",
    "laravel/tinker": "^2.9",
    "livewire/livewire": "3.6.4",
    "maatwebsite/excel": "^3.1.55",
    "spatie/laravel-multitenancy": "^3.2",
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
    "nunomaduro/collision": "^8.1",
    "phpunit/phpunit": "^10.5",
    "spatie/laravel-ignition": "^2.4"
}
```

---

### Paso 3: Simulación en Modo Seco (Dry Run)
Para garantizar que ninguna dependencia secundaria rompiera el árbol de resolución:
```bash
composer update --dry-run
```
**Resultado:** Resolución limpia con 0 errores (4 instalaciones, 102 actualizaciones de paquetes y 4 remociones de librerías obsoletas).

---

### Paso 4: Ejecución de la Actualización
Se ejecutó la descarga e instalación de los nuevos paquetes:
```bash
composer update
```
El framework se actualizó exitosamente a **Laravel 11.56.1** y el comando `package:discover` auto-registró todos los service providers sin incidencias.

---

## 4. Verificación y Pruebas del Sistema

### 1. Comprobación de Versión del Framework
```bash
php artisan --version
# Salida: Laravel Framework 11.56.1
```

### 2. Comprobación del Mapa de Rutas
Se verificó que los controladores y middlewares registrados cargaran correctamente:
```bash
php artisan route:list
# Salida: 344 rutas registradas y operativas sin advertencias ni colisiones.
```

### 3. Prueba de Conexión a Base de Datos Central
```bash
php artisan tinker --execute="echo 'Tenants: ' . \App\Models\Tenant::count() . ' | Universidades: ' . \App\Models\Universidad::count();"
# Salida: Tenants: 5 | Universidades: 1
```

### 4. Prueba de Conexión Multitenancy (Base de Datos Tenant)
Se verificó el cambio dinámico de conexión tenant y consultas Eloquent:
```bash
php artisan tinker --execute="\$t = \App\Models\Tenant::find(5); \$t->makeCurrent(); echo 'Tenant 5 Espacios: ' . \App\Models\Espacio::count() . ' | Planificaciones: ' . \App\Models\Planificacion_Asignatura::count();"
# Salida: Tenant 5 Espacios: 31 | Planificaciones: 459
```

### 5. Limpieza de Cachés de Framework
```bash
php artisan view:clear
php artisan route:clear
php artisan config:clear
# Salida: Todas las cachés limpiadas con éxito.
```

---

## 5. Próximos Pasos en la Rama `UpdateFramework`

1. **Paso a Laravel 12:**
   * Dado que el proyecto ya se encuentra en Laravel 11 estable con PHP 8.2, la transición hacia **Laravel 12** es el siguiente hito lógico (compatible con PHP 8.2).
2. **Paso a Laravel 13:**
   * Para alcanzar **Laravel 13**, se requerirá actualizar la versión del motor de PHP en el servidor/XAMPP a **PHP 8.3 o superior**, requisito obligatorio de dicha versión.

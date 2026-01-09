# Mantenedores Implementados

Este documento describe los nuevos mantenedores (CRUD modules) implementados en el sistema AulaSync.

## Mantenedores Creados

### 1. Configuración del Sistema (Logo Institucional)
- **Ruta**: `/configuracion`
- **Permiso**: `mantenedor de configuracion`
- **Descripción**: Permite administrar la configuración general del sistema, incluyendo el logo institucional
- **Características**:
  - Gestión del logo institucional con subida de archivos de imagen
  - Configuraciones clave-valor para parámetros del sistema
  - CRUD completo con validaciones

### 2. Sedes - Prefijo de Código de Sala
- **Ruta**: `/sedes` (actualizado)
- **Permiso**: `mantenedor de sedes`
- **Descripción**: Se agregó el campo `prefijo_sala` para identificar salas por sede
- **Características**:
  - Campo adicional `prefijo_sala` (ej: TH para Talcahuano, CT para Cañete)
  - Permite identificar salas por su prefijo de sede
  - Validaciones básicas

### 3. Escuelas
- **Ruta**: `/escuelas`
- **Permiso**: `mantenedor de escuelas`
- **Descripción**: Mantenedor de escuelas (filtro de area_academicas con tipo='escuela')
- **Características**:
  - Listado de escuelas con sus carreras asociadas
  - Vinculación con facultades
  - CRUD completo
  - Muestra cantidad de carreras por escuela

### 4. Jefes de Carrera
- **Ruta**: `/jefes-carrera`
- **Permiso**: `mantenedor de jefes de carrera`
- **Descripción**: Administra los jefes de carrera del sistema
- **Características**:
  - Un jefe de carrera por carrera
  - Datos: nombre, email, teléfono
  - Vinculación con carreras
  - CRUD completo con validaciones

### 5. Asistentes Académicos
- **Ruta**: `/asistentes-academicos`
- **Permiso**: `mantenedor de asistentes academicos`
- **Descripción**: Administra los asistentes académicos (1 por escuela)
- **Características**:
  - Un asistente académico por escuela
  - Datos: nombre, email, teléfono
  - Vinculación con área académica (escuelas)
  - CRUD completo con validaciones

## Estructura de Archivos

### Migraciones
- `2025_11_03_162433_create_configuracion_table.php`
- `2025_11_03_162433_add_prefijo_sala_to_sedes_table.php`
- `2025_11_03_162433_create_jefes_carrera_table.php`
- `2025_11_03_162433_create_asistentes_academicos_table.php`

### Modelos
- `app/Models/Configuracion.php`
- `app/Models/JefeCarrera.php`
- `app/Models/AsistenteAcademico.php`
- `app/Models/Sede.php` (actualizado)
- `app/Models/AreaAcademica.php` (actualizado)
- `app/Models/Carrera.php` (actualizado)

### Controladores
- `app/Http/Controllers/ConfiguracionController.php`
- `app/Http/Controllers/EscuelaController.php`
- `app/Http/Controllers/JefeCarreraController.php`
- `app/Http/Controllers/AsistenteAcademicoController.php`
- `app/Http/Controllers/SedeController.php` (actualizado)

### Componentes Livewire
- `app/Livewire/ConfiguracionTable.php`
- `app/Livewire/EscuelasTable.php`
- `app/Livewire/JefesCarreraTable.php`
- `app/Livewire/AsistentesAcademicosTable.php`

### Vistas
- `resources/views/layouts/configuracion/` (index, edit)
- `resources/views/layouts/escuelas/` (index, edit)
- `resources/views/layouts/jefes_carrera/` (index, edit)
- `resources/views/layouts/asistentes_academicos/` (index, edit)
- `resources/views/layouts/sedes/` (actualizadas con campo prefijo_sala)
- `resources/views/livewire/` (tablas para cada mantenedor)

### Seeders
- `database/seeders/NewMaintainersPermissionsSeeder.php` - Crea los permisos necesarios
- `database/seeders/ConfiguracionSeeder.php` - Crea configuraciones iniciales

## Instalación

Para usar estos nuevos mantenedores:

1. **Ejecutar migraciones**:
   ```bash
   php artisan migrate
   ```

2. **Ejecutar seeders de permisos**:
   ```bash
   php artisan db:seed --class=NewMaintainersPermissionsSeeder
   php artisan db:seed --class=ConfiguracionSeeder
   ```

3. **Asignar permisos a roles** (si es necesario):
   Los permisos se asignan automáticamente al rol "Administrador" en el seeder

## Permisos Requeridos

- `mantenedor de configuracion` - Para gestionar configuración del sistema
- `mantenedor de sedes` - Para gestionar sedes (ya existente, ahora con prefijo)
- `mantenedor de escuelas` - Para gestionar escuelas
- `mantenedor de jefes de carrera` - Para gestionar jefes de carrera
- `mantenedor de asistentes academicos` - Para gestionar asistentes académicos

## Características de Seguridad

- Control de permisos en todas las rutas
- Validaciones en controllers y requests
- Protección CSRF en formularios
- Validación de unicidad en emails
- Confirmación antes de eliminar registros (SweetAlert2)

## Relaciones del Modelo

### Configuracion
- Standalone (no tiene relaciones directas)

### Sede
- Tiene campo `prefijo_sala` para identificación de salas

### JefeCarrera
- `belongsTo` Carrera

### AsistenteAcademico
- `belongsTo` AreaAcademica (escuela)

### Carrera
- `hasOne` JefeCarrera

### AreaAcademica
- `hasOne` AsistenteAcademico

## Notas de Implementación

1. El mantenedor de escuelas utiliza la tabla `area_academicas` existente, filtrando por `tipo_area_academica = 'escuela'`
2. El logo institucional se guarda en `storage/app/public/images/logo/`
3. Todas las vistas siguen el mismo patrón de diseño que las existentes
4. Se utilizan componentes Livewire para las tablas con paginación y búsqueda
5. Todos los controladores incluyen manejo de excepciones

## Próximos Pasos

- [ ] Integrar el logo institucional en todas las vistas
- [ ] Probar cada CRUD completo
- [ ] Verificar permisos funcionan correctamente
- [ ] Tomar screenshots de cada mantenedor funcionando
- [ ] Documentar casos de uso específicos

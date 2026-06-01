# Sistema de Administración de Correos Masivos

## Descripción General

Este sistema permite administrar tipos de correos masivos y sus destinatarios de forma centralizada. Los administradores pueden:

1. **Crear tipos de correos** personalizados o usar los predefinidos del sistema
2. **Gestionar destinatarios** y asignarles roles específicos
3. **Asignar destinatarios a tipos de correos** mediante un sistema de habilitación/deshabilitación

## Acceso al Sistema

### URL de Acceso
```
/correos-masivos
```

### Permisos Requeridos
- **Rol:** Administrador únicamente
- Los demás usuarios no tienen acceso a este módulo

## Estructura de la Base de Datos

### Tablas Creadas

#### 1. `tipos_correos_masivos`
Almacena los tipos de correos que se pueden enviar de forma masiva.

**Campos:**
- `id`: Identificador único
- `nombre`: Nombre descriptivo (ej: "Informe Semanal de Clases No Realizadas")
- `codigo`: Código único para identificar el tipo (ej: "informe_semanal_clases_no_realizadas")
- `descripcion`: Descripción del propósito del correo
- `tipo`: 'sistema' o 'custom' (los del sistema no se pueden eliminar)
- `frecuencia`: 'diario', 'semanal', 'mensual' o 'manual'
- `activo`: Si el tipo de correo está activo
- `configuracion`: JSON con configuraciones adicionales

#### 2. `destinatarios_correos`
Almacena los usuarios que pueden recibir correos masivos.

**Campos:**
- `id`: Identificador único
- `user_id`: Referencia al RUN del usuario en la tabla `users`
- `rol`: Rol en el contexto de correos (ej: "Jefe de Carrera", "Director")
- `cargo`: Descripción adicional del cargo
- `activo`: Si el destinatario está activo

#### 3. `tipo_correo_destinatario` (Tabla Pivot)
Relaciona tipos de correos con destinatarios.

**Campos:**
- `tipo_correo_masivo_id`: ID del tipo de correo
- `destinatario_correo_id`: ID del destinatario
- `habilitado`: Si este destinatario está habilitado para recibir este tipo de correo

## Tipos de Correos Predefinidos

El sistema incluye 7 tipos de correos predefinidos:

1. **Informe Semanal de Clases No Realizadas**
   - Código: `informe_semanal_clases_no_realizadas`
   - Frecuencia: Semanal
   - Descripción: Resumen semanal para jefes de carrera

2. **Informe Mensual de Clases No Realizadas**
   - Código: `informe_mensual_clases_no_realizadas`
   - Frecuencia: Mensual
   - Descripción: Estadísticas consolidadas para directivos

3. **Notificación de Clase No Realizada**
   - Código: `notificacion_clase_no_realizada`
   - Frecuencia: Manual
   - Descripción: Notificación inmediata de clases no realizadas

4. **Alerta de Clases No Justificadas**
   - Código: `alerta_clases_no_justificadas`
   - Frecuencia: Diaria
   - Descripción: Alertas diarias de clases sin justificar

5. **Reporte de Ocupación de Espacios**
   - Código: `reporte_ocupacion_espacios`
   - Frecuencia: Semanal
   - Descripción: Uso y ocupación de espacios físicos

6. **Resumen de Reservas Semanales**
   - Código: `resumen_reservas_semanales`
   - Frecuencia: Semanal
   - Descripción: Reservas realizadas y pendientes

7. **Comunicados Administrativos**
   - Código: `comunicados_administrativos`
   - Frecuencia: Manual
   - Descripción: Comunicados generales de la administración

## Uso del Sistema

### 1. Gestión de Tipos de Correos

#### Crear un Nuevo Tipo
1. Ve a la pestaña **"Tipos de Correos"**
2. Completa el formulario:
   - **Nombre:** Nombre descriptivo del tipo de correo
   - **Código:** Se genera automáticamente del nombre (puedes editarlo)
   - **Descripción:** Explica el propósito del correo
   - **Frecuencia:** Selecciona la frecuencia de envío
   - **Activo:** Marca si el tipo está activo
3. Haz clic en **"Crear"**

#### Editar un Tipo
1. Haz clic en el ícono de editar (lápiz) en la tabla
2. Modifica los campos necesarios
3. Haz clic en **"Actualizar"**

#### Eliminar un Tipo
- Solo se pueden eliminar tipos **Personalizados**
- Los tipos del **Sistema** no se pueden eliminar
- Haz clic en el ícono de eliminar (basurero) y confirma

### 2. Gestión de Destinatarios

#### Agregar un Destinatario
1. Ve a la pestaña **"Destinatarios"**
2. Completa el formulario:
   - **Usuario:** Selecciona un usuario del sistema
   - **Rol:** Asigna un rol (ej: "Jefe de Carrera")
   - **Cargo/Descripción:** Información adicional (opcional)
   - **Activo:** Marca si está activo
3. Haz clic en **"Crear"**

**Nota:** Un usuario solo puede estar registrado una vez como destinatario.

#### Editar un Destinatario
1. Haz clic en el ícono de editar
2. Modifica rol, cargo o estado
3. **No se puede cambiar el usuario** una vez creado

#### Eliminar un Destinatario
1. Haz clic en el ícono de eliminar
2. Confirma la eliminación
3. Se eliminarán todas las asignaciones de tipos de correos asociadas

### 3. Asignar Destinatarios a Tipos de Correos

#### Abrir el Modal de Asignación
1. En la pestaña **"Tipos de Correos"**
2. Haz clic en el ícono de usuarios (👥) en la tabla
3. Se abrirá un modal con todos los destinatarios disponibles

#### Habilitar/Deshabilitar Destinatarios
- Usa el **toggle switch** junto a cada destinatario
- **Verde/Habilitado:** El destinatario recibirá este tipo de correo
- **Gris/Deshabilitado:** El destinatario NO recibirá este tipo de correo

#### Cerrar el Modal
- Haz clic en **"Listo"** o fuera del modal
- Los cambios se guardan automáticamente

## Casos de Uso Comunes

### Caso 1: Configurar Informes de Clases No Realizadas para Jefes de Carrera

1. **Crear Destinatarios:**
   - Agrega a los 5 profesores jefes de carrera como destinatarios
   - Asigna el rol "Jefe de Carrera" a cada uno

2. **Asignar al Tipo de Correo:**
   - Abre el tipo "Informe Semanal de Clases No Realizadas"
   - Habilita los 5 jefes de carrera
   - Cierra el modal

### Caso 2: Enviar Informe Mensual a Directivos

1. **Crear Destinatarios:**
   - Agrega al Director (rol: "Director")
   - Agrega al Subdirector (rol: "Subdirector")

2. **Asignar al Tipo de Correo:**
   - Abre el tipo "Informe Mensual de Clases No Realizadas"
   - Habilita al Director y Subdirector
   - Cierra el modal

### Caso 3: Crear un Tipo de Correo Personalizado

1. **Crear el Tipo:**
   - Nombre: "Reporte de Asistencia Docente"
   - Código: "reporte_asistencia_docente"
   - Descripción: "Reporte mensual de asistencia de docentes"
   - Frecuencia: Mensual
   - Tipo: Se marca automáticamente como "Custom"

2. **Asignar Destinatarios:**
   - Selecciona los destinatarios que deben recibirlo
   - Habilítalos usando el toggle

## Integración con el Sistema de Correos

### Uso en el Código

Para enviar correos a destinatarios habilitados:

```php
use App\Models\TipoCorreoMasivo;

// Obtener tipo de correo por código
$tipoCorreo = TipoCorreoMasivo::where('codigo', 'informe_semanal_clases_no_realizadas')
    ->activos()
    ->first();

// Obtener destinatarios habilitados
$destinatarios = $tipoCorreo->destinatariosHabilitados()->get();

// Enviar correos
foreach ($destinatarios as $destinatario) {
    $email = $destinatario->user->email;
    // Lógica de envío de correo
    Mail::to($email)->send(new InformeClasesNoRealizadas($data));
}
```

### Scopes Disponibles

#### En TipoCorreoMasivo:
```php
// Solo tipos activos
TipoCorreoMasivo::activos()->get();

// Solo tipos del sistema
TipoCorreoMasivo::sistema()->get();

// Solo tipos personalizados
TipoCorreoMasivo::custom()->get();
```

#### En DestinatarioCorreo:
```php
// Solo destinatarios activos
DestinatarioCorreo::activos()->get();
```

## Archivos del Sistema

### Migraciones
- `2025_10_07_110713_create_tipos_correos_masivos_table.php`
- `2025_10_07_110714_create_destinatarios_correos_table.php`
- `2025_10_07_110719_create_tipo_correo_destinatario_table.php`

### Modelos
- `app/Models/TipoCorreoMasivo.php`
- `app/Models/DestinatarioCorreo.php`

### Componente Livewire
- `app/Livewire/CorreosMasivosManager.php`

### Vistas
- `resources/views/livewire/correos-masivos-manager.blade.php`
- `resources/views/livewire/partials/tipos-correos-tab.blade.php`
- `resources/views/livewire/partials/destinatarios-correos-tab.blade.php`

### Seeders
- `database/seeders/TiposCorreosMasivosSeeder.php`

### Rutas
```php
// En routes/web.php dentro del grupo de Administrador
Route::get('/correos-masivos', \App\Livewire\CorreosMasivosManager::class)
    ->name('correos-masivos.index');
```

## Características Adicionales

### Búsqueda
- En ambas pestañas hay un buscador en tiempo real
- Busca por nombre, código, rol, email, etc.

### Paginación
- Ambas tablas están paginadas (10 registros por página)

### Feedback Visual
- Mensajes de éxito/error con SweetAlert2
- Confirmaciones antes de eliminar
- Estados visuales claros (activo/inactivo, habilitado/deshabilitado)

### Responsive
- El diseño se adapta a diferentes tamaños de pantalla
- En móviles, los formularios y tablas se reorganizan

## Seguridad

### Validaciones
- Nombres y códigos únicos para tipos de correos
- Un usuario solo puede ser destinatario una vez
- Los tipos del sistema no se pueden eliminar
- Solo administradores pueden acceder

### Protección de Datos
- Eliminación en cascada (eliminar un tipo elimina sus asignaciones)
- Claves foráneas con integridad referencial
- Validaciones tanto en frontend como backend

## Mantenimiento

### Agregar Nuevos Tipos del Sistema
Edita el seeder `TiposCorreosMasivosSeeder.php` y ejecuta:
```bash
php artisan db:seed --class=TiposCorreosMasivosSeeder
```

### Migrar Nuevamente
Si necesitas recrear las tablas:
```bash
php artisan migrate:fresh --seed
```

## Troubleshooting

### Error: "No tienes permisos para acceder"
- Verifica que tu usuario tenga el rol "Administrador"

### Error: "Este usuario ya está registrado como destinatario"
- Un usuario solo puede estar una vez en la lista de destinatarios
- Si necesitas cambiar su rol, edita el destinatario existente

### No aparecen tipos de correos
- Ejecuta el seeder: `php artisan db:seed --class=TiposCorreosMasivosSeeder`

### Los cambios en asignaciones no se guardan
- Verifica la consola del navegador en busca de errores JavaScript
- Asegúrate de que Livewire esté funcionando correctamente

## Soporte

Para reportar problemas o sugerencias de mejora, contacta al equipo de desarrollo.

---

**Versión:** 1.0  
**Fecha:** Octubre 2025  
**Autor:** Sistema SIA | Sistema de Informaci�n de Aulas

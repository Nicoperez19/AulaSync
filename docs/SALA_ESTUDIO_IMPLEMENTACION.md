# Implementación de Registro de Acceso a Salas de Estudio

## Descripción General

Se ha implementado un sistema especial para las **Salas de Estudio** en el plano digital que permite a los alumnos registrar su acceso mediante el escaneo de su carnet estudiantil. El sistema controla el aforo disponible según la capacidad máxima establecida en el mantenedor de espacios.

## Características Principales

### 1. Modal Especial para Salas de Estudio

Cuando un usuario hace clic en un espacio tipo "Sala de Estudio" en el plano digital, se abre un modal especial con las siguientes secciones:

- **Capacidad**: Muestra el número actual de alumnos registrados vs capacidad máxima
- **Barra de progreso**: Indicador visual del nivel de ocupación
- **Scanner QR**: Campo para escanear carnets estudiantiles
- **Lista de Alumnos Registrados**: Muestra todos los alumnos actualmente registrados con su hora de ingreso

### 2. Control de Aforo

El sistema respeta la capacidad máxima establecida en el campo `capacidad_maxima` del mantenedor de espacios:

- Permite registros hasta alcanzar la capacidad máxima
- Rechaza nuevos registros cuando se alcanza el límite
- Muestra alertas visuales cuando se acerca al límite (70% amarillo, 90% rojo)

### 3. Registro de Acceso

Los alumnos pueden registrar su acceso escaneando su carnet:

- **Detección automática**: El sistema extrae el RUN del código QR
- **Validación de usuario**: Verifica que el usuario exista en la base de datos
- **Prevención de duplicados**: No permite que el mismo alumno se registre dos veces
- **Registro temporal**: Crea una reserva con duración de 2 horas por defecto

### 4. Visualización en Tiempo Real

La lista de alumnos registrados se actualiza automáticamente mostrando:

- Número de orden
- Nombre completo del alumno
- RUN
- Hora de registro

## Archivos Modificados/Creados

### 1. Vista - Plano Digital
**Archivo**: `resources/views/layouts/plano_digital/show.blade.php`

**Cambios realizados**:

#### Modal de Sala de Estudio (líneas ~341-440)
```html
<div id="modal-sala-estudio" class="...">
    <!-- Encabezado con icono de libro -->
    <!-- Sección de capacidad con barra de progreso -->
    <!-- Scanner QR -->
    <!-- Lista de alumnos registrados -->
</div>
```

#### Función de Detección (línea ~2099)
```javascript
async function mostrarModalEspacio(indicator) {
    // Detecta si es Sala de Estudio
    if (indicator.tipo && (indicator.tipo.toLowerCase() === 'sala de estudio' || 
        indicator.tipo.toLowerCase() === 'sala estudio')) {
        mostrarModalSalaEstudio(indicator);
        return;
    }
    // ... resto del código para otros tipos de espacios
}
```

#### Funciones para Sala de Estudio (líneas ~3060-3380)
- `mostrarModalSalaEstudio()`: Abre el modal y configura el escáner
- `cerrarModalSalaEstudio()`: Cierra el modal y limpia datos
- `handleScanSalaEstudio()`: Maneja el escaneo del carnet
- `procesarQRSalaEstudio()`: Procesa el QR y registra el acceso
- `registrarAccesoSalaEstudio()`: Llamada API para registrar
- `cargarAlumnosRegistradosSalaEstudio()`: Carga la lista de alumnos
- `actualizarListaAlumnosSalaEstudio()`: Actualiza la UI con los alumnos
- `actualizarContadoresSalaEstudio()`: Actualiza contadores y barra
- `mostrarNotificacionSalaEstudio()`: Muestra notificaciones toast

#### Actualización del QRInputManager (línea ~758)
```javascript
this.qrInputs = {
    main: document.getElementById('qr-input'),
    devolucion: document.getElementById('qr-input-devolucion'),
    solicitud: document.getElementById('qr-input-solicitud'),
    salaEstudio: document.getElementById('qr-input-sala-estudio') // ← Nuevo
};
```

### 2. Controlador Backend
**Archivo**: `app/Http/Controllers/SalaEstudioController.php` (NUEVO)

#### Métodos:

##### `registrarAcceso(Request $request)`
Registra el acceso de un alumno a la sala de estudio.

**Parámetros**:
- `id_espacio`: Código del espacio
- `run`: RUN del alumno

**Validaciones**:
- Usuario existe en la base de datos
- Espacio es tipo "Sala de Estudio"
- No se ha excedido la capacidad máxima
- El alumno no está ya registrado

**Proceso**:
1. Busca el usuario por RUN
2. Verifica el tipo de espacio
3. Cuenta reservas activas
4. Crea nueva reserva con tipo `sala_estudio`
5. Actualiza estado del espacio a `Ocupado`

**Respuesta exitosa**:
```json
{
    "success": true,
    "mensaje": "Acceso registrado exitosamente",
    "nombre": "Juan Pérez",
    "run": "12345678",
    "hora_registro": "14:30"
}
```

##### `obtenerAlumnosRegistrados($idEspacio)`
Obtiene la lista de alumnos registrados en el día actual.

**Respuesta**:
```json
{
    "success": true,
    "alumnos": [
        {
            "run": "12345678",
            "nombre": "Juan Pérez",
            "hora_registro": "14:30"
        }
    ]
}
```

### 3. Rutas API
**Archivo**: `routes/api.php`

Rutas agregadas (líneas ~407-417):
```php
// Registrar acceso a sala de estudio
Route::post('/sala-estudio/registrar-acceso', 
    [SalaEstudioController::class, 'registrarAcceso']);

// Obtener alumnos registrados en sala de estudio
Route::get('/sala-estudio/{id_espacio}/alumnos-registrados', 
    [SalaEstudioController::class, 'obtenerAlumnosRegistrados']);
```

## Base de Datos

### Tabla: `reservas`

El sistema utiliza la tabla existente de reservas con los siguientes campos relevantes:

- `id_espacio`: Código de la sala de estudio
- `run_solicitante`: RUN del alumno
- `fecha_reserva`: Fecha y hora del registro
- `hora_inicio`: Hora de ingreso
- `hora_termino`: Hora estimada de salida (inicio + 2 horas)
- `estado_reserva`: `'activa'` mientras el alumno esté en la sala
- `tipo_reserva`: `'sala_estudio'` para identificar estos registros

### Tabla: `espacios`

Campos relevantes:
- `tipo_espacio`: Debe ser `'Sala de Estudio'`
- `capacidad_maxima`: Aforo máximo permitido
- `puestos_disponibles`: Espacios disponibles actuales
- `estado`: Se actualiza a `'Ocupado'` cuando hay alumnos registrados

## Flujo de Uso

1. **Alumno accede al plano digital**
   - Navega al piso donde está la sala de estudio

2. **Selecciona la sala de estudio**
   - Hace clic en el espacio en el plano
   - Se abre el modal especial de sala de estudio

3. **Escanea su carnet**
   - El sistema enfoca automáticamente el campo de escaneo
   - Escanea el código QR de su carnet estudiantil

4. **Sistema procesa el registro**
   - Extrae el RUN del QR
   - Valida el usuario
   - Verifica capacidad disponible
   - Crea el registro de acceso

5. **Confirmación visual**
   - Aparece notificación de éxito
   - El alumno aparece en la lista de registrados
   - Los contadores se actualizan

## Configuración del Mantenedor de Espacios

Para que una sala funcione correctamente como sala de estudio:

1. **Tipo de Espacio**: Debe estar configurado como `"Sala de Estudio"`
2. **Capacidad Máxima**: Debe tener un valor en el campo `capacidad_maxima`
   - Ejemplo: 20 para una sala de 20 personas

## Seguridad y Validaciones

### Validaciones en Backend

- ✅ Usuario existe en la base de datos
- ✅ Espacio existe y es del tipo correcto
- ✅ No se excede la capacidad máxima
- ✅ Usuario no está duplicado en el mismo día
- ✅ Registro de logs para auditoría

### Validaciones en Frontend

- ✅ QR válido con formato correcto
- ✅ RUN extraído correctamente
- ✅ Notificaciones claras de errores
- ✅ Prevención de múltiples envíos simultáneos

## Mejoras Futuras Sugeridas

1. **Registro de Salida**: Implementar escaneo de salida para liberar espacio
2. **Tiempo Variable**: Permitir seleccionar duración estimada de uso
3. **Estadísticas**: Dashboard con uso histórico de salas de estudio
4. **Notificaciones**: Alertar cuando la sala esté cerca de llenarse
5. **Reservas Anticipadas**: Permitir reservar un puesto con antelación
6. **Control de Horarios**: Limitar uso por franjas horarias

## Solución de Problemas

### El modal no se abre
- Verificar que el tipo de espacio sea exactamente "Sala de Estudio"
- Revisar consola del navegador para errores JavaScript

### No se registra el acceso
- Verificar que el usuario existe en la base de datos
- Comprobar que no se haya alcanzado la capacidad máxima
- Revisar logs del servidor en `storage/logs/laravel.log`

### Lista de alumnos no se carga
- Verificar conectividad con la API
- Comprobar que existen registros del día actual
- Revisar respuesta de la API en herramientas de desarrollo

## Logs y Debugging

El sistema genera logs en:
- ✅ Registro exitoso de acceso
- ✅ Errores al registrar acceso
- ✅ Errores al cargar alumnos

Ubicación: `storage/logs/laravel.log`

Ejemplo de log:
```
[2024-01-15 14:30:45] local.INFO: Acceso registrado en sala de estudio 
{"espacio":"TH-E1","usuario":"12345678","nombre":"Juan Pérez"}
```

## Consideraciones de Rendimiento

- Cache de consultas no implementado (puede agregarse si es necesario)
- Consultas optimizadas con índices en `fecha_reserva` y `estado_reserva`
- Actualización en tiempo real solo al abrir el modal

## Compatibilidad

- ✅ Navegadores modernos (Chrome, Firefox, Edge, Safari)
- ✅ Dispositivos móviles y tablets
- ✅ Lectores de código QR estándar
- ✅ Compatible con el sistema de QR existente

## Autor

Implementación realizada para el sistema AulaSync
Fecha: Noviembre 2025

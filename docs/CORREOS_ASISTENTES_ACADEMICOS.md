# 📧 Sistema de Correos Administrativos por Asistente Académico

## ✅ Cambios Implementados (07/11/2025)

### Resumen
El sistema de correos administrativos ahora está **vinculado a los Asistentes Académicos** de cada Escuela, en lugar de estar en la configuración general.

---

## 🎯 Cómo Funciona

### 1. **Cada Escuela tiene un Asistente Académico**
- El asistente académico gestiona el correo oficial de su escuela
- El correo del asistente se usa automáticamente como remitente en emails formales

### 2. **Campos del Asistente Académico**
- **Nombre**: Nombre del asistente
- **Email**: Correo que se usará como remitente ⭐
- **Nombre Remitente**: Nombre formal para correos (ej: "Asistencia Académica - Escuela de Ingeniería") ⭐ NUEVO
- **Teléfono**: Contacto opcional
- **Escuela**: Área académica asignada

---

## 🧪 Cómo Probarlo

### **Paso 1: Acceder al Mantenedor**
```
Ruta: Menú Principal → Mantenedores → Asistentes Académicos
URL: http://localhost/asistentes-academicos
Permiso requerido: "mantenedor de asistentes academicos"
```

### **Paso 2: Crear/Editar un Asistente Académico**

#### Formulario de Creación:
1. Click en "Agregar Asistente Académico"
2. Llenar campos:
   - **Nombre**: María González
   - **Email**: mgonzalez@ucsc.cl ← Este será el remitente
   - **Nombre Remitente**: Asistencia Académica - Escuela de Ingeniería ← Aparece en emails
   - **Teléfono**: +56 9 1234 5678
   - **Escuela**: Seleccionar escuela

3. Click "Crear Asistente Académico"

### **Paso 3: Verificar en la Tabla**
La tabla muestra:
- Nombre
- Email
- **Nombre Remitente** (columna nueva)
- Teléfono
- Escuela

---

## 🔧 Uso en el Código

### **Ejemplo 1: Enviar correo usando el asistente de un espacio**

```php
use App\Services\CorreoAdministrativoService;
use App\Mail\CorreoPersonalizado;
use Illuminate\Support\Facades\Mail;

// Obtener correo del asistente por espacio
$espacio = Espacio::find(123);
$correoData = CorreoAdministrativoService::getCorreoPorEspacio($espacio->id_espacio);

// $correoData contiene:
// ['email' => 'mgonzalez@ucsc.cl', 'name' => 'Asistencia Académica - Escuela de Ingeniería']

// Enviar correo
Mail::to('profesor@ucsc.cl')->send(
    new CorreoPersonalizado(
        'Notificación de Reserva',
        '<p>Su reserva ha sido confirmada</p>',
        'Dr. Juan Pérez',
        $espacio->id_area_academica // ← Pasas el área académica
    )
);
```

### **Ejemplo 2: Enviar correo usando el asistente de un profesor**

```php
$profesor = Profesor::where('run_profesor', '12345678-9')->first();
$correoData = CorreoAdministrativoService::getCorreoPorProfesor($profesor->run_profesor);

Mail::to('destinatario@ucsc.cl')->send(
    new CorreoPersonalizado(
        'Asunto del correo',
        '<p>Contenido HTML</p>',
        'Destinatario',
        $profesor->id_area_academica
    )
);
```

### **Ejemplo 3: Obtener correo directamente por área académica**

```php
$correoData = CorreoAdministrativoService::getCorreoAreaAcademica('ING');

// Si existe asistente:
// ['email' => 'asistente@ucsc.cl', 'name' => 'Nombre Formal']

// Si NO existe asistente (fallback):
// ['email' => 'noreply@SIA | Sistema de Informaci�n de Aulas.cl', 'name' => 'Sistema SIA | Sistema de Informaci�n de Aulas']
```

---

## 📊 Base de Datos

### **Migración Ejecutada**
```sql
ALTER TABLE asistentes_academicos 
ADD COLUMN nombre_remitente VARCHAR(150) NULL 
COMMENT 'Nombre formal para usar como remitente en correos oficiales'
AFTER email;
```

### **Verificar en Base de Datos**
```sql
-- Ver asistentes con sus correos
SELECT 
    nombre,
    email,
    nombre_remitente,
    id_area_academica
FROM asistentes_academicos;
```

---

## 🔄 Sistema de Caché

El servicio usa caché de 60 minutos:
- **Clave de caché**: `asistente_academico_{id_area_academica}`
- **Auto-invalidación**: Al crear/editar/eliminar un asistente

### Limpiar caché manualmente:
```php
// Limpiar caché de un área específica
CorreoAdministrativoService::limpiarCache('ING');

// Limpiar caché de todas las áreas
CorreoAdministrativoService::limpiarTodoElCache();
```

---

## 🎨 Interfaz de Usuario

### **Vista Index** (asistente_academico_index.blade.php)
- ✅ Campo "Nombre Remitente" en formulario modal
- ✅ Texto de ayuda explicativo
- ✅ Validación en frontend

### **Vista Edit** (asistente_academico_edit.blade.php)
- ✅ Campo "Nombre Remitente" editable
- ✅ Muestra valor actual si existe

### **Tabla Livewire** (asistentes-academicos-table.blade.php)
- ✅ Columna "Nombre Remitente" visible
- ✅ Muestra nombre del asistente si no hay nombre_remitente
- ✅ Ordenamiento por columna

---

## ✨ Ventajas del Nuevo Sistema

1. **Descentralizado**: Cada escuela gestiona su propio correo
2. **Flexible**: Nombre de remitente personalizable por escuela
3. **Mantenible**: Todo en un solo lugar (Asistentes Académicos)
4. **Cacheable**: Rendimiento optimizado con caché
5. **Fallback inteligente**: Si no hay asistente, usa correo del sistema

---

## 🚀 Próximos Pasos

### Para probar completamente:

1. **Crear asistentes** para todas las escuelas
2. **Configurar nombres de remitente** formales
3. **Probar envío de correos** desde reservas/notificaciones
4. **Verificar logs** de correos enviados

### Comando útil para verificar:
```bash
# Ver rutas del mantenedor
php artisan route:list --name=asistentes-academicos

# Ver configuración de mail
php artisan tinker
>>> config('mail.from')
```

---

## 📝 Notas Técnicas

- **Controlador**: `AsistenteAcademicoController.php`
- **Servicio**: `CorreoAdministrativoService.php`
- **Modelo**: `AsistenteAcademico.php`
- **Mailable**: `CorreoPersonalizado.php`
- **Migración**: `2025_11_07_000001_add_nombre_remitente_to_asistentes_academicos.php`

---

## ⚠️ Importante

- El campo `nombre_remitente` es **opcional**
- Si está vacío, se usa el campo `nombre` del asistente
- El sistema tiene **fallback** al correo por defecto si no hay asistente
- La limpieza de caché es **automática** al modificar asistentes

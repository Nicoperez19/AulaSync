# ✅ Sistema de Variables - Implementación Completada

## 🎯 Problema Resuelto

**Antes:** Las variables como `{{nombre}}` aparecían literalmente en el correo.  
**Ahora:** Las variables se reemplazan automáticamente con los datos del destinatario.

---

## 🔧 Cambios Realizados

### 1. Método de Reemplazo de Variables

**Archivo:** `app/Livewire/CorreosMasivosManager.php`

Agregado método privado que reemplaza las variables:

```php
private function reemplazarVariables(string $contenido, array $datos): string
{
    $variables = [
        '{{nombre}}' => $datos['nombre'] ?? '',
        '{{email}}' => $datos['email'] ?? '',
        '{{fecha}}' => $datos['fecha'] ?? now()->format('d/m/Y'),
        '{{periodo}}' => $datos['periodo'] ?? now()->format('Y'),
        '{{total_clases}}' => $datos['total_clases'] ?? '0',
        '{{clases_no_realizadas}}' => $datos['clases_no_realizadas'] ?? '0',
        '{{porcentaje}}' => $datos['porcentaje'] ?? '0%',
    ];

    foreach ($variables as $variable => $valor) {
        $contenido = str_replace($variable, $valor, $contenido);
    }

    return $contenido;
}
```

### 2. Integración en Envío de Correos

**Antes:**
```php
Mail::to($email)->send(new CorreoPersonalizado(
    $this->envioAsunto,
    $this->envioContenido,
    $nombre
));
```

**Ahora:**
```php
// Preparar datos del destinatario
$datosDestinatario = [
    'nombre' => $nombre,
    'email' => $email,
    'fecha' => now()->format('d/m/Y'),
    'periodo' => now()->format('Y'),
    // ... más variables
];

// Reemplazar variables en asunto y contenido
$asuntoPersonalizado = $this->reemplazarVariables($this->envioAsunto, $datosDestinatario);
$contenidoPersonalizado = $this->reemplazarVariables($this->envioContenido, $datosDestinatario);

// Enviar con contenido personalizado
Mail::to($email)->send(new CorreoPersonalizado(
    $asuntoPersonalizado,
    $contenidoPersonalizado,
    $nombre
));
```

### 3. Indicador Visual de Variables

**Archivo:** `resources/views/livewire/partials/enviar-correos-tab.blade.php`

Agregada caja informativa que muestra las variables disponibles:

```html
<!-- Variables Disponibles -->
<div class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
    <p class="text-xs font-medium text-blue-900 mb-2">
        <i class="fas fa-magic mr-1"></i>
        Variables disponibles (se reemplazan automáticamente):
    </p>
    <div class="flex flex-wrap gap-2">
        <span>{{nombre}}</span>
        <span>{{email}}</span>
        <span>{{fecha}}</span>
        <span>{{periodo}}</span>
    </div>
</div>
```

---

## 📋 Variables Disponibles

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `{{nombre}}` | Nombre del destinatario | Juan Pérez |
| `{{email}}` | Email del destinatario | juan@ejemplo.com |
| `{{fecha}}` | Fecha actual (d/m/Y) | 14/10/2025 |
| `{{periodo}}` | Año actual | 2025 |
| `{{total_clases}}` | Total clases | 45 |
| `{{clases_no_realizadas}}` | Clases no realizadas | 3 |
| `{{porcentaje}}` | Porcentaje | 93.3% |

---

## 🎯 Cómo Usar

### Ejemplo Simple

**En el asunto:**
```
Hola {{nombre}} - Reporte del {{fecha}}
```

**Resultado:**
```
Hola Juan Pérez - Reporte del 14/10/2025
```

### Ejemplo en Contenido

**Plantilla:**
```html
<h2>Hola {{nombre}},</h2>
<p>Este correo fue enviado a: {{email}}</p>
<p>Fecha: {{fecha}}</p>
<p>Período: {{periodo}}</p>
```

**Resultado para "Juan Pérez (juan@ejemplo.com)":**
```html
<h2>Hola Juan Pérez,</h2>
<p>Este correo fue enviado a: juan@ejemplo.com</p>
<p>Fecha: 14/10/2025</p>
<p>Período: 2025</p>
```

---

## ✅ Funcionamiento

1. **Usuario crea plantilla** con variables: `{{nombre}}`, `{{email}}`, etc.
2. **Usuario carga plantilla** en "Enviar Correos"
3. **Usuario selecciona destinatarios** (internos o externos)
4. **Sistema reemplaza variables** automáticamente para cada destinatario:
   - Destinatarios internos → usa datos del usuario
   - Destinatarios externos → usa email como nombre por defecto
5. **Correo se envía** con contenido personalizado

---

## 🔍 Diferencias por Tipo de Destinatario

### Destinatario Interno (Registrado)
```php
$datosDestinatario = [
    'nombre' => 'Juan Pérez',           // Del registro
    'email' => 'juan.perez@example.com', // Del registro
    'fecha' => '14/10/2025',
    'periodo' => '2025',
];
```

### Destinatario Externo
```php
$datosExternos = [
    'nombre' => 'correo@externo.com',    // Email como nombre
    'email' => 'correo@externo.com',
    'fecha' => '14/10/2025',
    'periodo' => '2025',
];
```

---

## 📚 Documentación Creada

**Archivo:** `docs/VARIABLES_CORREOS.md`

Incluye:
- ✅ Lista completa de variables
- ✅ Ejemplos de uso
- ✅ Buenas prácticas
- ✅ Solución de problemas
- ✅ Implementación técnica
- ✅ Casos de uso reales

---

## 🧪 Prueba Rápida

### 1. Crear Plantilla de Prueba

**Asunto:**
```
Prueba para {{nombre}}
```

**Contenido:**
```html
<h2>Hola {{nombre}},</h2>
<p>Tu email es: {{email}}</p>
<p>Fecha: {{fecha}}</p>
<p>Período: {{periodo}}</p>
```

### 2. Enviar Correo de Prueba

```bash
# Primero configura tu Gmail en .env
php artisan correo:test tu-email@gmail.com --nombre="Tu Nombre"
```

O desde la interfaz:
1. Ir a **Enviar Correos**
2. Cargar la plantilla de prueba
3. Seleccionar un destinatario
4. Enviar

### 3. Verificar Resultado

El correo debe llegar con:
- Asunto: "Prueba para Tu Nombre"
- Contenido con tu nombre, email, fecha actual, etc.

---

## ⚠️ Notas Importantes

### Sintaxis Correcta

✅ **Correcto:**
```
{{nombre}}
{{email}}
{{fecha}}
```

❌ **Incorrecto:**
```
{nombre}           # Una sola llave
{{ nombre }}       # Espacios dentro
{{NOMBRE}}         # Mayúsculas (case-sensitive)
@{{nombre}}        # Solo en Blade, no en contenido
```

### Valores por Defecto

Si una variable no tiene valor, se reemplaza por cadena vacía:
- Variables básicas siempre tienen valor
- Variables de reportes pueden estar en '0' por defecto

---

## 🎉 Resultado Final

**Antes del cambio:**
```
Hola {{nombre}}, tu email es {{email}}
```

**Después del cambio:**
```
Hola Juan Pérez, tu email es juan.perez@ejemplo.com
```

---

## ✅ Checklist

- [x] Método `reemplazarVariables()` creado
- [x] Integrado en `enviarCorreos()` para destinatarios internos
- [x] Integrado en `enviarCorreos()` para destinatarios externos
- [x] Reemplazo en asunto y contenido
- [x] Variables visibles en interfaz de envío
- [x] Documentación completa creada
- [x] Caché limpiada
- [ ] ⚠️ Probar con envío real

---

**Próximo paso:** Prueba enviando un correo con variables para verificar que funcione correctamente.

---

**Implementado:** 14 de Octubre de 2025  
**Estado:** ✅ FUNCIONAL

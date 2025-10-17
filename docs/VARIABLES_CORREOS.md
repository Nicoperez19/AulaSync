# 🔤 Sistema de Variables en Correos

## 📋 Variables Disponibles

El sistema de correos masivos soporta variables dinámicas que se reemplazan automáticamente por los datos del destinatario al enviar el correo.

### Variables Básicas

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `{{nombre}}` | Nombre completo del destinatario | Juan Pérez |
| `{{email}}` | Email del destinatario | juan.perez@ejemplo.com |
| `{{fecha}}` | Fecha actual | 14/10/2025 |
| `{{periodo}}` | Año/período actual | 2025 |

### Variables de Reportes (Futuro)

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `{{total_clases}}` | Total de clases | 45 |
| `{{clases_no_realizadas}}` | Clases no realizadas | 3 |
| `{{porcentaje}}` | Porcentaje de cumplimiento | 93.3% |

---

## 🎯 Cómo Usar las Variables

### En Plantillas

Cuando creas o editas una plantilla, simplemente incluye las variables en el contenido:

```html
<h2>Hola {{nombre}},</h2>

<p>Este es un correo enviado a tu dirección: <strong>{{email}}</strong></p>

<p>Fecha del reporte: {{fecha}}</p>

<p>Período académico: {{periodo}}</p>
```

### En Envío de Correos

1. Ve a **Correos Masivos > Enviar Correos**
2. Carga una plantilla o escribe tu contenido
3. Usa las variables disponibles (se muestran en la caja azul)
4. Al enviar, las variables se reemplazan automáticamente

---

## ✨ Ejemplo Completo

### Plantilla con Variables

**Asunto:**
```
Reporte de Actividades - {{periodo}}
```

**Contenido:**
```html
<h2>Hola {{nombre}},</h2>

<p>Te enviamos el reporte correspondiente al período <strong>{{periodo}}</strong>.</p>

<h3>Datos del reporte generado el {{fecha}}:</h3>

<ul>
    <li><strong>Destinatario:</strong> {{email}}</li>
    <li><strong>Total de clases:</strong> {{total_clases}}</li>
    <li><strong>Clases no realizadas:</strong> {{clases_no_realizadas}}</li>
    <li><strong>Porcentaje de cumplimiento:</strong> {{porcentaje}}</li>
</ul>

<p>Si tienes alguna duda, no dudes en contactarnos.</p>

<p>Saludos cordiales,<br>
<strong>Equipo AulaSync</strong></p>
```

### Resultado para "Juan Pérez"

**Asunto:**
```
Reporte de Actividades - 2025
```

**Contenido:**
```html
<h2>Hola Juan Pérez,</h2>

<p>Te enviamos el reporte correspondiente al período <strong>2025</strong>.</p>

<h3>Datos del reporte generado el 14/10/2025:</h3>

<ul>
    <li><strong>Destinatario:</strong> juan.perez@ejemplo.com</li>
    <li><strong>Total de clases:</strong> 45</li>
    <li><strong>Clases no realizadas:</strong> 3</li>
    <li><strong>Porcentaje de cumplimiento:</strong> 93.3%</li>
</ul>

<p>Si tienes alguna duda, no dudes en contactarnos.</p>

<p>Saludos cordiales,<br>
<strong>Equipo AulaSync</strong></p>
```

---

## 🔧 Implementación Técnica

### Método de Reemplazo

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

### Reemplazo en Envío

El reemplazo se realiza **justo antes de enviar** el correo a cada destinatario:

1. **Destinatarios Internos:** Usa datos del usuario registrado
2. **Destinatarios Externos:** Usa el email como nombre por defecto

```php
// Preparar datos
$datosDestinatario = [
    'nombre' => $nombre,
    'email' => $email,
    'fecha' => now()->format('d/m/Y'),
    'periodo' => now()->format('Y'),
    // ... más datos
];

// Reemplazar variables
$asuntoPersonalizado = $this->reemplazarVariables($this->envioAsunto, $datosDestinatario);
$contenidoPersonalizado = $this->reemplazarVariables($this->envioContenido, $datosDestinatario);

// Enviar correo
Mail::to($email)->send(new CorreoPersonalizado(
    $asuntoPersonalizado,
    $contenidoPersonalizado,
    $nombre
));
```

---

## 📝 Buenas Prácticas

### ✅ Recomendado

```html
<!-- Usar variables con contexto claro -->
<p>Hola {{nombre}}, tu correo registrado es {{email}}.</p>

<!-- Combinar variables con HTML -->
<p>Fecha del reporte: <strong>{{fecha}}</strong></p>

<!-- Usar en asunto también -->
Asunto: Reporte de {{nombre}} - {{periodo}}
```

### ❌ Evitar

```html
<!-- Variables sin contexto -->
{{nombre}} {{email}} {{fecha}}

<!-- Sintaxis incorrecta -->
{nombre}        <!-- Falta una llave -->
{ {nombre} }    <!-- Espacios extra -->
{{NOMBRE}}      <!-- Mayúsculas (sensible a mayúsculas) -->
```

---

## 🔮 Variables Futuras

En futuras versiones se planea agregar:

- `{{run}}` - RUN del usuario
- `{{cargo}}` - Cargo del destinatario
- `{{institucion}}` - Nombre de la institución
- `{{url_sistema}}` - URL del sistema
- `{{asignatura}}` - Nombre de asignatura
- `{{seccion}}` - Sección
- Variables personalizadas por usuario

---

## 🐛 Solución de Problemas

### Las variables no se reemplazan

**Problema:** El correo muestra `{{nombre}}` en lugar del nombre real.

**Solución:**
1. Verifica que usas dobles llaves: `{{nombre}}`
2. Verifica que no hay espacios: `{{ nombre }}` ❌
3. Limpia la caché: `php artisan optimize:clear`
4. Verifica que el destinatario tenga datos completos

### Variables con valores vacíos

**Problema:** Una variable se reemplaza por vacío.

**Solución:**
- Para destinatarios externos, algunas variables pueden estar vacías por defecto
- Usa valores por defecto en la plantilla:
  ```html
  <p>Hola {{nombre}} o estimado/a,</p>
  ```

### Sintaxis de variables en Blade

**Problema:** Al editar plantillas en Blade, las variables se interpretan.

**Solución:**
- En código Blade usa `@{{variable}}` (con @)
- En el contenido de la plantilla usa `{{variable}}` (sin @)

---

## 📊 Ejemplo Real de Uso

### Caso: Envío de Reporte Semanal

**Plantilla: "Reporte Semanal de Clases"**

```html
<h2>Hola {{nombre}},</h2>

<p>Te compartimos el resumen de la semana terminada el {{fecha}}:</p>

<div style="background: #f3f4f6; padding: 20px; border-radius: 8px; margin: 20px 0;">
    <h3>Estadísticas</h3>
    <table style="width: 100%;">
        <tr>
            <td><strong>Total de clases programadas:</strong></td>
            <td>{{total_clases}}</td>
        </tr>
        <tr>
            <td><strong>Clases no realizadas:</strong></td>
            <td>{{clases_no_realizadas}}</td>
        </tr>
        <tr>
            <td><strong>Porcentaje de cumplimiento:</strong></td>
            <td>{{porcentaje}}</td>
        </tr>
    </table>
</div>

<p>Para más detalles, ingresa al sistema.</p>

<p>Contacto: {{email}}</p>
```

**Resultado para cada profesor:**
- Nombre personalizado
- Estadísticas específicas de sus clases
- Email de contacto

---

## ✅ Checklist de Uso

- [ ] Usar sintaxis correcta: `{{variable}}`
- [ ] No usar espacios dentro de las llaves
- [ ] Sensible a mayúsculas/minúsculas
- [ ] Probar con destinatario de prueba primero
- [ ] Verificar que las variables se reemplazan antes de envío masivo
- [ ] Usar valores por defecto cuando sea necesario
- [ ] Documentar variables personalizadas si las agregas

---

**Última actualización:** 14 de Octubre de 2025  
**Versión:** 1.0

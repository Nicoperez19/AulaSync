# Guía de Uso: Plantillas de Correos Masivos

## Descripción General

El sistema de plantillas de correos masivos permite crear y gestionar plantillas HTML personalizadas para enviar correos electrónicos automáticos a destinatarios autorizados.

## Características Principales

### 1. Variables Dinámicas

Las plantillas soportan variables que se reemplazan automáticamente al enviar los correos:

- `{{nombre}}` - Nombre completo del destinatario
- `{{email}}` - Correo electrónico del destinatario
- `{{fecha}}` - Fecha actual del envío
- `{{periodo}}` - Período académico (ej: "Semana 15-21 Enero 2025")
- `{{total_clases}}` - Número total de clases programadas
- `{{clases_no_realizadas}}` - Cantidad de clases sin realizar
- `{{porcentaje}}` - Porcentaje de cumplimiento

### 2. Crear una Nueva Plantilla

1. Navega a **Administración → Correos Masivos → Pestaña Plantillas**
2. Haz clic en el botón **"Nueva Plantilla"**
3. Completa los campos:
   - **Nombre**: Identificador interno de la plantilla
   - **Asunto**: Asunto del correo (puede incluir variables)
   - **Tipo de Correo**: Asocia la plantilla a un tipo específico (opcional)
   - **Contenido HTML**: Código HTML del correo
   - **Versión en Texto Plano**: Alternativa sin formato (opcional)
   - **Activo**: Marca si la plantilla está disponible para uso

4. Usa los botones de variables disponibles para insertarlas en el contenido HTML
5. Haz clic en **"Crear Plantilla"**

### 3. Editar una Plantilla Existente

1. En la tabla de plantillas, haz clic en el icono de **editar (lápiz)** 
2. Modifica los campos necesarios
3. Haz clic en **"Actualizar Plantilla"**

### 4. Eliminar una Plantilla

1. En la tabla de plantillas, haz clic en el icono de **eliminar (papelera)**
2. Confirma la eliminación en el diálogo
3. La plantilla será eliminada permanentemente

### 5. Buscar Plantillas

Usa el buscador en la parte superior para filtrar plantillas por:
- Nombre de la plantilla
- Asunto del correo

## Plantillas de Ejemplo

El sistema incluye 3 plantillas de ejemplo:

### 1. Informe Semanal - Diseño Profesional
- **Uso**: Envío de informes semanales de clases
- **Estilo**: Diseño moderno con gradientes morados
- **Características**: Tabla de estadísticas, footer corporativo

### 2. Alerta - Clases No Realizadas
- **Uso**: Notificaciones urgentes de clases pendientes
- **Estilo**: Diseño de alerta con colores rojos
- **Características**: Énfasis en acción requerida

### 3. Plantilla Básica - Sin Diseño
- **Uso**: Notificaciones simples
- **Estilo**: Diseño minimalista
- **Características**: HTML básico sin estilos complejos

## Mejores Prácticas para HTML

### Estructura Recomendada

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Título del Correo</title>
    <style>
        /* Estilos CSS inline o en <style> */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
    </style>
</head>
<body>
    <div style="max-width: 600px; margin: 0 auto;">
        <!-- Contenido del correo -->
    </div>
</body>
</html>
```

### Consejos de Diseño

1. **Ancho máximo**: Mantén el contenido en 600px de ancho
2. **Estilos inline**: Algunos clientes de correo requieren estilos inline
3. **Fuentes seguras**: Usa fuentes web-safe (Arial, Helvetica, Georgia)
4. **Imágenes**: Incluye atributos `alt` en todas las imágenes
5. **Colores**: Usa códigos hexadecimales (#667eea) en lugar de nombres
6. **Responsive**: Considera dispositivos móviles con `max-width: 100%`

### Ejemplo de Variable en HTML

```html
<p>Estimado/a <strong>{{nombre}}</strong>,</p>
<p>Su porcentaje de cumplimiento es: <strong>{{porcentaje}}%</strong></p>
```

### Ejemplo de Variable en Asunto

```
Informe Semanal - {{periodo}} - {{porcentaje}}% cumplimiento
```

## Asociación con Tipos de Correos

Las plantillas pueden asociarse a tipos de correos específicos:

1. **Informe Semanal** → `informe_semanal`
2. **Informe Mensual** → `informe_mensual`
3. **Alerta Clase No Realizada** → `alerta_no_realizada`
4. **Notificaciones** → `notificacion_general`

Esta asociación facilita la selección automática de plantillas al enviar correos de ese tipo.

## Versión en Texto Plano

Siempre es recomendable incluir una versión en texto plano:

- Garantiza compatibilidad con clientes de correo antiguos
- Mejora la deliverability (evita filtros de spam)
- Proporciona alternativa para usuarios con preferencias de texto

Ejemplo:

```
Estimado/a {{nombre}},

Le enviamos el resumen semanal de sus clases:

- Período: {{periodo}}
- Total de clases: {{total_clases}}
- Clases no realizadas: {{clases_no_realizadas}}
- Porcentaje: {{porcentaje}}%

Saludos,
Equipo AulaSync
```

## Solución de Problemas

### La plantilla no se muestra correctamente

- Verifica que el HTML esté bien formado (etiquetas abiertas y cerradas)
- Revisa que no haya caracteres especiales sin escapar
- Prueba con estilos inline en lugar de clases CSS

### Las variables no se reemplazan

- Asegúrate de usar la sintaxis correcta: `{{variable}}`
- Verifica que el nombre de la variable esté en la lista de variables disponibles
- No uses espacios dentro de las llaves: `{{ variable }}` ❌

### El correo llega a spam

- Incluye versión en texto plano
- Evita palabras "spam" en el asunto ("GRATIS", "URGENTE", etc.)
- Mantén una proporción equilibrada de texto vs imágenes
- No uses demasiados enlaces

## Permisos Requeridos

Solo usuarios con rol **Administrador** pueden:
- Crear plantillas
- Editar plantillas
- Eliminar plantillas
- Ver la lista de plantillas

## Próximas Funcionalidades

En futuras versiones se agregarán:
- Editor WYSIWYG (visual) para HTML
- Vista previa en tiempo real
- Prueba de envío a correo de prueba
- Importar/exportar plantillas
- Plantillas predefinidas adicionales
- Estadísticas de apertura/clics

---

**Última actualización**: Enero 2025  
**Versión del sistema**: AulaSync 2.0

# Guía: Panel de Envío de Correos Masivos

## 📬 Resumen

El panel de envío de correos masivos te permite enviar correos electrónicos utilizando plantillas predefinidas a destinatarios seleccionados, ya sean usuarios registrados o correos externos.

---

## 🚀 Cómo Enviar un Correo Masivo

### Paso 1: Acceder al Panel de Envío

1. Ir a **Administración** → **Correos Masivos**
2. Seleccionar la pestaña **"Enviar Correo"** (ícono de avión de papel)

### Paso 2: Seleccionar una Plantilla

1. En el panel izquierdo, selecciona una plantilla del menú desplegable
2. La plantilla cargará automáticamente:
   - El asunto predeterminado
   - El tipo de correo asociado (si tiene)
   - El contenido HTML

**Información mostrada:**
- Nombre de la plantilla
- Tipo de correo asociado

### Paso 3: Personalizar el Asunto

1. Edita el asunto del correo en el campo correspondiente
2. Puedes usar variables dinámicas:
   - `{{nombre}}` - Nombre del destinatario
   - `{{fecha}}` - Fecha actual (formato dd/mm/yyyy)
   - `{{hora}}` - Hora actual (formato HH:mm)

**Ejemplo:**
```
Informe Semanal - {{fecha}} - {{nombre}}
```

Se convertirá en:
```
Informe Semanal - 14/10/2025 - Juan Pérez
```

### Paso 4: Seleccionar Destinatarios

Tienes **tres formas** de seleccionar destinatarios:

#### Opción A: Manualmente

- Haz clic en cada destinatario en la lista del panel derecho
- Los seleccionados mostrarán un check ✓

#### Opción B: Por Tipo de Correo

1. Selecciona un tipo de correo del menú desplegable
2. Haz clic en el botón **"Cargar"**
3. Se seleccionarán automáticamente todos los destinatarios habilitados para ese tipo

#### Opción C: Selección Rápida

- **"Todos"**: Selecciona todos los destinatarios activos
- **"Ninguno"**: Deselecciona todos los destinatarios

### Paso 5: Revisar Vista Previa (Opcional)

En la parte inferior verás una vista previa de la plantilla:
- Contenido HTML tal como se enviará
- Las variables se mostrarán como `{{nombre}}`, `{{fecha}}`, etc.
- Haz clic en **"Ver en pantalla completa"** para una vista ampliada

### Paso 6: Enviar el Correo

1. Verifica que todo esté correcto:
   - ✅ Plantilla seleccionada
   - ✅ Asunto personalizado
   - ✅ Al menos un destinatario seleccionado

2. Haz clic en **"Enviar Correo Masivo"**

3. Espera la confirmación:
   - ✅ **Éxito**: Muestra cuántos correos se enviaron
   - ❌ **Error**: Muestra detalles de fallos

---

## 📋 Tipos de Destinatarios

### Usuarios Registrados 🔵

```
Juan Pérez                    [🔵 Registrado]
juan.perez@institucion.cl • Director
```

- Tienen cuenta en el sistema
- Su información se obtiene del perfil de usuario
- Email y nombre del sistema

### Correos Externos 🟣

```
María González                [🟣 Externo]
maria@external.com • Supervisor MINEDUC
```

- No tienen cuenta en el sistema
- Email y nombre ingresados manualmente
- Ideal para autoridades externas

---

## 🎨 Características del Panel

### Panel de Configuración (Izquierda)

**Sección 1: Selección de Plantilla**
- Lista desplegable de todas las plantillas activas
- Información de la plantilla seleccionada
- Campo de asunto editable

**Sección 2: Tipo de Correo (Opcional)**
- Permite cargar destinatarios por tipo de correo
- Útil para envíos recurrentes al mismo grupo
- Botón de carga rápida

### Panel de Destinatarios (Derecha)

**Características:**
- ✅ Contador de destinatarios seleccionados
- ✅ Botones de selección rápida (Todos/Ninguno)
- ✅ Lista scrolleable con checkboxes
- ✅ Identificación visual de tipos (Registrado/Externo)
- ✅ Información de email y rol de cada destinatario

**Sección de Envío:**
- Botón grande de envío con confirmación visual
- Loading spinner durante el envío
- Advertencias antes de enviar

**Vista Previa:**
- Muestra el contenido HTML de la plantilla
- Opción de vista en pantalla completa
- Nota sobre reemplazo de variables

---

## 🔄 Variables Disponibles

Las variables se reemplazan automáticamente al enviar el correo:

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `{{nombre}}` | Nombre del destinatario | Juan Pérez |
| `{{fecha}}` | Fecha actual | 14/10/2025 |
| `{{hora}}` | Hora actual | 14:30 |

### Cómo Usar Variables

**En el asunto:**
```
Reporte de {{fecha}} para {{nombre}}
```

**En la plantilla (HTML):**
```html
<p>Estimado/a <strong>{{nombre}}</strong>,</p>
<p>Le enviamos el informe del día {{fecha}} a las {{hora}}.</p>
```

---

## ✅ Validaciones

El sistema valida antes de enviar:

### Plantilla
- ❌ Debe seleccionar una plantilla
- ✅ La plantilla debe estar activa

### Asunto
- ❌ El asunto es obligatorio
- ✅ Máximo 255 caracteres

### Destinatarios
- ❌ Debe seleccionar al menos 1 destinatario
- ✅ Los destinatarios deben estar activos
- ✅ Deben tener un email válido

---

## 📊 Resultados del Envío

### Envío Exitoso

```
✅ Correos enviados: 15
```

- Muestra el número total de correos enviados
- Se registra en los logs del sistema
- Los destinatarios reciben el correo inmediatamente

### Envío con Errores

```
⚠️ Correos enviados: 12, Errores: 3
Se encontraron 3 errores al enviar los correos. Revisa los logs.
```

- Muestra enviados y errores
- Los correos exitosos sí se enviaron
- Los errores se registran en `storage/logs/laravel.log`

### Errores Comunes

**Email inválido:**
```
Error: Invalid email address
```
- Verifica que el email del destinatario sea válido
- Edita el destinatario y corrige el email

**Servidor de correo no disponible:**
```
Error: Connection timeout
```
- Verifica la configuración de correo en `.env`
- Contacta al administrador del sistema

**Destinatario sin email:**
```
Error: No email address provided
```
- El destinatario no tiene email configurado
- Agrega el email al destinatario

---

## 💡 Consejos y Mejores Prácticas

### Antes de Enviar

1. **Prueba primero**
   - Envía a ti mismo antes del envío masivo
   - Verifica que las variables se reemplacen correctamente
   - Revisa el formato del correo

2. **Revisa la lista de destinatarios**
   - Asegúrate de que todos los destinatarios son correctos
   - Verifica que no haya duplicados
   - Confirma que todos estén activos

3. **Personaliza el asunto**
   - Usa un asunto descriptivo
   - Incluye la fecha si es un envío recurrente
   - Evita palabras que activen filtros de spam

### Durante el Envío

- ⏳ **No cierres la ventana** mientras se envían los correos
- ⏳ **Espera la confirmación** antes de hacer otra acción
- 📊 **Observa el contador** de envío en el botón

### Después de Enviar

1. **Verifica los resultados**
   - Revisa el mensaje de confirmación
   - Si hay errores, consulta los logs

2. **Registra el envío**
   - Anota cuántos correos se enviaron
   - Documenta cualquier error para seguimiento

3. **Revisa los logs** (si hay errores)
   ```bash
   tail -f storage/logs/laravel.log
   ```

---

## 🔍 Casos de Uso Comunes

### Caso 1: Informe Semanal a Directores

**Objetivo:** Enviar informe semanal a todos los directores

**Pasos:**
1. Seleccionar plantilla: "Informe Semanal - Diseño Profesional"
2. Personalizar asunto: "Informe Semanal - {{fecha}}"
3. Cargar destinatarios por tipo: "Informe Semanal"
4. Revisar lista (solo directores)
5. Enviar

### Caso 2: Alerta Específica a Grupo Selecto

**Objetivo:** Enviar alerta a ciertos destinatarios específicos

**Pasos:**
1. Seleccionar plantilla: "Alerta de Clase No Realizada"
2. Personalizar asunto según el caso
3. Seleccionar manualmente los destinatarios necesarios
4. Revisar vista previa
5. Enviar

### Caso 3: Comunicado a Todos los Destinatarios

**Objetivo:** Enviar comunicado general

**Pasos:**
1. Seleccionar plantilla apropiada
2. Personalizar asunto del comunicado
3. Hacer clic en "Todos" para seleccionar todos los destinatarios
4. Revisar el contador de destinatarios
5. Enviar

---

## 🛠️ Solución de Problemas

### El botón "Enviar" está deshabilitado

**Causas:**
- ❌ No has seleccionado una plantilla
- ❌ El asunto está vacío
- ❌ No hay destinatarios seleccionados

**Solución:**
- Completa todos los campos requeridos (marcados con *)
- Selecciona al menos un destinatario

### Los destinatarios no se cargan

**Causas:**
- No hay destinatarios activos en el sistema
- El tipo de correo no tiene destinatarios asignados

**Solución:**
1. Ir a la pestaña "Destinatarios"
2. Verificar que hay destinatarios activos
3. Ir a "Tipos de Correos" → "Asignar Destinatarios"
4. Asignar destinatarios al tipo de correo

### Las variables no se reemplazan

**Causas:**
- Formato incorrecto de las variables
- La plantilla no contiene las variables

**Solución:**
- Usa el formato exacto: `{{variable}}`
- Sin espacios: `{{nombre}}` ✅ `{{ nombre }}` ❌
- Verifica que la plantilla contenga las variables que usas

### Algunos correos no se envían

**Causas:**
- Emails inválidos de algunos destinatarios
- Problemas de conexión intermitentes
- Límites del servidor de correo

**Solución:**
1. Revisa los logs para identificar emails con problemas
2. Corrige los emails de los destinatarios afectados
3. Reenvía solo a los que fallaron

---

## 📞 Soporte

Si tienes problemas:

1. **Revisa esta guía** - Busca tu problema en "Solución de Problemas"
2. **Consulta los logs** - `storage/logs/laravel.log`
3. **Contacta soporte** - Proporciona:
   - Descripción del problema
   - Plantilla que intentaste usar
   - Número de destinatarios
   - Mensaje de error (si hay)

---

## 📝 Notas Adicionales

### Límites de Envío

- No hay límite en el número de destinatarios por envío
- Los envíos se procesan secuencialmente
- Para listas muy grandes (>100), considera envíos por lotes

### Seguridad

- 🔐 Solo administradores pueden enviar correos masivos
- 🔐 Todos los envíos se registran en logs
- 🔐 Los destinatarios deshabilitados no reciben correos

### Privacidad

- Los correos se envían con BCC (copia oculta)
- Los destinatarios no ven la lista completa
- Cada correo es personalizado con el nombre del destinatario

---

**Última actualización:** Octubre 2025  
**Versión:** 1.0

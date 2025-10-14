# Guía: Envío de Correos con Destinatarios Externos

## Descripción

El sistema de correos masivos ahora permite:
- **Agregar destinatarios externos** (personas no registradas en el sistema)
- **Enviar correos** usando plantillas predefinidas
- **Guardar emails externos** como destinatarios permanentes

## Características Nuevas

### 1. Destinatarios Externos

Los destinatarios ahora pueden ser de dos tipos:
- **Internos**: Usuarios registrados en el sistema (con RUN)
- **Externos**: Personas con solo email y nombre (no requieren cuenta)

#### Campos para destinatarios externos:
- `es_externo` (boolean): Indica si es un destinatario externo
- `email_externo` (string): Email del destinatario externo
- `nombre_externo` (string): Nombre del destinatario externo

### 2. Pestaña "Enviar Correos"

Nueva pestaña para enviar correos masivos desde las plantillas.

#### Funcionalidades:
1. **Seleccionar plantilla**: Elige una plantilla predefinida
2. **Cargar plantilla**: Carga automáticamente el asunto y contenido
3. **Editar contenido**: Modifica el asunto y contenido antes de enviar
4. **Seleccionar destinatarios**: Marca los destinatarios registrados
5. **Agregar emails externos**: Agrega emails adicionales separados por comas
6. **Guardar emails externos**: Convierte los emails externos en destinatarios permanentes

## Uso

### Agregar Destinatario Externo

1. Ve a la pestaña **"Destinatarios"**
2. Marca la casilla **"Destinatario Externo (no registrado)"**
3. Completa:
   - Email (requerido)
   - Nombre (requerido)
   - Rol (opcional)
   - Cargo (opcional)
4. Haz clic en **"Crear"**

### Enviar Correo Masivo

1. Ve a la pestaña **"Enviar Correos"**
2. Selecciona una **plantilla** del dropdown
3. Haz clic en **"Cargar"** para cargar el contenido
4. Edita el **asunto** y **contenido** si es necesario
5. Selecciona los **destinatarios registrados** que desees
6. (Opcional) Agrega **emails externos** separados por comas
7. (Opcional) Haz clic en **"Guardar estos emails como destinatarios externos"**
8. Haz clic en **"Enviar Correos"**

## Modelo de Datos

### Tabla: `destinatarios_correos`

```sql
- id
- user_id (nullable) - RUN del usuario (solo para internos)
- es_externo (boolean) - Indica si es externo
- email_externo (nullable) - Email del destinatario externo
- nombre_externo (nullable) - Nombre del destinatario externo
- rol (nullable)
- cargo (nullable)
- activo (boolean)
- created_at
- updated_at
```

## Validaciones

### Destinatarios Internos:
- `user_id` es requerido
- No puede haber duplicados de `user_id`

### Destinatarios Externos:
- `email_externo` es requerido y debe ser un email válido
- `nombre_externo` es requerido
- No puede haber duplicados de `email_externo`

### Envío de Correos:
- Al menos una plantilla debe ser seleccionada
- Asunto y contenido son requeridos
- Debe haber al menos un destinatario (interno o externo)

## API / Métodos del Componente

### CorreosMasivosManager

#### Destinatarios:
- `saveDestinatario()` - Guarda destinatario interno o externo
- `editDestinatario($id)` - Edita un destinatario
- `deleteDestinatario($id)` - Elimina un destinatario

#### Envío:
- `cargarPlantillaParaEnvio($plantillaId)` - Carga una plantilla para envío
- `enviarCorreos()` - Envía los correos a los destinatarios seleccionados
- `guardarEmailsExternos()` - Guarda los emails externos como destinatarios
- `resetEnvioForm()` - Limpia el formulario de envío

## Scopes del Modelo

### DestinatarioCorreo

- `activos()` - Filtra solo destinatarios activos
- `buscar($termino)` - Busca por nombre, email, RUN, rol o cargo
- `porTipoUsuario($tipo)` - Filtra por tipo de usuario (Profesor, Usuario, etc.)

## Atributos Calculados

- `nombre_completo` - Retorna el nombre completo (interno o externo) con rol
- `email` - Retorna el email (interno o externo)
- `info_busqueda` - Retorna información completa para búsqueda

## Notas de Implementación

### Envío de Correos (Pendiente)

El método `enviarCorreos()` actualmente tiene la lógica preparada pero comentada:

```php
// Mail::to($email)->send(new CorreoPersonalizado($this->envioAsunto, $this->envioContenido));
```

Para implementar el envío real:
1. Crear la clase Mailable `CorreoPersonalizado`
2. Configurar el driver de correo en `.env`
3. Descomentar las líneas de envío

### Integración con Sistema de Correos

El envío masivo puede integrarse con:
- **Mailtrap** (desarrollo)
- **Gmail SMTP** (producción)
- **Amazon SES**
- **SendGrid**

## Mejoras Futuras

1. **Vista previa de correo** antes de enviar
2. **Programación de envíos** (fecha/hora específica)
3. **Historial de envíos** (tracking)
4. **Estadísticas de apertura** (requiere servicio externo)
5. **Plantillas con variables dinámicas** mejoradas
6. **Grupos de destinatarios** predefinidos
7. **Importación masiva** de destinatarios externos (CSV/Excel)

## Seguridad

- Solo usuarios con rol **"Administrador"** pueden acceder
- Validación de emails en ambos lados (cliente y servidor)
- Protección CSRF incorporada (Livewire)
- Sanitización de contenido HTML

## Migración Ejecutada

```bash
php artisan migrate
```

Ejecuta: `2025_10_14_130808_add_external_fields_to_destinatarios_correos_table`

# Guía: Destinatarios Externos para Correos Masivos

## 📧 Resumen

El sistema de correos masivos ahora soporta dos tipos de destinatarios:

1. **Usuarios Registrados**: Usuarios existentes en el sistema (requieren tener cuenta)
2. **Correos Externos**: Cualquier correo electrónico sin necesidad de registro en el sistema

Esta funcionalidad permite enviar correos masivos a personas que no tienen cuenta en SIA | Sistema de Informaci�n de Aulas, manteniendo la organización y control de los envíos.

---

## 🎯 Casos de Uso

### ¿Cuándo usar Destinatarios Externos?

- Enviar informes a autoridades externas
- Notificar a stakeholders que no requieren acceso al sistema
- Incluir correos de supervisores o auditores externos
- Agregar correos de respaldo o administrativos

### ¿Cuándo usar Usuarios Registrados?

- Personal interno de la institución
- Usuarios que tienen cuenta activa en el sistema
- Cuando se requiere tracking por usuario específico

---

## 📝 Cómo Agregar Destinatarios Externos

### Paso 1: Acceder a la Administración de Correos Masivos

1. Ir a **Administración** → **Correos Masivos**
2. Seleccionar la pestaña **"Destinatarios"**

### Paso 2: Seleccionar el Tipo de Destinatario

Al crear un nuevo destinatario, verás dos opciones:

- 🔵 **Usuario registrado**: Selecciona de la lista de usuarios del sistema
- 🟣 **Correo externo**: Ingresa manualmente el email y nombre

### Paso 3: Completar el Formulario

#### Para Correo Externo:

**Campos Obligatorios:**
- **Correo electrónico**: Email válido del destinatario
- **Nombre**: Nombre completo de la persona

**Campos Opcionales:**
- **Rol**: Cargo en el contexto de correos (ej: "Director Externo", "Supervisor MINEDUC")
- **Cargo/Descripción**: Información adicional sobre sus responsabilidades
- **Activo**: Marcar para habilitar el destinatario (activo por defecto)

#### Para Usuario Registrado:

**Campos Obligatorios:**
- **Usuario**: Seleccionar del desplegable de usuarios del sistema

**Campos Opcionales:**
- **Rol**: Rol en el contexto de correos masivos
- **Cargo/Descripción**: Información adicional
- **Activo**: Estado del destinatario

### Paso 4: Guardar

Presiona el botón **"Crear"** y el destinatario estará disponible para asignar a tipos de correos.

---

## 🔧 Asignar Destinatarios a Tipos de Correos

### Proceso de Asignación

1. En la pestaña **"Tipos de Correos"**, localiza el tipo de correo deseado
2. Haz clic en el botón **"Asignar Destinatarios"** (ícono de usuarios)
3. En el modal que aparece, verás todos los destinatarios:
   - 🔵 **Etiqueta "Registrado"**: Usuario del sistema
   - 🟣 **Etiqueta "Externo"**: Correo externo
4. Usa el **switch toggle** para habilitar/deshabilitar cada destinatario
5. Los cambios se guardan automáticamente

---

## 🎨 Identificación Visual

### En la Lista de Destinatarios

Los destinatarios se distinguen visualmente:

| Tipo | Ícono | Color | Etiqueta |
|------|-------|-------|----------|
| Usuario Registrado | 👤 | Azul (Indigo) | "Registrado" |
| Correo Externo | ✉️ | Púrpura | "Externo" |

### Información Mostrada

**Usuarios Registrados:**
```
Nombre del Usuario               [🔵 Registrado]
correo@usuario.com • Rol (si tiene)
```

**Correos Externos:**
```
Nombre Ingresado                 [🟣 Externo]
correo@externo.com • Rol (si tiene)
```

---

## 📊 Gestión de Destinatarios

### Editar un Destinatario

1. Haz clic en el ícono de **editar** (lápiz) junto al destinatario
2. Modifica los campos necesarios
3. **Nota**: No puedes cambiar el tipo de destinatario al editar (usuario ↔ externo)
4. Presiona **"Actualizar"** para guardar los cambios

### Eliminar un Destinatario

1. Haz clic en el ícono de **eliminar** (papelera) junto al destinatario
2. Confirma la eliminación en el diálogo
3. Se eliminarán todas las asignaciones asociadas

### Buscar Destinatarios

Usa el buscador para filtrar por:
- Nombre (del usuario o ingresado)
- Correo electrónico
- RUN (solo para usuarios registrados)
- Rol
- Cargo

---

## 🔒 Validaciones y Restricciones

### Al Crear Destinatarios

**Correos Externos:**
- ✅ El email debe ser válido
- ✅ No se puede duplicar el mismo email externo
- ✅ Nombre es obligatorio

**Usuarios Registrados:**
- ✅ El usuario debe existir en el sistema
- ✅ No se puede duplicar el mismo usuario
- ✅ El usuario debe estar activo

### Al Enviar Correos

El sistema automáticamente:
- Envía correos solo a destinatarios habilitados
- Verifica que cada destinatario tenga un email válido
- Registra intentos fallidos en los logs
- No discrimina entre usuarios registrados y externos

---

## 💡 Mejores Prácticas

### Organización

1. **Usa roles descriptivos**: Ayuda a identificar el propósito del destinatario
   - ✅ "Director Externo MINEDUC"
   - ✅ "Supervisor Regional"
   - ❌ "Externo 1"

2. **Completa el campo cargo**: Proporciona contexto adicional
   ```
   Rol: Auditor Externo
   Cargo: Encargado de supervisión de procesos académicos región metropolitana
   ```

3. **Mantén la lista actualizada**: Desactiva destinatarios que ya no requieren los correos en lugar de eliminarlos

### Seguridad

- 🔐 Solo administradores pueden gestionar destinatarios
- 🔐 Los correos externos no tienen acceso al sistema
- 🔐 Se registran todos los envíos en los logs

### Rendimiento

- ⚡ Desactiva destinatarios temporalmente en lugar de eliminarlos si planeas reactivarlos
- ⚡ Usa el buscador para gestión de listas grandes
- ⚡ Agrupa destinatarios similares con roles específicos

---

## 🔍 Solución de Problemas

### El correo no se envía a un destinatario externo

**Verifica:**
1. ✓ El destinatario está **activo**
2. ✓ El destinatario está **asignado y habilitado** en el tipo de correo
3. ✓ El email es **válido**
4. ✓ El tipo de correo está **activo**
5. ✓ Revisa los **logs** del sistema para mensajes de error

### No puedo agregar un correo externo

**Posibles causas:**
- El email ya existe como destinatario externo
- El formato del email es inválido
- No se completó el campo nombre

### Un usuario aparece duplicado

**Solución:**
- Si un usuario está registrado en el sistema, usa la opción "Usuario registrado"
- Si necesitas un email alternativo, usa "Correo externo" con ese email específico

---

## 📖 Ejemplo Completo

### Caso: Envío de Informe Mensual a Autoridades

**Objetivo:** Enviar el informe de clases no realizadas a:
- Directores internos (usuarios del sistema)
- Supervisor MINEDUC (correo externo)
- Auditor regional (correo externo)

**Pasos:**

1. **Crear destinatarios externos:**
   ```
   Tipo: Correo externo
   Email: supervisor@mineduc.cl
   Nombre: Juan Pérez Supervisor
   Rol: Supervisor MINEDUC
   Cargo: Encargado de supervisión establecimientos región metropolitana
   ```
   
   ```
   Tipo: Correo externo
   Email: auditor@regional.cl
   Nombre: María González Auditor
   Rol: Auditor Regional
   Cargo: Auditoría procesos académicos
   ```

2. **Crear destinatarios de usuarios:**
   ```
   Tipo: Usuario registrado
   Usuario: [Seleccionar Director 1]
   Rol: Director
   ```

3. **Asignar al tipo de correo:**
   - Ir a "Tipos de Correos"
   - Seleccionar "Informe Clases No Realizadas"
   - Clic en "Asignar Destinatarios"
   - Habilitar todos los destinatarios creados

4. **Resultado:**
   Al enviar el informe, todos recibirán el correo automáticamente, sin importar si son usuarios del sistema o correos externos.

---

## 🔄 Cambios en la Base de Datos

### Migración Aplicada

La tabla `destinatarios_correos` ahora incluye:

```php
- user_id (nullable): ID del usuario registrado
- email (nullable): Email directo para destinatarios externos
- nombre (nullable): Nombre para destinatarios externos
- rol: Rol en contexto de correos
- cargo: Descripción adicional
- activo: Estado del destinatario
```

**Reglas:**
- Si `user_id` está presente → Usuario registrado
- Si `email` y `nombre` están presentes (sin `user_id`) → Correo externo
- No puede haber `user_id` y `email` simultáneamente

---

## 📞 Soporte

Si encuentras problemas o tienes dudas sobre los destinatarios externos:

1. Verifica esta guía primero
2. Consulta los logs del sistema en `storage/logs/laravel.log`
3. Contacta al equipo de desarrollo

---

**Última actualización:** Octubre 2025  
**Versión:** 1.0

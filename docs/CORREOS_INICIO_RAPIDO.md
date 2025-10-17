# 🚀 Inicio Rápido - Correos Masivos con Destinatarios Externos

## ✨ Novedades

1. **Destinatarios Externos**: Ahora puedes agregar personas que no están registradas en el sistema
2. **Enviar Correos**: Nueva pestaña para enviar correos masivos usando plantillas

---

## 📝 Agregar Destinatario Externo

1. Ve a: **Correos Masivos > Destinatarios**
2. Marca: ☑️ **"Destinatario Externo (no registrado)"**
3. Completa:
   - **Email** ✉️
   - **Nombre** 👤
   - Rol (opcional)
   - Cargo (opcional)
4. Click en **"Crear"**

✅ ¡Listo! El destinatario externo está guardado.

---

## 📧 Enviar Correo Masivo

### Paso 1: Selecciona una Plantilla
1. Ve a: **Correos Masivos > Enviar Correos**
2. En **"Seleccionar Plantilla"**, elige una plantilla
3. Click en botón **"Cargar"**

### Paso 2: Revisa/Edita el Contenido
- El **asunto** y **contenido** se cargan automáticamente
- Puedes editarlos antes de enviar

### Paso 3: Selecciona Destinatarios
- Marca los destinatarios que quieres (internos o externos)
- Usa el buscador para filtrar

### Paso 4: (Opcional) Agrega Emails Externos
- En **"Destinatarios Externos"**, ingresa emails separados por comas:
  ```
  juan@ejemplo.com, maria@ejemplo.com, pedro@ejemplo.com
  ```
- Click en **"Guardar estos emails..."** si quieres guardarlos como destinatarios permanentes

### Paso 5: Enviar
- Click en **"Enviar Correos"** 🚀

---

## 💡 Consejos

### Identificar Destinatarios Externos
- Los destinatarios externos tienen un icono 🔗 y badge morado "Externo"
- Los internos tienen iniciales en círculo azul

### Búsqueda Inteligente
- Busca por: nombre, email, RUN, rol, cargo
- Funciona en destinatarios internos y externos

### Limpiar Selección
- En la pestaña "Enviar Correos", click en **"Limpiar selección"** para deseleccionar todos

---

## ⚡ Atajos

| Acción | Ubicación |
|--------|-----------|
| Agregar destinatario externo | Destinatarios > Marcar checkbox |
| Enviar correo | Enviar Correos > Seleccionar plantilla |
| Guardar emails externos | Enviar Correos > "Guardar estos emails..." |
| Editar destinatario | Destinatarios > Click en ✏️ |
| Eliminar destinatario | Destinatarios > Click en 🗑️ |

---

## 📚 Documentación Completa

Para más detalles, consulta:
- `docs/CORREOS_EXTERNOS_Y_ENVIO.md` - Guía completa
- `docs/RESUMEN_CAMBIOS_CORREOS.md` - Changelog técnico

---

## 🔧 Nota Técnica

⚠️ El envío real de correos está preparado pero requiere configuración:
- Configurar `MAIL_*` en `.env`
- Crear Mailable `CorreoPersonalizado`

Actualmente la función registra los correos pero no los envía.

---

**¿Preguntas?** Consulta la documentación completa o contacta al administrador del sistema.

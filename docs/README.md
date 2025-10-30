# Índice de Documentación - AulaSync API

Este documento sirve como índice central para toda la documentación de la API de AulaSync.

---

## 📚 Documentación de APIs

### API de Espacios y Tipos de Espacios

Agregada el: **27 de octubre de 2025**

- **[API_ESPACIOS_Y_TIPOS.md](API_ESPACIOS_Y_TIPOS.md)** - Documentación completa del endpoint de espacios
  - GET `/api/espacios` - Listar todos los espacios
  - GET `/api/tipos-espacios` - Listar tipos de espacios
  - GET `/api/espacios/resumen` - Resumen estadístico
  
- **[PRUEBAS_API_ESPACIOS.md](PRUEBAS_API_ESPACIOS.md)** - Guía de pruebas y ejemplos
  - Ejemplos con cURL, HTTPie, Postman
  - JavaScript, Python
  - Resultados esperados

- **[EJEMPLOS_RESPUESTAS_API_ESPACIOS.md](EJEMPLOS_RESPUESTAS_API_ESPACIOS.md)** - Ejemplos de respuestas
  - Respuestas completas
  - Casos de filtrado
  - Manejo de errores

- **[RESUMEN_CAMBIOS_API_ESPACIOS.md](RESUMEN_CAMBIOS_API_ESPACIOS.md)** - Resumen de implementación
  - Archivos creados/modificados
  - Verificación de rutas
  - Próximos pasos

### API de Programación Semanal y Asistencia

#### Programación Semanal

Documentado en: **[API_PROGRAMACION_SEMANAL_ASISTENCIA.md](API_PROGRAMACION_SEMANAL_ASISTENCIA.md)**

- GET `/api/programacion-semanal/{id_espacio}` - Consultar programación

#### Reserva Activa por Espacio

Agregado el: **29 de octubre de 2025**

- **[API_RESERVA_ACTIVA_ESPACIO.md](API_RESERVA_ACTIVA_ESPACIO.md)** 🔥 NUEVO
  - GET `/api/reservas/activa/{id_espacio}` - Obtener reserva activa de un espacio
  - Consulta en tiempo real
  - **Diferencia entre ocupación con/sin reserva formal** ⚠️
  - Información completa (profesor, asignatura, asistencia)
  - Ideal para pantallas de estado y apps nativas
  - Lógica dual: Reserva formal O estado manual "Ocupado"

- **[LOGICA_OCUPACION_ESPACIOS.md](LOGICA_OCUPACION_ESPACIOS.md)** 📚 GUÍA CONCEPTUAL
  - Explicación detallada de cómo funciona la ocupación de espacios
  - Diferencia entre estado "Ocupado" con y sin reserva
  - Casos de uso reales con ejemplos
  - Diagramas de flujo y código de integración
  - **LECTURA RECOMENDADA** para entender el modelo de negocio

#### Registro de Asistencia (Actualizado)

Actualizado el: **29 de octubre de 2025**

- **[API_REGISTRO_ASISTENCIA.md](API_REGISTRO_ASISTENCIA.md)** ⭐ PRINCIPAL
  - POST `/api/asistencia` - Registrar asistencia y finalizar clase
  - Documentación completa y detallada
  - Ejemplos en múltiples lenguajes
  - Integración con apps nativas
  - Guía de migración

- **[GUIA_RAPIDA_ASISTENCIA.md](GUIA_RAPIDA_ASISTENCIA.md)** 🚀 INICIO RÁPIDO
  - Guía de 5 minutos
  - Casos de uso comunes
  - Código listo para copiar/pegar
  - Tips y troubleshooting

- **[RESUMEN_CAMBIOS_ASISTENCIA.md](RESUMEN_CAMBIOS_ASISTENCIA.md)** 📋 CHANGELOG
  - Lista completa de cambios
  - Migración desde versión anterior
  - Checklist de implementación
  - Próximos pasos sugeridos

- **Ejemplos JSON:** `ejemplos/`
  - `asistencia-completa.json` - Ejemplo completo con 5 estudiantes
  - `asistencia-simple.json` - Ejemplo mínimo
  - `asistencia-sin-finalizar.json` - Registro sin finalizar clase

---

## 🗂️ Documentación por Categoría

### Configuración y Setup

- [CONFIGURACION_GMAIL.md](CONFIGURACION_GMAIL.md) - Configuración de Gmail para envío de correos
- [CORREOS_INICIO_RAPIDO.md](CORREOS_INICIO_RAPIDO.md) - Guía rápida de correos
- [ACTIVACION_CORREOS.md](ACTIVACION_CORREOS.md) - Activación del sistema de correos

### Funcionalidades del Sistema

- [SISTEMA_LICENCIAS_RECUPERACION.md](SISTEMA_LICENCIAS_RECUPERACION.md) - Gestión de licencias y recuperación
- [REPORTES_CLASES_NO_REALIZADAS.md](REPORTES_CLASES_NO_REALIZADAS.md) - Sistema de reportes
- [MANTENEDORES_GUIA.md](MANTENEDORES_GUIA.md) - Guía de mantenedores

### Correos y Plantillas

- [CORREOS_MASIVOS_GUIA.md](CORREOS_MASIVOS_GUIA.md) - Envío de correos masivos
- [PLANTILLAS_CORREOS_GUIA.md](PLANTILLAS_CORREOS_GUIA.md) - Gestión de plantillas
- [DESTINATARIOS_EXTERNOS_GUIA.md](DESTINATARIOS_EXTERNOS_GUIA.md) - Gestión de destinatarios externos
- [ENVIO_CORREOS_GUIA.md](ENVIO_CORREOS_GUIA.md) - Guía de envío de correos
- [VARIABLES_CORREOS.md](VARIABLES_CORREOS.md) - Variables disponibles en plantillas
- [VARIABLES_IMPLEMENTACION.md](VARIABLES_IMPLEMENTACION.md) - Implementación de variables
- [CORREOS_EXTERNOS_Y_ENVIO.md](CORREOS_EXTERNOS_Y_ENVIO.md) - Correos a externos
- [RESUMEN_CAMBIOS_CORREOS.md](RESUMEN_CAMBIOS_CORREOS.md) - Changelog del sistema de correos

### Testing y Pruebas

- [TEST_PLANTILLAS_PDF.md](TEST_PLANTILLAS_PDF.md) - Testing de plantillas PDF
- [INTEGRACION_TEST_PLANTILLAS.md](INTEGRACION_TEST_PLANTILLAS.md) - Integración con tests
- [TESTS_API_PROGRAMACION_ASISTENCIA.md](TESTS_API_PROGRAMACION_ASISTENCIA.md) - Tests de API
- [EJEMPLOS_API_PROGRAMACION_ASISTENCIA.md](EJEMPLOS_API_PROGRAMACION_ASISTENCIA.md) - Ejemplos de API

### Comandos y Utilidades

- [COMANDO_USUARIOS_PROFESORES.md](COMANDO_USUARIOS_PROFESORES.md) - Comandos de gestión de usuarios

---

## 🎯 Guías de Inicio Rápido

### Para Desarrolladores Frontend/Apps Nativas

1. **Espacios:**
   - Leer: [API_ESPACIOS_Y_TIPOS.md](API_ESPACIOS_Y_TIPOS.md)
   - Probar: [PRUEBAS_API_ESPACIOS.md](PRUEBAS_API_ESPACIOS.md)

2. **Asistencia:**
   - Inicio: [GUIA_RAPIDA_ASISTENCIA.md](GUIA_RAPIDA_ASISTENCIA.md) ⭐
   - Detalle: [API_REGISTRO_ASISTENCIA.md](API_REGISTRO_ASISTENCIA.md)
   - Ejemplos: `ejemplos/asistencia-*.json`

3. **Programación:**
   - Leer: [API_PROGRAMACION_SEMANAL_ASISTENCIA.md](API_PROGRAMACION_SEMANAL_ASISTENCIA.md)

### Para Desarrolladores Backend

1. **Revisar cambios recientes:**
   - [RESUMEN_CAMBIOS_API_ESPACIOS.md](RESUMEN_CAMBIOS_API_ESPACIOS.md)
   - [RESUMEN_CAMBIOS_ASISTENCIA.md](RESUMEN_CAMBIOS_ASISTENCIA.md)

2. **Implementar nuevas features:**
   - Revisar estructura de controladores en `app/Http/Controllers/Api/`
   - Consultar documentación de modelos

### Para QA/Testing

1. **APIs:**
   - [PRUEBAS_API_ESPACIOS.md](PRUEBAS_API_ESPACIOS.md)
   - [TESTS_API_PROGRAMACION_ASISTENCIA.md](TESTS_API_PROGRAMACION_ASISTENCIA.md)

2. **Ejemplos listos:**
   - Carpeta `ejemplos/` con archivos JSON
   - Comandos cURL en las guías

---

## 📊 Endpoints Disponibles

### Espacios

| Método | Endpoint | Descripción | Documentación |
|--------|----------|-------------|---------------|
| GET | `/api/espacios` | Listar espacios | [Ver](API_ESPACIOS_Y_TIPOS.md#1-listar-todos-los-espacios) |
| GET | `/api/tipos-espacios` | Listar tipos | [Ver](API_ESPACIOS_Y_TIPOS.md#2-listar-tipos-de-espacios) |
| GET | `/api/espacios/resumen` | Resumen estadístico | [Ver](API_ESPACIOS_Y_TIPOS.md#3-resumen-de-espacios) |

### Programación y Asistencia

| Método | Endpoint | Descripción | Documentación |
|--------|----------|-------------|---------------|
| GET | `/api/programacion-semanal/{id_espacio}` | Consultar programación | [Ver](API_PROGRAMACION_SEMANAL_ASISTENCIA.md) |
| GET | `/api/reservas/activa/{id_espacio}` | Obtener reserva activa | [Ver](API_RESERVA_ACTIVA_ESPACIO.md) 🔥 |
| POST | `/api/asistencia` | Registrar asistencia | [Ver](API_REGISTRO_ASISTENCIA.md) ⭐ |

---

## 🔄 Historial de Cambios

### Octubre 2025

**29 de octubre:**
- ✅ Nuevo endpoint de reserva activa por espacio
- 📝 Documentación: API_RESERVA_ACTIVA_ESPACIO.md
- ✅ Actualización completa API de asistencia
- ✅ Nuevo sistema de observaciones por estudiante
- ✅ Finalización flexible de clases
- ✅ Vinculación con asignaturas
- 📝 Documentación: API_REGISTRO_ASISTENCIA.md
- 📝 Guía rápida: GUIA_RAPIDA_ASISTENCIA.md
- 📝 Changelog: RESUMEN_CAMBIOS_ASISTENCIA.md

**27 de octubre:**
- ✅ Nueva API de espacios y tipos
- 📝 Documentación: API_ESPACIOS_Y_TIPOS.md
- 📝 Pruebas: PRUEBAS_API_ESPACIOS.md
- 📝 Ejemplos: EJEMPLOS_RESPUESTAS_API_ESPACIOS.md
- 📝 Resumen: RESUMEN_CAMBIOS_API_ESPACIOS.md

---

## 🔍 Buscar por Tema

### Asistencia
- [API_REGISTRO_ASISTENCIA.md](API_REGISTRO_ASISTENCIA.md) - Principal
- [GUIA_RAPIDA_ASISTENCIA.md](GUIA_RAPIDA_ASISTENCIA.md) - Inicio rápido
- [RESUMEN_CAMBIOS_ASISTENCIA.md](RESUMEN_CAMBIOS_ASISTENCIA.md) - Changelog

### Espacios/Salas
- [API_ESPACIOS_Y_TIPOS.md](API_ESPACIOS_Y_TIPOS.md) - API completa
- [PRUEBAS_API_ESPACIOS.md](PRUEBAS_API_ESPACIOS.md) - Testing

### Programación Semanal
- [API_PROGRAMACION_SEMANAL_ASISTENCIA.md](API_PROGRAMACION_SEMANAL_ASISTENCIA.md)
- [EJEMPLOS_API_PROGRAMACION_ASISTENCIA.md](EJEMPLOS_API_PROGRAMACION_ASISTENCIA.md)

### Reservas
- [API_RESERVA_ACTIVA_ESPACIO.md](API_RESERVA_ACTIVA_ESPACIO.md) 🔥 - Consultar reserva activa

### Correos
- [CORREOS_MASIVOS_GUIA.md](CORREOS_MASIVOS_GUIA.md)
- [PLANTILLAS_CORREOS_GUIA.md](PLANTILLAS_CORREOS_GUIA.md)
- [ENVIO_CORREOS_GUIA.md](ENVIO_CORREOS_GUIA.md)

### Testing
- [PRUEBAS_API_ESPACIOS.md](PRUEBAS_API_ESPACIOS.md)
- [TEST_PLANTILLAS_PDF.md](TEST_PLANTILLAS_PDF.md)
- [TESTS_API_PROGRAMACION_ASISTENCIA.md](TESTS_API_PROGRAMACION_ASISTENCIA.md)

---

## 💾 Archivos de Ejemplo

### JSON para Testing

Ubicación: `docs/ejemplos/`

**Asistencia:**
- `asistencia-completa.json` - Registro completo con múltiples estudiantes
- `asistencia-simple.json` - Registro mínimo (1 estudiante)
- `asistencia-sin-finalizar.json` - Registro sin finalizar clase

**Reserva Activa:**
- `reserva-activa-con-reserva.json` - Respuesta con reserva activa
- `reserva-activa-sin-reserva.json` - Respuesta sin reserva activa

### Uso

```bash
curl -X POST http://localhost:8000/api/asistencia \
  -H "Content-Type: application/json" \
  -d @docs/ejemplos/asistencia-completa.json
```

---

## 🆘 Ayuda y Soporte

### Encontrar Información

1. **Usa este índice** para localizar la documentación relevante
2. **Busca por categoría** en las secciones de arriba
3. **Consulta las guías rápidas** para información concisa
4. **Revisa los ejemplos** en `ejemplos/` para código listo

### Reportar Problemas

Si encuentras errores o inconsistencias:
1. Verifica la fecha del documento (puede estar desactualizado)
2. Consulta el changelog correspondiente
3. Revisa los logs del sistema
4. Contacta al equipo de desarrollo

### Contribuir

Para agregar documentación:
1. Seguir el formato existente
2. Incluir ejemplos prácticos
3. Actualizar este índice
4. Crear PR con los cambios

---

## 📌 Convenciones

### Íconos Utilizados

- ⭐ - Documentación principal/más importante
- 🚀 - Guía de inicio rápido
- 📋 - Changelog/resumen de cambios
- ✅ - Característica implementada
- ❌ - Característica eliminada/obsoleta
- 📝 - Documentación
- 🔧 - Configuración
- 🧪 - Testing
- 💡 - Tips y mejores prácticas

### Estado de Documentos

- **Actualizado** - Documento vigente y actualizado
- **En progreso** - Documento en desarrollo
- **Obsoleto** - Documento desactualizado (se indica fecha)

---

## 📅 Última Actualización

**Fecha:** 29 de octubre de 2025  
**Versión:** 2.0  
**Mantenedor:** Sistema de IA

---

*Para más información, contacta al equipo de desarrollo de AulaSync.*

# Sistema de Reportes de Clases No Realizadas

## 📋 Descripción

Sistema automatizado para la generación y envío de reportes de clases no realizadas. Incluye exportación a PDF y envío automático por correo electrónico.

## 🎯 Características

### Reportes Disponibles

#### 1. Reporte Semanal
- **Contenido:**
  - Total de clases no realizadas en la semana
  - Clases sin justificar
  - Clases justificadas
  - Detalle por profesor con:
    - Fecha y día de la semana
    - Asignatura y código
    - Espacio y módulo
    - Estado y motivo

- **Estadísticas:**
  - Total de clases no realizadas
  - Total de profesores afectados
  - Distribución entre justificadas y no justificadas

#### 2. Reporte Mensual
- **Contenido:**
  - Resumen ejecutivo por profesor
  - Porcentaje de clases no realizadas vs. total programadas
  - Indicador de clases recuperadas
  - Indicador de clases justificadas
  - Porcentaje de cumplimiento por profesor

- **Estadísticas Avanzadas:**
  - Total de clases no realizadas
  - Clases recuperadas mediante reagendamiento
  - Porcentaje global de incumplimiento
  - Ranking de profesores con más ausencias
  - Análisis de tendencias

## 🚀 Uso Manual

### Exportar PDF desde la Interfaz

1. Accede a la sección "Clases No Realizadas"
2. Aplica los filtros deseados (fechas, profesor, estado)
3. Haz clic en:
   - **"Exportar Semanal"** para generar reporte de la semana
   - **"Exportar Mensual"** para generar reporte del mes

### Generar Reportes por Comando

#### Reporte Semanal
```bash
# Generar reporte de la semana pasada
php artisan reportes:clases-no-realizadas-semanal

# Enviar a correos específicos
php artisan reportes:clases-no-realizadas-semanal --email=usuario1@ejemplo.com,usuario2@ejemplo.com
```

#### Reporte Mensual
```bash
# Generar reporte del mes anterior
php artisan reportes:clases-no-realizadas-mensual

# Generar reporte de un mes específico
php artisan reportes:clases-no-realizadas-mensual --mes=9 --anio=2025

# Enviar a correos específicos
php artisan reportes:clases-no-realizadas-mensual --email=usuario1@ejemplo.com,usuario2@ejemplo.com
```

## ⏰ Envío Automático

### Configuración de Destinatarios

Edita el archivo `.env` y agrega:

```env
# Destinatarios de reportes (separados por comas)
REPORT_RECIPIENTS=direccion@institucion.edu,jefatura@institucion.edu,administracion@institucion.edu
```

### Programación Automática

Los reportes se envían automáticamente según el siguiente calendario:

| Reporte | Frecuencia | Horario | Contenido |
|---------|-----------|---------|-----------|
| **Semanal** | Cada lunes | 08:00 AM | Clases no realizadas de la semana anterior (lun-dom) |
| **Mensual** | Primer día del mes | 09:00 AM | Resumen completo del mes anterior |

### Verificar Programación

```bash
# Ver tareas programadas
php artisan schedule:list

# Ejecutar manualmente todas las tareas programadas (para testing)
php artisan schedule:run

# Ver logs de reportes
tail -f storage/logs/reporte-semanal.log
tail -f storage/logs/reporte-mensual.log
```

## 📧 Formato de Correos

### Reporte Semanal
- **Asunto:** Reporte Semanal - Clases No Realizadas (Semana X)
- **Adjunto:** PDF con detalle completo
- **Contenido:**
  - Resumen visual con estadísticas principales
  - Lista de profesores afectados
  - Gráfico de distribución

### Reporte Mensual
- **Asunto:** Reporte Mensual - Clases No Realizadas (Mes YYYY)
- **Adjunto:** PDF con análisis completo
- **Contenido:**
  - Porcentaje de incumplimiento
  - Top 5 profesores con más ausencias
  - Análisis de recuperaciones
  - Alertas si el porcentaje supera el 5%

## 🛠️ Configuración del Sistema

### Requisitos

- Laravel 10+
- DomPDF instalado y configurado
- Cron jobs habilitado en el servidor
- Configuración de correo (SMTP) funcional

### Configurar Cron (Servidor Linux)

```bash
# Editar crontab
crontab -e

# Agregar esta línea (ejecutar cada minuto)
* * * * * cd /ruta/a/tu/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

### Configurar Task Scheduler (Windows Server)

1. Abrir "Programador de tareas"
2. Crear nueva tarea básica
3. Configurar para ejecutar cada minuto:
   ```
   Programa: C:\ruta\a\php.exe
   Argumentos: C:\ruta\al\proyecto\artisan schedule:run
   ```

## 📊 Estructura de Datos

### Campos en el Reporte

#### Información del Profesor
- Nombre completo
- RUN
- Correo electrónico
- Total de ausencias
- Clases sin justificar
- Clases justificadas
- Clases recuperadas (mensual)
- Porcentaje de cumplimiento (mensual)

#### Información de Cada Clase
- Fecha (dd/mm/YYYY)
- Día de la semana
- Asignatura y código
- Espacio
- Módulo
- Estado (No Realizada / Justificado)
- Motivo
- Observaciones
- ¿Fue recuperada? (mensual)
- ¿Fue justificada? (mensual)

## 🔍 Personalización

### Modificar Horarios de Envío

Edita `app/Console/Kernel.php`:

```php
// Cambiar día y hora del reporte semanal
$schedule->command('reportes:clases-no-realizadas-semanal')
    ->weeklyOn(5, '15:00') // Viernes a las 3:00 PM
    ->withoutOverlapping();

// Cambiar día del reporte mensual
$schedule->command('reportes:clases-no-realizadas-mensual')
    ->monthlyOn(5, '10:00') // Día 5 de cada mes a las 10:00 AM
    ->withoutOverlapping();
```

### Personalizar PDFs

Los archivos de diseño se encuentran en:
- `resources/views/pdf/clases-no-realizadas-semanal.blade.php`
- `resources/views/pdf/clases-no-realizadas-mensual.blade.php`

### Personalizar Correos

Los archivos de plantillas de correo están en:
- `resources/views/emails/reporte-semanal-clases-no-realizadas.blade.php`
- `resources/views/emails/reporte-mensual-clases-no-realizadas.blade.php`

## 🐛 Solución de Problemas

### Los reportes no se envían automáticamente

1. Verificar que el cron esté configurado:
   ```bash
   php artisan schedule:list
   ```

2. Verificar configuración de correo:
   ```bash
   php artisan tinker
   >>> Mail::to('test@ejemplo.com')->send(new \App\Mail\ReporteSemanalClasesNoRealizadas([], null))
   ```

3. Revisar logs:
   ```bash
   tail -f storage/logs/laravel.log
   tail -f storage/logs/reporte-semanal.log
   ```

### Error al generar PDF

1. Verificar que DomPDF esté instalado:
   ```bash
   composer show barryvdh/laravel-dompdf
   ```

2. Limpiar caché:
   ```bash
   php artisan config:clear
   php artisan view:clear
   ```

### Destinatarios no reciben correos

1. Verificar configuración en `.env`:
   ```env
   REPORT_RECIPIENTS=correo1@ejemplo.com,correo2@ejemplo.com
   ```

2. Verificar configuración SMTP:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=tu-correo@gmail.com
   MAIL_PASSWORD=tu-password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@SIA | Sistema de Informaci�n de Aulas.com
   MAIL_FROM_NAME="SIA | Sistema de Informaci�n de Aulas - Sistema Académico"
   ```

## 📝 Logs

Los reportes generan logs en:
- `storage/logs/reporte-semanal.log` - Log de reportes semanales
- `storage/logs/reporte-mensual.log` - Log de reportes mensuales
- `storage/logs/laravel.log` - Log general de la aplicación

## 🔐 Seguridad

- Los reportes contienen información sensible académica
- Solo deben enviarse a correos institucionales autorizados
- Los PDFs incluyen marca de "Documento Confidencial"
- Se recomienda configurar SPF y DKIM para evitar que los correos sean marcados como spam

## 📞 Soporte

Para problemas o consultas:
- Revisar logs del sistema
- Verificar configuración de correo
- Contactar al administrador del sistema

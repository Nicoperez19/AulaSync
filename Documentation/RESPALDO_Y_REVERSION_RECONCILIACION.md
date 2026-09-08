# Guía de Respaldo, Reconciliación y Reversión de Reservas Espontáneas

Esta guía detalla el funcionamiento del comando `php artisan reservas:reconciliar-espontaneas`, las medidas de seguridad implementadas y cómo revertir los cambios de forma automática si algo no sale como esperabas.

---

## 1. ¿Qué hace el comando de reconciliación?

El comando:
```bash
php artisan reservas:reconciliar-espontaneas --desde=2026-08-01
```
Analiza los registros históricos desde la fecha indicada (en todos los tenants/sedes del sistema) y busca reservas de profesores que quedaron catalogadas como `"espontanea"` o sin asignatura asociada. Cuando el comando detecta que a esa misma hora y en esa misma sala el profesor tenía una clase programada (en la planificación regular o como profesor colaborador):

1. **En `reservas`**: Cambia el `tipo_reserva` a `'clase'`, asigna el `id_asignatura` correcto y agrega una nota en `observaciones`.
2. **En `asistencias`**: Vincula el `id_asignatura` a las asistencias de alumnos asociadas a esa reserva que estaban huérfanas.
3. **En `clases_no_realizadas`**: Elimina las falsas alertas de inasistencia que se habían generado por error para esa fecha y sala.

---

## 2. Red de Seguridad Implementada

Para garantizar que no pierdas ningún dato ni modifiques registros de manera irreversible, se implementaron 3 mecanismos:

### A. Modo Simulación (`--dry-run`)
Permite ver en pantalla exactamente qué registros encontraría y qué cambios haría, **sin modificar absolutamente nada en la base de datos**.

```bash
php artisan reservas:reconciliar-espontaneas --desde=2026-08-01 --dry-run
```

---

### B. Respaldo Automático Integrado (Snapshot JSON)
Cuando ejecutas el comando real (sin `--dry-run`), el sistema **captura y guarda automáticamente antes de actualizar** en `storage/app/backups/`:
* El estado original de cada reserva (`tipo_reserva`, `id_asignatura`, `observaciones`).
* Los IDs de las `asistencias` modificadas.
* El contenido completo de cualquier registro de `clases_no_realizadas` antes de ser eliminado.

El archivo se genera con el formato:  
`storage/app/backups/reconciliacion_backup_YYYY-MM-DD_HHMMSS.json`

---

### C. Comando de Reversión Automático (`reservas:revertir-reconciliacion`)
Si ejecutas la reconciliación y notas cualquier inconsistencia o prefieres dejar todo como estaba, dispones del comando:

```bash
php artisan reservas:revertir-reconciliacion
```

**¿Qué hace al ejecutarse?**
1. Detecta automáticamente el respaldo más reciente generado en `storage/app/backups/`.
2. Te solicita confirmación antes de proceder.
3. Para cada tenant:
   * Restaura el `tipo_reserva`, `id_asignatura` y `observaciones` originales de cada reserva.
   * Regresa las asistencias a `id_asignatura = null`.
   * Reinserta íntegramente las clases no realizadas que se habían eliminado.

*(Opcional: Si deseas especificar un archivo de respaldo anterior en particular, puedes usar `--archivo=ruta/al/archivo.json`).*

---

## 3. Respaldo Externo Completo de MySQL (Opcional, Máxima Seguridad)

Si antes de correr cualquier comando deseas un respaldo físico completo de las bases de datos de XAMPP mediante `mysqldump`:

### En PowerShell:
```powershell
# Crear carpeta de respaldos si no existe
New-Item -ItemType Directory -Force -Path "C:\xampp\backups_mysql"

# Exportar todas las bases de datos de MySQL
& "C:\xampp\mysql\bin\mysqldump.exe" -u root --all-databases > "C:\xampp\backups_mysql\backup_completo_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"
```

> Para restaurar este archivo completo en caso de catástrofe:
> ```powershell
> & "C:\xampp\mysql\bin\mysql.exe" -u root < "C:\xampp\backups_mysql\NOMBRE_DEL_ARCHIVO.sql"
> ```

---

## 4. Procedimiento Recomendado Paso a Paso

1. **Paso 1: Probar en simulación**
   ```bash
   php artisan reservas:reconciliar-espontaneas --desde=2026-08-01 --dry-run
   ```
   *Revisa en pantalla las reservas que detectó.*

2. **Paso 2: Ejecutar la reconciliación real**
   ```bash
   php artisan reservas:reconciliar-espontaneas --desde=2026-08-01
   ```
   *Al finalizar, verás el mensaje indicando que el archivo de respaldo fue guardado con éxito.*

3. **Paso 3 (Solo si no te gustó el resultado): Revertir**
   ```bash
   php artisan reservas:revertir-reconciliacion
   ```
   *Confirma con `yes` y todo volverá exactamente al estado que tenía antes de ejecutar el comando.*

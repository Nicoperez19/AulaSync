# Guía para la Corrección de Datos en el Reporte de Todas las Clases (TodasClasesExport)

El reporte exportado por `TodasClasesExport` consolida los datos de planificaciones académicas, ingresos QR de profesores, inasistencias y feriados en tiempo real. 

Si detectas alguna incoherencia en los datos del reporte (clases duplicadas, inasistencias incorrectas, faltas de marcas de entrada/salida), aquí tienes la guía técnica y administrativa para corregirlos desde el panel de administración de AulaSync.

---

## 📋 Origen de los Datos en el Reporte
Para saber cómo corregir un dato, primero debemos identificar de dónde proviene en la base de datos:

| Estado en el Reporte | Origen del Dato | Cómo se calcula |
| :--- | :--- | :--- |
| **Planificada** | `planificacion_asignaturas` | Es la planificación semestral cargada al inicio del período. |
| **Realizada** | `reservas` y `asistencias` | Se detecta un check-in de entrada QR válido del profesor dentro de la tolerancia de la clase. |
| **No Registrada** | `clases_no_realizadas` | El sistema no detectó ningún ingreso del docente pasados los 15 minutos de tolerancia. |
| **Justificada** / **Recuperada** | `clases_no_realizadas` | El administrador o asistente académico justificó/reprogramó la clase no realizada. |
| **Feriado/Justificado** | `dia_feriados` | La fecha coincide con un feriado o día sin actividades académicas registrado en el sistema. |

---

## 🛠️ Procedimientos de Corrección

### Caso 1: Una clase figura como "No Registrada" pero sí se realizó
Si el profesor sí asistió a realizar la clase pero olvidó escanear el código QR al entrar, figurará como **No Registrada**.

* **Opción A (Corrección Retroactiva Automática)**:
  Registra la asistencia/reserva correspondiente del docente en la base de datos (con fecha de la clase, el RUN del profesor y una hora de ingreso dentro del bloque).
  * *Nota:* Al registrarse el ingreso, el sistema ejecuta automáticamente `ClaseNoRealizada::limpiarRegistrosIncorrectos()`, lo que **elimina permanentemente** el registro de inasistencia anterior, y el reporte mostrará automáticamente la clase como **Realizada**.
* **Opción B (Justificación Administrativa desde el Panel)**:
  1. Ingresa al **Tablero Académico** o al menú de **Clases No Realizadas**.
  2. Busca la inasistencia del profesor para ese día y módulo.
  3. Cambia el estado a **Justificada** o **Recuperada**.
  4. Agrega los detalles y observaciones correspondientes. En el Excel pasará a tener fondo amarillo/azul.

---

### Caso 2: El reporte muestra un "Atraso" que no corresponde
* Si figura un atraso incorrecto, se debe a que la hora registrada en la tabla `reservas` (el primer escaneo QR del docente en esa sala y día) fue posterior al límite de tolerancia de 15 minutos.
* Para corregirlo o justificarlo:
  1. Ve al menú de **Atrasos de Profesores** en el panel.
  2. Busca la clase correspondiente y marca el registro como **Justificado**.

---

### Caso 3: Clases planificadas en días que fueron feriados o suspensiones
Si el reporte muestra clases como *"No Registradas"* en un día en que se suspendieron las actividades:
1. Ve al menú de **Calendario Académico** (Días Feriados).
2. Registra el día de la suspensión o feriado correspondiente.
3. Al guardarse el feriado, todas las planificaciones de ese día pasarán automáticamente al estado **Feriado/Justificado** en el reporte de Excel, eliminando automáticamente las inasistencias asociadas a ese día.

---

### Caso 4: Se listan laboratorios paralelos como una sola clase o se omiten profesores
Si detectas cruces donde varios laboratorios paralelos (misma asignatura, misma hora, diferentes salas/profesores) se unifican incorrectamente:
* Asegúrate de que cada sección de laboratorio en la tabla `planificacion_asignaturas` tenga asociado su **respectivo espacio físico (`id_espacio`)** y su **respectivo código de horario (`id_horario`)** con el docente asignado correctamente. 
* La lógica de exportación utiliza la combinación de espacio y planificación para separar correctamente los registros paralelos. Si falta el espacio físico o es el mismo, el sistema podría agruparlos de forma incorrecta.

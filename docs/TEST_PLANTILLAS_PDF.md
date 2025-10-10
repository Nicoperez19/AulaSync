# Test de Plantillas PDF - AulaSync

## 📋 Descripción

Esta herramienta permite probar y visualizar las plantillas de correo creadas en el sistema, generando PDFs de prueba con datos de ejemplo.

## 🚀 Características

- ✅ Visualización de todas las plantillas activas
- ✅ Vista previa HTML en el navegador
- ✅ Generación de PDFs individuales
- ✅ Descarga masiva de todas las plantillas en ZIP
- ✅ Reemplazo automático de variables con datos de ejemplo
- ✅ Información detallada de cada plantilla

## 📍 Acceso

Accede a la herramienta en:

```
http://localhost/test/plantillas-pdf
```

O en tu dominio:

```
https://tu-dominio.com/test/plantillas-pdf
```

## 🎯 Funcionalidades

### 1. Vista Principal
- Lista todas las plantillas activas del sistema
- Muestra información detallada:
  - Nombre de la plantilla
  - Asunto del correo
  - Tipo de correo asociado
  - Usuario creador
  - Fecha de creación
  - Variables utilizadas en el contenido

### 2. Vista Previa HTML
- Abre el contenido renderizado directamente en el navegador
- Muestra cómo se verá el correo antes de convertirlo a PDF
- Incluye header y footer automáticos
- Variables reemplazadas con datos de ejemplo

### 3. Generar PDF Individual
- Genera un PDF de la plantilla seleccionada
- Se abre automáticamente en el navegador
- Listo para descargar o imprimir
- Formato A4, orientación vertical

### 4. Descargar Todos (ZIP)
- Genera PDFs de todas las plantillas activas
- Comprime todo en un archivo ZIP
- Útil para revisión masiva o respaldo

## 📊 Datos de Ejemplo

Los PDFs utilizan los siguientes datos de ejemplo para reemplazar las variables:

| Variable | Valor de Ejemplo |
|----------|------------------|
| `{{nombre}}` | Juan Pérez González |
| `{{email}}` | juan.perez@ejemplo.cl |
| `{{fecha}}` | Fecha actual (formato d/m/Y) |
| `{{periodo}}` | Semana actual (del lunes al domingo) |
| `{{total_clases}}` | 20 |
| `{{clases_no_realizadas}}` | 3 |
| `{{porcentaje}}` | 85 |

## 🛠️ Configuración Técnica

### Controlador
`App\Http\Controllers\TestPlantillaPdfController.php`

### Vista
`resources/views/test/plantillas-pdf-index.blade.php`

### Rutas
```php
Route::prefix('test/plantillas-pdf')->name('test.plantillas.pdf.')->group(function () {
    Route::get('/', [TestPlantillaPdfController::class, 'index'])->name('index');
    Route::get('/preview/{id}', [TestPlantillaPdfController::class, 'vistaPrevia'])->name('preview');
    Route::get('/generar/{id}', [TestPlantillaPdfController::class, 'generarPdf'])->name('generar');
    Route::get('/todos', [TestPlantillaPdfController::class, 'generarTodos'])->name('todos');
});
```

## 📦 Dependencias

Esta herramienta utiliza:
- **DomPDF** (`barryvdh/laravel-dompdf`): Para la generación de PDFs
- **ZipArchive** (PHP): Para crear archivos ZIP con múltiples PDFs
- **Tailwind CSS**: Para el diseño de la interfaz
- **Font Awesome**: Para los iconos

## 🔍 Métodos del Controlador

### `index()`
Muestra la lista de plantillas activas disponibles para probar.

### `generarPdf($id)`
Genera un PDF individual de la plantilla especificada.
- **Parámetros**: `$id` (ID de la plantilla)
- **Retorna**: Stream del PDF para visualización/descarga

### `vistaPrevia($id)`
Muestra la vista previa HTML sin convertir a PDF.
- **Parámetros**: `$id` (ID de la plantilla)
- **Retorna**: HTML renderizado con variables reemplazadas

### `generarTodos()`
Genera PDFs de todas las plantillas activas y las comprime en ZIP.
- **Retorna**: Descarga del archivo ZIP

## 💡 Casos de Uso

1. **Verificar diseño de plantillas**: Usa la vista previa HTML para verificar rápidamente el diseño.

2. **Probar antes de enviar**: Genera el PDF para ver exactamente cómo se verá el correo.

3. **Documentación**: Descarga todos los PDFs para tener un respaldo o documentación de las plantillas.

4. **Presentación**: Muestra las plantillas a stakeholders sin necesidad de enviar correos reales.

## ⚠️ Consideraciones

- Solo muestra plantillas **activas**
- Los datos son de ejemplo y no corresponden a usuarios reales
- Las imágenes (como logos) deben estar en la carpeta `public/images/`
- Los PDFs se generan con márgenes cero para mejor aprovechamiento del espacio

## 🎨 Personalización

### Modificar Datos de Ejemplo

Para cambiar los datos de ejemplo, edita el array `$datosEjemplo` en el controlador:

```php
$datosEjemplo = [
    'nombre' => 'Tu Nombre Personalizado',
    'email' => 'tu-email@ejemplo.com',
    // ... más variables
];
```

### Cambiar Formato del PDF

En el método `generarPdf()`, puedes ajustar:

```php
$pdf = Pdf::loadHTML($contenidoHTML)
    ->setPaper('a4', 'portrait')  // Cambiar a 'landscape' para horizontal
    ->setOption('margin-top', 0)   // Ajustar márgenes
    ->setOption('margin-bottom', 0);
```

## 📝 Ejemplo de Uso

1. Accede a `/test/plantillas-pdf`
2. Revisa la lista de plantillas disponibles
3. Click en "Vista Previa HTML" para ver el contenido en el navegador
4. Click en "Generar PDF" para descargar el PDF
5. O usa "Descargar Todos (ZIP)" para obtener todas las plantillas a la vez

## 🐛 Troubleshooting

### El PDF no se genera correctamente
- Verifica que DomPDF esté instalado: `composer require barryvdh/laravel-dompdf`
- Limpia la caché: `php artisan config:clear`

### Las imágenes no se muestran en el PDF
- Asegúrate de que las rutas sean absolutas
- Verifica que las imágenes existan en `public/images/`
- Usa `asset()` helper para las rutas

### Error al generar ZIP
- Verifica que el directorio `storage/app/` tenga permisos de escritura
- Asegúrate de que la extensión `ZipArchive` esté habilitada en PHP

## 📞 Soporte

Para más información o problemas, contacta al equipo de desarrollo.

---

**Última actualización**: Octubre 2025
**Versión**: 1.0.0

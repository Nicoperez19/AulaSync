<?php

namespace App\Http\Controllers;

use App\Models\PlantillaCorreo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class TestPlantillaPdfController extends Controller
{
    /**
     * Muestra una lista de todas las plantillas disponibles para probar
     */
    public function index()
    {
        $plantillas = PlantillaCorreo::with('tipoCorreo', 'creador')
            ->activas()
            ->get();

        return view('test.plantillas-pdf-index', compact('plantillas'));
    }

    /**
     * Genera un PDF de prueba para una plantilla específica
     */
    public function generarPdf($id)
    {
        $plantilla = PlantillaCorreo::with('tipoCorreo')->findOrFail($id);

        // Datos de ejemplo para reemplazar las variables
        $datosEjemplo = [
            'nombre' => 'Juan Pérez González',
            'email' => 'juan.perez@ejemplo.cl',
            'fecha' => now()->format('d/m/Y'),
            'periodo' => 'Semana del ' . now()->startOfWeek()->format('d/m/Y') . ' al ' . now()->endOfWeek()->format('d/m/Y'),
            'total_clases' => '20',
            'clases_no_realizadas' => '3',
            'porcentaje' => '85',
        ];

        // Renderizar contenido con variables reemplazadas
        $contenidoHTML = $plantilla->renderizarContenido($datosEjemplo);

        // Generar PDF
        $pdf = Pdf::loadHTML($contenidoHTML)
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 0)
            ->setOption('margin-bottom', 0)
            ->setOption('margin-left', 0)
            ->setOption('margin-right', 0);

        $nombreArchivo = 'plantilla_' . $plantilla->id . '_' . now()->format('YmdHis') . '.pdf';

        return $pdf->stream($nombreArchivo);
    }

    /**
     * Vista previa HTML (sin PDF) para depuración
     */
    public function vistaPrevia($id)
    {
        $plantilla = PlantillaCorreo::with('tipoCorreo')->findOrFail($id);

        // Datos de ejemplo para reemplazar las variables
        $datosEjemplo = [
            'nombre' => 'Juan Pérez González',
            'email' => 'juan.perez@ejemplo.cl',
            'fecha' => now()->format('d/m/Y'),
            'periodo' => 'Semana del ' . now()->startOfWeek()->format('d/m/Y') . ' al ' . now()->endOfWeek()->format('d/m/Y'),
            'total_clases' => '20',
            'clases_no_realizadas' => '3',
            'porcentaje' => '85',
        ];

        // Renderizar contenido con variables reemplazadas
        $contenidoHTML = $plantilla->renderizarContenido($datosEjemplo);

        // Retornar directamente el HTML
        return response($contenidoHTML);
    }

    /**
     * Genera PDFs de todas las plantillas activas en un solo archivo ZIP
     */
    public function generarTodos()
    {
        $plantillas = PlantillaCorreo::activas()->get();

        if ($plantillas->isEmpty()) {
            return redirect()->back()->with('error', 'No hay plantillas activas para generar.');
        }

        // Crear directorio temporal
        $tempDir = storage_path('app/temp_pdfs_' . uniqid());
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Datos de ejemplo
        $datosEjemplo = [
            'nombre' => 'Juan Pérez González',
            'email' => 'juan.perez@ejemplo.cl',
            'fecha' => now()->format('d/m/Y'),
            'periodo' => 'Semana del ' . now()->startOfWeek()->format('d/m/Y') . ' al ' . now()->endOfWeek()->format('d/m/Y'),
            'total_clases' => '20',
            'clases_no_realizadas' => '3',
            'porcentaje' => '85',
        ];

        $archivos = [];

        foreach ($plantillas as $plantilla) {
            $contenidoHTML = $plantilla->renderizarContenido($datosEjemplo);
            
            $pdf = Pdf::loadHTML($contenidoHTML)
                ->setPaper('a4', 'portrait')
                ->setOption('margin-top', 0)
                ->setOption('margin-bottom', 0)
                ->setOption('margin-left', 0)
                ->setOption('margin-right', 0);

            $nombreArchivo = $tempDir . '/plantilla_' . $plantilla->id . '_' . str_replace(' ', '_', $plantilla->nombre) . '.pdf';
            $pdf->save($nombreArchivo);
            $archivos[] = $nombreArchivo;
        }

        // Crear archivo ZIP
        $zipNombre = 'plantillas_correos_' . now()->format('YmdHis') . '.zip';
        $zipPath = storage_path('app/' . $zipNombre);
        
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            foreach ($archivos as $archivo) {
                $zip->addFile($archivo, basename($archivo));
            }
            $zip->close();
        }

        // Limpiar archivos temporales
        foreach ($archivos as $archivo) {
            if (file_exists($archivo)) {
                unlink($archivo);
            }
        }
        rmdir($tempDir);

        // Descargar ZIP
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}

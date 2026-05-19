<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\Facultad;
use App\Models\Piso;
use App\Models\Universidad;
use App\Models\Modulo;
use App\Models\Planificacion_Asignatura;
use App\Models\Solicitante;
use App\Models\PlanificacionProfesorColaborador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\QRService;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use App\Models\Sede;
use App\Models\User;
use App\Models\Profesor;
use App\Models\Reserva;
use App\Traits\SafeCacheTrait;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EspacioController extends Controller
{
    use \App\Traits\EspacioHelperTrait;

    use SafeCacheTrait;
    /**
     * Muestra el listado de espacios
     */
    public function index(Request $request)
    {
        $espacios = Espacio::with('piso.facultad')->get();
        $universidades = Universidad::all();

        return view('layouts.spaces.spaces_index', compact('espacios', 'universidades'));
    }

    /**
     * Almacena un nuevo espacio
     */
    public function store(Request $request)
    {


        try {
            $validated = $this->validarDatosEspacio($request);

            $espacio = Espacio::create([
                'id_espacio' => $validated['id_espacio'],
                'nombre_espacio' => $validated['nombre_espacio'],
                'piso_id' => $validated['piso_id'],
                'tipo_espacio' => $validated['tipo_espacio'],
                'estado' => $validated['estado'],
                'puestos_disponibles' => $validated['puestos_disponibles'],
            ]);

            return redirect()
                ->route('spaces_index')
                ->with('success', 'Espacio creado exitosamente.');

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error al crear espacio:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al crear el espacio: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario de edición de un espacio
     */
    public function edit(string $id_espacio)
    {
        $espacio = Espacio::with('piso.facultad.sede.universidad')
            ->where('id_espacio', $id_espacio)
            ->firstOrFail();

        $this->autorizarGestionEspacio($espacio);

        $universidades = Universidad::all();
        $sedes = Sede::where('id_universidad', $espacio->piso->facultad->sede->id_universidad)->get();
        $facultades = Facultad::where('id_sede', $espacio->piso->facultad->id_sede)->get();
        $pisos = Piso::where('id_facultad', $espacio->piso->id_facultad)->get();

        return view('layouts.spaces.spaces_edit', compact('espacio', 'universidades', 'sedes', 'facultades', 'pisos'));
    }

    /**
     * Actualiza un espacio existente
     */
    public function update(Request $request, string $id_espacio)
    {
        try {
            $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();
            $this->autorizarGestionEspacio($espacio);

            $validated = $this->validarDatosEspacio($request, $id_espacio);

            $espacio->update([
                'nombre_espacio' => $validated['nombre_espacio'],
                'piso_id' => $validated['piso_id'],
                'tipo_espacio' => $validated['tipo_espacio'],
                'estado' => $validated['estado'],
                'puestos_disponibles' => $validated['puestos_disponibles'],
            ]);

            return redirect()
                ->route('spaces_index')
                ->with('success', 'Espacio actualizado correctamente.');

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error al actualizar espacio:', [
                'error' => $e->getMessage(),
                'espacio_id' => $id_espacio
            ]);

            return redirect()
                ->route('spaces_index')
                ->with('error', 'Error al actualizar el espacio: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un espacio
     */
    public function destroy(string $id_espacio)
    {
        try {
            $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();
            $this->autorizarGestionEspacio($espacio);
            $espacio->delete();

            return redirect()
                ->route('spaces_index')
                ->with('success', 'Espacio eliminado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al eliminar espacio:', [
                'error' => $e->getMessage(),
                'espacio_id' => $id_espacio
            ]);

            return redirect()
                ->route('spaces_index')
                ->with('error', 'Error al eliminar el espacio: ' . $e->getMessage());
        }
    }

    /**
     * Normaliza y valida los datos del espacio para create/update.
     */


    /**
     * Normaliza id_espacio aplicando prefijo del tenant cuando corresponda.
     */


    /**
     * Autoriza CRUD de espacios según sede (admins solo su sede, superusers todas).
     */


    /**
     * Autoriza gestión de un espacio ya existente.
     */


    /**
     * Obtiene las facultades de una universidad
     */
    public function getFacultades($universidadId)
    {
        return Facultad::where('id_universidad', $universidadId)->get();
    }

    /**
     * Obtiene los pisos de una facultad
     */
    public function getPisos($facultadId)
    {
        return Piso::where('id_facultad', $facultadId)->get();
    }

    /**
     * Obtiene los espacios de un piso
     */
    public function getEspacios($pisoId)
    {
        return Espacio::where('piso_id', $pisoId)->get();
    }

    /**
     * Obtiene las sedes de una universidad
     */
    public function getSedes($universidadId)
    {
        try {
            $sedes = Sede::where('id_universidad', $universidadId)->get();
            return response()->json($sedes);
        } catch (\Exception $e) {
            Log::error('Error al obtener sedes:', [
                'universidad_id' => $universidadId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error al obtener las sedes'], 500);
        }
    }

    /**
     * Obtiene las facultades de una sede
     */
    public function getFacultadesPorSede($sedeId)
    {
        try {
            $facultades = Facultad::where('id_sede', $sedeId)->get();
            return response()->json($facultades);
        } catch (\Exception $e) {
            Log::error('Error al obtener facultades:', [
                'sede_id' => $sedeId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error al obtener las facultades'], 500);
        }
    }

    /**
     * Devuelve los módulos disponibles para reservar en un espacio
     * Solo revisa la tabla planificacion_asignaturas usando el formato id_modulo (ej: "VI.4")
     */
    public function modulosDisponibles(Request $request, $espacioId)
    {
        // Obtener día y módulo actual
        $horaActual = $request->input('hora_actual', now()->format('H:i:s'));
        $diaActual = $request->input('dia_actual', strtolower(now()->locale('es')->isoFormat('dddd')));

        // Log para debugging


        // Mapeo de días a códigos
        $codigosDias = [
            'lunes' => 'LU',
            'martes' => 'MA',
            'miercoles' => 'MI',
            'jueves' => 'JU',
            'viernes' => 'VI',
            'sabado' => 'SA',
            'domingo' => 'DO'
        ];

        $codigoDia = $codigosDias[$diaActual] ?? 'LU';

        // Determinar el módulo actual según la hora
        $moduloActual = $this->determinarModuloActual($horaActual, $diaActual);

        // Log para debugging del módulo actual


        if (!$moduloActual) {
            Log::warning('modulosDisponibles - No se pudo determinar el módulo actual', [
                'horaActual' => $horaActual,
                'diaActual' => $diaActual
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'No hay módulo actual disponible.',
                'max_modulos' => 0,
                'modulo_actual' => null,
                'detalles' => [
                    'razon' => 'fuera_horario',
                    'descripcion' => 'El sistema de reservas solo está disponible durante el horario de clases (08:10 - 23:00)'
                ]
            ]);
        }

        // Obtener todas las planificaciones para este espacio en este día
        $planificaciones = Planificacion_Asignatura::where('id_espacio', $espacioId)
            ->where('id_modulo', 'like', $codigoDia . '.%')
            ->pluck('id_modulo')
            ->toArray();

        // Obtener reservas activas para este espacio en este día
        $fechaActual = now()->toDateString();
        $reservasActivas = Reserva::where('id_espacio', $espacioId)
            ->where('fecha_reserva', $fechaActual)
            ->where('estado', 'activa')
            ->get();

        // Crear array de módulos ocupados por reservas
        $modulosOcupadosPorReservas = [];
        foreach ($reservasActivas as $reserva) {
            $horaInicio = $reserva->hora;
            $horaFin = $reserva->hora_salida;

            // Determinar qué módulos cubre esta reserva
            for ($i = 1; $i <= 15; $i++) {
                $moduloCodigo = $codigoDia . '.' . $i;
                $horarioModulo = $this->obtenerHorarioModulo($i, $diaActual);

                if (
                    $horarioModulo &&
                    $horaInicio <= $horarioModulo['fin'] &&
                    $horaFin >= $horarioModulo['inicio']
                ) {
                    $modulosOcupadosPorReservas[] = $moduloCodigo;
                }
            }
        }

        // Combinar planificaciones y reservas activas
        $modulosOcupados = array_merge($planificaciones, $modulosOcupadosPorReservas);
        $modulosOcupados = array_unique($modulosOcupados);

        // Contar módulos consecutivos disponibles desde el módulo actual
        $maxModulos = 0;
        $modulosDisponibles = [];
        $proximaClase = null;

        for ($i = $moduloActual; $i <= 15; $i++) {
            $moduloCodigo = $codigoDia . '.' . $i;

            // Si existe planificación o reserva para este módulo, terminar
            if (in_array($moduloCodigo, $modulosOcupados)) {
                // Encontrar información de la próxima clase
                if (in_array($moduloCodigo, $planificaciones)) {
                    $proximaClase = $this->obtenerInfoProximaClase($moduloCodigo, $espacioId);
                }
                break;
            }

            $modulosDisponibles[] = $i;
            $maxModulos++;
        }

        // Verificar si hay clases próximas (dentro de 2 módulos)
        $clasesProximas = [];
        for ($i = $moduloActual + $maxModulos; $i <= min(15, $moduloActual + $maxModulos + 2); $i++) {
            $moduloCodigo = $codigoDia . '.' . $i;
            if (in_array($moduloCodigo, $planificaciones)) {
                $clasesProximas[] = $this->obtenerInfoProximaClase($moduloCodigo, $espacioId);
            }
        }

        // Construir detalle por módulo con horario inicio/fin
        $modulosDetalle = [];
        foreach ($modulosDisponibles as $m) {
            $horario = $this->obtenerHorarioModulo($m, $diaActual);
            $modulosDetalle[] = [
                'modulo' => $m,
                'inicio' => $horario['inicio'] ?? null,
                'fin' => $horario['fin'] ?? null
            ];
        }

        return response()->json([
            'success' => true,
            'max_modulos' => $maxModulos,
            'modulo_actual' => $moduloActual,
            'codigo_dia' => $codigoDia,
            'modulos_disponibles' => $modulosDisponibles,
            'modulos_detalle' => $modulosDetalle,
            'proxima_clase' => $proximaClase,
            'clases_proximas' => $clasesProximas,
            'detalles' => [
                'planificaciones_encontradas' => count($planificaciones),
                'reservas_activas' => count($reservasActivas),
                'modulos_ocupados' => count($modulosOcupados)
            ]
        ]);

        // Log final para debugging

    }

    /**
     * Determina el módulo actual según la hora y día
     */


    /**
     * Obtiene el horario de un módulo específico
     */


    /**
     * Obtiene información de la próxima clase programada
     */


    /**
     * Descarga el código QR de un espacio individual
     */
    public function downloadQR($id_espacio)
    {
        try {
            $espacio = Espacio::where('id_espacio', $id_espacio)->firstOrFail();
            $qrService = new QRService();

            // Generar el código QR
            $qrPath = $qrService->generateQRForEspacio($espacio->id_espacio);

            // Verificar si el archivo existe
            if (!Storage::disk('public')->exists($qrPath)) {
                return redirect()->back()->with('error', 'No se pudo generar el código QR.');
            }

            // Descargar el archivo usando response()->download con la ruta completa
            // Asumir storage/app/public como disco 'public'
            $fullPath = storage_path('app/public/' . ltrim($qrPath, '/'));
            if (file_exists($fullPath)) {
                return response()->download($fullPath, 'QR_' . $espacio->id_espacio . '.png');
            }
            return redirect()->back()->with('error', 'No se pudo encontrar el archivo QR generado.');

        } catch (\Exception $e) {
            Log::error('Error al descargar QR individual:', [
                'error' => $e->getMessage(),
                'espacio_id' => $id_espacio
            ]);

            return redirect()->back()->with('error', 'Error al descargar el código QR: ' . $e->getMessage());
        }
    }

    /**
     * Descarga todos los códigos QR en un archivo ZIP
     */
    public function downloadAllQR()
    {
        try {
            $espacios = Espacio::all();
            $qrService = new QRService();

            // Crear archivo ZIP temporal
            $zipName = 'QRs_Espacios_' . date('Y-m-d_H-i-s') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipName);

            // Crear directorio temporal si no existe
            if (!file_exists(storage_path('app/temp'))) {
                mkdir(storage_path('app/temp'), 0755, true);
            }

            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
                return redirect()->back()->with('error', 'No se pudo crear el archivo ZIP.');
            }

            foreach ($espacios as $espacio) {
                // Generar QR para cada espacio
                $qrPath = $qrService->generateQRForEspacio($espacio->id_espacio);

                // Verificar si el archivo existe
                if (Storage::disk('public')->exists($qrPath)) {
                    $qrContent = Storage::disk('public')->get($qrPath);
                    $zip->addFromString('QR_' . $espacio->id_espacio . '.png', $qrContent);
                }
            }

            $zip->close();

            // Descargar el archivo ZIP
            return response()->download($zipPath, $zipName)->deleteFileAfterSend();

        } catch (\Exception $e) {
            Log::error('Error al descargar QRs en ZIP:', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al generar el archivo ZIP: ' . $e->getMessage());
        }
    }

    /**
     * Descarga todos los códigos QR en un PDF con formato de grilla
     */
    public function downloadAllQRPdf()
    {
        try {
            // Obtener espacios ordenados y agrupados por piso
            $espacios = Espacio::orderBy('piso_id')->orderBy('id_espacio')->get();
            $espaciosPorPiso = $espacios->groupBy('piso_id');
            $qrService = new QRService();

            // HTML para el PDF
            $html = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <style>
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    body {
                        font-family: Arial, sans-serif;
                        padding: 15px;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .header h1 {
                        font-size: 24px;
                        margin-bottom: 5px;
                        color: #1f2937;
                    }
                    .header p {
                        font-size: 11px;
                        color: #6b7280;
                    }
                    .piso-header {
                        background-color: #f3f4f6;
                        padding: 10px;
                        margin: 20px 0;
                        border-radius: 5px;
                        font-size: 18px;
                        font-weight: bold;
                        color: #1f2937;
                        text-align: center;
                    }
                    .page-break {
                        page-break-before: always;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 15px;
                        table-layout: fixed;
                    }
                    td {
                        border: 1px solid #e5e7eb;
                        text-align: center;
                        padding: 8px;
                        vertical-align: top;
                        width: 20%;
                    }
                    .empty-td {
                        border: none;
                    }
                    .qr-container {
                        height: auto;
                    }
                    .qr-image {
                        width: 90px;
                        height: 90px;
                        margin: 0 auto 6px;
                        border: 1px solid #e5e7eb;
                        padding: 3px;
                        background: white;
                    }
                    .qr-id {
                        font-weight: bold;
                        font-size: 10px;
                        color: #1f2937;
                        word-break: break-word;
                        margin-bottom: 2px;
                    }
                    .qr-name {
                        font-size: 8px;
                        color: #6b7280;
                        margin-top: 2px;
                        max-width: 100%;
                        overflow: hidden;
                        text-overflow: ellipsis;
                    }
                </style>
            </head>
            <body>';

            $firstPiso = true;

            foreach ($espaciosPorPiso as $pisoId => $espaciosDelPiso) {
                if (!$firstPiso) {
                    $html .= '<div class="page-break"></div>';
                }
                
                $html .= '<div class="header">';
                if ($firstPiso) {
                    $html .= '<h1>Códigos QR de Espacios</h1>
                              <p>Códigos QR generados por piso</p>
                              <p>Generado: ' . now()->format('d/m/Y H:i:s') . '</p>';
                }
                $html .= '</div>';
                
                $nombrePiso = $pisoId ? 'Piso ' . $pisoId : 'Sin piso asignado';
                $html .= '<div class="piso-header">' . htmlspecialchars($nombrePiso) . '</div>';
                
                $html .= '<table>';
                $count = 0;
                $itemsPerRow = 5;

                foreach ($espaciosDelPiso as $espacio) {
                    // Iniciar nueva fila
                    if ($count % $itemsPerRow == 0) {
                        if ($count > 0) {
                            $html .= '</tr>';
                        }
                        $html .= '<tr>';
                    }

                    // Generar QR para cada espacio
                    $qrPath = $qrService->generateQRForEspacio($espacio->id_espacio);

                    // Verificar si el archivo existe
                    if (Storage::disk('public')->exists($qrPath)) {
                        $qrContent = Storage::disk('public')->get($qrPath);
                        $base64QR = base64_encode($qrContent);

                        $html .= '<td class="qr-container">
                            <img src="data:image/png;base64,' . $base64QR . '" class="qr-image" alt="QR ' . $espacio->id_espacio . '">
                            <div class="qr-id">' . htmlspecialchars($espacio->id_espacio) . '</div>
                            <div class="qr-name">' . htmlspecialchars($espacio->nombre_espacio ?? '') . '</div>
                        </td>';

                        $count++;
                    }
                }

                // Rellenar las celdas vacías si la última fila no está completa
                $remainder = $count % $itemsPerRow;
                if ($remainder > 0 && $count > 0) {
                    for ($i = 0; $i < ($itemsPerRow - $remainder); $i++) {
                        $html .= '<td class="empty-td"></td>';
                    }
                }
                
                if ($count > 0) {
                    $html .= '</tr>';
                }

                $html .= '</table>';
                $firstPiso = false;
            }

            $html .= '</body>
            </html>';

            // Crear PDF
            $dompdf = new Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Descargar el PDF
            return $dompdf->stream('QRs_Espacios_' . date('Y-m-d_H-i-s') . '.pdf');

        } catch (\Exception $e) {
            Log::error('Error al descargar QRs en PDF:', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene las asignaturas de un profesor para el día actual
     */
    public function getAsignaturasProfesorHoy(Request $request, $runProfesor)
    {
        try {
            $codigoDia = $request->input('codigo_dia', 'LU');

            // Obtener las asignaturas del profesor para el día especificado
            $asignaturas = Planificacion_Asignatura::with(['asignatura', 'modulo'])
                ->whereHas('asignatura', function ($query) use ($runProfesor) {
                    $query->where('run_profesor', $runProfesor);
                })
                ->where('id_modulo', 'like', $codigoDia . '.%')
                ->get()
                ->map(function ($planificacion) {
                    return [
                        'nombre_asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'Sin asignatura',
                        'codigo_asignatura' => $planificacion->asignatura->codigo_asignatura ?? 'No especificado',
                        'modulo' => $planificacion->modulo->id_modulo ?? 'No especificado',
                        'hora_inicio' => $planificacion->modulo->hora_inicio ?? '',
                        'hora_termino' => $planificacion->modulo->hora_termino ?? ''
                    ];
                });

            return response()->json([
                'success' => true,
                'asignaturas' => $asignaturas,
                'total' => $asignaturas->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener asignaturas del profesor:', [
                'run_profesor' => $runProfesor,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener las asignaturas del profesor',
                'asignaturas' => []
            ], 500);
        }
    }

    public function getInformacionDetalladaEspacio($idEspacio)
    {
        try {
            $cacheKey = "espacio_info_{$idEspacio}";
            $cachedData = $this->safeGet($cacheKey);
            $cacheTime = $this->safeGet("{$cacheKey}_time", 0);

            if ($cachedData && ((time() - $cacheTime) < 30)) {
                return response()->json($cachedData);
            }

            $espacio = Espacio::where('id_espacio', $idEspacio)->first();
            if (!$espacio) {
                return response()->json(['success' => false, 'mensaje' => 'Espacio no encontrado'], 404);
            }

            $horaActual = now()->format('H:i:s');
            $fechaActual = now()->format('Y-m-d');
            $diaActual = strtolower(now()->format('l'));
            // Convertir a nombres de días en español (como están en la BD)
            $diasEnEspanol = [
                'monday' => 'lunes',
                'tuesday' => 'martes',
                'wednesday' => 'miércoles',
                'thursday' => 'jueves',
                'friday' => 'viernes',
                'saturday' => 'sábado',
                'sunday' => 'domingo'
            ];
            $codigoDia = $diasEnEspanol[$diaActual] ?? 'lunes';

            // ─── BATCH: todas las consultas necesarias en una sola carga ───────────────
            // 1. Reserva activa + vencida del día (una sola query con orWhere)
            $reservaHoy = Reserva::select('id_reserva', 'run_profesor', 'run_solicitante', 'hora', 'hora_salida', 'estado', 'tipo_reserva', 'id_asignatura')
                ->with([
                    'asignatura:id_asignatura,nombre_asignatura',
                    'profesor:run_profesor,name',
                    'solicitante:run_solicitante,nombre,correo,telefono,tipo_solicitante,activo,fecha_registro',
                ])
                ->where('id_espacio', $idEspacio)
                ->where('fecha_reserva', $fechaActual)
                ->whereIn('estado', ['activa', 'programada'])
                ->orderByRaw("FIELD(estado, 'activa', 'programada')")
                ->orderBy('hora', 'asc')
                ->get();

            // 2. Planificación actual del día (una query)
            $planActual = Planificacion_Asignatura::with(['asignatura:id_asignatura,nombre_asignatura', 'modulo', 'horario.profesor'])
                ->where('id_espacio', $idEspacio)
                ->whereHas('modulo', fn($q) => $q->where('dia', $codigoDia)
                    ->where('hora_inicio', '<=', $horaActual)
                    ->where('hora_termino', '>', $horaActual))
                ->first();

            // 3. Próxima planificación (una query)
            $planProxima = Planificacion_Asignatura::with(['asignatura:id_asignatura,nombre_asignatura', 'modulo', 'horario.profesor'])
                ->where('id_espacio', $idEspacio)
                ->whereHas('modulo', fn($q) => $q->where('dia', $codigoDia)->where('hora_inicio', '>', $horaActual))
                ->join('modulos', 'planificacion_asignaturas.id_modulo', '=', 'modulos.id_modulo')
                ->orderBy('modulos.hora_inicio', 'asc')
                ->select('planificacion_asignaturas.*')
                ->first();

            // 4. Planificación anterior (una query)
            $planAnterior = Planificacion_Asignatura::with(['asignatura:id_asignatura,nombre_asignatura', 'modulo', 'horario.profesor'])
                ->where('id_espacio', $idEspacio)
                ->whereHas('modulo', fn($q) => $q->where('dia', $codigoDia)->where('hora_termino', '<=', $horaActual))
                ->join('modulos', 'planificacion_asignaturas.id_modulo', '=', 'modulos.id_modulo')
                ->orderBy('modulos.hora_termino', 'desc')
                ->select('planificacion_asignaturas.*')
                ->first();

            // 5. Reserva finalizada anterior (una query)
            $reservaAnterior = Reserva::select('id_reserva', 'run_profesor', 'hora', 'hora_salida', 'id_asignatura', 'tipo_reserva')
                ->with(['asignatura:id_asignatura,nombre_asignatura', 'profesor:run_profesor,name'])
                ->where('id_espacio', $idEspacio)
                ->where('fecha_reserva', $fechaActual)
                ->where('hora', '<', $horaActual)
                ->where('estado', 'finalizada')
                ->orderBy('hora', 'desc')
                ->first();
            // ─────────────────────────────────────────────────────────────────────────

            $response = [
                'success' => true,
                'tipo_ocupacion' => 'libre',
                'nombre' => null,
                'asignatura' => null,
                'hora_inicio' => null,
                'hora_salida' => null,
                'tipo_reserva' => null,
                'detalles' => null,
                'proxima_clase' => null,
                'clase_anterior' => null,
            ];

            // ── Determinar ocupante actual ────────────────────────────────────────────
            // Prioridad: reserva activa en curso > reserva activa vencida > reserva programada > planificación
            $reservaActiva = $reservaHoy->filter(fn($r) => $r->estado === 'activa')
                ->first(
                    fn($r) =>
                    $r->hora <= $horaActual && ($r->hora_salida === null || $r->hora_salida > $horaActual)
                );

            // Reserva activa vencida (fallback)
            if (!$reservaActiva) {
                $reservaActiva = $reservaHoy->filter(fn($r) => $r->estado === 'activa' && $r->hora_salida !== null && $r->hora_salida <= $horaActual)->first();
            }

            if ($reservaActiva) {
                if ($reservaActiva->run_profesor) {
                    $response = $this->obtenerInformacionProfesorConDatos($reservaActiva, $horaActual, $codigoDia);
                } elseif ($reservaActiva->run_solicitante) {
                    $response = $this->obtenerInformacionSolicitanteConDatos($reservaActiva);
                } else {
                    $response = [
                        'success' => true,
                        'tipo_ocupacion' => 'ocupado_sin_info',
                        'nombre' => 'No especificado',
                        'tipo_reserva' => 'Reserva sin usuario',
                        'asignatura' => null,
                        'hora_inicio' => $reservaActiva->hora,
                        'hora_salida' => $reservaActiva->hora_salida,
                        'run_profesor' => null,
                        'run_solicitante' => null,
                        'id_reserva' => $reservaActiva->id_reserva,
                    ];
                }
            } else {
                // Buscar reserva programada en curso
                $reservaProgramada = $reservaHoy->filter(fn($r) => $r->estado === 'programada')
                    ->first(
                        fn($r) =>
                        $r->hora <= $horaActual && ($r->hora_salida === null || $r->hora_salida > $horaActual)
                    );

                if ($reservaProgramada) {
                    if ($reservaProgramada->run_profesor) {
                        $response = $this->obtenerInformacionProfesorConDatos($reservaProgramada, $horaActual, $codigoDia);
                    } else {
                        $response = $this->obtenerInformacionSolicitanteConDatos($reservaProgramada);
                    }
                    $response['tipo_reserva'] = 'programada';
                } elseif ($planActual) {
                    $response = [
                        'success' => true,
                        'tipo_ocupacion' => 'profesor',
                        'nombre' => $planActual->horario->profesor->name ?? 'Profesor no asignado',
                        'run_profesor' => $planActual->horario->profesor->run_profesor ?? null,
                        'asignatura' => $planActual->asignatura->nombre_asignatura ?? 'Sin asignatura',
                        'hora_inicio' => $planActual->modulo->hora_inicio,
                        'hora_salida' => $planActual->modulo->hora_termino,
                        'tipo_reserva' => 'clase_regular',
                    ];
                }
            }

            // Fallback: cualquier reserva del día
            if ($response['tipo_ocupacion'] === 'libre') {
                $fallback = $reservaHoy->first();
                if ($fallback) {
                    if ($fallback->run_profesor) {
                        $response = $this->obtenerInformacionProfesorConDatos($fallback, $horaActual, $codigoDia);
                    } elseif ($fallback->run_solicitante) {
                        $response = $this->obtenerInformacionSolicitanteConDatos($fallback);
                    } else {
                        $response = [
                            'success' => true,
                            'tipo_ocupacion' => 'ocupado_sin_info',
                            'nombre' => 'No especificado',
                            'tipo_reserva' => $fallback->tipo_reserva,
                            'asignatura' => null,
                            'hora_inicio' => $fallback->hora,
                            'hora_salida' => $fallback->hora_salida,
                            'run_profesor' => null,
                            'run_solicitante' => null,
                        ];
                    }
                    // Asegurar que se mantengan los campos adicionales de la reserva
                    $response['estado_reserva'] = $fallback->estado;
                    $response['id_reserva'] = $fallback->id_reserva;
                }
            }

            // ── Próxima clase ────────────────────────────────────────────────────────
            $proxFuturaReserva = $reservaHoy->filter(fn($r) => $r->hora > $horaActual && $r->tipo_reserva !== 'espontanea')->first();
            if ($proxFuturaReserva) {
                $response['proxima_clase'] = [
                    'asignatura' => $proxFuturaReserva->asignatura?->nombre_asignatura ?? 'Reserva sin asignatura',
                    'profesor' => $proxFuturaReserva->profesor?->name ?? 'No especificado',
                    'profesor_run' => $proxFuturaReserva->run_profesor ?? null,
                    'hora_inicio' => $proxFuturaReserva->hora,
                    'hora_termino' => $proxFuturaReserva->hora_salida,
                ];
            } elseif ($planProxima) {
                $response['proxima_clase'] = [
                    'asignatura' => $planProxima->asignatura->nombre_asignatura ?? 'Sin asignatura',
                    'profesor' => $planProxima->horario->profesor->name ?? 'No especificado',
                    'profesor_run' => $planProxima->horario->profesor->run_profesor ?? null,
                    'hora_inicio' => $planProxima->modulo->hora_inicio ?? null,
                    'hora_termino' => $planProxima->modulo->hora_termino ?? null,
                ];
            }

            // ── Clase anterior ───────────────────────────────────────────────────────
            if ($reservaAnterior) {
                $esEspontanea = $reservaAnterior->tipo_reserva === 'espontanea';
                $response['clase_anterior'] = [
                    'asignatura' => $esEspontanea ? 'Reserva espontánea' : ($reservaAnterior->asignatura?->nombre_asignatura ?? 'Reserva sin asignatura'),
                    'profesor' => $reservaAnterior->profesor?->name ?? 'No especificado',
                    'profesor_run' => $reservaAnterior->run_profesor ?? null,
                    'hora_inicio' => $reservaAnterior->hora ?? null,
                    'hora_termino' => $reservaAnterior->hora_salida ?? null,
                ];
            } elseif ($planAnterior) {
                $response['clase_anterior'] = [
                    'asignatura' => $planAnterior->asignatura->nombre_asignatura ?? 'Sin asignatura',
                    'profesor' => $planAnterior->horario->profesor->name ?? 'No especificado',
                    'profesor_run' => $planAnterior->horario->profesor->run_profesor ?? null,
                    'hora_inicio' => $planAnterior->modulo->hora_inicio ?? null,
                    'hora_termino' => $planAnterior->modulo->hora_termino ?? null,
                ];
            }

            $this->safeCache($cacheKey, $response, 30);
            $this->safeCache("{$cacheKey}_time", time(), 30);

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error("Error al obtener información del espacio {$idEspacio}:", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'mensaje' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Obtiene información de un profesor usando datos ya cargados en la reserva (sin query extra)
     */


    /**
     * Obtiene información de un solicitante usando datos ya cargados en la reserva (sin query extra)
     */


    /**

     * Obtiene información de un profesor
     */


    /**
     * Obtiene información de la planificación actual del espacio (Regular o Colaborador)
     */


    /**
     * Obtiene información de un solicitante con cache optimizado
     */


    /**
     * Construye la respuesta para solicitantes
     */


    /**
     * Obtiene información de la próxima clase
     */


}

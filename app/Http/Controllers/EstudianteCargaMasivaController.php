<?php

namespace App\Http\Controllers;

use App\Helpers\SemesterHelper;
use App\Models\Asignatura;
use App\Models\Estudiante;
use App\Models\Sede;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class EstudianteCargaMasivaController extends Controller
{
    public function index()
    {
        // Solo Superadmin o Administrador
        if (!auth()->user()?->is_superuser && !auth()->user()?->hasRole(['Super Admin', 'Administrador'])) {
            abort(403, 'Acceso restringido únicamente para Superadministrador.');
        }

        $tenants = Tenant::where('is_active', true)->with('sede')->get();
        $periodoActual = SemesterHelper::getCurrentPeriod();

        return view('estudiantes.carga-masiva', compact('tenants', 'periodoActual'));
    }

    public function procesar(Request $request)
    {
        if (!auth()->user()?->is_superuser && !auth()->user()?->hasRole(['Super Admin', 'Administrador'])) {
            abort(403, 'Acceso restringido únicamente para Superadministrador.');
        }

        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv,txt|max:15360',
            'sede_defecto' => 'nullable|string',
            'periodo' => 'nullable|string',
        ]);

        $archivo = $request->file('archivo');
        $periodo = $request->input('periodo') ?: SemesterHelper::getCurrentPeriod();
        $sedeDefectoId = $request->input('sede_defecto');

        try {
            $spreadsheet = IOFactory::load($archivo->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray(null, true, true, true);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al leer el archivo Excel: ' . $e->getMessage());
        }

        if (empty($rows) || count($rows) < 2) {
            return back()->with('error', 'El archivo no contiene filas de datos para procesar.');
        }

        // 1. Detectar cabeceras en la Fila 1
        $headerRow = array_shift($rows);
        $colMap = [];
        foreach ($headerRow as $colLetter => $val) {
            $valClean = strtoupper(trim((string)$val));
            $valNorm = preg_replace('/[^A-Z0-9_]/', '', str_replace(['Á','É','Í','Ó','Ú','Ñ'], ['A','E','I','O','U','N'], $valClean));

            if (in_array($valNorm, ['RUN', 'RUT', 'DNI', 'CEDULA'])) {
                $colMap['run'] = $colLetter;
            } elseif (in_array($valNorm, ['NOMBRE', 'NOMBRES', 'APELLIDOSYNOMBRES', 'ALUMNO', 'ESTUDIANTE'])) {
                $colMap['nombre'] = $colLetter;
            } elseif (in_array($valNorm, ['CORREO', 'EMAIL', 'MAIL', 'CORREOINSTITUCIONAL'])) {
                $colMap['correo'] = $colLetter;
            } elseif (in_array($valNorm, ['CARRERA', 'ID_CARRERA', 'NOMBRECARRERA', 'PLAN'])) {
                $colMap['carrera'] = $colLetter;
            } elseif (in_array($valNorm, ['COD_ASIGNATURA', 'CODASIGNATURA', 'ASIGNATURA', 'CURSO', 'NRC', 'ID_ASIGNATURA'])) {
                $colMap['asignatura'] = $colLetter;
            } elseif (in_array($valNorm, ['SEDE', 'CAMPUS', 'TENANT'])) {
                $colMap['sede'] = $colLetter;
            }
        }

        if (!isset($colMap['run'])) {
            return back()->with('error', 'No se encontró la columna obligatoria "RUN" en el encabezado del archivo.');
        }

        // Cache de Tenants por Sede o Nombre para resolver rápidamente
        $allTenants = Tenant::with('sede')->get();
        $tenantsPorNombre = [];
        $tenantsPorIdSede = [];
        foreach ($allTenants as $t) {
            $tenantsPorNombre[strtoupper(trim($t->name))] = $t;
            if ($t->sede) {
                $tenantsPorNombre[strtoupper(trim($t->sede->nombre_sede))] = $t;
                $tenantsPorIdSede[(string)$t->sede_id] = $t;
            }
        }

        $totalLeidos = 0;
        $totalEstudiantesCreados = 0;
        $totalEstudiantesActualizados = 0;
        $totalInscripcionesCreadas = 0;
        $errores = [];

        foreach ($rows as $rowIndex => $row) {
            $totalLeidos++;
            $rawRun = isset($colMap['run']) ? trim((string)$row[$colMap['run']]) : '';
            if (empty($rawRun)) {
                continue;
            }

            $nombre = isset($colMap['nombre']) ? trim((string)$row[$colMap['nombre']]) : '';
            $correo = isset($colMap['correo']) ? trim((string)$row[$colMap['correo']]) : '';
            $carrera = isset($colMap['carrera']) ? trim((string)$row[$colMap['carrera']]) : null;
            $codAsignatura = isset($colMap['asignatura']) ? trim((string)$row[$colMap['asignatura']]) : '';
            $sedeStr = isset($colMap['sede']) ? trim((string)$row[$colMap['sede']]) : '';

            // Normalizar RUN
            $runNormalizado = strtoupper(preg_replace('/[^0-9Kk]/', '', $rawRun));

            // Resolver el Tenant correspondiente
            $tenantObjetivo = null;
            if (!empty($sedeStr)) {
                $sedeUpper = strtoupper($sedeStr);
                $tenantObjetivo = $tenantsPorNombre[$sedeUpper] ?? ($tenantsPorIdSede[$sedeStr] ?? null);
            }
            if (!$tenantObjetivo && $sedeDefectoId) {
                $tenantObjetivo = $allTenants->firstWhere('id', $sedeDefectoId) ?? $allTenants->firstWhere('sede_id', $sedeDefectoId);
            }
            if (!$tenantObjetivo) {
                // Si aún no se resolvió, usar el primer tenant activo disponible
                $tenantObjetivo = $allTenants->firstWhere('is_active', true);
            }

            if (!$tenantObjetivo) {
                $errores[] = "Fila {$rowIndex}: No se pudo determinar el tenant/sede para el RUN {$rawRun}.";
                continue;
            }

            try {
                // Cambiar contexto al tenant de la sede
                $tenantObjetivo->makeCurrent();

                // Resolver el ID de Carrera (la columna en BD tiene max 20 caracteres)
                $idCarreraFinal = null;
                if (!empty($carrera)) {
                    // Si ya es un ID corto (<= 20 caracteres)
                    if (strlen($carrera) <= 20) {
                        $idCarreraFinal = $carrera;
                    } else {
                        // Buscar por nombre de carrera en la tabla carreras del tenant
                        $carreraModel = \App\Models\Carrera::where('nombre', 'like', '%' . trim($carrera) . '%')
                            ->orWhere('nombre', 'like', '%' . substr(trim($carrera), 0, 15) . '%')
                            ->first();

                        if ($carreraModel) {
                            $idCarreraFinal = (string)$carreraModel->id_carrera;
                        } else {
                            $idCarreraFinal = substr(trim($carrera), 0, 20);
                        }
                    }
                }

                // Si viene asignatura, intentar heredar el id_carrera de la asignatura si falta
                if (empty($idCarreraFinal) && !empty($codAsignatura)) {
                    $asigCarrera = Asignatura::where('id_asignatura', $codAsignatura)
                        ->orWhere('codigo_asignatura', $codAsignatura)
                        ->value('id_carrera');
                    if ($asigCarrera) {
                        $idCarreraFinal = (string)$asigCarrera;
                    }
                }

                // 1. Crear o actualizar estudiante
                $estudiante = Estudiante::where('run', $runNormalizado)->first();
                if (!$estudiante) {
                    $estudiante = Estudiante::create([
                        'run' => $runNormalizado,
                        'nombre' => $nombre ?: ('Estudiante ' . $runNormalizado),
                        'email' => $correo ?: null,
                        'id_carrera' => $idCarreraFinal,
                        'origen' => 'carga_masiva',
                    ]);
                    $totalEstudiantesCreados++;
                } else {
                    $cambios = [];
                    if (!empty($nombre) && $estudiante->nombre !== $nombre) $cambios['nombre'] = $nombre;
                    if (!empty($correo) && $estudiante->email !== $correo) $cambios['email'] = $correo;
                    if (!empty($carrera) && $idCarreraFinal && $estudiante->id_carrera !== $idCarreraFinal) $cambios['id_carrera'] = $idCarreraFinal;
                    if (!empty($cambios)) {
                        $estudiante->update($cambios);
                        $totalEstudiantesActualizados++;
                    }
                }

                // 2. Si se especificó asignatura, inscribir al estudiante
                if (!empty($codAsignatura)) {
                    // Buscar asignatura por id_asignatura o por codigo_asignatura
                    $asig = Asignatura::where('id_asignatura', $codAsignatura)
                        ->orWhere('codigo_asignatura', $codAsignatura)
                        ->first();

                    if ($asig) {
                        $yaInscrito = DB::connection('tenant')->table('inscripciones')
                            ->where('id_asignatura', $asig->id_asignatura)
                            ->where('estudiante_id', $estudiante->id)
                            ->exists();

                        if (!$yaInscrito) {
                            DB::connection('tenant')->table('inscripciones')->insert([
                                'id_asignatura' => $asig->id_asignatura,
                                'estudiante_id' => $estudiante->id,
                                'periodo' => $periodo,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                            $totalInscripcionesCreadas++;
                        }
                    } else {
                        Log::info("[CargaEstudiantes] Asignatura no encontrada '{$codAsignatura}' en tenant {$tenantObjetivo->name} para el alumno {$runNormalizado}");
                    }
                }
            } catch (\Throwable $e) {
                $errores[] = "Fila {$rowIndex} (RUN {$rawRun}): " . $e->getMessage();
            }
        }

        return back()->with('resumen', [
            'total_leidos' => $totalLeidos,
            'creados' => $totalEstudiantesCreados,
            'actualizados' => $totalEstudiantesActualizados,
            'inscripciones' => $totalInscripcionesCreadas,
            'errores' => $errores,
        ]);
    }

    public function descargarPlantilla()
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_carga_estudiantes.csv"',
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            // BOM para compatibilidad con Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados
            fputcsv($handle, ['RUN', 'Nombre', 'Correo', 'Carrera', 'COD_ASIGNATURA', 'Sede'], ';');

            // Filas de ejemplo
            fputcsv($handle, ['21655430', 'AGUIRRE SANTIBÁÑEZ, SAYEN MILLARAY', 'saguirres@it.ucsc.cl', 'INGENIERÍA DE EJECUCIÓN EN INFORMÁTICA', '573259', 'Talcahuano'], ';');
            fputcsv($handle, ['19814651', 'AGURTO CAMACHO, SEBASTIÁN LEONARDO', 'sagurtoc@it.ucsc.cl', 'INGENIERÍA DE EJECUCIÓN EN INFORMÁTICA', '573259', 'Talcahuano'], ';');
            fputcsv($handle, ['22220848', 'BUSTOS GATICA, DIEGO ALONSO', 'dbustosga@it.ucsc.cl', 'INGENIERÍA DE EJECUCIÓN EN INFORMÁTICA', '573259', 'Talcahuano'], ';');

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

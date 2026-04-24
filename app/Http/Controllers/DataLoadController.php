<?php

namespace App\Http\Controllers;

use App\Models\Asignatura;
use App\Models\Carrera;
use App\Models\DataLoad;
use App\Models\Espacio;
use App\Models\Facultad;
use App\Models\Horario;
use App\Models\Modulo;
use App\Models\Piso;
use App\Models\Planificacion_Asignatura;
use App\Models\Profesor;
use App\Models\Sede;
use App\Services\QRService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DataLoadController extends Controller
{
    protected $qrService;

    public function __construct(QRService $qrService)
    {
        $this->qrService = $qrService;
    }

    public function index(Request $request)
    {
        $semestreFiltro = $request->input('semestre');
        $anioFiltro = $request->input('anio');

        $periodosDisponibles = Horario::select('periodo')
            ->whereNotNull('periodo')
            ->where('periodo', '!=', '')
            ->distinct()
            ->pluck('periodo')
            ->sort()
            ->values();

        $aniosDisponibles = [];
        $semestresDisponibles = [];

        foreach ($periodosDisponibles as $periodo) {
            if (preg_match('/^(\d{4})-(\d+)$/', $periodo, $matches)) {
                $anio = $matches[1];
                $semestre = $matches[2];

                if (!in_array($anio, $aniosDisponibles)) {
                    $aniosDisponibles[] = $anio;
                }

                if (!in_array($semestre, $semestresDisponibles)) {
                    $semestresDisponibles[] = $semestre;
                }
            }
        }

        sort($aniosDisponibles);
        sort($semestresDisponibles);

        $query = DataLoad::latest();

        if ($semestreFiltro && $anioFiltro) {
            $periodoFiltro = $anioFiltro . '-' . $semestreFiltro;

            $query->whereHas('profesor.horarios', function ($q) use ($periodoFiltro) {
                $q->where('periodo', $periodoFiltro);
            });
        } elseif ($anioFiltro) {
            $query->whereHas('profesor.horarios', function ($q) use ($anioFiltro) {
                $q->where('periodo', 'like', $anioFiltro . '-%');
            });
        }

        $dataLoads = $query->paginate(10);

        return view('layouts.data.data_index', compact(
            'dataLoads',
            'aniosDisponibles',
            'semestresDisponibles',
            'semestreFiltro',
            'anioFiltro'
        ));
    }

    public function upload(Request $request)
    {
        set_time_limit(300);

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'semestre_selector' => 'required|in:1,2'
        ]);

        $semestreSeleccionado = $request->input('semestre_selector');
        // Usar el año académico configurado en lugar de la fecha del sistema
        $anioActual = \App\Helpers\SemesterHelper::getCurrentAcademicYear();
        $periodoSeleccionado = $anioActual . '-' . $semestreSeleccionado;

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileExtension = $file->getClientOriginalExtension();

            $uniqueFileName = date('Y-m-d_His') . '_' . Auth::user()->run . '_' . Str::random(10) . '.' . $fileExtension;
            $path = $file->storeAs('datos_subidos', $uniqueFileName, 'public');

            $dataLoad = DataLoad::create([
                'nombre_archivo' => $fileName,
                'ruta_archivo' => $path,
                'tipo_carga' => $fileExtension,
                'registros_cargados' => 0,
                'estado' => 'pendiente',
                'user_run' => Auth::user()->run
            ]);

            $rows = Excel::toArray([], $file)[0];
            $processedUsersCount = 0;
            $processedAsignaturasCount = 0;
            $processedHorariosCount = 0;
            $errors = [];
            $skippedRows = 0;
            $espaciosOtrosTenants = 0;  // Espacios que no pertenecen a este tenant
            $espaciosNoEncontrados = 0;  // Espacios del tenant que no existen en BD
            $espaciosFaltantes = [];  // Lista de espacios TH- que no se encontraron en BD

            // Actualizar estado inicial
            $dataLoad->update([
                'estado' => 'procesando',
                'registros_cargados' => 0
            ]);

            // LIMPIEZA PREVIA: Eliminar planificaciones del período seleccionado
            Log::info('Iniciando limpieza previa del período: ' . $periodoSeleccionado);
            $horariosDelPeriodo = Horario::where('periodo', $periodoSeleccionado)->pluck('id_horario');
            $planificacionesEliminadas = Planificacion_Asignatura::whereIn('id_horario', $horariosDelPeriodo)->delete();
            Log::info('Planificaciones eliminadas del período ' . $periodoSeleccionado . ': ' . $planificacionesEliminadas);

            // GARANTIZAR QUE EXISTAN MÓDULOS: Sin ellos la FK falla y 0 planificaciones se crean
            $modulosExistentes = Modulo::count();
            if ($modulosExistentes === 0) {
                Log::info('⚠ Tabla modulos VACÍA - Creando módulos automáticamente...');
                $dias = [
                    'LU' => 'lunes',
                    'MA' => 'martes',
                    'MI' => 'miércoles',
                    'JU' => 'jueves',
                    'VI' => 'viernes',
                    'SA' => 'sábado',
                ];
                $modulosBase = [
                    ['hora_inicio' => '08:10', 'hora_termino' => '09:00'],
                    ['hora_inicio' => '09:10', 'hora_termino' => '10:00'],
                    ['hora_inicio' => '10:10', 'hora_termino' => '11:00'],
                    ['hora_inicio' => '11:10', 'hora_termino' => '12:00'],
                    ['hora_inicio' => '12:10', 'hora_termino' => '13:00'],
                    ['hora_inicio' => '13:10', 'hora_termino' => '14:00'],
                    ['hora_inicio' => '14:10', 'hora_termino' => '15:00'],
                    ['hora_inicio' => '15:10', 'hora_termino' => '16:00'],
                    ['hora_inicio' => '16:10', 'hora_termino' => '17:00'],
                    ['hora_inicio' => '17:10', 'hora_termino' => '18:00'],
                    ['hora_inicio' => '18:10', 'hora_termino' => '19:00'],
                    ['hora_inicio' => '19:10', 'hora_termino' => '20:00'],
                    ['hora_inicio' => '20:10', 'hora_termino' => '21:00'],
                    ['hora_inicio' => '21:10', 'hora_termino' => '22:00'],
                    ['hora_inicio' => '22:10', 'hora_termino' => '23:00'],
                ];
                $modulosCreados = 0;
                foreach ($dias as $codigoDia => $nombreDia) {
                    // Sábado solo tiene módulos 1-5 (hasta 13:00hrs)
                    $maxModulos = ($codigoDia === 'SA') ? 5 : 15;

                    foreach ($modulosBase as $idx => $mod) {
                        $numeroModulo = $idx + 1;
                        if ($numeroModulo > $maxModulos) {
                            break;  // Skip módulos > 5 para sábado
                        }

                        $idModulo = $codigoDia . '.' . $numeroModulo;
                        Modulo::firstOrCreate(
                            ['id_modulo' => $idModulo],
                            ['dia' => $nombreDia, 'hora_inicio' => $mod['hora_inicio'], 'hora_termino' => $mod['hora_termino']]
                        );
                        $modulosCreados++;
                    }
                }
                Log::info("✓ Módulos creados: {$modulosCreados} (LU-VI: 15 módulos, SA: 5 módulos)");
            } else {
                Log::info("✓ Módulos existentes en BD: {$modulosExistentes}");
            }

            // PRESERVAR ESPACIOS DEL SEEDER: Solo eliminar espacios SIN piso_id válido (creados por cargas anteriores)
            // Los espacios con piso_id configurado vienen del seeder y deben mantenerse
            $tenant = \App\Models\Tenant::current();
            if ($tenant) {
                // Contar espacios del seeder (con piso_id configurado)
                $espaciosSeeder = Espacio::whereNotNull('piso_id')->count();
                // Eliminar solo espacios sin piso_id (generados por cargas masivas previas)
                $espaciosEliminados = Espacio::whereNull('piso_id')->delete();
                Log::info('Espacios del seeder preservados: ' . $espaciosSeeder);
                Log::info('Espacios sin piso eliminados del tenant ' . $tenant->domain . ': ' . $espaciosEliminados);
            }

            // Obtener la sede actual del tenant
            $sedeActual = $tenant ? Sede::find($tenant->sede_id) : null;
            $nombreSedeActual = $sedeActual ? strtolower(trim($sedeActual->nombre_sede)) : null;

            // Obtener la primera facultad de la sede para crear carreras automáticamente si es necesario
            $facultadDeLaSede = $sedeActual ? Facultad::where('id_sede', $sedeActual->id_sede)->first() : null;

            // LOGS DETALLADOS DE CONFIGURACIÓN DEL TENANT
            Log::info('═══════════════════════════════════════════════════════════');
            Log::info('🔧 CONFIGURACIÓN DEL TENANT PARA IMPORTACIÓN');
            Log::info('  → Tenant ID: ' . ($tenant ? $tenant->id : 'NO CONFIGURADO'));
            Log::info('  → Prefijo espacios (raw): "' . ($tenant && $tenant->prefijo_espacios ? $tenant->prefijo_espacios : 'NO CONFIGURADO') . '"');
            Log::info('  → Prefijo espacios (normalizado): "' . ($tenant && $tenant->prefijo_espacios ? strtoupper(trim($tenant->prefijo_espacios)) : 'NO CONFIGURADO') . '"');
            Log::info('  → Sede ID: ' . ($tenant ? $tenant->sede_id : 'N/A'));
            Log::info('  → Nombre sede: ' . ($nombreSedeActual ?? 'N/A'));
            Log::info('  → Filtro espacios: ' . ($tenant && $tenant->prefijo_espacios ? strtoupper(trim($tenant->prefijo_espacios)) . '-*' : 'NO CONFIGURADO'));
            Log::info('═══════════════════════════════════════════════════════════');

            // Si no existe facultad, crear una genérica para la sede
            if ($sedeActual && !$facultadDeLaSede) {
                try {
                    $facultadDeLaSede = Facultad::create([
                        'id_facultad' => $sedeActual->id_sede . '_FAC',
                        'nombre_facultad' => 'Facultad de ' . $sedeActual->nombre_sede,
                        'id_sede' => $sedeActual->id_sede,
                        'id_universidad' => $sedeActual->id_universidad,
                    ]);
                    Log::info('Facultad genérica creada: ' . $facultadDeLaSede->id_facultad);
                } catch (\Exception $e) {
                    Log::error('No se pudo crear facultad genérica: ' . $e->getMessage());
                }
            }

            // IMPORTANTE: Los espacios YA DEBEN EXISTIR en la BD (creados manualmente o por seeder)
            // No se crean espacios durante la importación, solo se usan los existentes
            Log::info('✓ Espacios existentes en BD del tenant: ' . Espacio::count() . ' (no se crearán nuevos)');

            Log::info('→ Iniciando procesamiento de ' . (count($rows) - 1) . ' filas de datos...');

            foreach ($rows as $index => $row) {
                if ($index === 0) {
                    continue;  // Saltar encabezados
                }

                try {
                    $sede = $row[7];

                    // Filtrar por sede actual del tenant (dinámico)
                    if ($nombreSedeActual && strtolower(trim($sede)) !== $nombreSedeActual) {
                        $skippedRows++;
                        continue;
                    }

                    $idCarrera = $row[17];

                    // Durante la inicialización o si la carrera no existe, intentar crearla o usar una genérica
                    $carrera = Carrera::find($idCarrera);
                    if (!$carrera) {
                        // Intentar crear una carrera genérica para esta sede
                        $nombreCarrera = isset($row[18]) && !empty($row[18]) ? $row[18] : 'Carrera ' . $idCarrera;

                        // Necesitamos un área académica genérica para esta facultad
                        if ($facultadDeLaSede) {
                            $areaAcademicaGenerica = \App\Models\AreaAcademica::where('id_facultad', $facultadDeLaSede->id_facultad)->first();

                            if (!$areaAcademicaGenerica) {
                                // Crear un área académica genérica para esta facultad
                                try {
                                    $areaAcademicaGenerica = \App\Models\AreaAcademica::create([
                                        'id_area_academica' => $facultadDeLaSede->id_facultad . '_AA',
                                        'nombre_area_academica' => 'Área Académica de ' . $facultadDeLaSede->nombre_facultad,
                                        'tipo_area_academica' => 'escuela',
                                        'id_facultad' => $facultadDeLaSede->id_facultad,
                                    ]);
                                    Log::info('Área académica genérica creada: ' . $areaAcademicaGenerica->id_area_academica);
                                } catch (\Exception $e) {
                                    Log::warning('Error al crear área académica: ' . $e->getMessage());
                                }
                            }
                        }

                        try {
                            $carrera = Carrera::create([
                                'id_carrera' => $idCarrera,
                                'nombre' => $nombreCarrera,
                                'id_area_academica' => isset($areaAcademicaGenerica) ? $areaAcademicaGenerica->id_area_academica : null,
                            ]);
                            Log::info('Carrera creada automáticamente: ' . $idCarrera . ' - ' . $nombreCarrera);
                        } catch (\Exception $e) {
                            $errors[] = 'Fila ' . ($index + 1) . ': No se pudo crear la carrera ' . $idCarrera;
                            Log::warning('Error al crear carrera ' . $idCarrera . ': ' . $e->getMessage());
                            continue;
                        }
                    }

                    $run = $row[11];
                    $name = $row[12];
                    $email = $row[13];
                    $tipoProfesor = $row[16];
                    $existingProfesor = Profesor::where('run_profesor', $run)->first();

                    if ($existingProfesor) {
                        // ACTUALIZACIÓN COMPLETA del profesor
                        $existingProfesor->update([
                            'name' => $name,
                            'email' => $email,
                            'id_carrera' => $idCarrera,
                            'tipo_profesor' => $tipoProfesor,  // También actualizar tipo
                            'sede_id' => $sedeActual ? $sedeActual->id_sede : null,
                            'id_facultad' => $facultadDeLaSede ? $facultadDeLaSede->id_facultad : null
                        ]);
                        $processedUsersCount++;
                    } else {
                        // Crear nuevo profesor
                        $profesor = Profesor::create([
                            'run_profesor' => $run,
                            'name' => $name,
                            'email' => $email,
                            'id_carrera' => $idCarrera,
                            'tipo_profesor' => $tipoProfesor,
                            'sede_id' => $sedeActual ? $sedeActual->id_sede : null,
                            'id_facultad' => $facultadDeLaSede ? $facultadDeLaSede->id_facultad : null
                        ]);
                        $processedUsersCount++;
                    }

                    $idAsignatura = $row[0];
                    $codigoAsignatura = $row[1];
                    $nombreAsignatura = preg_replace('/^[a-z]{2}:\s*/i', '', $row[2]);

                    $numeroSeccion = trim($row[3]);  // Columna D
                    $inscritos = isset($row[9]) ? (int) $row[9] : null;  // Columna J - Inscritos

                    // Validar que la sección sea un número de hasta 4 dígitos
                    if (!empty($numeroSeccion) && !preg_match('/^\d{1,4}$/', $numeroSeccion)) {
                        $errors[] = 'Fila ' . ($index + 1) . ': Sección inválida - debe ser un número de 1 a 4 dígitos (valor: ' . $numeroSeccion . ')';
                        continue;
                    }

                    // Si la sección está vacía, asignar un valor por defecto
                    if (empty($numeroSeccion)) {
                        $numeroSeccion = '1';
                    }

                    $existingAsignatura = Asignatura::where('id_asignatura', $idAsignatura)->first();
                    if (!$existingAsignatura) {
                        $asignatura = Asignatura::create([
                            'id_asignatura' => $idAsignatura,
                            'codigo_asignatura' => $codigoAsignatura,
                            'nombre_asignatura' => $nombreAsignatura,
                            'seccion' => $numeroSeccion,
                            'run_profesor' => $run,
                            'id_carrera' => $idCarrera
                        ]);
                    } else {
                        // ACTUALIZACIÓN COMPLETA de la asignatura
                        $existingAsignatura->update([
                            'codigo_asignatura' => $codigoAsignatura,
                            'nombre_asignatura' => $nombreAsignatura,
                            'seccion' => $numeroSeccion,
                            'run_profesor' => $run,
                            'id_carrera' => $idCarrera
                        ]);
                        $asignatura = $existingAsignatura;
                    }

                    $processedAsignaturasCount++;

                    $horarioProfesor = $row[20];
                    $periodo = $periodoSeleccionado;

                    try {
                        $idHorario = 'HOR_' . $run . '_' . $periodo;

                        $existingHorario = Horario::where('id_horario', $idHorario)->first();

                        if (!$existingHorario) {
                            $oldIdHorario = 'HOR_' . $run;
                            $existingHorario = Horario::where('id_horario', $oldIdHorario)->first();

                            if ($existingHorario) {
                                // Migrar horario existente al nuevo formato
                                $existingHorario->id_horario = $idHorario;
                                $existingHorario->periodo = $periodo;
                                $existingHorario->save();

                                // Actualizar planificaciones asociadas
                                Planificacion_Asignatura::where('id_horario', $oldIdHorario)
                                    ->update(['id_horario' => $idHorario]);
                            }
                        }

                        if ($existingHorario) {
                            $horario = $existingHorario;
                            // ACTUALIZACIÓN COMPLETA del horario
                            $horario->update([
                                'nombre' => 'Horario de ' . $name,
                                'periodo' => $periodo,
                                'run_profesor' => $run
                            ]);
                        } else {
                            $horario = new Horario();
                            $horario->id_horario = $idHorario;
                            $horario->nombre = 'Horario de ' . $name;
                            $horario->periodo = $periodo;
                            $horario->run_profesor = $run;

                            if (!$horario->save()) {
                                throw new \Exception('Error al guardar el horario');
                            }

                            if (!$horario->id_horario) {
                                throw new \Exception('El horario no se creó correctamente');
                            }
                        }

                        // Obtener prefijo del tenant UNA SOLA VEZ (fuera del loop de matches)
                        $prefijoTenantFiltro = '';
                        if (isset($tenant) && $tenant && $tenant->prefijo_espacios) {
                            $prefijoTenantFiltro = strtoupper(trim($tenant->prefijo_espacios));
                        }

                        if ($horario && $horario->id_horario && !empty($horarioProfesor)) {
                            // Limpieza de caracteres invisibles o raros
                            $horarioProfesor = preg_replace('/[\x00-\x1F\x7F]/u', '', $horarioProfesor);
                            $horarioProfesorNormalizado = preg_replace('/(?<!-)\s*([a-z]{2}:\s*)/i', ' - $1', $horarioProfesor);

                            // LOG DIAGNÓSTICO: primeras 3 filas para verificar qué se está leyendo de col U
                            if ($index <= 3) {
                                Log::info("[DIAG] Fila $index - Col U raw: '" . substr($horarioProfesor, 0, 200) . "'");
                                Log::info("[DIAG] Fila $index - Prefijo tenant: '" . $prefijoTenantFiltro . "'");
                            }

                            // Busca TODAS las coincidencias del patrón en todo el texto, sin importar cómo estén separadas.
                            // Formato esperado: DIA.MODULO/G:GRUPO (ESPACIO)
                            // El /G:GRUPO es opcional.
                            preg_match_all('/([A-Za-z]{2})\s*\.\s*(\d{1,2})(?:\s*\/G:(\d+))?\s*\(([^)]+)\)/', $horarioProfesorNormalizado, $matchesList, PREG_SET_ORDER);

                            if ($index <= 3 && !empty($matchesList)) {
                                Log::info("[DIAG] Fila $index - Matches encontrados: " . count($matchesList));
                                foreach ($matchesList as $mi => $mm) {
                                    Log::info("[DIAG]   Match $mi: DIA={$mm[1]}, MOD={$mm[2]}, ESP=" . (isset($mm[4]) ? $mm[4] : 'N/A'));
                                }
                            }

                            $planificacionesGuardadas = 0;

                            foreach ($matchesList as $matches) {
                                    $dia = strtoupper($matches[1]);
                                    $modulo = $matches[2];
                                    $grupo = !empty($matches[3]) ? $matches[3] : '1';
                                    $espacioNombreExcel = trim($matches[4]);

                                    // FILTRO ESTRICTO: Solo procesar espacios que EMPIECEN con el prefijo del tenant
                                    // El Excel contiene múltiples sedes mezcladas (TH-01, CC-05, 07-34, etc.)
                                    // SOLO procesamos los que empiecen con el prefijo configurado (ej: "TH-")
                                    $espacioNombreUpper = strtoupper($espacioNombreExcel);
                                    if (!$prefijoTenantFiltro || !str_starts_with($espacioNombreUpper, $prefijoTenantFiltro . '-')) {
                                        // Espacio sin prefijo del tenant o de otra sede - IGNORAR
                                        if ($index <= 3) {
                                            Log::info("[DIAG] Fila $index - SALTANDO espacio '{$espacioNombreExcel}' (prefijo no coincide con '{$prefijoTenantFiltro}-')");
                                        }
                                        $espaciosOtrosTenants++;
                                        continue;
                                    }

                                    // Buscar el espacio EXACTAMENTE como viene en el Excel (ya incluye prefijo TH-)
                                    $espacioModel = Espacio::withoutGlobalScope('tenant')
                                        ->where('id_espacio', $espacioNombreExcel)
                                        ->first();

                                    if (!$espacioModel) {
                                        Log::warning("⚠ Fila $index: Espacio '{$espacioNombreExcel}' del tenant actual NO EXISTE en BD");
                                        $espaciosNoEncontrados++;

                                        // Trackear espacios faltantes únicos
                                        if (!in_array($espacioNombreExcel, $espaciosFaltantes)) {
                                            $espaciosFaltantes[] = $espacioNombreExcel;
                                        }

                                        continue;
                                    }

                                    $espacioIdFinal = $espacioModel->id_espacio;

                                    // CREAR planificación (ya se hizo limpieza previa)
                                    $idModulo = $dia . '.' . $modulo;

                                    try {
                                        $planificacion = new Planificacion_Asignatura();
                                        $planificacion->id_asignatura = $idAsignatura;
                                        $planificacion->id_horario = $horario->id_horario;
                                        $planificacion->id_modulo = $idModulo;
                                        $planificacion->id_espacio = $espacioIdFinal;
                                        $planificacion->inscritos = $inscritos;
                                        $planificacion->save();

                                        $processedHorariosCount++;
                                        $planificacionesGuardadas++;

                                        // Log de éxito: primeras 5 y cada 100
                                        if ($planificacionesGuardadas <= 5 || $planificacionesGuardadas % 100 == 0) {
                                            Log::info("✓ Planificación #{$planificacionesGuardadas}: asig={$idAsignatura}, hor={$horario->id_horario}, mod={$idModulo}, esp={$espacioIdFinal}");
                                        }
                                    } catch (\Exception $planEx) {
                                        Log::error("✗ Fila $index - Error al crear planificación: " . $planEx->getMessage());
                                        Log::error("  → Datos: asig={$idAsignatura}, hor={$horario->id_horario}, mod={$idModulo}, esp={$espacioIdFinal}");
                                        // NO lanzar excepción, continuar con las demás
                                    }
                                }

                            if ($planificacionesGuardadas == 0 && !empty($horarioProfesor)) {
                                Log::warning("⚠ Fila $index - Horario presente pero 0 planificaciones creadas. Raw: '$horarioProfesor'");
                            }
                        }
                    } catch (\Exception $e) {
                        $errors[] = 'Fila ' . ($index + 1) . ': Error al procesar horario - ' . $e->getMessage();
                        continue;
                    }
                } catch (\Exception $e) {
                    $errorMsg = 'Fila ' . ($index + 1) . ': ' . $e->getMessage();
                    $errors[] = $errorMsg;
                }
            }

            $dataLoad->update([
                'estado' => 'completado',
                'registros_cargados' => $processedUsersCount + $processedAsignaturasCount + $processedHorariosCount
            ]);

            // RESUMEN FINAL
            $totalPlanificaciones = Planificacion_Asignatura::whereHas('horario', function ($q) use ($periodoSeleccionado) {
                $q->where('periodo', $periodoSeleccionado);
            })->count();
            $totalEspaciosConClases = Planificacion_Asignatura::whereHas('horario', function ($q) use ($periodoSeleccionado) {
                $q->where('periodo', $periodoSeleccionado);
            })->distinct('id_espacio')->count('id_espacio');

            Log::info('═══════════════════════════════════════════════════════════');
            Log::info('✓ IMPORTACIÓN COMPLETADA - PERÍODO: ' . $periodoSeleccionado);
            Log::info('  → Profesores procesados: ' . $processedUsersCount);
            Log::info('  → Asignaturas procesadas: ' . $processedAsignaturasCount);
            Log::info('  → Planificaciones creadas: ' . $processedHorariosCount);
            Log::info('  → Planificaciones en BD (período): ' . $totalPlanificaciones);
            Log::info('  → Espacios con clases: ' . $totalEspaciosConClases);
            Log::info('  → Espacios otros tenants (saltados): ' . $espaciosOtrosTenants);
            Log::info('  → Espacios no encontrados en BD: ' . $espaciosNoEncontrados);

            if (!empty($espaciosFaltantes)) {
                Log::warning('⚠ ESPACIOS FALTANTES QUE DEBES CREAR MANUALMENTE:');
                foreach ($espaciosFaltantes as $espFaltante) {
                    Log::warning('  - ' . $espFaltante);
                }
            }

            Log::info('  → Errores encontrados: ' . count($errors));
            Log::info('═══════════════════════════════════════════════════════════');

            $message = 'Archivo procesado exitosamente. Se procesaron ' . $processedUsersCount . ' profesores, '
                . $processedAsignaturasCount . ' asignaturas y ' . $processedHorariosCount . ' planificaciones. '
                . 'Total en BD: ' . $totalPlanificaciones . ' planificaciones en ' . $totalEspaciosConClases . ' espacios.';
            if (!empty($errors)) {
                $message .= ' Se encontraron ' . count($errors) . ' errores.';
            }

            return response()->json([
                'message' => $message,
                'data' => [
                    'nombre_archivo' => $dataLoad,
                    'profesores_procesados' => $processedUsersCount,
                    'asignaturas_procesadas' => $processedAsignaturasCount,
                    'horarios_procesados' => $processedHorariosCount,
                    'filas_omitidas' => $skippedRows,
                    'errores' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en carga masiva: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extraer espacios únicos de los datos cargados
     * Evita duplicados usando un conjunto de espacios ya procesados
     */
    private function extraerEspaciosDelArchivo($rows, $nombreSedeActual, $facultadDeLaSede)
    {
        try {
            if (!$facultadDeLaSede) {
                Log::info('No hay facultad disponible para crear espacios');
                return;
            }

            $espaciosEncontrados = [];

            // Iterar por las filas del archivo
            foreach ($rows as $index => $row) {
                if ($index === 0)
                    continue;  // Saltar encabezados

                $sede = isset($row[7]) ? $row[7] : '';

                // Filtrar por sede actual
                if ($nombreSedeActual && strtolower(trim($sede)) !== $nombreSedeActual) {
                    continue;
                }

                // Columna 20 es el horario del profesor
                $horarioProfesor = isset($row[20]) ? $row[20] : '';

                // Limpieza de caracteres invisibles o raros
                $horarioProfesor = preg_replace('/[\x00-\x1F\x7F]/u', '', $horarioProfesor);

                if (empty($horarioProfesor))
                    continue;

                preg_match_all('/([A-Za-z]{2})\s*\.\s*(\d{1,2})(?:\s*\/G:(\d+))?\s*\(([^)]+)\)/', $horarioProfesor, $matchesList, PREG_SET_ORDER);

                // Extraer espacios de las coincidencias
                foreach ($matchesList as $matches) {
                    if (count($matches) >= 5) {
                        $espacioNombre = preg_replace('/^[a-z]{2}:\s*/i', '', $matches[4]);

                        // Agregar a conjunto si no existe
                        if (!in_array($espacioNombre, $espaciosEncontrados)) {
                            $espaciosEncontrados[] = $espacioNombre;
                        }
                    }
                }
            }

            // Crear espacios en la base de datos si no existen
            if (empty($espaciosEncontrados)) {
                Log::info('No se encontraron espacios para crear');
                return;
            }

            // Obtener el primer piso de la facultad, o crear uno genérico
            $primerPiso = Piso::where('id_facultad', $facultadDeLaSede->id_facultad)
                ->orderBy('numero_piso')
                ->first();

            if (!$primerPiso) {
                // Crear un piso genérico si no existe
                try {
                    $primerPiso = Piso::create([
                        'numero_piso' => 1,
                        'nombre_piso' => 'Piso 1',
                        'id_facultad' => $facultadDeLaSede->id_facultad
                    ]);
                    Log::info('Piso genérico creado: ' . $primerPiso->id);
                } catch (\Exception $e) {
                    Log::warning('No se pudo crear piso genérico: ' . $e->getMessage());
                    return;  // Si no se puede crear el piso, no continuamos
                }
            }

            // Crear espacios únicos con piso válido
            $espaciosCreados = 0;
            $espaciosYaExistentes = 0;

            // Obtener prefijo del tenant
            $tenant = \App\Models\Tenant::current();
            $prefijoTenant = $tenant ? $tenant->prefijo_espacios : '';

            Log::info('→ Intentando crear ' . count($espaciosEncontrados) . ' espacios únicos encontrados en el archivo...');

            foreach ($espaciosEncontrados as $espacioNombre) {
                try {
                    // Construir ID del espacio con prefijo si no lo tiene
                    $espacioId = $espacioNombre;
                    if ($prefijoTenant && !str_starts_with($espacioNombre, $prefijoTenant)) {
                        $espacioId = $prefijoTenant . $espacioNombre;
                    }

                    $espacioExiste = Espacio::withoutGlobalScope('tenant')
                        ->where('id_espacio', $espacioId)
                        ->orWhere('nombre_espacio', $espacioNombre)
                        ->exists();

                    if (!$espacioExiste) {
                        // Crear espacio con piso válido del primer piso de la facultad
                        Espacio::create([
                            'id_espacio' => $espacioId,
                            'nombre_espacio' => $espacioNombre,
                            'tipo_espacio' => 'Sala de Clases',
                            'puestos_disponibles' => 30,
                            'capacidad_maxima' => 30,
                            'piso_id' => $primerPiso->id_piso  // Usar el piso de la facultad
                        ]);
                        $espaciosCreados++;
                    } else {
                        $espaciosYaExistentes++;
                    }
                } catch (\Exception $e) {
                    Log::error("✗ Error al crear espacio '$espacioNombre': " . $e->getMessage());
                }
            }

            Log::info("✓ Espacios procesados: $espaciosCreados nuevos, $espaciosYaExistentes ya existían");
        } catch (\Exception $e) {
            Log::error('Error al extraer espacios del archivo: ' . $e->getMessage());
        }
    }

    public function destroy(DataLoad $dataLoad)
    {
        try {
            if (Storage::exists($dataLoad->ruta_archivo)) {
                Storage::delete($dataLoad->ruta_archivo);
            }

            $dataLoad->delete();

            return redirect()
                ->route('data.index')
                ->with('success', 'Registro de carga eliminado exitosamente.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al eliminar el registro de carga.']);
        }
    }

    public function detalleJson($id)
    {
        $dataLoad = DataLoad::with('user')->findOrFail($id);

        // Calcula el tamaño del archivo en MB
        $tamano = null;
        if ($dataLoad->ruta_archivo && \Storage::disk('public')->exists($dataLoad->ruta_archivo)) {
            $tamano = round(\Storage::disk('public')->size($dataLoad->ruta_archivo) / 1024 / 1024, 1) . ' MB';
        }

        return response()->json([
            'id' => $dataLoad->id,
            'nombre_archivo' => $dataLoad->nombre_archivo,
            'estado' => $dataLoad->estado,
            'registros_cargados' => $dataLoad->registros_cargados,
            'tamano' => $tamano,
            'tipo_carga' => $dataLoad->tipo_carga,
            'ruta_archivo' => $dataLoad->ruta_archivo,
            'usuario_nombre' => $dataLoad->user->name ?? '',
            'usuario_run' => $dataLoad->user->run ?? '',
            'fecha_carga' => $dataLoad->created_at ? $dataLoad->created_at->format('d/m/Y H:i:s') : '',
            'fecha_actualizacion' => $dataLoad->updated_at ? $dataLoad->updated_at->format('d/m/Y H:i:s') : '',
            'url_descarga' => $dataLoad->ruta_archivo ? \Storage::disk('public')->url($dataLoad->ruta_archivo) : '',
        ]);
    }

    public function download($id)
    {
        $dataLoad = DataLoad::findOrFail($id);

        if (!$dataLoad->ruta_archivo || !Storage::disk('public')->exists($dataLoad->ruta_archivo)) {
            return back()->withErrors(['error' => 'El archivo no existe o ha sido eliminado.']);
        }

        return Storage::disk('public')->download($dataLoad->ruta_archivo, $dataLoad->nombre_archivo);
    }

    public function progress($id)
    {
        try {
            $dataLoad = DataLoad::findOrFail($id);

            return response()->json([
                'estado' => $dataLoad->estado,
                'registros_cargados' => $dataLoad->registros_cargados,
                'mensaje' => $this->getEstadoMensaje($dataLoad->estado)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error al obtener el progreso'
            ], 500);
        }
    }

    private function getEstadoMensaje($estado)
    {
        switch ($estado) {
            case 'pendiente':
                return 'Archivo en cola de procesamiento';
            case 'procesando':
                return 'Procesando archivo...';
            case 'completado':
                return 'Proceso finalizado';
            case 'error':
                return 'Error en el procesamiento';
            default:
                return 'Estado desconocido';
        }
    }

    public function limpiarPeriodo(Request $request)
    {
        $request->validate([
            'semestre_selector' => 'required|in:1,2'
        ]);

        $semestreSeleccionado = $request->input('semestre_selector');
        $anioActual = \App\Helpers\SemesterHelper::getCurrentAcademicYear();
        $periodoSeleccionado = $anioActual . '-' . $semestreSeleccionado;

        try {
            // Eliminar planificaciones del periodo
            $horariosDelPeriodo = Horario::where('periodo', $periodoSeleccionado)->pluck('id_horario');
            $planificacionesEliminadas = Planificacion_Asignatura::whereIn('id_horario', $horariosDelPeriodo)->delete();
            
            // Eliminar los horarios asociados a ese periodo
            $horariosEliminados = Horario::where('periodo', $periodoSeleccionado)->delete();

            Log::info("Limpieza manual del periodo {$periodoSeleccionado}: {$planificacionesEliminadas} planificaciones y {$horariosEliminados} horarios eliminados.");

            return response()->json([
                'success' => true,
                'message' => "Se han eliminado {$planificacionesEliminadas} planificaciones y {$horariosEliminados} horarios del periodo {$periodoSeleccionado} exitosamente."
            ]);
        } catch (\Exception $e) {
            Log::error('Error al limpiar periodo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar los datos del periodo: ' . $e->getMessage()
            ], 500);
        }
    }
}

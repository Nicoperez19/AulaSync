<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\DataLoad;
use App\Models\Asignatura;
use App\Models\Profesor;
use App\Models\ProfesorColaborador;
use App\Models\PlanificacionProfesorColaborador;
use App\Models\Planificacion_Asignatura;
use App\Models\Horario;
use App\Models\Espacio;
use App\Models\Sede;
use App\Helpers\EspacioAliasHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReconciliarHorariosDocentesCommand extends Command
{
    protected $signature = 'horarios:reconciliar-docentes {--tenant= : ID o dominio del tenant específico}';
    protected $description = 'Reconcilia las planificaciones de docentes y colaboradores usando la columna HORARIO_PROFESOR del último archivo Excel cargado';

    public function handle(): int
    {
        $tenantFilter = $this->option('tenant');

        $tenantsQuery = Tenant::query();
        if ($tenantFilter) {
            $tenantsQuery->where('id', $tenantFilter)->orWhere('domain', $tenantFilter);
        }

        $tenants = $tenantsQuery->get();

        if ($tenants->isEmpty()) {
            $this->error('No se encontraron tenants para procesar.');
            return 1;
        }

        foreach ($tenants as $tenant) {
            $this->info("═══════════════════════════════════════════════════════════");
            $this->info("Procesando Tenant: {$tenant->domain} ({$tenant->nombre_sede}) - ID: {$tenant->id}");
            $this->info("═══════════════════════════════════════════════════════════");

            $tenant->makeCurrent();

            $dataLoad = DB::connection('tenant')
                ->table('data_loads')
                ->where('estado', 'completado')
                ->orderBy('id', 'desc')
                ->first();

            if (!$dataLoad || empty($dataLoad->ruta_archivo)) {
                $this->warn("  -> No se encontró carga de datos para este tenant. Omitiendo.");
                continue;
            }

            $filePath = storage_path('app/public/' . $dataLoad->ruta_archivo);
            if (!file_exists($filePath)) {
                $this->warn("  -> Archivo no existe: {$filePath}. Omitiendo.");
                continue;
            }

            $this->reconciliarTenant($tenant, $filePath);
        }

        // Limpiar cachés
        Cache::flush();
        $this->info("✓ Caché de planificaciones y dashboard invalidada.");
        $this->info("✓ Reconciliación completada con éxito.");

        return 0;
    }

    protected function reconciliarTenant(Tenant $tenant, string $filePath): void
    {
        $this->line("  -> Leyendo archivo Excel: " . basename($filePath));

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        if (empty($rows)) {
            $this->warn("  -> Archivo vacío.");
            return;
        }

        $headers = array_map(function($h) {
            return strtoupper(trim(preg_replace('/[^A-Za-z0-9_]/', '', (string)$h)));
        }, $rows[0]);

        $colMap = [
            'id_asignatura' => 0,
            'codigo_asignatura' => 1,
            'nombre_asignatura' => 2,
            'seccion' => 3,
            'sede' => 7,
            'inscritos' => 9,
            'run_profesor' => 11,
            'nombre_profesor' => 12,
            'email_profesor' => 13,
            'tipo_profesor' => 16,
            'id_carrera' => 17,
            'nombre_carrera' => 18,
            'horario' => 19,
            'horario_profesor' => 20,
        ];

        foreach ($headers as $colIdx => $headerName) {
            if (in_array($headerName, ['RUN_PROFESOR', 'RUN_PROF', 'RUT_PROFESOR'])) $colMap['run_profesor'] = $colIdx;
            if (in_array($headerName, ['NOMBRE_PROFESOR', 'NOMBRE_PROF'])) $colMap['nombre_profesor'] = $colIdx;
            if (in_array($headerName, ['HORARIO', 'HORARIOS', 'BLOQUES'])) $colMap['horario'] = $colIdx;
            if (in_array($headerName, ['HORARIO_PROFESOR', 'HORARIO_DOCENTE', 'HORARIOPROFESOR', 'HORARIODOCENTE', 'HORARIO_PROF'])) $colMap['horario_profesor'] = $colIdx;
            if (in_array($headerName, ['SEDE', 'NOMBRE_SEDE'])) $colMap['sede'] = $colIdx;
        }

        $sedeActual = Sede::find($tenant->sede_id);
        $prefijoTenantFiltro = $tenant->prefijo_espacios ? strtoupper(trim($tenant->prefijo_espacios)) : '';

        $planifsEliminadas = 0;
        $planifsCreadas = 0;
        $colabsActualizados = 0;

        // Primero recopilamos las planificaciones válidas según HORARIO_PROFESOR
        // Estructura:
        // [
        //   'responsables' => [
        //      id_asignatura => [
        //         'run' => ...,
        //         'slots' => [ ['dia' => ..., 'modulo' => ..., 'espacio' => ...] ]
        //      ]
        //   ],
        //   'colaboradores' => [
        //      [
        //         'run' => ...,
        //         'id_asignatura' => ...,
        //         'slots' => [ ['dia' => ..., 'modulo' => ..., 'espacio' => ...] ]
        //      ]
        //   ]
        // ]
        $responsablesPorAsignatura = [];
        $colaboradoresList = [];

        foreach ($rows as $index => $row) {
            if ($index === 0) continue;

            // Filtrar únicamente filas que correspondan a la sede de este tenant
            $rowSede = trim($row[$colMap['sede']] ?? '');
            if ($sedeActual && !empty($rowSede)) {
                $coincideSede = stripos($rowSede, $sedeActual->nombre_sede) !== false 
                    || stripos($rowSede, $tenant->name) !== false 
                    || strcasecmp($rowSede, $sedeActual->id_sede) === 0;
                if (!$coincideSede) {
                    continue;
                }
            }

            $runRaw = trim($row[$colMap['run_profesor']] ?? '');
            $run = preg_replace('/[^0-9Kk]/', '', strtoupper($runRaw));
            if (empty($run)) continue;

            $nomProf = trim($row[$colMap['nombre_profesor']] ?? '');
            $tipoProfesor = trim($row[$colMap['tipo_profesor']] ?? 'Profesor Responsable');
            $idAsig = trim($row[$colMap['id_asignatura']] ?? '');
            $nomAsig = trim($row[$colMap['nombre_asignatura']] ?? '');

            // Asegurar que el profesor exista en la tabla profesors del tenant
            $profModel = Profesor::withoutGlobalScope('tenant')->where('run_profesor', $run)->first();
            if (!$profModel) {
                $emailProf = trim($row[$colMap['email_profesor']] ?? '');
                if (empty($emailProf) || !filter_var($emailProf, FILTER_VALIDATE_EMAIL)) {
                    $emailProf = $run . '@ucsc.cl';
                }
                $profModel = Profesor::create([
                    'run_profesor' => $run,
                    'name' => !empty($nomProf) ? $nomProf : 'Profesor ' . $run,
                    'email' => $emailProf,
                    'sede_id' => $tenant->sede_id,
                    'tipo_profesor' => $tipoProfesor,
                ]);
            }

            // Horario específico del docente (prioridad) o horario general
            $hProfRaw = trim($row[$colMap['horario_profesor']] ?? '');
            $hGenRaw = trim($row[$colMap['horario']] ?? '');
            $hUsar = !empty($hProfRaw) ? $hProfRaw : $hGenRaw;

            if (empty($hUsar)) continue;

            // Extraer slots: DIA.MODULO/G:GRUPO (ESPACIO)
            $hUsar = preg_replace('/[\x00-\x1F\x7F]/u', '', $hUsar);
            $hNormalizado = preg_replace('/(?<!-)\s*([a-z]{2}:\s*)/i', ' - $1', $hUsar);
            preg_match_all('/([A-Za-z]{2})\s*\.\s*(\d{1,2})(?:\s*\/G:(\d+))?\s*\(([^)]+)\)/', $hNormalizado, $matches, PREG_SET_ORDER);

            $slots = [];
            foreach ($matches as $m) {
                $dia = strtoupper($m[1]);
                $modNum = $m[2];
                $espRaw = trim($m[4]);
                $espNorm = EspacioAliasHelper::normalizar(
                    $espRaw,
                    $sedeActual ? $sedeActual->id_sede : ($prefijoTenantFiltro ?: null),
                    ['asignatura' => $nomAsig]
                );

                // Buscar espacio en BD
                $espModel = Espacio::withoutGlobalScope('tenant')->where('id_espacio', $espNorm)->first();
                if (!$espModel && $prefijoTenantFiltro) {
                    $espModel = Espacio::withoutGlobalScope('tenant')->where('id_espacio', $prefijoTenantFiltro . '-' . $espNorm)->first();
                }
                if (!$espModel) {
                    $espModel = Espacio::withoutGlobalScope('tenant')->where('nombre_espacio', $espNorm)->first();
                }

                if ($espModel) {
                    $slots[] = [
                        'dia' => $dia,
                        'modulo_num' => $modNum,
                        'id_modulo' => $dia . '.' . $modNum,
                        'id_espacio' => $espModel->id_espacio,
                    ];
                }
            }

            if (stripos($tipoProfesor, 'colaborador') !== false) {
                $colaboradoresList[] = [
                    'run' => $run,
                    'id_asignatura' => $idAsig,
                    'nombre_asignatura' => $nomAsig,
                    'slots' => $slots,
                ];
            } else {
                if (!isset($responsablesPorAsignatura[$idAsig])) {
                    $responsablesPorAsignatura[$idAsig] = [
                        'run' => $run,
                        'slots' => [],
                    ];
                }
                foreach ($slots as $s) {
                    $responsablesPorAsignatura[$idAsig]['slots'][] = $s;
                }
            }
        }

        $this->line("  -> Encontradas " . count($responsablesPorAsignatura) . " asignaturas con profesor responsable.");
        $this->line("  -> Encontrados " . count($colaboradoresList) . " registros de profesores colaboradores.");

        // 1. Reconciliar Planificacion_Asignatura para cada asignatura
        foreach ($responsablesPorAsignatura as $idAsig => $dataResp) {
            $slotsValidos = $dataResp['slots'];
            $espaciosValidos = array_unique(array_column($slotsValidos, 'id_espacio'));

            // Buscar planificaciones en BD para esta asignatura
            $planifsActuales = Planificacion_Asignatura::withoutGlobalScope('tenant')
                ->where('id_asignatura', $idAsignatura = $idAsig)
                ->get();

            foreach ($planifsActuales as $pActual) {
                // Verificar si este slot es válido para el profesor responsable
                $esValido = false;
                foreach ($slotsValidos as $sv) {
                    if ($sv['id_espacio'] === $pActual->id_espacio && $sv['id_modulo'] === $pActual->id_modulo) {
                        $esValido = true;
                        break;
                    }
                }

                if (!$esValido) {
                    // Esta planificación era de un espacio/módulo que no le corresponde al responsable (ej. TH-L04 en MI.7)
                    $pActual->delete();
                    $planifsEliminadas++;
                }
            }

            // Asegurar que los slots válidos existan en BD
            $asigModel = Asignatura::withoutGlobalScope('tenant')->where('id_asignatura', $idAsig)->first();
            $idHorario = 'HOR_' . $dataResp['run'] . '_2026-2'; // periodo activo
            $horario = Horario::withoutGlobalScope('tenant')->where('id_horario', $idHorario)->first();

            if ($asigModel && $horario) {
                foreach ($slotsValidos as $sv) {
                    $existe = Planificacion_Asignatura::withoutGlobalScope('tenant')
                        ->where('id_asignatura', $idAsig)
                        ->where('id_espacio', $sv['id_espacio'])
                        ->where('id_modulo', $sv['id_modulo'])
                        ->exists();

                    if (!$existe) {
                        Planificacion_Asignatura::create([
                            'id_asignatura' => $idAsig,
                            'id_horario' => $horario->id_horario,
                            'id_modulo' => $sv['id_modulo'],
                            'id_espacio' => $sv['id_espacio'],
                            'inscritos' => $asigModel->inscritos ?? 0,
                        ]);
                        $planifsCreadas++;
                    }
                }
            }
        }

        // 2. Reconciliar PlanificacionProfesorColaborador
        // Limpiar planificaciones previas de colaboradores de este tenant y re-crear con sus slots individuales
        foreach ($colaboradoresList as $colabData) {
            $pc = ProfesorColaborador::withoutGlobalScope('tenant')
                ->where('run_profesor_colaborador', $colabData['run'])
                ->where('id_asignatura', $colabData['id_asignatura'])
                ->first();

            if (!$pc) {
                $pc = ProfesorColaborador::create([
                    'run_profesor_colaborador' => $colabData['run'],
                    'id_asignatura' => $colabData['id_asignatura'],
                    'nombre_asignatura_temporal' => $colabData['nombre_asignatura'],
                    'fecha_inicio' => now()->startOfYear(),
                    'fecha_termino' => now()->endOfYear(),
                    'estado' => 'activo',
                ]);
            } else {
                $pc->update([
                    'id_asignatura' => $colabData['id_asignatura'],
                    'estado' => 'activo',
                ]);
            }

            // Eliminar asignaciones anteriores de este colaborador que no correspondan
            $planifsColabActuales = PlanificacionProfesorColaborador::withoutGlobalScope('tenant')
                ->where('id_profesor_colaborador', $pc->id)
                ->get();

            foreach ($planifsColabActuales as $pca) {
                $valido = false;
                foreach ($colabData['slots'] as $s) {
                    if ($s['id_espacio'] === $pca->id_espacio && $s['id_modulo'] === $pca->id_modulo) {
                        $valido = true;
                        break;
                    }
                }
                if (!$valido) {
                    $pca->delete();
                }
            }

            // Crear los slots válidos
            foreach ($colabData['slots'] as $s) {
                PlanificacionProfesorColaborador::firstOrCreate([
                    'id_profesor_colaborador' => $pc->id,
                    'id_modulo' => $s['id_modulo'],
                    'id_espacio' => $s['id_espacio'],
                ]);
            }
            $colabsActualizados++;
        }

        $this->info("  ✓ Planificaciones incorrectas eliminadas: {$planifsEliminadas}");
        $this->info("  ✓ Planificaciones verificadas/creadas: {$planifsCreadas}");
        $this->info("  ✓ Colaboradores actualizados con sala exacta: {$colabsActualizados}");
    }
}

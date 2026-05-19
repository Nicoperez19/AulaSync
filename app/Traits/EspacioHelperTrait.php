<?php

namespace App\Traits;

use App\Models\PlanificacionProfesorColaborador;
use Illuminate\Http\Request;
use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\Planificacion_Asignatura;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\DayCodeHelper;

trait EspacioHelperTrait
{
    private function validarDatosEspacio(Request $request, ?string $idEspacioActual = null): array
    {
        // En edición no se permite cambiar la PK, por lo que se fija el id actual.
        if ($idEspacioActual !== null) {
            $request->merge([
                'id_espacio' => $idEspacioActual,
            ]);
        } else {
            $this->normalizarIdEspacio($request);
        }

        $reglaIdEspacio = ['required', 'string', 'max:50'];
        if ($idEspacioActual === null) {
            $reglaIdEspacio[] = Rule::unique('tenant.espacios', 'id_espacio');
        }

        $validated = $request->validate([
            'id_espacio' => $reglaIdEspacio,
            'nombre_espacio' => ['required', 'string', 'max:255'],
            'id_universidad' => ['required', Rule::exists('mysql.universidades', 'id_universidad')],
            'id_facultad' => ['required', Rule::exists('tenant.facultades', 'id_facultad')],
            'piso_id' => ['required', Rule::exists('tenant.pisos', 'id')],
            'tipo_espacio' => [
                'required',
                Rule::in([
                    'Sala de Clases',
                    'Laboratorio',
                    'Laboratorio de Computación',
                    'Biblioteca',
                    'Sala de Reuniones',
                    'Oficinas',
                    'Taller',
                    'Auditorio',
                    'Sala de Estudio',
                    'Gimnasio',
                    'Sala Multiusos',
                ])
            ],
            'estado' => ['required', Rule::in(['Disponible', 'Ocupado', 'Reservado', 'Mantenimiento'])],
            'puestos_disponibles' => ['nullable', 'integer', 'min:1'],
        ], [
            'id_espacio.unique' => 'El identificador del espacio ya existe en esta sede.',
            'id_espacio.required' => 'Debes ingresar el identificador del espacio.',
            'id_espacio.max' => 'El identificador del espacio no puede superar 50 caracteres.',
            'piso_id.exists' => 'El piso seleccionado no existe en el tenant actual.',
            'id_facultad.exists' => 'La facultad seleccionada no existe en el tenant actual.',
            'id_universidad.exists' => 'La universidad seleccionada no existe.',
            'tipo_espacio.in' => 'El tipo de espacio seleccionado no es válido.',
            'estado.in' => 'El estado seleccionado no es válido.',
        ]);

        $piso = Piso::with('facultad')->find($validated['piso_id']);
        if (!$piso || !$piso->facultad) {
            throw ValidationException::withMessages([
                'piso_id' => 'El piso seleccionado no tiene una facultad válida.',
            ]);
        }

        if ((string) $piso->id_facultad !== (string) $validated['id_facultad']) {
            throw ValidationException::withMessages([
                'id_facultad' => 'La facultad seleccionada no corresponde al piso elegido.',
            ]);
        }

        if ((string) $piso->facultad->id_universidad !== (string) $validated['id_universidad']) {
            throw ValidationException::withMessages([
                'id_universidad' => 'La universidad seleccionada no corresponde a la facultad/piso elegido.',
            ]);
        }

        $this->autorizarGestionSede((string) $piso->facultad->id_sede);

        return $validated;
    }

    private function normalizarIdEspacio(Request $request): void
    {
        $idEspacio = trim((string) $request->input('id_espacio', ''));
        if ($idEspacio === '') {
            return;
        }

        $prefijo = tenant_prefijo();
        if ($prefijo) {
            $prefijoNormalizado = strtoupper(trim($prefijo));
            if ($prefijoNormalizado !== '' && !str_starts_with(strtoupper($idEspacio), $prefijoNormalizado)) {
                $idEspacio = $prefijoNormalizado . $idEspacio;
            }
        }

        $request->merge([
            'id_espacio' => $idEspacio,
        ]);
    }

    private function autorizarGestionSede(string $idSede): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Usuario no autenticado.');
        }

        if ($user->is_superuser) {
            return;
        }

        if (!$user->id_sede) {
            abort(403, 'Tu cuenta no tiene una sede asignada.');
        }

        if ((string) $user->id_sede !== $idSede) {
            abort(403, 'No tienes permisos para gestionar espacios de otra sede.');
        }
    }

    private function autorizarGestionEspacio(Espacio $espacio): void
    {
        $espacio->loadMissing('piso.facultad');

        $idSede = $espacio->piso?->facultad?->id_sede;
        if (!$idSede) {
            abort(403, 'No se pudo determinar la sede del espacio.');
        }

        $this->autorizarGestionSede((string) $idSede);
    }

    private function determinarModuloActual($horaActual, $diaActual)
    {
        // Log para debugging


        // Definir horarios de módulos (mismo formato que en el frontend)
        $horariosModulos = [
            'lunes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'martes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'miercoles' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'jueves' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'viernes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ]
        ];

        $horariosDia = $horariosModulos[$diaActual] ?? null;

        // Log para debugging de horarios del día


        if (!$horariosDia) {
            Log::warning('determinarModuloActual - No se encontraron horarios para el día:', [
                'diaActual' => $diaActual
            ]);
            return null;
        }

        // Buscar en qué módulo estamos según la hora actual
        foreach ($horariosDia as $modulo => $horario) {
            if ($horaActual >= $horario['inicio'] && $horaActual < $horario['fin']) {

                return $modulo;
            }
        }

        // Si no estamos en ningún módulo (break), buscar el siguiente módulo disponible
        // Esto permite hacer reservas durante los breaks
        foreach ($horariosDia as $modulo => $horario) {
            if ($horaActual < $horario['inicio']) {

                return $modulo; // Retornar el siguiente módulo
            }
        }

        Log::warning('determinarModuloActual - No se encontró módulo para la hora:', [
            'horaActual' => $horaActual,
            'diaActual' => $diaActual
        ]);

        return null;
    }

    private function obtenerHorarioModulo($modulo, $diaActual)
    {
        $horariosModulos = [
            'lunes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'martes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'miercoles' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'jueves' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ],
            'viernes' => [
                1 => ['inicio' => '08:10:00', 'fin' => '09:00:00'],
                2 => ['inicio' => '09:10:00', 'fin' => '10:00:00'],
                3 => ['inicio' => '10:10:00', 'fin' => '11:00:00'],
                4 => ['inicio' => '11:10:00', 'fin' => '12:00:00'],
                5 => ['inicio' => '12:10:00', 'fin' => '13:00:00'],
                6 => ['inicio' => '13:10:00', 'fin' => '14:00:00'],
                7 => ['inicio' => '14:10:00', 'fin' => '15:00:00'],
                8 => ['inicio' => '15:10:00', 'fin' => '16:00:00'],
                9 => ['inicio' => '16:10:00', 'fin' => '17:00:00'],
                10 => ['inicio' => '17:10:00', 'fin' => '18:00:00'],
                11 => ['inicio' => '18:10:00', 'fin' => '19:00:00'],
                12 => ['inicio' => '19:10:00', 'fin' => '20:00:00'],
                13 => ['inicio' => '20:10:00', 'fin' => '21:00:00'],
                14 => ['inicio' => '21:10:00', 'fin' => '22:00:00'],
                15 => ['inicio' => '22:10:00', 'fin' => '23:00:00']
            ]
        ];

        return $horariosModulos[$diaActual][$modulo] ?? null;
    }

    private function obtenerInfoProximaClase($moduloCodigo, $espacioId)
    {
        $planificacion = Planificacion_Asignatura::with(['asignatura', 'horario.profesor', 'modulo'])
            ->where('id_espacio', $espacioId)
            ->where('id_modulo', $moduloCodigo)
            ->first();

        if ($planificacion) {
            return [
                'modulo' => $moduloCodigo,
                'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'Sin asignatura',
                // acceder al profesor a través del horario
                'profesor' => $planificacion->horario->profesor->name ?? 'No especificado',
                'hora_inicio' => $planificacion->modulo->hora_inicio ?? '',
                'hora_termino' => $planificacion->modulo->hora_termino ?? ''
            ];
        }

        return null;
    }

    private function obtenerInformacionProfesorConDatos($reserva, $horaActual, $codigoDia)
    {
        $runProfesor = $reserva->run_profesor;

        // El profesor ya viene cargado con eager loading desde la reserva
        $profesorNombre = $reserva->profesor?->name;

        if (!$profesorNombre) {
            // Fallback: solo si realmente no está en la relación
            $prof = Profesor::select('name', 'run_profesor')->where('run_profesor', $runProfesor)->first();
            $profesorNombre = $prof?->name ?? 'Profesor no encontrado';
        }

        $asignatura = null;
        if ($reserva->tipo_reserva === 'espontanea') {
            $asignatura = null;
        } elseif ($reserva->id_asignatura && $reserva->asignatura) {
            $asignatura = $reserva->asignatura->nombre_asignatura;
        } else {
            // Solo si es clase regular sin asignatura en la reserva, buscar en planificación
            $plan = Planificacion_Asignatura::with(['asignatura:id_asignatura,nombre_asignatura'])
                ->whereHas('horario', fn($q) => $q->where('run_profesor', $runProfesor))
                ->whereHas('modulo', fn($q) => $q->where('dia', $codigoDia)
                    ->where('hora_inicio', '<=', $horaActual)
                    ->where('hora_termino', '>', $horaActual))
                ->select('planificacion_asignaturas.id_asignatura')
                ->first();
            $asignatura = $plan?->asignatura?->nombre_asignatura;
        }

        return [
            'success' => true,
            'tipo_ocupacion' => 'profesor',
            'nombre' => $profesorNombre,
            'run_profesor' => $runProfesor,
            'asignatura' => $asignatura,
            'hora_inicio' => $reserva->hora,
            'hora_salida' => $reserva->hora_salida,
            'tipo_reserva' => $reserva->tipo_reserva,
        ];
    }

    private function obtenerInformacionSolicitanteConDatos($reserva)
    {
        // El solicitante ya viene cargado con eager loading desde la reserva
        $solicitante = $reserva->solicitante;

        if ($solicitante) {
            return [
                'success' => true,
                'tipo_ocupacion' => 'solicitante',
                'nombre' => $solicitante->nombre ?? 'No especificado',
                'run_solicitante' => $solicitante->run_solicitante ?? 'No especificado',
                'correo' => $solicitante->correo ?? 'No especificado',
                'telefono' => $solicitante->telefono ?? 'No especificado',
                'tipo_solicitante' => $solicitante->tipo_solicitante ?? 'No especificado',
                'activo' => $solicitante->activo ?? false,
                'fecha_registro' => $solicitante->fecha_registro ?? null,
                'hora_inicio' => $reserva->hora,
                'hora_salida' => $reserva->hora_salida,
                'tipo_reserva' => $reserva->tipo_reserva,
            ];
        }

        // Fallback: buscar en Users (si es un usuario del sistema)
        $usuario = User::where('run', $reserva->run_solicitante)->first();
        if ($usuario) {
            return [
                'success' => true,
                'tipo_ocupacion' => 'solicitante',
                'nombre' => $usuario->name,
                'run_solicitante' => $usuario->run,
                'correo' => $usuario->email ?? 'No especificado',
                'telefono' => 'No especificado',
                'tipo_solicitante' => 'Usuario del sistema',
                'activo' => true,
                'fecha_registro' => $usuario->created_at ?? null,
                'hora_inicio' => $reserva->hora,
                'hora_salida' => $reserva->hora_salida,
                'tipo_reserva' => $reserva->tipo_reserva,
            ];
        }

        return [
            'success' => true,
            'tipo_ocupacion' => 'ocupado_sin_info',
            'nombre' => 'Solicitante no encontrado',
            'tipo_reserva' => $reserva->tipo_reserva,
            'asignatura' => null,
            'hora_inicio' => $reserva->hora,
            'hora_salida' => $reserva->hora_salida,
        ];
    }

    private function obtenerInformacionProfesor($reserva, $horaActual)
    {
        try {
            $runProfesor = $reserva->run_profesor;

            // Consulta optimizada para profesor desde la tabla profesors
            $profesor = Profesor::select('name', 'run_profesor')
                ->where('run_profesor', $runProfesor)
                ->first();

            if (!$profesor) {
                Log::warning("Profesor no encontrado en BD", ['run_profesor' => $runProfesor]);
                return [
                    'success' => true,
                    'tipo_ocupacion' => 'ocupado_sin_info',
                    'nombre' => 'Profesor no encontrado',
                    'run_profesor' => $runProfesor,
                    'tipo_reserva' => $reserva->tipo_reserva,
                    'asignatura' => null,
                    'hora_inicio' => $reserva->hora,
                    'hora_salida' => $reserva->hora_salida
                ];
            }
        } catch (\Exception $e) {
            Log::error("Error al obtener información del profesor", [
                'run_profesor' => $reserva->run_profesor,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => true,
                'tipo_ocupacion' => 'ocupado_sin_info',
                'nombre' => 'Error al cargar profesor',
                'run_profesor' => $reserva->run_profesor,
                'tipo_reserva' => $reserva->tipo_reserva,
                'asignatura' => null,
                'hora_inicio' => $reserva->hora,
                'hora_salida' => $reserva->hora_salida
            ];
        }

        // Primero intentar obtener asignatura directamente de la reserva
        $asignatura = null;

        // Las reservas espontáneas NO deben mostrar información de clase programada.
        // Esto evita que el modal de cronología muestre una asignatura incorrecta.
        if ($reserva->tipo_reserva === 'espontanea') {
            $asignatura = null;
        } elseif ($reserva->id_asignatura && $reserva->asignatura) {
            // La reserva tiene asignatura asignada directamente (ej. desde acciones rápidas)
            $asignatura = $reserva->asignatura->nombre_asignatura;
        } else {
            // Si no hay asignatura en la reserva, buscar en planificación (clases regulares)
            try {
                // Buscar planificación actual utilizando la relación 'modulo'
                $diaActual = strtolower(now()->format('l'));
                $codigosDias = [
                    'monday' => 'LU',
                    'tuesday' => 'MA',
                    'wednesday' => 'MI',
                    'thursday' => 'JU',
                    'friday' => 'VI',
                    'saturday' => 'SA',
                    'sunday' => 'DO'
                ];
                $codigoDia = $codigosDias[$diaActual] ?? 'LU';

                // Cargar planificaciones del profesor para el día (usando la relación con horario)
                $planificaciones = Planificacion_Asignatura::with(['asignatura:id_asignatura,nombre_asignatura', 'modulo', 'horario'])
                    ->whereHas('horario', function ($query) use ($runProfesor) {
                        $query->where('run_profesor', $runProfesor);
                    })
                    ->where('id_modulo', 'like', $codigoDia . '.%')
                    ->get();

                // Filtrar en memoria por los módulos cuyo horario contiene la hora actual
                $planificacion = $planificaciones->first(function ($p) use ($horaActual) {
                    return isset($p->modulo->hora_inicio, $p->modulo->hora_termino)
                        && $p->modulo->hora_inicio <= $horaActual
                        && $p->modulo->hora_termino > $horaActual;
                });

                $asignatura = $planificacion ? $planificacion->asignatura->nombre_asignatura : null;
            } catch (\Exception $e) {
                Log::error("Error al obtener planificación del profesor", [
                    'run_profesor' => $runProfesor,
                    'error' => $e->getMessage()
                ]);
                $asignatura = null;
            }
        }

        return [
            'success' => true,
            'tipo_ocupacion' => 'profesor',
            'nombre' => $profesor->name,
            'run_profesor' => $runProfesor,
            'asignatura' => $asignatura,
            'hora_inicio' => $reserva->hora,
            'hora_salida' => $reserva->hora_salida,
            'tipo_reserva' => $reserva->tipo_reserva
        ];
    }

    private function obtenerInformacionPlanificacionActual($idEspacio, $horaActual)
    {
        try {
            // Usar el mismo formato de día que PlanoDigitalController para consistencia
            $diaActual = strtolower(now()->locale('es')->isoFormat('dddd'));
            $codigosDias = [
                'lunes' => 'LU',
                'martes' => 'MA',
                'miércoles' => 'MI',
                'jueves' => 'JU',
                'viernes' => 'VI',
                'sábado' => 'SA',
                'domingo' => 'DO'
            ];
            $codigoDia = $codigosDias[$diaActual] ?? 'LU';

            // [OPTIMIZACIÓN] Buscar directamente la planificación que coincide con la hora actual en SQL
            $planificacion = Planificacion_Asignatura::with(['asignatura:id_asignatura,nombre_asignatura', 'modulo', 'horario.profesor'])
                ->where('id_espacio', $idEspacio)
                ->whereHas('modulo', function ($query) use ($codigoDia, $horaActual) {
                    $query->where('dia', $codigoDia)
                        ->where('hora_inicio', '<=', $horaActual)
                        ->where('hora_termino', '>', $horaActual);
                })
                ->first();

            if ($planificacion) {
                return [
                    'success' => true,
                    'tipo_ocupacion' => 'profesor',
                    'nombre' => $planificacion->horario->profesor->name ?? 'Profesor no asignado',
                    'run_profesor' => $planificacion->horario->profesor->run_profesor ?? null,
                    'asignatura' => $planificacion->asignatura->nombre_asignatura ?? 'Sin asignatura',
                    'hora_inicio' => $planificacion->modulo->hora_inicio,
                    'hora_salida' => $planificacion->modulo->hora_termino,
                    'tipo_reserva' => 'clase_regular'
                ];
            }

            // 2. Buscar en Planificaciones de Profesores Colaboradores (SQL filtrado)
            $planColaborador = PlanificacionProfesorColaborador::with(['profesorColaborador', 'modulo'])
                ->where('id_espacio', $idEspacio)
                ->whereHas('modulo', function ($query) use ($codigoDia, $horaActual) {
                    $query->where('dia', $codigoDia)
                        ->where('hora_inicio', '<=', $horaActual)
                        ->where('hora_termino', '>', $horaActual);
                })
                ->first();

            if ($planColaborador) {
                return [
                    'success' => true,
                    'tipo_ocupacion' => 'profesor',
                    'nombre' => $planColaborador->profesorColaborador->name ?? 'Profesor colaborador',
                    'run_profesor' => $planColaborador->profesorColaborador->run_profesor ?? null,
                    'asignatura' => 'Clase Temporal / Colaborador',
                    'hora_inicio' => $planColaborador->modulo->hora_inicio,
                    'hora_salida' => $planColaborador->modulo->hora_termino,
                    'tipo_reserva' => 'clase_colaborador'
                ];
            }

        } catch (\Exception $e) {
            Log::error("Error al obtener planificación actual para espacio {$idEspacio}: " . $e->getMessage());
        }

        return null;
    }

    private function obtenerInformacionSolicitante($reserva)
    {
        $runSolicitante = $reserva->run_solicitante;

        // Usar el método optimizado del modelo Solicitante
        $solicitante = Solicitante::buscarActivoPorRun($runSolicitante);

        if (!$solicitante) {
            // Verificar si es un usuario del sistema (estudiante, admin, etc.)
            $usuario = User::where('run', $runSolicitante)->first();
            if ($usuario) {
                return [
                    'success' => true,
                    'tipo_ocupacion' => 'solicitante',
                    'nombre' => $usuario->name,
                    'run_solicitante' => $usuario->run,
                    'correo' => $usuario->email ?? 'No especificado',
                    'telefono' => 'No especificado',
                    'tipo_solicitante' => 'Usuario del sistema',
                    'activo' => true,
                    'fecha_registro' => $usuario->created_at ?? null,
                    'hora_inicio' => $reserva->hora,
                    'hora_salida' => $reserva->hora_salida,
                    'tipo_reserva' => $reserva->tipo_reserva
                ];
            }

            return [
                'success' => true,
                'tipo_ocupacion' => 'ocupado_sin_info',
                'nombre' => 'Solicitante no encontrado',
                'tipo_reserva' => $reserva->tipo_reserva,
                'asignatura' => null,
                'hora_inicio' => $reserva->hora,
                'hora_salida' => $reserva->hora_salida
            ];
        }

        return $this->construirRespuestaSolicitante($solicitante, $reserva);
    }

    private function construirRespuestaSolicitante($solicitante, $reserva)
    {
        return [
            'success' => true,
            'tipo_ocupacion' => 'solicitante',
            'nombre' => $solicitante->nombre ?? 'No especificado',
            'run_solicitante' => $solicitante->run_solicitante ?? 'No especificado',
            'correo' => $solicitante->correo ?? 'No especificado',
            'telefono' => $solicitante->telefono ?? 'No especificado',
            'tipo_solicitante' => $solicitante->tipo_solicitante ?? 'No especificado',
            'activo' => $solicitante->activo ?? false,
            'fecha_registro' => $solicitante->fecha_registro ?? null,
            'hora_inicio' => $reserva->hora,
            'hora_salida' => $reserva->hora_salida,
            'tipo_reserva' => $reserva->tipo_reserva
        ];
    }

    private function obtenerProximaClase($idEspacio, $horaActual)
    {
        $fechaActual = now()->format('Y-m-d');

        // PRIMERO: Buscar reservas futuras del día actual.
        // Excluimos reservas espontáneas porque no tienen clase asociada y mostrarían "Reserva sin asignatura".
        $reservaFutura = Reserva::select('id_reserva', 'run_profesor', 'run_solicitante', 'hora', 'hora_salida', 'id_asignatura')
            ->with(['asignatura:id_asignatura,nombre_asignatura', 'profesor:run_profesor,name'])
            ->where('id_espacio', $idEspacio)
            ->where('fecha_reserva', $fechaActual)
            ->where('hora', '>', $horaActual)
            ->where('estado', 'activa')
            ->where('tipo_reserva', '!=', 'espontanea')
            ->orderBy('hora', 'asc')
            ->first();

        if ($reservaFutura) {
            return [
                'asignatura' => $reservaFutura->asignatura?->nombre_asignatura ?? 'Reserva sin asignatura',
                'profesor' => $reservaFutura->profesor?->name ?? 'No especificado',
                'profesor_run' => $reservaFutura->run_profesor ?? null,
                'hora_inicio' => $reservaFutura->hora ?? null,
                'hora_termino' => $reservaFutura->hora_salida ?? null
            ];
        }

        // SEGUNDO: Si no hay reservas futuras, buscar en planificaciones
        $diaActual = strtolower(now()->format('l'));
        $codigosDias = [
            'monday' => 'LU',
            'tuesday' => 'MA',
            'wednesday' => 'MI',
            'thursday' => 'JU',
            'friday' => 'VI',
            'saturday' => 'SA',
            'sunday' => 'DO'
        ];
        $codigoDia = $codigosDias[$diaActual] ?? 'LU';

        // [OPTIMIZACIÓN] Filtrar directamente en SQL para mayor rapidez
        $proxima = Planificacion_Asignatura::with(['modulo', 'asignatura', 'horario.profesor'])
            ->where('id_espacio', $idEspacio)
            ->whereHas('modulo', function ($query) use ($codigoDia, $horaActual) {
                $query->where('dia', $codigoDia)
                    ->where('hora_inicio', '>', $horaActual);
            })
            ->join('modulos', 'planificacion_asignaturas.id_modulo', '=', 'modulos.id_modulo')
            ->orderBy('modulos.hora_inicio', 'asc')
            ->select('planificacion_asignaturas.*')
            ->first();

        if (!$proxima) {
            return null;
        }

        return [
            'asignatura' => $proxima->asignatura->nombre_asignatura ?? 'Sin asignatura',
            'profesor' => $proxima->horario->profesor->name ?? 'No especificado',
            'profesor_run' => $proxima->horario->profesor->run_profesor ?? null,
            'hora_inicio' => $proxima->modulo->hora_inicio ?? null,
            'hora_termino' => $proxima->modulo->hora_termino ?? null
        ];
    }


    private function obtenerClaseAnterior($idEspacio, $horaActual)
    {
        $fechaActual = now()->format('Y-m-d');

        // PRIMERO: Buscar reservas FINALIZADAS anteriores del día actual.
        // Excluimos reservas activas para no mostrar la reserva actual como "clase anterior".
        $reservaAnterior = Reserva::select('id_reserva', 'run_profesor', 'run_solicitante', 'hora', 'hora_salida', 'id_asignatura', 'tipo_reserva')
            ->with(['asignatura:id_asignatura,nombre_asignatura', 'profesor:run_profesor,name'])
            ->where('id_espacio', $idEspacio)
            ->where('fecha_reserva', $fechaActual)
            ->where('hora', '<', $horaActual)
            ->where('estado', 'finalizada')
            ->orderBy('hora', 'desc')
            ->first();

        if ($reservaAnterior) {
            // Las reservas espontáneas no deben mostrar nombre de asignatura
            $esEspontanea = $reservaAnterior->tipo_reserva === 'espontanea';

            return [
                'asignatura' => $esEspontanea
                    ? 'Reserva espontánea'
                    : ($reservaAnterior->asignatura?->nombre_asignatura ?? 'Reserva sin asignatura'),
                'profesor' => $reservaAnterior->profesor?->name ?? 'No especificado',
                'profesor_run' => $reservaAnterior->run_profesor ?? null,
                'hora_inicio' => $reservaAnterior->hora ?? null,
                'hora_termino' => $reservaAnterior->hora_salida ?? null
            ];
        }

        // SEGUNDO: Si no hay reservas anteriores, buscar en planificaciones (SQL filtrado)
        $diaActual = strtolower(now()->format('l'));
        $codigosDias = [
            'monday' => 'LU',
            'tuesday' => 'MA',
            'wednesday' => 'MI',
            'thursday' => 'JU',
            'friday' => 'VI',
            'saturday' => 'SA',
            'sunday' => 'DO'
        ];
        $codigoDia = $codigosDias[$diaActual] ?? 'LU';

        $anterior = Planificacion_Asignatura::with(['modulo', 'asignatura', 'horario.profesor'])
            ->where('id_espacio', $idEspacio)
            ->whereHas('modulo', function ($query) use ($codigoDia, $horaActual) {
                $query->where('dia', $codigoDia)
                    ->where('hora_termino', '<=', $horaActual);
            })
            ->join('modulos', 'planificacion_asignaturas.id_modulo', '=', 'modulos.id_modulo')
            ->orderBy('modulos.hora_termino', 'desc')
            ->select('planificacion_asignaturas.*')
            ->first();

        if ($anterior) {
            return [
                'asignatura' => $anterior->asignatura->nombre_asignatura ?? 'Sin asignatura',
                'profesor' => $anterior->horario->profesor->name ?? 'No especificado',
                'profesor_run' => $anterior->horario->profesor->run_profesor ?? null,
                'hora_inicio' => $anterior->modulo->hora_inicio ?? null,
                'hora_termino' => $anterior->modulo->hora_termino ?? null
            ];
        }

        return null;
    }
}

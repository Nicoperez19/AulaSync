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

    /**
     * Normaliza id_espacio aplicando prefijo del tenant cuando corresponda.
     */
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

    /**
     * Autoriza CRUD de espacios según sede (admins solo su sede, superusers todas).
     */
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

    /**
     * Autoriza gestión de un espacio ya existente.
     */
    private function autorizarGestionEspacio(Espacio $espacio): void
    {
        $espacio->loadMissing('piso.facultad');

        $idSede = $espacio->piso?->facultad?->id_sede;
        if (!$idSede) {
            abort(403, 'No se pudo determinar la sede del espacio.');
        }

        $this->autorizarGestionSede((string) $idSede);
    }

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

    /**
     * Obtiene el horario de un módulo específico
     */
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

    /**
     * Obtiene información de la próxima clase programada
     */
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

            $reservasFinalizadasAnticipadamente = Reserva::where('fecha_reserva', $fechaActual)
                ->where('estado', 'finalizada')
                ->where('clase_finalizada_anticipadamente', true)
                ->where('id_espacio', $idEspacio)
                ->get();

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

            if ($planActual) {
                $claseFinalizada = $reservasFinalizadasAnticipadamente->where('id_asignatura', $planActual->id_asignatura)->first();
                if ($claseFinalizada) {
                    $planActual = null;
                }
            }

            if ($planProxima) {
                $claseFinalizada = $reservasFinalizadasAnticipadamente->where('id_asignatura', $planProxima->id_asignatura)->first();
                if ($claseFinalizada) {
                    $planProxima = null;
                }
            }

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

    /**
     * Obtiene información de un solicitante usando datos ya cargados en la reserva (sin query extra)
     */
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

    /**

     * Obtiene información de un profesor
     */
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

    /**
     * Obtiene información de la planificación actual del espacio (Regular o Colaborador)
     */
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

    /**
     * Obtiene información de un solicitante con cache optimizado
     */
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

    /**
     * Construye la respuesta para solicitantes
     */
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

    /**
     * Obtiene información de la próxima clase
     */
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

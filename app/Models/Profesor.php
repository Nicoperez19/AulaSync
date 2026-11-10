<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Profesor extends Model
{
    use HasFactory, BelongsToTenant;

    protected $connection = 'tenant';
    protected $table = 'profesors';
    protected $primaryKey = 'run_profesor';
    public $incrementing = false;

    protected $fillable = [
        'run_profesor',
        'name',
        'email',
        'celular',
        'direccion',
        'fecha_nacimiento',
        'anio_ingreso',
        'tipo_profesor',
        'id_universidad',
        'id_facultad',
        'id_carrera',
        'id_area_academica',
        'sede_id'
    ];





    // Relación con Universidad
    public function universidad()
    {
        return $this->belongsTo(Universidad::class, 'id_universidad');
    }

    // Relación con Facultad
    public function facultad()
    {
        return $this->belongsTo(Facultad::class, 'id_facultad');
    }

    // Relación con Carrera
    public function carrera()
    {
        return $this->belongsTo(Carrera::class, 'id_carrera');
    }

    // Relación con Area Académica
    public function areaAcademica()
    {
        return $this->belongsTo(AreaAcademica::class, 'id_area_academica');
    }

    // Relación con Sede
    public function sede()
    {
        return $this->belongsTo(Sede::class, 'sede_id', 'id_sede');
    }

    // Relación con Horarios
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'run_profesor', 'run_profesor');
    }

    // Relación con Asignaturas
    public function asignaturas()
    {
        return $this->hasMany(Asignatura::class, 'run_profesor', 'run_profesor');
    }

    // Relación con Reservas
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'run_profesor', 'run_profesor');
    }

    // Relación con DataLoads
    public function dataLoads()
    {
        return $this->hasMany(DataLoad::class, 'profesor_run', 'run_profesor');
    }

    // Relación con Licencias
    public function licencias()
    {
        return $this->hasMany(LicenciaProfesor::class, 'run_profesor', 'run_profesor');
    }

    // Relación con Recuperaciones de Clases
    public function recuperaciones()
    {
        return $this->hasMany(RecuperacionClase::class, 'run_profesor', 'run_profesor');
    }

    // Relación con Clases Temporales (como colaborador)
    public function clasesTemporalesColaborador()
    {
        return $this->hasMany(ProfesorColaborador::class, 'id_profesor', 'run_profesor');
    }

  
    // Método para obtener el nombre completo
    public function getNombreCompleto()
    {
        return $this->name;
    }

    // Método para verificar si es un tipo específico de profesor
    public function esTipo($tipo)
    {
        return $this->tipo_profesor === $tipo;
    }

    // Método para verificar si es Profesor Responsable
    public function esResponsable()
    {
        return $this->esTipo('Profesor Responsable');
    }

    // Método para verificar si es Profesor Colaborador
    public function esColaborador()
    {
        return $this->esTipo('Profesor Colaborador') || $this->esTipo('Colaborador');
    }

    // Método para verificar si es Invitado
    public function esInvitado()
    {
        return $this->esTipo('Invitado');
    }

    // Método para verificar si es Ayudante
    public function esAyudante()
    {
        return $this->esTipo('Ayudante');
    }

    /**
     * Busca un profesor por su RUN en el tenant actual o mediante fallback cross-tenant.
     * Soporta formatos con o sin dígito verificador, puntos y guiones.
     */
    public static function buscarProfesor($run): ?self
    {
        if (empty($run)) {
            return null;
        }

        $runRaw = (string) $run;
        $runLimpio = strtoupper(preg_replace('/[^0-9Kk]/', '', $runRaw));
        $runSinDv = strlen($runLimpio) > 7 ? substr($runLimpio, 0, -1) : $runLimpio;

        // 1. Buscar en el tenant actual
        $profesor = self::withoutGlobalScopes()
            ->where(function ($q) use ($runRaw, $runLimpio, $runSinDv) {
                $q->where('run_profesor', $runRaw)
                  ->orWhere('run_profesor', $runLimpio)
                  ->orWhere('run_profesor', $runSinDv)
                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runLimpio])
                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(run_profesor, '.', ''), '-', ''), ' ', '') = ?", [$runSinDv]);
            })
            ->first();

        if ($profesor) {
            return $profesor;
        }

        // 2. Fallback cross-tenant: buscar en las bases de datos de otros tenants
        if (class_exists(Tenant::class)) {
            $currentId = Tenant::current()?->id ?? 0;
            $otherTenants = Tenant::where('id', '!=', $currentId)->get();
            foreach ($otherTenants as $ot) {
                if (empty($ot->database)) {
                    continue;
                }
                try {
                    $row = \Illuminate\Support\Facades\DB::connection('mysql')
                        ->table("{$ot->database}.profesors")
                        ->where(function ($q) use ($runRaw, $runLimpio, $runSinDv) {
                            $q->where('run_profesor', $runRaw)
                              ->orWhere('run_profesor', $runLimpio)
                              ->orWhere('run_profesor', $runSinDv);
                        })
                        ->first();
                    if ($row) {
                        $prof = new self();
                        $prof->forceFill((array) $row);
                        $prof->exists = true;
                        return $prof;
                    }
                } catch (\Throwable $e) {
                    // ignorar errores si no existe la base de datos o tabla
                }
            }
        }

        return null;
    }
}

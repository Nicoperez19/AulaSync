<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioNoRegistrado extends Model
{
    use HasFactory;

    protected $table = 'usuarios_no_registrados';

    protected $fillable = [
        'run',
        'nombre',
        'email',
        'telefono',
        'modulos_utilizacion',
        'convertido_a_usuario',
        'id_usuario_registrado'
    ];

    protected $casts = [
        'convertido_a_usuario' => 'boolean',
        'modulos_utilizacion' => 'integer',
    ];

    /**
     * Relación con las reservas
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'run', 'run');
    }

    /**
     * Relación con el profesor registrado (si se convierte)
     */
    public function profesorRegistrado()
    {
        return $this->belongsTo(Profesor::class, 'id_usuario_registrado', 'run_profesor');
    }



    /**
     * Convierte el usuario no registrado en un profesor registrado
     */
    public function convertirAProfesorRegistrado()
    {
        // Crear el profesor registrado
        $profesor = new Profesor();
        $profesor->run_profesor = $this->run;
        $profesor->name = $this->nombre;
        $profesor->email = $this->email;
        $profesor->tipo_profesor = 'Profesor Colaborador'; // Por defecto
        $profesor->save();

        // Marcar como convertido
        $this->convertido_a_usuario = true;
        $this->id_usuario_registrado = $this->run;
        $this->save();

        return $profesor;
    }

    /**
     * Scope para usuarios no convertidos
     */
    public function scopeNoConvertidos($query)
    {
        return $query->where('convertido_a_usuario', false);
    }

    /**
     * Scope para usuarios convertidos
     */
    public function scopeConvertidos($query)
    {
        return $query->where('convertido_a_usuario', true);
    }
}

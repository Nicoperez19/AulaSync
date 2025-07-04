<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\QRService;

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
        'qr_run',
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
     * Relación con el usuario registrado (si se convierte)
     */
    public function usuarioRegistrado()
    {
        return $this->belongsTo(User::class, 'id_usuario_registrado', 'run');
    }

    /**
     * Genera el código QR para el usuario no registrado
     */
    public function generateQR()
    {
        $qrService = new QRService();
        $qrFileName = $qrService->generateQRForUser($this->run);
        $this->qr_run = $qrFileName;
        $this->save();
        return $this;
    }

    /**
     * Convierte el usuario no registrado en un usuario registrado
     */
    public function convertirAUsuarioRegistrado()
    {
        // Crear el usuario registrado
        $usuario = new User();
        $usuario->run = $this->run;
        $usuario->name = $this->nombre;
        $usuario->email = $this->email;
        $usuario->celular = $this->telefono;
        $usuario->password = bcrypt('password123'); // Contraseña temporal
        $usuario->save();

        // Generar QR para el usuario registrado
        $usuario->generateQR();

        // Marcar como convertido
        $this->convertido_a_usuario = true;
        $this->id_usuario_registrado = $this->run;
        $this->save();

        return $usuario;
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

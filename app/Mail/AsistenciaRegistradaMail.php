<?php

namespace App\Mail;

use App\Models\Asignatura;
use App\Models\Estudiante;
use App\Models\Profesor;
use App\Models\SesionAsistencia;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AsistenciaRegistradaMail extends Mailable
{
    use Queueable, SerializesModels;

    public Estudiante $estudiante;
    public ?Asignatura $asignatura;
    public ?SesionAsistencia $sesion;
    public ?Profesor $docente;
    public string $nombreClase;
    public string $horaRegistro;
    public string $fechaRegistro;
    public string $sala;

    public function __construct(Estudiante $estudiante, ?Asignatura $asignatura, ?SesionAsistencia $sesion, ?Profesor $docente, string $nombreClase, string $sala)
    {
        $this->estudiante = $estudiante;
        $this->asignatura = $asignatura;
        $this->sesion = $sesion;
        $this->docente = $docente;
        $this->nombreClase = $nombreClase;
        $this->sala = $sala;
        $this->fechaRegistro = Carbon::today()->translatedFormat('l, d \d\e F \d\e Y');
        $this->horaRegistro = Carbon::now()->format('H:i');
    }

    public function build()
    {
        return $this->subject('Comprobante de Asistencia: ' . $this->nombreClase)
                    ->view('emails.asistencia-registrada');
    }
}

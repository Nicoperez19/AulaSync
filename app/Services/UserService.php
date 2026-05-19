<?php

namespace App\Services;

use App\Models\Profesor;
use App\Models\Solicitante;
use App\Traits\BelongsToTenant;

class UserService
{
    use BelongsToTenant;

    /**
     * Busca un usuario en la base de datos de Profesores y luego en Solicitantes.
     * Retorna un arreglo estructurado o null si no se encuentra.
     *
     * @param string $run RUN del usuario a buscar (soporta formatos con o sin guion/dv)
     * @return array|null
     */
    public function buscarPorRun($run)
    {
        // Limpiar el RUN dejando solo números y letra K/k
        $runLimpio = preg_replace('/[^0-9Kk]/', '', $run);
        
        // El RUN en la BD puede estar guardado de diferentes formas, 
        // pero vamos a usar la forma que manda la vista o el escáner.
        // La normalización asume que las consultas se hacen con RUN con DV pero sin puntos ni guion,
        // o con guion dependiendo del estándar de AulaSync.
        // NOTA: En PlanoDigitalController::normalizeRun se hace un formateo con guion (ej. 12345678-9).
        // Vamos a asegurarnos de buscar el formato proporcionado.
        
        $runBuscado = $run; // Dejar que el controlador normalice antes de pasar

        // 1. Buscar en Profesores
        $profesor = Profesor::select('run_profesor', 'name', 'email', 'celular', 'tipo_profesor')
            ->where('run_profesor', $runBuscado)
            ->first();

        if ($profesor) {
            return [
                'tipo_usuario' => 'profesor',
                'usuario' => [
                    'run' => $profesor->run_profesor,
                    'nombre' => $profesor->name,
                    'email' => $profesor->email,
                    'telefono' => $profesor->celular,
                    'tipo_profesor' => $profesor->tipo_profesor
                ]
            ];
        }

        // 2. Buscar en Solicitantes (Tabla del Tenant)
        $solicitante = Solicitante::on('tenant')
            ->where('run_solicitante', $runBuscado)
            ->where('activo', true)
            ->first();

        if ($solicitante) {
            return [
                'tipo_usuario' => 'solicitante_registrado',
                'usuario' => [
                    'run' => $solicitante->run_solicitante,
                    'nombre' => $solicitante->nombre,
                    'email' => $solicitante->correo,
                    'telefono' => $solicitante->telefono
                ]
            ];
        }

        return null;
    }
}

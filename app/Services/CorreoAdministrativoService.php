<?php

namespace App\Services;

use App\Models\AsistenteAcademico;
use App\Models\AreaAcademica;
use App\Models\Espacio;
use App\Models\Profesor;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CorreoAdministrativoService
{
    /**
     * Obtiene el correo administrativo del asistente académico de un área académica
     *
     * @param string $idAreaAcademica ID del área académica
     * @return array ['email' => string, 'name' => string]
     */
    public static function getCorreoAreaAcademica(string $idAreaAcademica): array
    {
        try {
            return Cache::remember("asistente_academico_{$idAreaAcademica}", 3600, function () use ($idAreaAcademica) {
                $asistente = AsistenteAcademico::where('id_area_academica', $idAreaAcademica)->first();
                
                if ($asistente && $asistente->email) {
                    return [
                        'email' => $asistente->email,
                        'name' => $asistente->nombre_remitente ?? $asistente->nombre,
                    ];
                }
                
                // Fallback al correo por defecto del sistema
                return self::getCorreoDefault();
            });
        } catch (\Exception $e) {
            Log::error('Error al obtener correo del asistente académico', [
                'area_academica_id' => $idAreaAcademica,
                'error' => $e->getMessage()
            ]);
            
            return self::getCorreoDefault();
        }
    }

    /**
     * Obtiene el correo administrativo según el espacio (detecta el área académica automáticamente)
     * 
     * @param int $idEspacio ID del espacio
     * @return array ['email' => string, 'name' => string]
     */
    public static function getCorreoPorEspacio(int $idEspacio): array
    {
        try {
            $espacio = Espacio::with('areaAcademica')->find($idEspacio);
            
            if ($espacio && $espacio->id_area_academica) {
                return self::getCorreoAreaAcademica($espacio->id_area_academica);
            }
            
            // Fallback
            return self::getCorreoDefault();
        } catch (\Exception $e) {
            Log::error('Error al obtener correo por espacio', [
                'espacio_id' => $idEspacio,
                'error' => $e->getMessage()
            ]);
            
            return self::getCorreoDefault();
        }
    }

    /**
     * Obtiene el correo administrativo según el profesor (detecta el área académica automáticamente)
     * 
     * @param string $runProfesor RUN del profesor
     * @return array ['email' => string, 'name' => string]
     */
    public static function getCorreoPorProfesor(string $runProfesor): array
    {
        try {
            $profesor = Profesor::with('areaAcademica')->where('run_profesor', $runProfesor)->first();
            
            if ($profesor && $profesor->id_area_academica) {
                return self::getCorreoAreaAcademica($profesor->id_area_academica);
            }
            
            // Fallback
            return self::getCorreoDefault();
        } catch (\Exception $e) {
            Log::error('Error al obtener correo por profesor', [
                'profesor_run' => $runProfesor,
                'error' => $e->getMessage()
            ]);
            
            return self::getCorreoDefault();
        }
    }

    /**
     * Obtiene la configuración de correo por defecto del sistema
     * 
     * @return array ['email' => string, 'name' => string]
     */
    public static function getCorreoDefault(): array
    {
        return [
            'email' => config('mail.from.address'),
            'name' => config('mail.from.name'),
        ];
    }

    /**
     * Limpia el caché del asistente académico de un área específica
     * 
     * @param string $idAreaAcademica ID del área académica
     * @return void
     */
    public static function limpiarCache(string $idAreaAcademica): void
    {
        Cache::forget("asistente_academico_{$idAreaAcademica}");
    }

    /**
     * Limpia todo el caché de asistentes académicos
     * 
     * @return void
     */
    public static function limpiarTodoElCache(): void
    {
        $asistentes = AsistenteAcademico::all();
        
        foreach ($asistentes as $asistente) {
            Cache::forget("asistente_academico_{$asistente->id_area_academica}");
        }
    }
}

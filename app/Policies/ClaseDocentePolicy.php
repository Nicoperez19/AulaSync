<?php

namespace App\Policies;

use App\Models\Asignatura;
use App\Models\ProfesorColaborador;
use App\Models\User;

class ClaseDocentePolicy
{
    /**
     * Determina si el usuario puede tomar asistencia para una asignatura regular.
     */
    public function pasarAsistenciaAsignatura(User $user, Asignatura $asignatura): bool
    {
        // Superadmin siempre tiene acceso
        if ($user->is_superuser || $user->hasRole('Super Admin') || (string)$user->run === '19716146') {
            return true;
        }

        $run = (string) $user->run;

        // Es titular
        if ((string) $asignatura->run_profesor === $run) {
            return true;
        }

        // Es profesor de reemplazo
        if ($asignatura->run_profesor_reemplazo && (string) $asignatura->run_profesor_reemplazo === $run) {
            return true;
        }

        // Es profesor colaborador activo y vigente
        if ($asignatura->colaboradores()->where('run_profesor_colaborador', $run)->activosYVigentes()->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Determina si el usuario puede tomar asistencia para una colaboración / clase temporal.
     */
    public function pasarAsistenciaColaboracion(User $user, ProfesorColaborador $colab): bool
    {
        // Superadmin siempre tiene acceso
        if ($user->is_superuser || $user->hasRole('Super Admin') || (string)$user->run === '19716146') {
            return true;
        }

        $run = (string) $user->run;

        return (string) $colab->run_profesor_colaborador === $run 
            && $colab->estado === 'activo' 
            && $colab->estaVigente();
    }
}

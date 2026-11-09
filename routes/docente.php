<?php

use App\Http\Controllers\Docente\AsistenciaController;
use App\Http\Controllers\Docente\DocenteDashboardController;
use App\Http\Controllers\Docente\ReporteAsistenciaDocenteController;
use App\Http\Controllers\Docente\SupervisionDocenteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->get('/docente/sede-no-inicializada', [DocenteDashboardController::class, 'sedeNoInicializada'])->name('docente.sede-no-inicializada');

Route::middleware(['auth', 'portal.docente'])->prefix('docente')->name('docente.')->group(function () {
    Route::get('/', [DocenteDashboardController::class, 'index'])->name('dashboard');

    Route::get('/asignaturas/{id}/asistencia', [AsistenciaController::class, 'show'])->name('asistencia.show');
    Route::get('/asignaturas/{id}/asistencia/en-vivo', [AsistenciaController::class, 'estadoEnVivo'])->name('asistencia.en-vivo');
    Route::post('/asignaturas/{id}/asistencia', [AsistenciaController::class, 'store'])->name('asistencia.store');
    Route::get('/asignaturas/{id}/historial', [AsistenciaController::class, 'historial'])->name('asistencia.historial');

    Route::get('/reportes-asistencia', [ReporteAsistenciaDocenteController::class, 'index'])->name('reportes-asistencia.index');
    Route::get('/reportes-asistencia/asignatura/{id}', [ReporteAsistenciaDocenteController::class, 'asignatura'])->name('reportes-asistencia.asignatura');
    Route::get('/reportes-asistencia/sesion/{sesionId}', [ReporteAsistenciaDocenteController::class, 'sesionDetalle'])->name('reportes-asistencia.sesion');
    Route::get('/reportes-asistencia/export', [ReporteAsistenciaDocenteController::class, 'exportar'])->name('reportes-asistencia.export');

    Route::get('/supervision', [SupervisionDocenteController::class, 'index'])->name('supervision');
    Route::post('/supervision/seleccionar/{run}', [SupervisionDocenteController::class, 'seleccionar'])->name('supervision.seleccionar');
    Route::post('/supervision/restablecer', [SupervisionDocenteController::class, 'restablecer'])->name('supervision.restablecer');
});

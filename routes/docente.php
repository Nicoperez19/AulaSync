<?php

use App\Http\Controllers\Docente\AsistenciaController;
use App\Http\Controllers\Docente\DocenteDashboardController;
use App\Http\Controllers\Docente\SupervisionDocenteController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Docente\ReporteAsistenciaDocenteController;

// Ruta informativa cuando la sede no está inicializada
Route::middleware(['auth'])->get('/docente/sede-no-inicializada', [DocenteDashboardController::class, 'sedeNoInicializada'])->name('docente.sede-no-inicializada');

// Rutas protegidas del Portal Docente (exclusivas para profesores y superadministrador)
Route::middleware(['auth', 'portal.docente'])->prefix('docente')->name('docente.')->group(function () {
    // Dashboard: Mis asignaturas y clases
    Route::get('/', [DocenteDashboardController::class, 'index'])->name('dashboard');

    // Toma y consulta de asistencia
    Route::get('/asignaturas/{id}/asistencia', [AsistenciaController::class, 'show'])->name('asistencia.show');
    Route::post('/asignaturas/{id}/asistencia', [AsistenciaController::class, 'store'])->name('asistencia.store');
    Route::get('/asignaturas/{id}/historial', [AsistenciaController::class, 'historial'])->name('asistencia.historial');

    // Reportes de asistencia docente (rutas particulares e independientes)
    Route::get('/reportes-asistencia', [ReporteAsistenciaDocenteController::class, 'index'])->name('reportes-asistencia.index');
    Route::get('/reportes-asistencia/asignatura/{id}', [ReporteAsistenciaDocenteController::class, 'asignatura'])->name('reportes-asistencia.asignatura');
    Route::get('/reportes-asistencia/sesion/{sesionId}', [ReporteAsistenciaDocenteController::class, 'sesionDetalle'])->name('reportes-asistencia.sesion');
    Route::get('/reportes-asistencia/export', [ReporteAsistenciaDocenteController::class, 'exportar'])->name('reportes-asistencia.export');

    // Supervisión del Portal Docente (Superadministrador)
    Route::get('/supervision', [SupervisionDocenteController::class, 'index'])->name('supervision');
    Route::post('/supervision/seleccionar/{run}', [SupervisionDocenteController::class, 'seleccionar'])->name('supervision.seleccionar');
    Route::post('/supervision/restablecer', [SupervisionDocenteController::class, 'restablecer'])->name('supervision.restablecer');
});

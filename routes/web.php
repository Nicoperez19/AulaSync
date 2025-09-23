<?php
use App\Http\Controllers\AreaAcademicaController;
use App\Http\Controllers\EspacioController;
use App\Http\Controllers\FacultadController;
use App\Http\Controllers\CarreraController;
use App\Http\Controllers\HorariosController;
use App\Http\Controllers\MapasController;
use App\Http\Controllers\ReservasController;
use App\Http\Controllers\PermisionController;
use App\Http\Controllers\PisoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UniversidadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AsignaturaController;
use App\Http\Controllers\DataLoadController;
use App\Http\Controllers\PlanoDigitalController;
use App\Http\Controllers\ProfesorController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\SedeController;
use App\Http\Controllers\QuickActionsController;
use App\Http\Controllers\ClasesNoRealizadasController;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;

use Spatie\Permission\Middleware\RoleMiddleware;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth/login');
})->middleware('guest');

// Dashboard - Solo Administrador y Supervisor
Route::middleware(['auth', 'permission:dashboard', 'dashboard.timelimit'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/widget-data', [DashboardController::class, 'getWidgetData'])->name('dashboard.widget-data');
    Route::get('/dashboard/horarios-semana', [DashboardController::class, 'horariosSemana'])->name('dashboard.horarios-semana');
});

Route::middleware(['auth', 'role:Administrador'])->group(function () {
    Route::get('/user/user_index', [UserController::class, 'index'])->name('users.index');
    Route::post('/user/user_store', [UserController::class, 'store'])->name('users.add');
    Route::delete('/user/user_delete/{run}', [UserController::class, 'destroy'])->name('users.delete');
    Route::get('/user/user_edit/{run}', [UserController::class, 'edit'])->name('users.edit');
    Route::put('user/user_update/{run}', [UserController::class, 'update'])->name('users.update');
    
    // Estadísticas de clases no realizadas
    Route::get('/clases-no-realizadas', [\App\Http\Controllers\ClasesNoRealizadasController::class, 'index'])->name('clases-no-realizadas.index');
});

// Horarios profesores - Solo Administrador y Supervisor
Route::middleware(['auth', 'permission:horarios profesores'])->group(function () {
    Route::get('/horarios/horarios_index', [HorariosController::class, 'index'])->name('horarios.index');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/horarios/{run}', [HorariosController::class, 'getHorarioProfesor'])->name('horarios.get');
    Route::get('/horarios/{run}/export-pdf', [HorariosController::class, 'exportHorarioProfesorPDF'])->name('horarios.export-pdf');
    
    // Rutas con tiempo de ejecución extendido para el módulo de visualización
    Route::middleware(['extend.execution:180'])->group(function () {
        Route::get('/modulos-actuales', [\App\Http\Controllers\TableController::class, 'index'])->name('modulos.actuales');
        Route::get('/modulos-actuales/actualizar-datos', [\App\Http\Controllers\TableController::class, 'actualizarDatos'])->name('modulos.actuales.datos');
    });
    
    // Autocomplete de usuarios (email)
    Route::get('/api/usuarios/autocomplete', [\App\Http\Controllers\UserController::class, 'autocomplete'])->name('usuarios.autocomplete');
    // Endpoint para obtener módulos disponibles de un espacio (usado por reservas)
    Route::get('/api/espacio/{idEspacio}/modulos-disponibles', [\App\Http\Controllers\EspacioController::class, 'modulosDisponibles'])->name('espacio.modulos.disponibles');
});

// Horarios por espacios - Solo Administrador y Supervisor
Route::middleware(['auth', 'permission:horarios por espacios'])->group(function () {
    Route::get('/spacetime/spacetime_index', [HorariosController::class, 'showEspacios'])->name('horarios_espacios.index');
    Route::get('/horarios-espacios', [HorariosController::class, 'getHorariosEspacios'])->name('horarios.espacios.get');
    Route::get('/espacios', action: [HorariosController::class, 'showEspacios'])->name('espacios.show');
    Route::get('/espacios/{idEspacio}/export-pdf', [HorariosController::class, 'exportHorarioEspacioPDF'])->name('espacios.export-pdf');
    Route::get('/horarios-por-periodo', [HorariosController::class, 'getHorariosPorPeriodo'])->name('horarios.por-periodo');
});

Route::group(['middleware' => ['permission:mantenedor de roles']], function () {
    Route::get('/rol/rol_index', [RoleController::class, 'index'])->name('roles.index');
    Route::delete('/rol/rol_delete/{id}', [RoleController::class, 'destroy'])->name('roles.delete');
    Route::get('/rol/rol_edit/{id}', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/rol/rol_update/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::post('/rol/rol_store', [RoleController::class, 'store'])->name('roles.add');
});

Route::group(['middleware' => ['permission:mantenedor de permisos']], function () {
    Route::get('/permission/permission_index', [PermisionController::class, 'index'])->name('permissions.index');
    Route::delete('/permission/permission_delete/{id}', [PermisionController::class, 'destroy'])->name('permission.delete');
    Route::get('/permission/permission_edit/{id}', [PermisionController::class, 'edit'])->name('permissions.edit');
    Route::put('/permission/permission_update/{id}', [PermisionController::class, 'update'])->name('permissions.update');
    Route::post('/permission/permission_store', [PermisionController::class, 'store'])->name('permission.add');
});

Route::group(['middleware' => ['permission:mantenedor de universidades']], function () {
    Route::get('/universities', [UniversidadController::class, 'index'])->name('universities.index');
    Route::post('/universities', [UniversidadController::class, 'store'])->name('universities.store');
    Route::get('/universities/{id}/edit', [UniversidadController::class, 'edit'])->name('universities.edit');
    Route::put('/universities/{id}', [UniversidadController::class, 'update'])->name('universities.update');
    Route::delete('/universities/{id}', [UniversidadController::class, 'destroy'])->name('universities.delete');
});

Route::group(['middleware' => ['permission:mantenedor de sedes']], function () {
    Route::get('/sedes', [SedeController::class, 'index'])->name('sedes.index');
    Route::post('/sedes', [SedeController::class, 'store'])->name('sedes.store');
    Route::get('/sedes/{id}/edit', [SedeController::class, 'edit'])->name('sedes.edit');
    Route::put('/sedes/{id}', [SedeController::class, 'update'])->name('sedes.update');
    Route::delete('/sedes/{id}', [SedeController::class, 'destroy'])->name('sedes.destroy');
});

Route::group(['middleware' => ['permission:mantenedor de facultades']], function () {
    Route::get('/faculties', [FacultadController::class, 'index'])->name('faculties.index');
    Route::post('/faculties', [FacultadController::class, 'store'])->name('faculties.add');
    Route::get('/faculties/{id}/edit', [FacultadController::class, 'edit'])->name('faculties.edit');
    Route::put('/faculties/{id}', [FacultadController::class, 'update'])->name('faculties.update');
    Route::delete('/faculties/{id}', [FacultadController::class, 'destroy'])->name('faculties.delete');
});

Route::group(['middleware' => ['permission:mantenedor de campus']], function () {
    Route::get('/campus', [CampusController::class, 'index'])->name('campus.index');
    Route::post('/campus', [CampusController::class, 'store'])->name('campus.store');
    Route::get('/campus/{id}/edit', [CampusController::class, 'edit'])->name('campus.edit');
    Route::put('/campus/{id}', [CampusController::class, 'update'])->name('campus.update');
    Route::delete('/campus/{id}', [CampusController::class, 'destroy'])->name('campus.destroy');
});

Route::group(['middleware' => ['permission:mantenedor de carreras']], function () {
    Route::get('/careers', [CarreraController::class, 'index'])->name('careers.index');
    Route::post('/careers', [CarreraController::class, 'store'])->name('careers.add');
    Route::get('/careers/{id}/edit', [CarreraController::class, 'edit'])->name('careers.edit');
    Route::put('/careers/{id}', [CarreraController::class, 'update'])->name('careers.update');
    Route::delete('/careers/{id}', [CarreraController::class, 'destroy'])->name('careers.delete');
});

Route::group(['middleware' => ['permission:mantenedor de areas academicas']], function () {
    Route::get('/academic_areas', [AreaAcademicaController::class, 'index'])->name('academic_areas.index');
    Route::post('/academic_areas', [AreaAcademicaController::class, 'store'])->name('academic_areas.add');
    Route::get('/academic_areas/{id}/edit', [AreaAcademicaController::class, 'edit'])->name('academic_areas.edit');
    Route::put('/academic_areas/{id}', [AreaAcademicaController::class, 'update'])->name('academic_areas.update');
    Route::delete('/academic_areas/{id}', [AreaAcademicaController::class, 'destroy'])->name('academic_areas.delete');
});

Route::group(['middleware' => ['permission:mantenedor de profesores']], function () {
    Route::get('/professors', [ProfesorController::class, 'index'])->name('professors.index');
    Route::post('/professors', [ProfesorController::class, 'store'])->name('professors.add');
    Route::get('/professors/{id}/edit', [ProfesorController::class, 'edit'])->name('professors.edit');
    Route::put('/professors/{id}', [ProfesorController::class, 'update'])->name('professors.update');
    Route::delete('/professors/{id}', [ProfesorController::class, 'destroy'])->name('professors.delete');
});

Route::group(['middleware' => ['permission:mantenedor de pisos']], function () {
    Route::get('/floors', [PisoController::class, 'index'])->name('floors_index');
    Route::post('/facultad/{facultadId}/agregar-piso', [PisoController::class, 'agregarPiso'])->name('floors.agregarPiso');
    Route::delete('/facultad/{facultadId}/eliminar-piso', [PisoController::class, 'eliminarPiso'])->name('floors.eliminarPiso');
});

Route::group(['middleware' => ['auth', 'permission:mantenedor de mapas']], function () {
    Route::get('/mapas', [MapasController::class, 'index'])->name('mapas.index');
    Route::get('/mapas/add', [MapasController::class, 'add'])->name('mapas.add');
    Route::post('/mapas/store', [MapasController::class, 'store'])->name('mapas.store');
    Route::get('/mapas/{id}/edit', [MapasController::class, 'edit'])->name('mapas.edit');
    Route::put('/mapas/{id}', [MapasController::class, 'update'])->name('mapas.update');
    Route::get('/sedes/{universidadId}', [MapasController::class, 'getSedes']);
    Route::get('/facultades-por-sede/{sedeId}', [MapasController::class, 'getFacultadesPorSede']);
    Route::get('/pisos/{facultadId}', [MapasController::class, 'getPisos']);
    Route::get('/espacios-por-piso/{pisoId}', [MapasController::class, 'getEspaciosPorPiso']);
    Route::get('/mapa/{mapa}/bloques', [MapasController::class, 'getBloques'])->name('mapa.bloques');
});

Route::group(['middleware' => ['permission:mantenedor de espacios']], function () {
    Route::get('spaces', [EspacioController::class, 'index'])->name('spaces_index');
    Route::get('spaces/{id_espacio}/edit', [EspacioController::class, 'edit'])->name('spaces.edit');
    Route::post('/spaces', [EspacioController::class, 'store'])->name(name: 'spaces.store');
    Route::put('spaces/{id_espacio}', [EspacioController::class, 'update'])->name('spaces.update');
    Route::delete('/spaces/{id}', [EspacioController::class, 'destroy'])->name('spaces.delete');
    Route::get('/pisos/{facultadId}', [EspacioController::class, 'getPisos']);
    Route::get('/spaces/{id_espacio}/download-qr', [EspacioController::class, 'downloadQR'])->name('spaces.download-qr');
    Route::get('/spaces/download-all-qr', [EspacioController::class, 'downloadAllQR'])->name('spaces.download-all-qr');
    Route::get('/sedes/{universidadId}', [EspacioController::class, 'getSedes']);
    Route::get('/facultades-por-sede/{sedeId}', [EspacioController::class, 'getFacultadesPorSede']);
    // Endpoint para obtener facultades por universidad (usado por reservas)
    Route::get('/facultades/{universidadId}', [EspacioController::class, 'getFacultades']);
});

Route::group(['middleware' => ['permission:mantenedor de reservas']], function () {
    Route::get('/reservas', [ReservasController::class, 'index'])->name('reservas.index');
    Route::get('/reservas/create', [ReservasController::class, 'create'])->name('reservas.add');
    Route::post('/reservas', [ReservasController::class, 'store'])->name('reservas.store');
    Route::get('/reservas/{id_reserva}/edit', [ReservasController::class, 'edit'])->name('reservas.edit');
    Route::put('/reservas/{id_reserva}', [ReservasController::class, 'update'])->name('reservas.update');
    Route::delete('/reservas/{id_reserva}', [ReservasController::class, 'destroy'])->name('reservas.delete');
    Route::get('/espacios-disponibles', [ReservasController::class, 'getEspaciosDisponibles']);
});

Route::group(['middleware' => ['permission:mantenedor de asignaturas']], function () {
    Route::get('/asignaturas', [AsignaturaController::class, 'index'])->name('asignaturas.index');
    Route::post('/asignaturas', [AsignaturaController::class, 'store'])->name('asignaturas.store');
    Route::get('/{id_asignatura}/edit', [AsignaturaController::class, 'edit'])->name('asignaturas.edit');
    Route::put('/{id_asignatura}', [AsignaturaController::class, 'update'])->name('asignaturas.update');
    Route::delete('/{id_asignatura}', [AsignaturaController::class, 'destroy'])->name('asignaturas.destroy');
});

Route::group(['middleware' => ['permission:mantenedor de carga de datos']], function () {
    Route::get('/data', [DataLoadController::class, 'index'])->name('data.index');
    Route::post('/data_loads/upload', [DataLoadController::class, 'upload'])->name('data.upload');
    Route::delete('/data/{dataLoad}', [DataLoadController::class, 'destroy'])->name('data.destroy');
    Route::get('/data/detalle/{id}', [DataLoadController::class, 'detalleJson'])->name('data.detalle');
    Route::get('/data/download/{id}', [DataLoadController::class, 'download'])->name('data.download');
    Route::get('/data/progress/{id}', [DataLoadController::class, 'progress'])->name('data.progress');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Monitoreo de espacios - Todos los roles
Route::group(['middleware' => ['auth', 'session.timeout', 'permission:monitoreo de espacios']], function () {
    Route::get('/plano-digital', [PlanoDigitalController::class, 'index'])->name('plano.index');
    Route::get('/plano-digital/{id}', [PlanoDigitalController::class, 'show'])->name('plano.show');
    Route::get('/plano/{id}/bloques', [PlanoDigitalController::class, 'bloques'])->name('plano.bloques');
    Route::get('/plano/{id}/modulo-actual', [PlanoDigitalController::class, 'getModuloActual'])->name('plano.modulo-actual');
    Route::get('/plano/{id}/data', [PlanoDigitalController::class, 'getPlanoData'])->name('plano.data');
    Route::get('/api/profesor/{run}', [ProfesorController::class, 'getProfesor'])->name('profesor.get');
});

// Reportes - Solo Administrador y Supervisor
Route::prefix('reportes')->middleware(['auth', 'permission:reportes'])->group(function () {
    Route::get('tipo-espacio', [ReportController::class, 'tipoEspacio'])->name('reportes.tipo-espacio');
    Route::get('tipo-espacio/datos-historicos', [ReportController::class, 'getHistoricoTipoEspacio'])->name('reportes.tipo-espacio.historico');
    Route::get('tipo-espacio/debug-planificacion', [ReportController::class, 'debugPlanificacion'])->name('reportes.tipo-espacio.debug');
    Route::get('tipo-espacio/export/{format}', [ReportController::class, 'exportTipoEspacio'])->name('reportes.tipo-espacio.export');
    Route::get('espacios', [ReportController::class, 'espacios'])->name('reportes.espacios');
    Route::get('accesos', [ReportController::class, 'accesos'])->name('reportes.accesos');
    Route::get('accesos/limpiar', [ReportController::class, 'limpiarFiltrosAccesos'])->name('reportes.accesos.limpiar');
    Route::get('accesos/{id}/detalles', [ReportController::class, 'getDetallesAcceso'])->name('reportes.accesos.detalles');
    Route::get('espacios/export/{format}', [ReportController::class, 'exportEspacios'])->name('reportes.espacios.export');
    Route::get('accesos/export/{format}', [ReportController::class, 'exportAccesos'])->name('reportes.accesos.export');
    Route::post('accesos/export/{format}', [ReportController::class, 'exportAccesosConFiltros'])->name('reportes.accesos.export.filtros');
});

require __DIR__ . '/auth.php';

// Rutas para Acciones Rápidas (Mantenedores)
Route::middleware(['auth'])->prefix('quick-actions')->name('quick-actions.')->group(function () {
    Route::get('/', [QuickActionsController::class, 'index'])->name('index');
    Route::get('/crear-reserva', [QuickActionsController::class, 'crearReserva'])->name('crear-reserva');
    Route::get('/gestionar-reservas', [QuickActionsController::class, 'gestionarReservas'])->name('gestionar-reservas');
    Route::get('/gestionar-espacios', [QuickActionsController::class, 'gestionarEspacios'])->name('gestionar-espacios');
    
    // API endpoints para quick actions
    Route::get('/dashboard-data', [QuickActionsController::class, 'getDashboardData'])->name('dashboard-data');
    Route::get('/api/espacios', [QuickActionsController::class, 'getEspacios'])->name('api.espacios');
    Route::get('/api/reservas', [QuickActionsController::class, 'getReservas'])->name('api.reservas');
    Route::get('/debug/test', function () {
        return response()->json([
            'message' => 'Quick Actions API funcionando',
            'user' => auth()->user()->name ?? 'No autenticado',
            'timestamp' => now()
        ]);
    })->name('debug.test');
    
    Route::get('/debug/espacios', function () {
        try {
            $count = \App\Models\Espacio::count();
            $first = \App\Models\Espacio::first();
            return response()->json([
                'message' => 'Debug espacios',
                'count' => $count,
                'first_espacio' => $first,
                'columns' => $first ? array_keys($first->toArray()) : []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
        }
    })->name('debug.espacios');
    
    // API para buscar personas por RUN (autocompletado)
    Route::get('/api/buscar-personas', [QuickActionsController::class, 'buscarPersonas'])->name('api.buscar-personas');
    
    // API para crear reserva
    Route::post('/api/crear-reserva', [QuickActionsController::class, 'procesarCrearReserva'])->name('api.crear-reserva');
    
    // API para obtener reservas
    Route::get('/api/reservas', [QuickActionsController::class, 'getReservas'])->name('api.reservas');
    
    // API para cambiar estado de espacio
    Route::put('/api/espacio/{codigo}/estado', [QuickActionsController::class, 'cambiarEstadoEspacio'])->name('quick-actions.api.cambiar-estado-espacio');
    
    // API para cambiar estado de reserva
    Route::put('/api/reserva/{id}/estado', [QuickActionsController::class, 'cambiarEstadoReserva'])->name('quick-actions.api.cambiar-estado-reserva');
    
    // Debug para verificar estructura de personas
    Route::get('/debug/personas', function () {
        try {
            $profesorCount = \App\Models\Profesor::count();
            $solicitanteCount = \App\Models\Solicitante::count();
            $firstProfesor = \App\Models\Profesor::first();
            $firstSolicitante = \App\Models\Solicitante::first();
            
            return response()->json([
                'message' => 'Debug personas',
                'profesores_count' => $profesorCount,
                'solicitantes_count' => $solicitanteCount,
                'first_profesor' => $firstProfesor,
                'first_solicitante' => $firstSolicitante,
                'profesor_columns' => $firstProfesor ? array_keys($firstProfesor->toArray()) : [],
                'solicitante_columns' => $firstSolicitante ? array_keys($firstSolicitante->toArray()) : []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
        }
    })->name('debug.personas');
    Route::put('/api/espacio/{codigo}/estado', [QuickActionsController::class, 'cambiarEstadoEspacio'])->name('api.espacio.estado');
    Route::put('/api/reserva/{id}/estado', [QuickActionsController::class, 'cambiarEstadoReserva'])->name('api.reserva.estado');
});

// Ruta para obtener el token CSRF
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
});

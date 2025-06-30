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
use App\Http\Controllers\ReporteriaController;
use App\Http\Controllers\NotificationController;

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
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::middleware(['auth', 'role:Administrador'])->group(function () {
    // Notifications
    Route::get('/api/notifications/key-returns', [DashboardController::class, 'getKeyReturnNotifications'])->name('notifications.key-returns');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::post('/notifications/delete', [NotificationController::class, 'delete'])->name('notifications.delete');
    Route::post('/notifications/clear-all', [NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::get('/notifications/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/notifications/recent', [NotificationController::class, 'getRecentNotifications'])->name('notifications.recent');
    Route::get('/notifications/filter', [NotificationController::class, 'filter'])->name('notifications.filter');

    Route::get('/user/user_index', [UserController::class, 'index'])->name('users.index');
    Route::post('/user/user_store', [UserController::class, 'store'])->name('users.add');
    Route::delete('/user/user_delete/{run}', [UserController::class, 'destroy'])->name('users.delete');
    Route::get('/user/user_edit/{run}', [UserController::class, 'edit'])->name('users.edit');
    Route::put('user/user_update/{run}', [UserController::class, 'update'])->name('users.update');
    Route::get('/horarios/horarios_index', [HorariosController::class, 'index'])->name('horarios.index');
    Route::get('/horarios/{run}', [HorariosController::class, 'getHorarioProfesor'])->name('horarios.get');
    Route::get('/spacetime/spacetime_index', [HorariosController::class, 'mostrarHorarios'])->name('horarios_espacios.index');
    Route::get('/horarios-espacios', [HorariosController::class, 'getHorariosEspacios'])->name('horarios.espacios.get');
    Route::get('/espacios', action: [HorariosController::class, 'showEspacios'])->name('espacios.show');

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

Route::group(['middleware' => ['permission:mantenedor de facultades']], function () {
    Route::get('/faculties', [FacultadController::class, 'index'])->name('faculties.index');
    Route::post('/faculties', [FacultadController::class, 'store'])->name('faculties.add');
    Route::get('/faculties/{id}/edit', [FacultadController::class, 'edit'])->name('faculties.edit');
    Route::put('/faculties/{id}', [FacultadController::class, 'update'])->name('faculties.update');
    Route::delete('/faculties/{id}', [FacultadController::class, 'destroy'])->name('faculties.delete');
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

Route::group(['middleware' => ['permission:mantenedor de pisos']], function () {
    Route::get('/floors', [PisoController::class, 'index'])->name('floors_index');
    Route::post('/facultad/{facultadId}/agregar-piso', [PisoController::class, 'agregarPiso'])->name('floors.agregarPiso');
    Route::delete('/facultad/{facultadId}/eliminar-piso', [PisoController::class, 'eliminarPiso'])->name('floors.eliminarPiso');
});

Route::group(['middleware' => ['auth', 'permission:mantenedor de mapas']], function () {
    Route::get('/mapas', [MapasController::class, 'index'])->name('mapas.index');
    Route::get('/mapas/add', [MapasController::class, 'add'])->name('mapas.add');
    Route::post('/mapas/store', [MapasController::class, 'store'])->name('mapas.store');
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
    Route::get('/data/{dataLoad}', [DataLoadController::class, 'show'])->name('data.show');
    Route::post('/data_loads/upload', [DataLoadController::class, 'upload'])->name('data.upload');
    Route::delete('/data/{dataLoad}', [DataLoadController::class, 'destroy'])->name('data.destroy');
    Route::get('/data/progress/{dataLoad}', [DataLoadController::class, 'getProgress'])->name('data.progress');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::get('/buttons/text', function () {
    return view('buttons-showcase.text');
})->middleware(['auth'])->name('buttons.text');

Route::get('/buttons/icon', function () {
    return view('buttons-showcase.icon');
})->middleware(['auth'])->name('buttons.icon');

Route::get('/buttons/text-icon', function () {
    return view('buttons-showcase.text-icon');
})->middleware(['auth'])->name('buttons.text-icon');

Route::group(['middleware' => ['auth', 'session.timeout']], function () {
    Route::get('/plano-digital', [PlanoDigitalController::class, 'index'])->name('plano.index');
    Route::get('/plano-digital/{id}', [PlanoDigitalController::class, 'show'])->name('plano.show');
    Route::get('/plano/{id}/bloques', [PlanoDigitalController::class, 'bloques'])->name('plano.bloques');
    Route::get('/plano/{id}/modulo-actual', [PlanoDigitalController::class, 'getModuloActual'])->name('plano.modulo-actual');
    Route::get('/plano/{id}/data', [PlanoDigitalController::class, 'getPlanoData'])->name('plano.data');
    Route::get('/api/profesor/{run}', [ProfesorController::class, 'getProfesor'])->name('profesor.get');
});

Route::prefix('reporteria')->group(function () {
    Route::get('utilizacion_por_espacio', [ReporteriaController::class, 'utilizacion'])->name('reporteria.utilizacion_por_espacio');
    Route::get('tipo-espacio', [ReporteriaController::class, 'tipoEspacio'])->name('reporteria.tipo-espacio');
    Route::get('accesos', [ReporteriaController::class, 'accesos'])->name('reporteria.accesos');
    Route::get('accesos/limpiar', [ReporteriaController::class, 'limpiarFiltrosAccesos'])->name('reporteria.accesos.limpiar');
    Route::get('accesos/{id}/detalles', [ReporteriaController::class, 'getDetallesAcceso'])->name('reporteria.accesos.detalles');
    Route::get('unidad-academica', [ReporteriaController::class, 'unidadAcademica'])->name('reporteria.unidad-academica');
    // Rutas para exportar a Excel y PDF
    Route::get('utilizacion/export/{format}', [ReporteriaController::class, 'exportUtilizacion'])->name('reporteria.utilizacion.export');
    Route::get('tipo-espacio/export/{format}', [ReporteriaController::class, 'exportTipoEspacio'])->name('reporteria.tipo-espacio.export');
    Route::get('accesos/export/{format}', [ReporteriaController::class, 'exportAccesos'])->name('reporteria.accesos.export');
    Route::post('accesos/export/{format}', [ReporteriaController::class, 'exportAccesosConFiltros'])->name('reporteria.accesos.export.filtros');
    Route::get('unidad-academica/export/{format}', [ReporteriaController::class, 'exportUnidadAcademica'])->name('reporteria.unidad-academica.export');
});

Route::post('/dashboard/set-piso', [DashboardController::class, 'setPiso'])->name('dashboard.setPiso');
Route::get('/dashboard/widget-data', [DashboardController::class, 'getWidgetData'])->name('dashboard.widgetData');
Route::get('/dashboard/utilizacion-tipo-espacio', [App\Http\Controllers\DashboardController::class, 'utilizacionTipoEspacioAjax'])->name('dashboard.utilizacion_tipo_espacio');
Route::get('/dashboard/no-utilizadas-dia', [App\Http\Controllers\DashboardController::class, 'noUtilizadasDiaAjax']);

require __DIR__ . '/auth.php';

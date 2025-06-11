<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Espacio;
use App\Models\Reserva;
use App\Models\User;
use App\Models\Carrera;
use App\Models\Incidente;
use App\Models\Acceso;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class ReporteriaController extends Controller
{
    // 1. Utilización de espacios
    public function utilizacion(Request $request) {
        // Lógica para obtener datos de utilización de espacios
        return view('reporteria.utilizacion');
    }
    public function exportUtilizacion($format) {
        // Lógica para exportar a Excel o PDF
    }

    // 2. Análisis por tipo de espacio
    public function tipoEspacio(Request $request) {
        return view('reporteria.tipo-espacio');
    }
    public function exportTipoEspacio($format) {
    }

    // 3. Accesos registrados
    public function accesos(Request $request) {
        return view('reporteria.accesos');
    }
    public function exportAccesos($format) {
    }

    // 4. Reportes por unidad académica
    public function unidadAcademica(Request $request) {
        return view('reporteria.unidad-academica');
    }
    public function exportUnidadAcademica($format) {
    }
} 
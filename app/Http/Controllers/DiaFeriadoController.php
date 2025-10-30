<?php

namespace App\Http\Controllers;

use App\Models\DiaFeriado;
use Illuminate\Http\Request;

class DiaFeriadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('layouts.dias-feriados.index');
    }
}

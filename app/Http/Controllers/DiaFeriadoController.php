<?php

namespace App\Http\Controllers;

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

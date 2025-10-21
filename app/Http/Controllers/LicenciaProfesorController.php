<?php

namespace App\Http\Controllers;

use App\Models\LicenciaProfesor;
use Illuminate\Http\Request;

class LicenciaProfesorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('layouts.licencias.index');
    }
}

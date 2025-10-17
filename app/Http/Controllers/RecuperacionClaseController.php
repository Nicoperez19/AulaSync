<?php

namespace App\Http\Controllers;

use App\Models\RecuperacionClase;
use Illuminate\Http\Request;

class RecuperacionClaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('layouts.recuperacion-clases.index');
    }
}

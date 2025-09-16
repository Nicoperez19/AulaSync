<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClasesNoRealizadasController extends Controller
{
    public function index()
    {
        return view('admin.clases-no-realizadas');
    }
}

<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Jurisprudence;
use Inertia\Inertia;

class JurisprudenceController extends Controller 
{
    public function index(Request $request) 
    {
        return Inertia::render('Jurisprudence/Index');
    }

    public function create() 
    {
        return Inertia::render('Jurisprudence/Create');
    }
}
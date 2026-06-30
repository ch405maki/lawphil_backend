<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Genor;
use Inertia\Inertia;

class GenorController extends Controller 
{
    public function index(Request $request) 
    {
        return Inertia::render('Executive/Genor/Index');
    }

    public function create() 
    {
        return Inertia::render('Executive/Genor/Create');
    }
}
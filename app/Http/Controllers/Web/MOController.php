<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\MO;
use Inertia\Inertia;

class MOController extends Controller 
{
    public function index(Request $request) 
    {
        return Inertia::render('Executive/MO/Index');
    }

    public function create() 
    {
        return Inertia::render('Executive/MO/Create');
    }
}
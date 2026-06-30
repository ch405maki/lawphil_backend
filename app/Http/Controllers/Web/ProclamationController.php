<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Proclamation;
use Inertia\Inertia;

class ProclamationController extends Controller 
{
    public function index(Request $request) 
    {
        return Inertia::render('Executive/Proclamation/Index');
    }

    public function create() 
    {
        return Inertia::render('Executive/Proclamation/Create');
    }
}
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Execord;
use Inertia\Inertia;

class ExecordController extends Controller 
{
    public function index(Request $request) 
    {
        return Inertia::render('Executive/Execord/Index');
    }

    public function create() 
    {
        return Inertia::render('Executive/Execord/Create');
    }
}
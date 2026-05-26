<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AO;
use Inertia\Inertia;

class AOController extends Controller 
{
    public function index(Request $request) 
    {
        return Inertia::render('Executive/AO/Index');
    }

    public function create() 
    {
        return Inertia::render('Executive/AO/Create');
    }
}
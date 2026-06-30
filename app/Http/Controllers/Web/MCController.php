<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\MC;
use Inertia\Inertia;

class MCController extends Controller 
{
    public function index(Request $request) 
    {
        return Inertia::render('Executive/MC/Index');
    }

    public function create() 
    {
        return Inertia::render('Executive/MC/Create');
    }
}
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Presidential;
use Inertia\Inertia;

class PresidentialController extends Controller 
{
    public function index(Request $request) 
    {
        return Inertia::render('Executive/Presidential/Index');
    }

    public function create() 
    {
        return Inertia::render('Executive/Presidential/Create');
    }
}
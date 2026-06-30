<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Republic;
use Inertia\Inertia;

class RepublicController extends Controller 
{
    public function index(Request $request) 
    {
        return Inertia::render('Republic/Index');
    }

    public function create() 
    {
        return Inertia::render('Republic/Create');
    }
}
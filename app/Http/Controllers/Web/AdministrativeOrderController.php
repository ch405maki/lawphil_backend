<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\AdministrativeOrder;
use Inertia\Inertia;

class AdministrativeOrderController extends Controller 
{
    public function index(Request $request) 
    {
        return Inertia::render('AdministrativeOrder/Index');
    }

    public function create() 
    {
        return Inertia::render('AdministrativeOrder/Create');
    }
}
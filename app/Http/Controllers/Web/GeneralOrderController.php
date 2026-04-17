<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\GeneralOrder;
use Inertia\Inertia;

class GeneralOrderController extends Controller 
{
    public function index(Request $request) 
    {
        // I-a-assume natin na gagawa tayo ng folder na 'GeneralOrder' sa loob ng Vue Pages
        return Inertia::render('GeneralOrder/Index');
    }

    public function create() 
    {
        return Inertia::render('GeneralOrder/Create');
    }
}
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExecutiveOrder;
use Inertia\Inertia;

class ExecutiveOrderController extends Controller 
{
    public function index(Request $request) 
    {
        return Inertia::render('ExecutiveOrders/Index');
    }

    public function create() 
    {
        
        return Inertia::render('ExecutiveOrders/Create');
    }
}
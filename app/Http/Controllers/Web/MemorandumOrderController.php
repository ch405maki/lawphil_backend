<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\MemorandumOrder;
use Inertia\Inertia;

class MemorandumOrderController extends Controller 
{
    public function index(Request $request) 
    {
        // I-a-assume natin na gagawa tayo ng folder na 'MemorandumOrder' sa loob ng Vue Pages
        return Inertia::render('MemorandumOrder/Index'); 
    }

    public function create() 
    {
        return Inertia::render('MemorandumOrder/Create');
    }
}
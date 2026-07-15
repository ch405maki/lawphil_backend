<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BatasPambansaController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('BatasPambansa/Index');
    }

    public function create()
    {
        return Inertia::render('BatasPambansa/Create');
    }
}

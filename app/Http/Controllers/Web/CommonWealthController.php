<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CommonWealthController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Commonwealth/Index');
    }

    public function create()
    {
        return Inertia::render('Commonwealth/Create');
    }
}

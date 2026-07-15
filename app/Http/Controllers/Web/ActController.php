<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Acts/Index');
    }

    public function create()
    {
        return Inertia::render('Acts/Create');
    }
}

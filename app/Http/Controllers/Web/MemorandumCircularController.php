<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class MemorandumCircularController extends Controller 
{
    public function index() 
    {
        return Inertia::render('MemorandumCirculars/Index');
    }

    public function create() 
    {
        return Inertia::render('MemorandumCirculars/Create');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Jurisprudence;
use App\Imports\JurisprudenceImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class JurisprudenceController extends Controller 
{
    public function index(Request $request) 
    {
        $query = Jurisprudence::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('citation', 'LIKE', "%$s%")
                  ->orWhere('gr_number', 'LIKE', "%$s%");
            });
        }

        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        if ($request->sort === 'az') {
            $query->orderBy('citation', 'asc');
        } elseif ($request->sort === 'za') {
            $query->orderBy('citation', 'desc');
        } elseif ($request->sort === 'oldest') {
            $query->orderBy('date', 'asc');
        } else {
            $query->orderBy('date', 'desc');
        }

        return Inertia::render('Jurisprudence/Index', [
            'cases' => $query->paginate($request->get('rows', 10))->withQueryString(),
            'filters' => $request->only(['search', 'year', 'sort', 'rows'])
        ]);
    }

    public function create() 
    {
        return Inertia::render('Jurisprudence/Create');
    }

    public function import(Request $request) 
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);

        try {
            Excel::import(new JurisprudenceImport, $request->file('file'));
            return redirect()->back();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
        }
    }
}
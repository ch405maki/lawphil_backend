<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Imports\GeneralOrderImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class GeneralOrderImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new GeneralOrderImport, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'General Orders imported successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
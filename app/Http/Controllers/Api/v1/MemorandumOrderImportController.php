<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Imports\MemorandumOrderImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MemorandumOrderImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new MemorandumOrderImport, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Import successful!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error during import: ' . $e->getMessage(),
            ], 500);
        }
    }
}
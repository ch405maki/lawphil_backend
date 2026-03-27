<?php

namespace App\Http\Controllers;

use App\Models\Jurisprudence;
use App\Imports\JurisprudenceImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class JurisprudenceController extends Controller {

    public function index(Request $request) {
        try {
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

            if ($request->filled('volume')) {
                $query->where('reference', 'LIKE', "%" . $request->volume . "%");
            }

            return response()->json($query->latest('date')->paginate($request->get('rows', 10)));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id) {
        $record = Jurisprudence::findOrFail($id);

        $request->validate([
            'gr_number' => 'required|string',
            'date'      => 'required|date',
            'citation'  => 'required|string',
            'pdf_file'  => 'nullable|mimes:pdf|max:15360', // Adjusted to 15MB
        ]);

        $data = $request->only(['gr_number', 'date', 'citation', 'ponente', 'reference', 'url']);

        if ($request->hasFile('pdf_file')) {
            // Cleanup old file if it exists
            if ($record->pdf_path) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $record->pdf_path));
            }

            $path = $request->file('pdf_file')->store('jurisprudence_pdfs', 'public');
            $data['pdf_path'] = '/storage/' . $path;
            $data['pdf_availability'] = true;
        }

        $record->update($data);

        return response()->json($record);
    }

    public function destroy($id) {
        $record = Jurisprudence::findOrFail($id);
        if ($record->pdf_path) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $record->pdf_path));
        }
        $record->delete();
        return response()->json(['status' => 'deleted']);
    }

    public function truncate() {
        Jurisprudence::truncate();
        return response()->json(['status' => 'table cleared']);
    }

    public function import(Request $request) {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        try {
            Excel::import(new JurisprudenceImport, $request->file('file'));
            return response()->json(['message' => 'Import Successful']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
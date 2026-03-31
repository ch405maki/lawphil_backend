<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Jurisprudence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class JurisprudenceController extends Controller 
{
    public function index(Request $request) 
    {
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

            if ($request->sort === 'az') {
                $query->orderBy('citation', 'asc');
            } elseif ($request->sort === 'za') {
                $query->orderBy('citation', 'desc');
            } elseif ($request->sort === 'oldest') {
                $query->orderBy('date', 'asc');
            } else {
                $query->orderBy('date', 'desc');
            }

            return response()->json($query->paginate($request->get('rows', 10)));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'gr_number' => 'required|string|max:255',
                'date' => 'required|date',
                'citation' => 'nullable|string',
                'ponente' => 'nullable|string',
                'reference' => 'nullable|string',
                'url' => 'nullable|string',
                'pdf_availability' => 'nullable|boolean',
                'subject' => 'nullable|string',
                'pdf_path' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $jurisprudence = Jurisprudence::create([
                'user_id' => 1,
                'gr_number' => $request->gr_number,
                'date' => $request->date,
                'citation' => $request->citation,
                'ponente' => $request->ponente,
                'reference' => $request->reference,
                'url' => $request->url,
                'pdf_availability' => $request->pdf_availability ?? false,
                'subject' => $request->subject,
                'pdf_path' => $request->pdf_path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jurisprudence record created successfully',
                'data' => $jurisprudence
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id) 
    {
        try {
            $record = Jurisprudence::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'gr_number' => 'sometimes|required|string|max:255',
                'date' => 'sometimes|required|date',
                'citation' => 'nullable|string',
                'ponente' => 'nullable|string',
                'reference' => 'nullable|string',
                'url' => 'nullable|string',
                'pdf_availability' => 'nullable|boolean',
                'subject' => 'nullable|string',
                'pdf_path' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->only([
                'gr_number', 
                'date', 
                'citation', 
                'ponente', 
                'reference', 
                'url',
                'pdf_availability',
                'subject',
                'pdf_path'
            ]);

            $record->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Jurisprudence record updated successfully',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id) 
    {
        try {
            $record = Jurisprudence::findOrFail($id);
            $record->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Jurisprudence record deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete record: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:jurisprudence,id'
            ]);

            $deleted = Jurisprudence::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deleted} records",
                'deleted_count' => $deleted
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete records: ' . $e->getMessage()
            ], 500);
        }
    }
}
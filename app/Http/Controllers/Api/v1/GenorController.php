<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Genor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class GenorController extends Controller 
{
    public function index(Request $request) 
    {
        try {
            $query = Genor::query();

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function($q) use ($s) {
                    $q->where('citation', 'LIKE', "%$s%")
                    ->orWhere('genor_number', 'LIKE', "%$s%")
                    ->orWhere('tenure', 'LIKE', "%$s%");
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
                'genor_number' => 'required|string|max:255',
                'date' => 'required|date',
                'citation' => 'nullable|string',
                'tenure' => 'nullable|string',
                'url' => 'nullable|string',
                'pdf_availability' => 'nullable|boolean',
                'description' => 'nullable|string',
                'pdf_path' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $genor = Genor::create([
                'user_id' => 1,
                'genor_number' => $request->genor_number,
                'date' => $request->date,
                'citation' => $request->citation,
                'tenure' => $request->tenure,
                'url' => $request->url,
                'pdf_availability' => $request->pdf_availability ?? false,
                'description' => $request->description,
                'pdf_path' => $request->pdf_path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'General Order record created successfully',
                'data' => $genor
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
            $record = Genor::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'genor_number' => 'sometimes|required|string|max:255',
                'date' => 'sometimes|required|date',
                'citation' => 'nullable|string',
                'tenure' => 'nullable|string',
                'description' => 'nullable|string',
                'url' => 'nullable|string',
                'pdf_availability' => 'nullable|boolean',
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
                'genor_number', 
                'date', 
                'citation', 
                'tenure', 
                'description', 
                'url',
                'pdf_availability',
                'pdf_path'
            ]);

            $record->update($data);

            return response()->json([
                'success' => true,
                'message' => 'General Order record updated successfully',
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
            $record = Genor::findOrFail($id);
            $record->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'General Order record deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete record: ' . $e->getMessage()
            ], 500);
        }
    }

}
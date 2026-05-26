<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Proclamation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ProclamationController extends Controller 
{
    public function index(Request $request) 
    {
        try {
            $query = Proclamation::query();

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function($q) use ($s) {
                    $q->where('citation', 'LIKE', "%$s%")
                    ->orWhere('proc_number', 'LIKE', "%$s%");
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
                'proc_number' => 'required|string|max:255',
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

            $proclamation = Proclamation::create([
                'user_id' => 1,
                'proc_number' => $request->proc_number,
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
                'message' => 'Proclamation record created successfully',
                'data' => $proclamation
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
            $record = Proclamation::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'proc_number' => 'sometimes|required|string|max:255',
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
                'proc_number', 
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
                'message' => 'Proclamation record updated successfully',
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
            $record = Proclamation::findOrFail($id);
            $record->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Proclamation record deleted successfully'
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
            // If select_all is true, delete all records matching filters
            if ($request->select_all) {
                $query = Proclamation::query();
                
                // Apply filters
                if (!empty($request->filters['search'])) {
                    $s = $request->filters['search'];
                    $query->where(function($q) use ($s) {
                        $q->where('citation', 'LIKE', "%$s%")
                        ->orWhere('proc_number', 'LIKE', "%$s%");
                    });
                }

                if (!empty($request->filters['year'])) {
                    $query->whereYear('date', $request->filters['year']);
                }

                if (!empty($request->filters['sort'])) {
                    if ($request->filters['sort'] === 'az') {
                        $query->orderBy('citation', 'asc');
                    } elseif ($request->filters['sort'] === 'za') {
                        $query->orderBy('citation', 'desc');
                    } elseif ($request->filters['sort'] === 'oldest') {
                        $query->orderBy('date', 'asc');
                    } else {
                        $query->orderBy('date', 'desc');
                    }
                }

                $deleted = $query->delete();

                return response()->json([
                    'success' => true,
                    'message' => "Successfully deleted {$deleted} records",
                    'deleted_count' => $deleted
                ]);
            } 
            
            // Otherwise, delete only selected IDs
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:proclamation,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $deleted = Proclamation::whereIn('id', $request->ids)->delete();

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
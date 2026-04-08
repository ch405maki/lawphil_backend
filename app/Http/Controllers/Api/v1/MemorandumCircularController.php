<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\MemorandumCircular;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MemorandumCircularController extends Controller 
{
    public function index(Request $request) 
    {
        try {
            $query = MemorandumCircular::query();

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function($q) use ($s) {
                    $q->where('subject', 'LIKE', "%$s%")
                      ->orWhere('mc_number', 'LIKE', "%$s%");
                });
            }

            if ($request->filled('year')) {
                $query->whereYear('date', $request->year);
            }

            if ($request->sort === 'az') {
                $query->orderBy('subject', 'asc');
            } elseif ($request->sort === 'za') {
                $query->orderBy('subject', 'desc');
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
                'mc_number' => 'required|string|max:255',
                'date' => 'required|date',
                'subject' => 'nullable|string',
                'reference' => 'nullable|string',
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

            $circular = MemorandumCircular::create([
                'user_id' => 1, // Or auth()->id() if you are using authentication
                'mc_number' => $request->mc_number,
                'date' => $request->date,
                'subject' => $request->subject,
                'reference' => $request->reference,
                'url' => $request->url,
                'pdf_availability' => $request->pdf_availability ?? false,
                'pdf_path' => $request->pdf_path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Memorandum Circular record created successfully',
                'data' => $circular
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
            $record = MemorandumCircular::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'mc_number' => 'sometimes|required|string|max:255',
                'date' => 'sometimes|required|date',
                'subject' => 'nullable|string',
                'reference' => 'nullable|string',
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
                'mc_number', 
                'date', 
                'subject', 
                'reference', 
                'url',
                'pdf_availability',
                'pdf_path'
            ]);

            $record->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Memorandum Circular record updated successfully',
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
            $record = MemorandumCircular::findOrFail($id);
            $record->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Memorandum Circular record deleted successfully'
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
            // Kung select_all ay true, buburahin lahat ng pasok sa filter
            if ($request->select_all) {
                $query = MemorandumCircular::query();
                
                // I-apply ang filters
                if (!empty($request->filters['search'])) {
                    $s = $request->filters['search'];
                    $query->where(function($q) use ($s) {
                        $q->where('subject', 'LIKE', "%$s%")
                          ->orWhere('mc_number', 'LIKE', "%$s%");
                    });
                }

                if (!empty($request->filters['year'])) {
                    $query->whereYear('date', $request->filters['year']);
                }

                if (!empty($request->filters['sort'])) {
                    if ($request->filters['sort'] === 'az') {
                        $query->orderBy('subject', 'asc');
                    } elseif ($request->filters['sort'] === 'za') {
                        $query->orderBy('subject', 'desc');
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
            
            // Kung hindi select_all, buburahin lang yung mga naka-check na IDs
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:memorandum_circulars,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $deleted = MemorandumCircular::whereIn('id', $request->ids)->delete();

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
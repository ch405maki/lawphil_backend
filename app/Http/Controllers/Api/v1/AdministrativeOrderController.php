<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdministrativeOrderController extends Controller 
{
    /**
     * Display a listing of the resource with filters and sorting.
     */
    public function index(Request $request) 
    {
        try {
            $query = AdministrativeOrder::query();

            // Unified search for ao_number, description, and subject
            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function($q) use ($s) {
                    $q->where('description', 'LIKE', "%$s%")
                      ->orWhere('ao_number', 'LIKE', "%$s%")
                      ->orWhere('subject', 'LIKE', "%$s%");
                });
            }

            // Year filtering
            if ($request->filled('year')) {
                $query->whereYear('date', $request->year);
            }

            // Sorting logic
            if ($request->sort === 'az') {
                $query->orderBy('description', 'asc');
            } elseif ($request->sort === 'za') {
                $query->orderBy('description', 'desc');
            } elseif ($request->sort === 'oldest') {
                $query->orderBy('date', 'asc');
            } else {
                $query->orderBy('date', 'desc'); // Default: Newest first
            }

            return response()->json($query->paginate($request->get('rows', 10)));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ao_number' => 'required|string|max:255',
                'date' => 'required|date',
                'description' => 'nullable|string',
                'subject' => 'nullable|string',
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

            $order = AdministrativeOrder::create([
                'user_id' => auth()->id() ?? 1,
                'ao_number' => $request->ao_number,
                'date' => $request->date,
                'description' => $request->description,
                'subject' => $request->subject,
                'url' => $request->url,
                'pdf_availability' => $request->boolean('pdf_availability', false),
                'pdf_path' => $request->pdf_path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Administrative Order created successfully',
                'data' => $order
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) 
    {
        try {
            $record = AdministrativeOrder::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'ao_number' => 'sometimes|required|string|max:255',
                'date' => 'sometimes|required|date',
                'description' => 'nullable|string',
                'subject' => 'nullable|string',
                'url' => 'nullable|string',
                'pdf_availability' => 'nullable|boolean',
                'pdf_path' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            // Manual mapping for boolean field if present
            $data = $request->all();
            if ($request->has('pdf_availability')) {
                $data['pdf_availability'] = $request->boolean('pdf_availability');
            }

            $record->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Administrative Order updated successfully',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) 
    {
        try {
            $record = AdministrativeOrder::findOrFail($id);
            $record->delete();
            return response()->json(['success' => true, 'message' => 'Record deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle bulk deletion.
     */
    public function bulkDelete(Request $request)
    {
        try {
            if ($request->select_all) {
                $query = AdministrativeOrder::query();
                $deleted = $query->delete();
                return response()->json(['success' => true, 'deleted_count' => $deleted]);
            } 
            
            if (!$request->has('ids') || empty($request->ids)) {
                return response()->json(['success' => false, 'message' => 'No IDs provided'], 400);
            }

            $deleted = AdministrativeOrder::whereIn('id', $request->ids)->delete();
            return response()->json(['success' => true, 'deleted_count' => $deleted]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
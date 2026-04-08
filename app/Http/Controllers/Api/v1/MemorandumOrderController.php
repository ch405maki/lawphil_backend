<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\MemorandumOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MemorandumOrderController extends Controller 
{
    public function index(Request $request) 
    {
        try {
            $query = MemorandumOrder::query();

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function($q) use ($s) {
                    $q->where('citation', 'LIKE', "%$s%")
                    ->orWhere('mo_number', 'LIKE', "%$s%"); // Pinalitan ang gr_number
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
                'mo_number' => 'required|string|max:255', // Pinalitan
                'date' => 'required|date',
                'citation' => 'nullable|string',
                'signatory' => 'nullable|string', // Pinalitan
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

            $order = MemorandumOrder::create([
                'user_id' => auth()->id() ?? 1, // Mas safe kung auth()->id()
                'mo_number' => $request->mo_number,
                'date' => $request->date,
                'citation' => $request->citation,
                'signatory' => $request->signatory,
                'reference' => $request->reference,
                'url' => $request->url,
                'pdf_availability' => $request->pdf_availability ?? false,
                'subject' => $request->subject,
                'pdf_path' => $request->pdf_path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Memorandum Order record created successfully',
                'data' => $order
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
            $record = MemorandumOrder::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'mo_number' => 'sometimes|required|string|max:255',
                'date' => 'sometimes|required|date',
                'citation' => 'nullable|string',
                'signatory' => 'nullable|string',
                'reference' => 'nullable|string',
                'url' => 'nullable|string',
                'pdf_availability' => 'nullable|boolean',
                'subject' => 'nullable|string',
                'pdf_path' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
            }

            $record->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Memorandum Order updated successfully',
                'data' => $record
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id) 
    {
        try {
            $record = MemorandumOrder::findOrFail($id);
            $record->delete();
            return response()->json(['success' => true, 'message' => 'Deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            if ($request->select_all) {
                $query = MemorandumOrder::query();
                if (!empty($request->filters['search'])) {
                    $s = $request->filters['search'];
                    $query->where(function($q) use ($s) {
                        $q->where('citation', 'LIKE', "%$s%")->orWhere('mo_number', 'LIKE', "%$s%");
                    });
                }
                $deleted = $query->delete();
            } else {
                $validator = Validator::make($request->all(), [
                    'ids' => 'required|array',
                    'ids.*' => 'integer|exists:memorandum_orders,id'
                ]);
                if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
                $deleted = MemorandumOrder::whereIn('id', $request->ids)->delete();
            }

            return response()->json(['success' => true, 'deleted_count' => $deleted]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
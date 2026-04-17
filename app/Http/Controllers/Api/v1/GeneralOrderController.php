<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\GeneralOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralOrderController extends Controller 
{
    public function index(Request $request) 
    {
        try {
            $query = GeneralOrder::query();

            if ($request->filled('search')) {
                $s = $request->search;
                $query->where(function($q) use ($s) {
                    $q->where('citation', 'LIKE', "%$s%")
                    ->orWhere('go_number', 'LIKE', "%$s%"); 
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
                'go_number' => 'required|string|max:255',
                'date' => 'required|date',
                'citation' => 'nullable|string',
                'signatory' => 'nullable|string',
                'reference' => 'nullable|string',
                'url' => 'nullable|string',
                'pdf_availability' => 'nullable|boolean',
                'subject' => 'nullable|string',
                'pdf_path' => 'nullable|string',
            ]);

            if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

            $order = GeneralOrder::create([
                'user_id' => auth()->id() ?? 1,
                'go_number' => $request->go_number,
                'date' => $request->date,
                'citation' => $request->citation,
                'signatory' => $request->signatory,
                'reference' => $request->reference,
                'url' => $request->url,
                'pdf_availability' => $request->pdf_availability ?? false,
                'subject' => $request->subject,
                'pdf_path' => $request->pdf_path,
            ]);

            return response()->json(['success' => true, 'message' => 'General Order created', 'data' => $order], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ... Ang Update, Destroy, at BulkDelete ay pareho lang din ang logic, 
    // siguraduhin lang na 'go_number' at 'GeneralOrder' model ang gamit.
    // (Para hindi masyadong mahaba ang text dito, copy mo yung logic mula sa MemorandumOrder sa taas
    // at i-rename mo lang yung Model at column names).
}
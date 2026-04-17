<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ExecutiveOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExecutiveOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = ExecutiveOrder::query();

        // Search logic
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('eo_number', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        // Filter by Year
        if ($request->filled('year')) {
            $query->whereYear('date', $request->year);
        }

        // Sort logic
        $sort = $request->get('sort', 'latest');
        $query->orderBy('date', $sort === 'latest' ? 'desc' : 'asc');

        $rows = $request->get('rows', 10);

        return response()->json($query->paginate($rows));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'eo_number' => 'required|string',
            'date' => 'required|date',
            'subject' => 'nullable|string',
            'reference' => 'nullable|string',
            'url' => 'nullable|string',
            'pdf_availability' => 'boolean',
            'pdf_path' => 'nullable|string',
        ]);

        // Isama ang user_id ng kung sinong admin ang naka-login
        $validated['user_id'] = Auth::id() ?? 1;

        $eo = ExecutiveOrder::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Executive Order created successfully',
            'data' => $eo
        ]);
    }
    public function update(Request $request, $id)
    {
        // 1. Hahanapin natin yung record sa database gamit ang ID nito
        $eo = ExecutiveOrder::findOrFail($id);

        // 2. I-validate natin ang mga bagong input mula sa Edit Form
        $validated = $request->validate([
            'eo_number' => 'required|string',
            'date' => 'required|date',
            'subject' => 'nullable|string',
            'reference' => 'nullable|string',
            'url' => 'nullable|string',
            'pdf_availability' => 'boolean',
            'pdf_path' => 'nullable|string',
        ]);

        // 3. I-save na natin ang mga pagbabago sa database
        $eo->update($validated);

        // 4. Magpapadala tayo ng confirmation pabalik sa Vue (Frontend)
        return response()->json([
            'success' => true,
            'message' => 'Executive Order updated successfully',
            'data' => $eo
        ]);
    }
    public function destroy($id)
    {
        // 1. Hanapin ang record, kung wala, mag-404 error
        $eo = ExecutiveOrder::findOrFail($id);
        
        // 2. Burahin ang record
        $eo->delete();

        // 3. Mag-return ng success response
        return response()->json([
            'success' => true,
            'message' => 'Executive Order deleted successfully'
        ]);
    }
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No IDs provided for deletion.'
            ], 400);
        }

        // Burahin ang mga records na may mga ID na nasa listahan
        ExecutiveOrder::whereIn('id', $ids)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected Executive Orders deleted successfully.'
        ]);
    }   
}
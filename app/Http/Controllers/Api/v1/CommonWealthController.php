<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\CommonWealth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommonWealthController extends Controller
{
    private const SORTABLE = [
        'az'     => ['citation', 'asc'],
        'za'     => ['citation', 'desc'],
        'oldest' => ['date',      'asc'],
        'newest' => ['date',      'desc'],
    ];

    private const SEARCHABLE_COLUMNS = [
        'citation',
        'ca_number',
        'tenure',
        'description',
    ];

    public function index(Request $request)
    {
        try {
            $query = CommonWealth::query();

            $this->applySearch($query, $request->input('search'));
            $this->applyYearFilter($query, $request->input('year'));
            $this->applySort($query, $request->input('sort'));

            $rows = min((int) $request->input('rows', 10), 100);

            return response()->json(
                $query->paginate($rows)->withQueryString()
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ca_number' => 'required|string|max:255',
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

            $cw = CommonWealth::create([
                'user_id' => 1,
                'ca_number' => $request->ca_number,
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
                'message' => 'Commonwealth record created successfully',
                'data' => $cw
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
            $record = CommonWealth::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'ca_number' => 'sometimes|required|string|max:255',
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
                'ca_number',
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
                'message' => 'Commonwealth record updated successfully',
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
            $record = CommonWealth::findOrFail($id);
            $record->delete();

            return response()->json([
                'success' => true,
                'message' => 'Commonwealth record deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete record: ' . $e->getMessage()
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function applySearch($query, ?string $search): void
    {
        if (blank($search)) {
            return;
        }

        $keywords = collect(preg_split('/\s+/', trim($search)))
            ->filter()
            ->unique()
            ->values();

        foreach ($keywords as $keyword) {
            $query->where(function ($q) use ($keyword) {
                foreach (self::SEARCHABLE_COLUMNS as $column) {
                    $q->orWhere($column, 'LIKE', "%{$keyword}%");
                }
            });
        }
    }

    private function applyYearFilter($query, mixed $year): void
    {
        if (blank($year) || ! ctype_digit((string) $year)) {
            return;
        }

        $query->whereYear('date', (int) $year);
    }

    private function applySort($query, ?string $sort): void
    {
        [$column, $direction] = self::SORTABLE[$sort] ?? self::SORTABLE['newest'];

        $query->orderBy($column, $direction);
    }
}

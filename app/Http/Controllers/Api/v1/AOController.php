<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\AO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class AOController extends Controller 
{
    private const NUMBER_COLUMN = 'ao_number';

    private const SORTABLE = [
        'az'     => ['citation', 'asc'],
        'za'     => ['citation', 'desc'],
        'oldest' => ['date',      'asc'],
        'newest' => ['date',      'desc'],
    ];

    private const SEARCHABLE_COLUMNS = [
        'citation',
        'ao_number',
        'tenure',
        'description',
    ];

    public function index(Request $request) 
    {
        try {
            $query = AO::query();

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
                'ao_number' => 'required|string|max:255',
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

            $ao = AO::create([
                'user_id' => 1,
                'ao_number' => $request->ao_number,
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
                'message' => 'Administrative Order record created successfully',
                'data' => $ao
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
            $record = AO::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'ao_number' => 'sometimes|required|string|max:255',
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
                'ao_number', 
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
                'message' => 'Administrative Order record updated successfully',
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
            $record = AO::findOrFail($id);
            $record->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Administrative Order record deleted successfully'
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

        if (in_array($sort, ['oldest', 'newest'])) {
            $query->orderByRaw('CAST(SUBSTRING_INDEX(' . self::NUMBER_COLUMN . ', "No. ", -1) AS UNSIGNED) ' . $direction);
        }
    }
}
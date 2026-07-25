<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Presidential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class PresidentialController extends Controller 
{
    private const NUMBER_COLUMN = 'pd_number';

    private const SORTABLE = [
        'az'     => ['citation', 'asc'],
        'za'     => ['citation', 'desc'],
        'oldest' => ['date',      'asc'],
        'newest' => ['date',      'desc'],
    ];

    private const SEARCHABLE_COLUMNS = [
        'citation',
        'pd_number',
        'tenure',
        'subject',
        'ponente',
        'reference',
    ];

    public function index(Request $request) 
    {
        try {
            $query = Presidential::query();

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
                'pd_number' => 'required|string|max:255',
                'date' => 'required|date',
                'citation' => 'nullable|string',
                'ponente' => 'nullable|string',
                'reference' => 'nullable|string',
                'url' => 'nullable|string',
                'pdf_availability' => 'nullable|boolean',
                'subject' => 'nullable|string',
                'tenure' => 'nullable|string',
                'pdf_path' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $presidential = Presidential::create([
                'user_id' => 1,
                'pd_number' => $request->pd_number,
                'date' => $request->date,
                'citation' => $request->citation,
                'ponente' => $request->ponente,
                'reference' => $request->reference,
                'url' => $request->url,
                'pdf_availability' => $request->pdf_availability ?? false,
                'subject' => $request->subject,
                'tenure' => $request->tenure,
                'pdf_path' => $request->pdf_path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Presidential Decrees record created successfully',
                'data' => $presidential
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
            $record = Presidential::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'pd_number' => 'sometimes|required|string|max:255',
                'date' => 'sometimes|required|date',
                'citation' => 'nullable|string',
                'ponente' => 'nullable|string',
                'reference' => 'nullable|string',
                'url' => 'nullable|string',
                'pdf_availability' => 'nullable|boolean',
                'subject' => 'nullable|string',
                'tenure' => 'nullable|string',
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
                'pd_number', 
                'date', 
                'citation', 
                'ponente', 
                'reference', 
                'url',
                'pdf_availability',
                'subject',
                'tenure',
                'pdf_path'
            ]);

            $record->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Presidential Decrees record updated successfully',
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
            $record = Presidential::findOrFail($id);
            $record->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Presidential Decrees record deleted successfully'
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
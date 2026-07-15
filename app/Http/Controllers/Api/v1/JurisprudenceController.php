<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Jurisprudence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class JurisprudenceController extends Controller 
{
    private const SORTABLE = [
        'az'     => ['gr_number', 'asc'],
        'za'     => ['gr_number', 'desc'],
        'oldest' => ['date',      'asc'],
        'newest' => ['date',      'desc'],
    ];

    private const SEARCHABLE_COLUMNS = [
        'citation',
        'gr_number',
        'ponente',
        'reference',
        'subject',
    ];

    public function index(Request $request)
    {
        try {
            $query = Jurisprudence::query();

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
                'gr_number' => 'required|string|max:255',
                'date' => 'required|date',
                'citation' => 'nullable|string',
                'ponente' => 'nullable|string',
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

            $jurisprudence = Jurisprudence::create([
                'user_id' => 1,
                'gr_number' => $request->gr_number,
                'date' => $request->date,
                'citation' => $request->citation,
                'ponente' => $request->ponente,
                'reference' => $request->reference,
                'url' => $request->url,
                'pdf_availability' => $request->pdf_availability ?? false,
                'subject' => $request->subject,
                'pdf_path' => $request->pdf_path,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jurisprudence record created successfully',
                'data' => $jurisprudence
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
            $record = Jurisprudence::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'gr_number' => 'sometimes|required|string|max:255',
                'date' => 'sometimes|required|date',
                'citation' => 'nullable|string',
                'ponente' => 'nullable|string',
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

            $data = $request->only([
                'gr_number', 
                'date', 
                'citation', 
                'ponente', 
                'reference', 
                'url',
                'pdf_availability',
                'subject',
                'pdf_path'
            ]);

            $record->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Jurisprudence record updated successfully',
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
            $record = Jurisprudence::findOrFail($id);
            $record->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Jurisprudence record deleted successfully'
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

    /**
     * Keyword-based search: splits the input into individual tokens and requires
     * every token to match at least one searchable column.
     * 
     * e.g. "republic philippines" → two AND-wrapped OR groups, so a row must
     * contain BOTH "republic" and "philippines" somewhere across the columns.
     */
    private function applySearch($query, ?string $search): void
    {
        if (blank($search)) {
            return;
        }

        // Normalize whitespace and split into unique, non-empty tokens
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

    /**
     * Filter by year using the `date` column.
     */
    private function applyYearFilter($query, mixed $year): void
    {
        if (blank($year) || ! ctype_digit((string) $year)) {
            return;
        }

        $query->whereYear('date', (int) $year);
    }

    /**
     * Apply sorting from the allow-list; default to newest.
     */
    private function applySort($query, ?string $sort): void
    {
        [$column, $direction] = self::SORTABLE[$sort] ?? self::SORTABLE['newest'];

        $query->orderBy($column, $direction);
    }
}
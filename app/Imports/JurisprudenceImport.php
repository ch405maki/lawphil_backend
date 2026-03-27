<?php

namespace App\Imports;

use App\Models\Jurisprudence;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class JurisprudenceImport implements ToModel, WithStartRow
{
    public function startRow(): int
    {
        return 2; 
    }

    public function model(array $row)
    {
        if (empty($row[0])) {
            return null;
        }

        return new Jurisprudence([
            'user_id'          => Auth::id() ?? 1,
            'gr_number'        => $row[0] ?? null,
            'date'             => $this->transformDate($row[1] ?? null),
            'citation'         => $row[2] ?? null,
            'reference'        => $row[3] ?? null,
            'url'              => $row[4] ?? null, 
            'pdf_availability' => isset($row[5]) ? (bool)$row[5] : false,
            'ponente'          => $row[6] ?? null,
            'subject'          => $row[7] ?? null,
        ]);
    }

    private function transformDate($value)
    {
        if (!$value || $value == 'DATE') return null;
        
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return null;
        }
    }
}
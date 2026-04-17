<?php

namespace App\Imports;

use App\Models\MemorandumOrder;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MemorandumOrderImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new MemorandumOrder([
            'user_id'          => auth()->id() ?? 1,
            'mo_number'        => $row['mo_number'] ?? $row['number'], // Depende sa column name sa Excel mo
            'date'             => isset($row['date']) ? \Carbon\Carbon::parse($row['date']) : null,
            'citation'         => $row['citation'] ?? $row['title'],
            'signatory'        => $row['signatory'] ?? null,
            'reference'        => $row['reference'] ?? null,
            'url'              => $row['url'] ?? null,
            'pdf_availability' => filter_var($row['pdf_availability'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'subject'          => $row['subject'] ?? null,
        ]);
    }
}
<?php

namespace App\Imports;

use App\Models\GeneralOrder;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GeneralOrderImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new GeneralOrder([
            'user_id'          => auth()->id() ?? 1,
            'go_number'        => $row['go_number'] ?? $row['number'] ?? $row['g_o_no'],
            'date'             => isset($row['date']) ? \Carbon\Carbon::parse($row['date']) : null,
            'citation'         => $row['citation'] ?? $row['title'] ?? $row['subject'],
            'signatory'        => $row['signatory'] ?? $row['ponente'] ?? null,
            'reference'        => $row['reference'] ?? null,
            'url'              => $row['url'] ?? null,
            'pdf_availability' => filter_var($row['pdf_availability'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'subject'          => $row['subject'] ?? null,
        ]);
    }
}
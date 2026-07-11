<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ArchiveController extends Controller
{
    private const MODULES = [
        'jurisprudence'      => 'jurisprudence',
        'presidential'       => 'presidential_decrees',
        'proclamation'       => 'proclamations',
        'republic'           => 'republic',
        'execord'            => 'executive_order',
        'ao'                 => 'administrative_order',
        'mo'                 => 'memorandum_order',
        'mc'                 => 'memorandum_circular',
        'genor'              => 'general_order',
    ];

    public function index()
    {
        $result = [];

        foreach (self::MODULES as $key => $table) {
            $rows = DB::table($table)
                ->selectRaw('YEAR(date) as year, MONTH(date) as month')
                ->whereNotNull('date')
                ->where('date', '!=', '')
                ->distinct()
                ->orderBy('year', 'desc')
                ->orderBy('month', 'asc')
                ->get();

            $grouped = [];
            foreach ($rows as $row) {
                $grouped[(int) $row->year][] = (int) $row->month;
            }

            $result[$key] = $grouped;
        }

        return response()->json($result);
    }
}

<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArchiveController extends Controller
{
    private const MODULES = [
        'jurisprudence' => 'jurisprudence',
        'presidential'  => 'presidential_decrees',
        'proclamation'  => 'proclamations',
        'republic'      => 'republic',
        'execord'       => 'executive_order',
        'ao'            => 'administrative_order',
        'mo'            => 'memorandum_order',
        'mc'            => 'memorandum_circular',
        'genor'         => 'general_order',
        'acts'          => 'acts',
        'bataspambansa' => 'batas_pambansa',
        'commonwealth'  => 'commonwealth',
    ];

    public function show(string $module)
    {
        $table = self::MODULES[$module] ?? null;

        if (!$table) {
            throw new NotFoundHttpException("Module '{$module}' not found");
        }

        $rows = DB::table($table)
            ->selectRaw('YEAR(date) as year, MONTH(date) as month')
            ->whereNotNull('date')
            ->distinct()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc')
            ->get();

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int) $row->year][] = (int) $row->month;
        }

        return response()->json($grouped);
    }
}

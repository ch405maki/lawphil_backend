<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Act;
use App\Models\AO;
use App\Models\BatasPambansa;
use App\Models\CommonWealth;
use App\Models\Execord;
use App\Models\Genor;
use App\Models\Jurisprudence;
use App\Models\MC;
use App\Models\MO;
use App\Models\Presidential;
use App\Models\Proclamation;
use App\Models\Republic;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $selectedModule = $request->query('module', 'all');

        $modules = [
            'jurisprudence' => ['model' => Jurisprudence::class, 'label' => 'Jurisprudence', 'identifier' => 'gr_number', 'route' => 'jurisprudence.index'],
            'presidential' => ['model' => Presidential::class, 'label' => 'Presidential Decrees', 'identifier' => 'pd_number', 'route' => 'presidential.index'],
            'proclamation' => ['model' => Proclamation::class, 'label' => 'Proclamations', 'identifier' => 'proc_number', 'route' => 'proclamation.index'],
            'republic' => ['model' => Republic::class, 'label' => 'Republic Acts', 'identifier' => 'ra_number', 'route' => 'republic.index'],
            'execord' => ['model' => Execord::class, 'label' => 'Executive Orders', 'identifier' => 'execord_number', 'route' => 'execord.index'],
            'ao' => ['model' => AO::class, 'label' => 'Administrative Orders', 'identifier' => 'ao_number', 'route' => 'ao.index'],
            'mo' => ['model' => MO::class, 'label' => 'Memorandum Orders', 'identifier' => 'mo_number', 'route' => 'mo.index'],
            'mc' => ['model' => MC::class, 'label' => 'Memorandum Circulars', 'identifier' => 'mc_number', 'route' => 'mc.index'],
            'genor' => ['model' => Genor::class, 'label' => 'General Orders', 'identifier' => 'genor_number', 'route' => 'genor.index'],
            'acts' => ['model' => Act::class, 'label' => 'Acts', 'identifier' => 'act_number', 'route' => 'acts.index'],
            'batas_pambansa' => ['model' => BatasPambansa::class, 'label' => 'Batas Pambansa', 'identifier' => 'bp_number', 'route' => 'batas_pambansa.index'],
            'commonwealth' => ['model' => CommonWealth::class, 'label' => 'Commonwealth Acts', 'identifier' => 'ca_number', 'route' => 'commonwealth.index'],
        ];

        $moduleBreakdown = [];
        $totalRecords = 0;
        $totalThisMonth = 0;
        $totalWithPdf = 0;

        foreach ($modules as $key => $m) {
            $model = $m['model'];
            $count = $model::count();
            $monthCount = $model::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            $withPdf = $model::where('pdf_availability', true)->count();

            $moduleBreakdown[] = [
                'key' => $key,
                'label' => $m['label'],
                'total' => $count,
                'this_month' => $monthCount,
                'with_pdf' => $withPdf,
                'without_pdf' => $count - $withPdf,
            ];

            $totalRecords += $count;
            $totalThisMonth += $monthCount;
            $totalWithPdf += $withPdf;
        }

        $stats = [
            'total_records' => $totalRecords,
            'records_this_month' => $totalThisMonth,
            'records_with_pdf' => $totalWithPdf,
            'records_without_pdf' => $totalRecords - $totalWithPdf,
            'pdf_coverage_rate' => $totalRecords > 0 ? round(($totalWithPdf / $totalRecords) * 100) : 0,
        ];

        $recentRecords = [];
        foreach ($modules as $key => $m) {
            $model = $m['model'];
            $records = $model::latest()->take(5)->get();
            foreach ($records as $r) {
                $recentRecords[] = [
                    'id' => $r->id,
                    'module' => $key,
                    'module_label' => $m['label'],
                    'identifier' => $r->{$m['identifier']} ?? '',
                    'citation' => $r->citation ?? '',
                    'date' => $r->date ?? '',
                    'pdf_availability' => (bool) ($r->pdf_availability ?? false),
                    'created_at' => $r->created_at ? $r->created_at->toISOString() : null,
                ];
            }
        }

        usort($recentRecords, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));
        $recentRecords = array_slice($recentRecords, 0, 10);

        $recentActivities = Activity::query()
            ->with('causer')
            ->latest()
            ->take(10)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'moduleBreakdown' => $moduleBreakdown,
            'recentRecords' => $recentRecords,
            'recentActivities' => $recentActivities,
            'selectedModule' => $selectedModule,
            'currentMonth' => now()->format('F'),
        ]);
    }
}

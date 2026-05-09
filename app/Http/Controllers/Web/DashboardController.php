<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Jurisprudence;
use Spatie\Activitylog\Models\Activity;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'total_records' => Jurisprudence::count(),
            'records_this_month' => Jurisprudence::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'records_with_pdf' => Jurisprudence::where('pdf_availability', true)->count(),
            'records_without_pdf' => Jurisprudence::where('pdf_availability', false)->count(),
        ];

        $recentActivities = Activity::query()
            ->with('causer')
            ->latest()
            ->take(10)
            ->get();

        $recentJurisprudence = Jurisprudence::latest()
            ->take(10)
            ->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'recentJurisprudence' => $recentJurisprudence,
        ]);
    }
}

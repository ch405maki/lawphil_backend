<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    // Return paginated activity logs
    public function index(Request $request)
    {
        $query = Activity::query()
            ->with(['causer']) // eager load user if available
            ->orderBy('created_at', 'desc');

        // Optional: filter by log name
        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        // Optional: filter by user id
        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id);
        }

        // Optional: search description
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('description', 'LIKE', "%$s%");
        }

        $perPage = $request->get('rows', 10);

        return response()->json($query->paginate($perPage));
    }
}
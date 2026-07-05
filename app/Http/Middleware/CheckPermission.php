<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $module, string $action): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        if (!$user->canModule($module, $action)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Forbidden: You do not have permission to perform this action.'], 403);
            }

            abort(403, 'Forbidden: You do not have permission to perform this action.');
        }

        return $next($request);
    }
}

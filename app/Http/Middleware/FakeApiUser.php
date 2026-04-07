<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class FakeApiUser
{
    public function handle($request, Closure $next)
    {
        Auth::loginUsingId(1);

        return $next($request);
    }
}
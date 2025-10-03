<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        // For API requests, return 401 JSON instead of redirecting to a non-existent route
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        return null;
    }
}

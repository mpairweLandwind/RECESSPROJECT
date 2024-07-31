<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check() || Auth::user()->role !== $role) {
            // If the user is not authenticated or doesn't have the right role,
            // redirect them to the home page or any other page you prefer
            return redirect('/');
        }

        return $next($request);
    }
}

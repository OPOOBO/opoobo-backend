<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeveloperAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('developer_id')) {
            return redirect()->route('developer.login');
        }
        return $next($request);
    }
}

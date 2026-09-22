<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('developer_id')) {
            return redirect()->route('admin.login');
        }

        if (session('login_via') !== 'admin') {
            abort(403, 'Unauthorized. Please log in via the admin login page.');
        }

        return $next($request);
    }
}

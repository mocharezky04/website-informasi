<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->session()->get('admin_logged_in')) {
            return redirect()->route('login')->with('error', 'Anda harus masuk sebagai Admin untuk mengakses konsol CMS.');
        }

        return $next($request);
    }
}

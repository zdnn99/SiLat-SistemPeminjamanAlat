<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kalau belum login atau peran-nya BUKAN admin, tendang ke halaman home!
        if (!Auth::check() || Auth::user()->peran !== 'admin') {
            return redirect('/')->with('error', 'Hayo mau ngapain? Kamu bukan Admin ygy!');
        }

        // Kalau dia beneran admin, silakan masuk
        return $next($request);
    }
}
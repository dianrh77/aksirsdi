<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('login');
        }

        // Debugging optional
        // dd($user->role_name, $roles);

        // Jika role user tidak termasuk salah satu role yang diizinkan
        if (!in_array($user->role_name, $roles)) {
            abort(403, 'Anda tidak memiliki izin untuk melakukan aksi atau mengakses halaman ini. Hubungi administrator');
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOrangTuaMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'orang_tua') {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        if ($user->status === 'pending') {
            return redirect()->route('orang-tua.pending');
        }

        if (in_array($user->status, ['rejected', 'suspended'])) {
            return redirect()->route('orang-tua.rejected');
        }

        if ($user->status !== 'active') {
            abort(403, 'Akun Anda tidak aktif.');
        }

        return $next($request);
    }
}

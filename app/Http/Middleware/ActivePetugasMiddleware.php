<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ActivePetugasMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isPetugas()) {
            if ($user->isPending()) {
                return redirect()->route('petugas.pending');
            }

            if ($user->isRejected()) {
                return redirect()->route('petugas.rejected');
            }

            if ($user->isSuspended()) {
                return redirect()->route('petugas.suspended');
            }
        }

        return $next($request);
    }
}

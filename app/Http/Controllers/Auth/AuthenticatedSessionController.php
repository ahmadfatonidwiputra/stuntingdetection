<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login', [
            'needsInitialSuperadmin' => User::needsInitialSuperadmin(),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Redirect based on role and status
        if ($user->isSuperAdmin()) {
            return redirect()->intended(route('super-admin.dashboard', absolute: false));
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

            // Active petugas
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Orang Tua
        if ($user->isOrangTua()) {
            if ($user->isPending()) {
                return redirect()->route('orang-tua.pending');
            }

            if (in_array($user->status, ['rejected', 'suspended'])) {
                return redirect()->route('orang-tua.rejected');
            }

            return redirect()->intended(route('orang-tua.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

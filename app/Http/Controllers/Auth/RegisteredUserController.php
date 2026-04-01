<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PetugasProfile;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the initial superadmin registration view.
     */
    public function create(): View|RedirectResponse
    {
        if (! User::needsInitialSuperadmin()) {
            return redirect()
                ->route('login')
                ->with('status', 'Registrasi publik sudah ditutup. Silakan masuk dengan akun superadmin yang terdaftar.');
        }

        return view('auth.register');
    }

    /**
     * Handle initial superadmin registration.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! User::needsInitialSuperadmin()) {
            throw ValidationException::withMessages([
                'email' => 'Registrasi publik sudah ditutup. Akun superadmin sudah tersedia.',
            ]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'super_admin',
                'status' => 'active',
                'password' => Hash::make($request->password),
            ]);
        } catch (QueryException $exception) {
            if (! User::needsInitialSuperadmin()) {
                throw ValidationException::withMessages([
                    'email' => 'Registrasi superadmin pertama sudah selesai. Silakan masuk dengan akun yang tersedia.',
                ]);
            }

            throw $exception;
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('super-admin.dashboard', absolute: false));
    }

    /**
     * Display petugas posyandu registration form.
     */
    public function createPetugas(): View
    {
        return view('auth.register-petugas');
    }

    /**
     * Handle petugas posyandu registration.
     */
    public function storePetugas(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nama_lengkap' => ['required', 'string', 'max:200'],
            'nik' => ['required', 'string', 'size:16', 'unique:petugas_profiles,nik'],
            'no_telepon' => ['nullable', 'string', 'max:15'],
            'posyandu_name' => ['required', 'string', 'max:200'],
            'posyandu_address' => ['nullable', 'string', 'max:500'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kota' => ['nullable', 'string', 'max:100'],
            'provinsi' => ['nullable', 'string', 'max:100'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'agreement' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'petugas_posyandu',
            'status' => 'pending',
            'password' => Hash::make($request->password),
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('documents', 'public');
        }

        PetugasProfile::create([
            'user_id' => $user->id,
            'nama_lengkap' => $request->nama_lengkap,
            'nik' => $request->nik,
            'no_telepon' => $request->no_telepon,
            'posyandu_name' => $request->posyandu_name,
            'posyandu_address' => $request->posyandu_address,
            'kelurahan' => $request->kelurahan,
            'kecamatan' => $request->kecamatan,
            'kota' => $request->kota,
            'provinsi' => $request->provinsi,
            'document_path' => $documentPath,
        ]);

        event(new Registered($user));

        return redirect()->route('login')
            ->with('status', 'Registrasi berhasil! Akun Anda sedang menunggu verifikasi oleh Super Admin. Anda akan dihubungi setelah akun diverifikasi.');
    }
}

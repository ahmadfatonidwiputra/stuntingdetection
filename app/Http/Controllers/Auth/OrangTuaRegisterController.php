<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\OrangTuaAnak;
use App\Models\OrangTuaProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class OrangTuaRegisterController extends Controller
{
    /**
     * Tampilkan form registrasi orang tua.
     */
    public function create()
    {
        $posyandus = \App\Models\Posyandu::orderBy('nama')->get();
        return view('auth.register-orang-tua', compact('posyandus'));
    }

    /**
     * Proses registrasi orang tua.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:200',
            'email'         => 'required|email|unique:users,email',
            'password'      => ['required', 'confirmed', Password::min(8)],
            'nama_lengkap'  => 'required|string|max:200',
            'nik'           => 'required|digits:16|unique:orang_tua_profile,nik',
            'no_kk'         => 'required|digits:16',
            'hubungan'      => 'required|in:ayah,ibu,wali',
            'no_telepon'    => 'nullable|max:15',
            'posyandu_id'   => 'required|exists:posyandu,id',
            'nik_anak'      => 'required|digits:16',
            'nama_anak'     => 'required|string',
            'nama_ibu'      => 'required|string',
        ]);

        // Cari anak berdasarkan NIK dan Posyandu
        $anak = Anak::where('nik_anak', $request->nik_anak)
                    ->where('posyandu_id', $request->posyandu_id)
                    ->first();

        if (! $anak) {
            return back()->withErrors([
                'nik_anak' => 'Data anak tidak ditemukan di Posyandu yang dipilih. Pastikan NIK Anak dan pilihan Posyandu sudah benar.',
            ])->withInput();
        }

        // Validasi nama anak
        if (! str_contains(strtolower($anak->nama), strtolower($request->nama_anak))
            && ! str_contains(strtolower($request->nama_anak), strtolower($anak->nama))) {
            return back()->withErrors([
                'nama_anak' => 'Nama anak tidak cocok dengan data yang ada.',
            ])->withInput();
        }

        // Validasi No. KK
        if ($anak->no_kk && $anak->no_kk !== $request->no_kk) {
            return back()->withErrors([
                'no_kk' => 'Nomor KK tidak cocok dengan data anak di database.',
            ])->withInput();
        }

        // Validasi Nama Ibu Kandung
        if (strtolower(trim($anak->nama_ibu)) !== strtolower(trim($request->nama_ibu))) {
            return back()->withErrors([
                'nama_ibu' => 'Nama Ibu Kandung tidak cocok dengan data anak di database.',
            ])->withInput();
        }

        DB::transaction(function () use ($request, $anak) {
            // Buat user
            $user = User::create([
                'name'             => $request->name,
                'email'            => $request->email,
                'password'         => Hash::make($request->password),
                'role'             => 'orang_tua',
                'status'           => 'pending',
                'email_verified_at' => now(),
            ]);

            // Buat profil orang tua
            $profile = OrangTuaProfile::create([
                'user_id'      => $user->id,
                'nama_lengkap' => $request->nama_lengkap,
                'nik'          => $request->nik,
                'no_kk'        => $request->no_kk,
                'hubungan'     => $request->hubungan,
                'no_telepon'   => $request->no_telepon,
                'alamat'       => $request->alamat,
            ]);

            // Link ke anak
            OrangTuaAnak::create([
                'orang_tua_id' => $profile->id,
                'anak_id'      => $anak->id,
                'hubungan'     => $request->hubungan,
                'is_primary'   => true,
            ]);
        });

        return redirect()->route('login')->with('status',
            'Registrasi berhasil! Akun Anda sedang menunggu verifikasi dari petugas posyandu. Anda akan bisa login setelah diverifikasi.');
    }
}

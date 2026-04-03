<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\OrangTuaAnak;
use App\Models\OrangTuaProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifikasiOrangTuaController extends Controller
{
    /**
     * List orang tua yang menunggu verifikasi.
     */
    public function index()
    {
        $user = auth()->user();

        $queryPending = User::orangTua()
            ->where('status', 'pending')
            ->with(['orangTuaProfile.anakRelations.anak.posyandu']);
            
        $queryVerified = User::orangTua()
            ->whereIn('status', ['active', 'suspended'])
            ->with(['orangTuaProfile.anakRelations.anak.posyandu']);

        // Petugas hanya lihat yang terhubung ke posyanduny
        if ($user->isPetugasPosyandu()) {
            $posyanduId = $user->petugasProfile?->posyandu_id;
            if ($posyanduId) {
                $closure = function ($q) use ($posyanduId) {
                    $q->where('posyandu_id', $posyanduId);
                };
                $queryPending->whereHas('orangTuaProfile.anakRelations.anak', $closure);
                $queryVerified->whereHas('orangTuaProfile.anakRelations.anak', $closure);
            }
        }

        $pending = $queryPending->latest()->get();
        $verified = $queryVerified->latest()->get();

        return view('verifikasi-orang-tua.index', compact('pending', 'verified'));
    }

    /**
     * Approve orang tua.
     */
    public function approve(int $id)
    {
        $user = User::findOrFail($id);

        DB::transaction(function () use ($user) {
            $user->update(['status' => 'active']);

            // Update verified_at di relasi
            if ($user->orangTuaProfile) {
                $user->orangTuaProfile->anakRelations()->update([
                    'verified_at' => now(),
                    'verified_by' => auth()->id(),
                ]);
            }
        });

        return redirect()->route('verifikasi-orang-tua.index')
            ->with('success', "Akun {$user->name} berhasil diverifikasi.");
    }

    /**
     * Reject orang tua.
     */
    public function reject(Request $request, int $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $user = User::findOrFail($id);
        $user->update(['status' => 'rejected']);

        return redirect()->route('verifikasi-orang-tua.index')
            ->with('success', "Pendaftaran {$user->name} telah ditolak.");
    }

    public function edit(User $user)
    {
        $user->load('orangTuaProfile');
        return view('verifikasi-orang-tua.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:200',
            'nik'          => 'required|digits:16|unique:orang_tua_profile,nik,'.$user->orangTuaProfile?->id,
            'no_kk'        => 'required|digits:16',
            'hubungan'     => 'required|in:ayah,ibu,wali',
            'no_telepon'   => 'nullable|max:15',
        ]);

        if ($user->orangTuaProfile) {
            $user->orangTuaProfile->update($request->only('nama_lengkap', 'nik', 'no_kk', 'hubungan', 'no_telepon'));
            $user->update(['name' => $request->nama_lengkap]);
        }

        return redirect()->route('verifikasi-orang-tua.index')->with('success', 'Data orang tua berhasil diperbarui.');
    }

    public function suspend(User $user)
    {
        $newStatus = $user->status === 'suspended' ? 'active' : 'suspended';
        $user->update(['status' => $newStatus]);
        $statusText = $newStatus === 'active' ? 'diaktifkan kembali' : 'dinonaktifkan';
        
        return redirect()->route('verifikasi-orang-tua.index')->with('success', "Akun berhasil {$statusText}.");
    }

    public function destroy(User $user)
    {
        DB::transaction(function () use ($user) {
            if ($user->orangTuaProfile) {
                // Hapus kaitan orang tua dan anak
                $user->orangTuaProfile->anakRelations()->delete();
                $user->orangTuaProfile->delete();
            }
            $user->delete();
        });

        return redirect()->route('verifikasi-orang-tua.index')->with('success', 'Akun orang tua berhasil dihapus permanen.');
    }
}

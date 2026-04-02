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

        $query = User::orangTua()
            ->where('status', 'pending')
            ->with(['orangTuaProfile.anakRelations.anak.posyandu']);

        // Petugas hanya lihat yang terhubung ke posyanduny
        if ($user->isPetugasPosyandu()) {
            $posyanduId = $user->petugasProfile?->posyandu_id;
            if ($posyanduId) {
                $query->whereHas('orangTuaProfile.anakRelations.anak', function ($q) use ($posyanduId) {
                    $q->where('posyandu_id', $posyanduId);
                });
            }
        }

        $pending = $query->latest()->get();

        return view('verifikasi-orang-tua.index', compact('pending'));
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
}

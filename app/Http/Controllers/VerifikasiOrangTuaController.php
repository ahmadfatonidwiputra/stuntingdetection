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
     * List orang tua yang menunggu dan sudah terverifikasi.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = strtolower(trim($request->query('search')));
        $searchVerified = strtolower(trim($request->query('search_verified')));

        $queryPending = User::orangTua()
            ->where('status', 'pending')
            ->with(['orangTuaProfile.anakRelations.anak.posyandu']);

        $queryVerified = User::orangTua()
            ->whereIn('status', ['active', 'suspended'])
            ->with(['orangTuaProfile.anakRelations.anak.posyandu']);

        $makeSearchClosure = function($term) {
            return function($q) use ($term) {
                $q->whereRaw('lower(name) like ?', ['%' . $term . '%'])
                  ->orWhereRaw('lower(email) like ?', ['%' . $term . '%'])
                  ->orWhereHas('orangTuaProfile', function($pQ) use ($term) {
                      $pQ->whereRaw('lower(nama_lengkap) like ?', ['%' . $term . '%'])
                         ->orWhereRaw('lower(nik) like ?', ['%' . $term . '%']);
                  });
            };
        };

        if ($search) {
            $queryPending->where($makeSearchClosure($search));
        }

        if ($searchVerified) {
            $queryVerified->where($makeSearchClosure($searchVerified));
        }

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
        $this->ensureCanManageOrangTua($user);

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
        $this->ensureCanManageOrangTua($user);
        $user->update(['status' => 'rejected']);

        return redirect()->route('verifikasi-orang-tua.index')
            ->with('success', "Pendaftaran {$user->name} telah ditolak.");
    }

    public function edit(User $user)
    {
        $this->ensureCanManageOrangTua($user);
        $user->load('orangTuaProfile');
        return view('verifikasi-orang-tua.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureCanManageOrangTua($user);
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
        $this->ensureCanManageOrangTua($user);
        $newStatus = $user->status === 'suspended' ? 'active' : 'suspended';
        $user->update(['status' => $newStatus]);
        $statusText = $newStatus === 'active' ? 'diaktifkan kembali' : 'dinonaktifkan';
        
        return redirect()->route('verifikasi-orang-tua.index')->with('success', "Akun berhasil {$statusText}.");
    }

    public function destroy(User $user)
    {
        $this->ensureCanManageOrangTua($user);
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

    private function ensureCanManageOrangTua(User $orangTua): void
    {
        $user = auth()->user();
        if (! $user->isPetugasPosyandu()) {
            return;
        }
        $posyanduId = $user->petugasProfile?->posyandu_id;
        if (! $posyanduId || ! $orangTua->orangTuaProfile) {
            abort(403);
        }
        $linked = $orangTua->orangTuaProfile
            ->anakRelations()
            ->whereHas('anak', fn($q) => $q->where('posyandu_id', $posyanduId))
            ->exists();
        abort_unless($linked, 403);
    }
}

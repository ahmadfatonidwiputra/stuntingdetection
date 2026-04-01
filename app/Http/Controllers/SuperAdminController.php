<?php

namespace App\Http\Controllers;

use App\Models\Measurement;
use App\Models\PetugasProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $totalPetugas = User::petugas()->count();
        $pendingCount = User::petugas()->status('pending')->count();
        $activeCount = User::petugas()->status('active')->count();
        $rejectedCount = User::petugas()->status('rejected')->count();
        $totalAnak = Measurement::distinct('child_name')->count('child_name');
        $totalPemeriksaan = Measurement::count();

        // Recent pending registrations
        $recentPending = User::petugas()
            ->status('pending')
            ->with('petugasProfile')
            ->latest()
            ->take(5)
            ->get();

        // Monthly registration stats (last 6 months)
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyStats[] = [
                'month' => $date->translatedFormat('M Y'),
                'count' => User::petugas()
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }

        return view('super-admin.dashboard', compact(
            'totalPetugas',
            'pendingCount',
            'activeCount',
            'rejectedCount',
            'totalAnak',
            'totalPemeriksaan',
            'recentPending',
            'monthlyStats'
        ));
    }

    public function petugasList(Request $request)
    {
        $tab = $request->get('tab', 'pending');
        $search = $request->get('search');

        $query = User::petugas()->with('petugasProfile');

        // Filter by tab
        if ($tab === 'pending') {
            $query->status('pending');
        } elseif ($tab === 'active') {
            $query->status('active');
        } elseif ($tab === 'rejected') {
            $query->status('rejected');
        } elseif ($tab === 'suspended') {
            $query->status('suspended');
        }

        // Search
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('petugasProfile', function ($pq) use ($search) {
                      $pq->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('posyandu_name', 'like', "%{$search}%")
                         ->orWhere('kota', 'like', "%{$search}%");
                  });
            });
        }

        $petugas = $query->latest()->paginate(15)->withQueryString();

        // Counts for tabs
        $counts = [
            'pending' => User::petugas()->status('pending')->count(),
            'active' => User::petugas()->status('active')->count(),
            'rejected' => User::petugas()->status('rejected')->count(),
            'suspended' => User::petugas()->status('suspended')->count(),
        ];

        return view('super-admin.petugas.index', compact('petugas', 'tab', 'search', 'counts'));
    }

    public function petugasShow(User $user)
    {
        if (! $user->isPetugas()) {
            abort(404);
        }

        $user->load('petugasProfile');

        // Get measurement stats for this petugas
        $measurementCount = Measurement::where('user_id', $user->id)->count();
        $childCount = Measurement::where('user_id', $user->id)->distinct('child_name')->count('child_name');

        return view('super-admin.petugas.show', compact('user', 'measurementCount', 'childCount'));
    }

    public function approve(User $user)
    {
        if (! $user->isPetugas()) {
            abort(404);
        }

        $user->update(['status' => 'active']);

        if ($user->petugasProfile) {
            $user->petugasProfile->update([
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);
        }

        return back()->with('success', "Petugas {$user->name} berhasil disetujui.");
    }

    public function reject(Request $request, User $user)
    {
        if (! $user->isPetugas()) {
            abort(404);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $user->update(['status' => 'rejected']);

        if ($user->petugasProfile) {
            $user->petugasProfile->update([
                'rejection_reason' => $request->rejection_reason,
            ]);
        }

        return back()->with('success', "Registrasi petugas {$user->name} ditolak.");
    }

    public function suspend(User $user)
    {
        if (! $user->isPetugas()) {
            abort(404);
        }

        $user->update(['status' => 'suspended']);

        return back()->with('success', "Akun petugas {$user->name} berhasil disuspend.");
    }

    public function reactivate(User $user)
    {
        if (! $user->isPetugas()) {
            abort(404);
        }

        $user->update(['status' => 'active']);

        if ($user->petugasProfile) {
            $user->petugasProfile->update([
                'rejection_reason' => null,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);
        }

        return back()->with('success', "Akun petugas {$user->name} berhasil diaktifkan kembali.");
    }

    public function destroy(User $user)
    {
        if (! $user->isPetugas()) {
            abort(404);
        }

        // Delete uploaded document if exists
        if ($user->petugasProfile && $user->petugasProfile->document_path) {
            Storage::disk('public')->delete($user->petugasProfile->document_path);
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('super-admin.petugas.index')
            ->with('success', "Akun petugas {$name} berhasil dihapus.");
    }
}

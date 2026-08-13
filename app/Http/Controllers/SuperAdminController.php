<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsMeasurementsCsv;
use App\Models\Measurement;
use App\Models\PetugasProfile;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SuperAdminController extends Controller
{
    use ExportsMeasurementsCsv;

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
            $profile = $user->petugasProfile;

            // Link to Posyandu or create new
            if (!$profile->posyandu_id && $profile->posyandu_name) {
                $posyandu = Posyandu::firstOrCreate(
                    ['nama' => $profile->posyandu_name],
                    [
                        'kota' => $profile->kota ?? 'Unknown',
                        'provinsi' => $profile->provinsi ?? 'Unknown',
                        'kelurahan' => $profile->kelurahan ?? null,
                        'kecamatan' => $profile->kecamatan ?? null,
                        'alamat' => $profile->posyandu_address ?? null,
                        'status' => 'active'
                    ]
                );
                $profile->posyandu_id = $posyandu->id;
            }

            $profile->update([
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'rejection_reason' => null,
                'posyandu_id' => $profile->posyandu_id,
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
            Storage::disk('r2')->delete($user->petugasProfile->document_path);
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('super-admin.petugas.index')
            ->with('success', "Akun petugas {$name} berhasil dihapus.");
    }

    // ── CRUD Posyandu ─────────────────────────────────

    public function posyanduIndex(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');

        $posyandu = Posyandu::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                        ->orWhere('kode_posyandu', 'like', "%{$search}%")
                        ->orWhere('kota', 'like', "%{$search}%")
                        ->orWhere('kecamatan', 'like', "%{$search}%");
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->withCount(['petugas', 'anak'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('super-admin.posyandu.index', compact('posyandu', 'search', 'status'));
    }

    public function posyanduCreate()
    {
        return view('super-admin.posyandu.create');
    }

    public function posyanduStore(Request $request)
    {
        $validated = $request->validate([
            'nama'          => 'required|string|max:200|unique:posyandu,nama',
            'kode_posyandu' => 'nullable|string|max:20|unique:posyandu,kode_posyandu',
            'alamat'        => 'nullable|string',
            'kelurahan'     => 'nullable|string|max:100',
            'kecamatan'     => 'nullable|string|max:100',
            'kota'          => 'nullable|string|max:100',
            'provinsi'      => 'nullable|string|max:100',
            'no_telepon'    => 'nullable|string|max:15',
            'status'        => 'required|in:active,inactive',
        ]);

        Posyandu::create($validated);

        return redirect()->route('super-admin.posyandu.index')
            ->with('success', "Posyandu {$validated['nama']} berhasil ditambahkan.");
    }

    public function posyanduEdit(Posyandu $posyandu)
    {
        return view('super-admin.posyandu.edit', compact('posyandu'));
    }

    public function posyanduUpdate(Request $request, Posyandu $posyandu)
    {
        $validated = $request->validate([
            'nama'          => 'required|string|max:200|unique:posyandu,nama,' . $posyandu->id,
            'kode_posyandu' => 'nullable|string|max:20|unique:posyandu,kode_posyandu,' . $posyandu->id,
            'alamat'        => 'nullable|string',
            'kelurahan'     => 'nullable|string|max:100',
            'kecamatan'     => 'nullable|string|max:100',
            'kota'          => 'nullable|string|max:100',
            'provinsi'      => 'nullable|string|max:100',
            'no_telepon'    => 'nullable|string|max:15',
            'status'        => 'required|in:active,inactive',
        ]);

        $posyandu->update($validated);

        return redirect()->route('super-admin.posyandu.index')
            ->with('success', "Data posyandu berhasil diperbarui.");
    }

    public function posyanduDestroy(Posyandu $posyandu)
    {
        $nama = $posyandu->nama;
        $posyandu->delete();
        return redirect()->route('super-admin.posyandu.index')
            ->with('success', "Posyandu {$nama} berhasil dihapus.");
    }

    // ── Manajemen Laporan ──────────────────────────────

    public function laporanIndex(Request $request)
    {
        $search = $request->get('search');

        $posyanduList = Posyandu::query()
            ->when($search, fn ($q) => $q->where('nama', 'like', "%{$search}%"))
            ->withCount(['anak', 'measurements'])
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        return view('super-admin.laporan.index', compact('posyanduList', 'search'));
    }

    public function laporanShow(Request $request, Posyandu $posyandu)
    {
        $request->validate([
            'dari'   => 'nullable|date',
            'sampai' => 'nullable|date|after_or_equal:dari',
        ]);

        $dari = $request->get('dari');
        $sampai = $request->get('sampai');

        $filtered = fn () => $posyandu->measurements()
            ->when($dari, fn ($q) => $q->whereDate('measured_at', '>=', $dari))
            ->when($sampai, fn ($q) => $q->whereDate('measured_at', '<=', $sampai));

        $measurements = $filtered()->with('anak')->latest('measured_at')->paginate(20)->withQueryString();

        $summary = [
            'total'           => $filtered()->count(),
            'normal'          => $filtered()->where('stunting_category', 'Normal')->count(),
            'stunting'        => $filtered()->where('stunting_category', 'Stunting')->count(),
            'sangat_stunting' => $filtered()->where('stunting_category', 'Sangat Stunting')->count(),
        ];

        return view('super-admin.laporan.show', compact('posyandu', 'measurements', 'summary', 'dari', 'sampai'));
    }

    public function laporanDownload(Request $request, Posyandu $posyandu): StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $dari = $request->get('dari');
        $sampai = $request->get('sampai');

        if (! $dari || ! $sampai) {
            return back()->with('error', 'Pilih rentang tanggal (dari dan sampai) terlebih dahulu sebelum mengunduh.');
        }

        $validator = validator($request->all(), [
            'dari'   => 'date',
            'sampai' => 'date|after_or_equal:dari',
        ]);

        if ($validator->fails()) {
            return back()->with('error', 'Rentang tanggal tidak valid. Pastikan tanggal akhir setelah tanggal mulai.');
        }

        $measurements = $posyandu->measurements()
            ->with('anak')
            ->whereDate('measured_at', '>=', $dari)
            ->whereDate('measured_at', '<=', $sampai)
            ->orderBy('measured_at')
            ->get();

        $filename = 'laporan_' . str($posyandu->nama)->slug() . "_{$dari}_sampai_{$sampai}.csv";
        $periodLabel = \Carbon\Carbon::parse($dari)->format('d/m/Y') . ' s/d ' . \Carbon\Carbon::parse($sampai)->format('d/m/Y');

        return $this->streamMeasurementsCsv($measurements, $filename, $posyandu->nama, $periodLabel);
    }
}


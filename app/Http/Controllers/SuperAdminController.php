<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ExportsMeasurementsCsv;
use App\Models\Anak;
use App\Models\Measurement;
use App\Models\PetugasProfile;
use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
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

        // Status gizi anak (berdasarkan pengukuran terakhir tiap anak)
        $anakStatusCounts = $this->anakStatusCounts();

        return view('super-admin.dashboard', compact(
            'totalPetugas',
            'pendingCount',
            'activeCount',
            'rejectedCount',
            'totalAnak',
            'totalPemeriksaan',
            'recentPending',
            'monthlyStats',
            'anakStatusCounts'
        ));
    }

    /**
     * Hitung jumlah anak per status gizi (Normal, Stunting, Sangat Stunting, Belum Diukur)
     * berdasarkan kategori stunting dari pengukuran terakhir masing-masing anak.
     */
    private function anakStatusCounts(): array
    {
        return [
            'Normal' => Anak::whereHas('latestMeasurement', fn ($q) => $q->where('stunting_category', 'Normal'))->count(),
            'Stunting' => Anak::whereHas('latestMeasurement', fn ($q) => $q->where('stunting_category', 'Stunting'))->count(),
            'Sangat Stunting' => Anak::whereHas('latestMeasurement', fn ($q) => $q->where('stunting_category', 'Sangat Stunting'))->count(),
            'Belum Diukur' => Anak::whereDoesntHave('measurements')->count(),
        ];
    }

    /**
     * Data Anak: daftar seluruh anak lintas posyandu, dengan filter & pencarian.
     */
    public function anakIndex(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $posyanduId = $request->get('posyandu_id');
        $jenisKelamin = $request->get('jenis_kelamin');
        $kecamatan = $request->get('kecamatan');

        $sortable = ['nama', 'nik_anak', 'tanggal_lahir', 'posyandu', 'status_gizi'];
        $sort = in_array($request->get('sort'), $sortable, true) ? $request->get('sort') : 'created_at';
        $direction = $request->get('direction') === 'asc' ? 'asc' : 'desc';

        $query = Anak::with(['posyandu', 'latestMeasurement']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereLike('nama', "%{$search}%")
                    ->orWhereLike('nik_anak', "%{$search}%")
                    ->orWhereLike('nama_ayah', "%{$search}%")
                    ->orWhereLike('nama_ibu', "%{$search}%");
            });
        }

        if ($posyanduId) {
            $query->where('posyandu_id', $posyanduId);
        }

        if ($jenisKelamin) {
            $query->where('jenis_kelamin', $jenisKelamin);
        }

        if ($kecamatan) {
            $query->whereHas('posyandu', fn ($q) => $q->where('kecamatan', $kecamatan));
        }

        if ($status) {
            if ($status === 'Belum Diukur') {
                $query->whereDoesntHave('measurements');
            } else {
                $query->whereHas('latestMeasurement', fn ($q) => $q->where('stunting_category', $status));
            }
        }

        match ($sort) {
            'posyandu' => $query->select('anak.*')
                ->leftJoin('posyandu', 'posyandu.id', '=', 'anak.posyandu_id')
                ->orderBy('posyandu.nama', $direction),
            'status_gizi' => $query->orderBy(
                Measurement::select('stunting_category')
                    ->whereColumn('anak_id', 'anak.id')
                    ->orderByDesc('measured_at')
                    ->limit(1),
                $direction
            ),
            'nama', 'nik_anak', 'tanggal_lahir' => $query->orderBy($sort, $direction),
            default => $query->orderBy('anak.created_at', 'desc'),
        };

        $anak = $query->paginate(20)->withQueryString();

        $posyanduList = Posyandu::orderBy('nama')->get();
        $kecamatanList = Posyandu::whereNotNull('kecamatan')->distinct()->orderBy('kecamatan')->pluck('kecamatan');
        $statusCounts = $this->anakStatusCounts();

        return view('super-admin.anak.index', compact(
            'anak',
            'search',
            'status',
            'posyanduId',
            'jenisKelamin',
            'kecamatan',
            'posyanduList',
            'kecamatanList',
            'statusCounts',
            'sort',
            'direction'
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
                $q->whereLike('name', "%{$search}%")
                  ->orWhereLike('email', "%{$search}%")
                  ->orWhereHas('petugasProfile', function ($pq) use ($search) {
                      $pq->whereLike('nama_lengkap', "%{$search}%")
                         ->orWhereLike('posyandu_name', "%{$search}%")
                         ->orWhereLike('kota', "%{$search}%");
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

    /**
     * Reset password petugas.
     *
     * Password tersimpan sebagai hash bcrypt sehingga tidak bisa dibaca kembali;
     * satu-satunya cara memulihkan akses adalah menetapkan password baru.
     * Password baru dikirim balik lewat flash session agar bisa ditampilkan
     * sekali saja di halaman detail, lalu diserahkan ke petugas ybs.
     */
    public function resetPassword(Request $request, User $user)
    {
        if (! $user->isPetugas()) {
            abort(404);
        }

        $validated = $request->validate([
            'mode' => ['required', 'in:auto,manual'],
            'password' => ['exclude_if:mode,auto', 'required', 'confirmed', Password::defaults()],
        ], [], [
            'password' => 'password baru',
        ]);

        $newPassword = ($validated['mode'] === 'auto')
            ? $this->generateReadablePassword()
            : $validated['password'];

        // remember_token ikut diacak supaya sesi "ingat saya" yang lama tidak
        // bisa dipakai lagi setelah password diganti.
        $user->forceFill([
            'password' => $newPassword,
            'remember_token' => Str::random(60),
        ])->save();

        return back()
            ->with('success', "Password petugas {$user->name} berhasil direset.")
            ->with('new_password', $newPassword);
    }

    /**
     * Ubah username (alamat email) yang dipakai petugas untuk login.
     */
    public function updateUsername(Request $request, User $user)
    {
        if (! $user->isPetugas()) {
            abort(404);
        }

        $validated = $request->validate([
            'email' => [
                'required', 'string', 'lowercase', 'email', 'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
        ], [], [
            'email' => 'username (email)',
        ]);

        if ($validated['email'] === $user->email) {
            return back()->with('error', 'Username baru sama dengan username saat ini.');
        }

        $oldEmail = $user->email;

        // email_verified_at sengaja tidak direset: perubahan ini dilakukan
        // manual oleh super admin, dan mereset status verifikasi akan mengunci
        // petugas dari seluruh route ber-middleware "verified".
        $user->update(['email' => $validated['email']]);

        return back()->with('success', "Username petugas {$user->name} diubah dari {$oldEmail} menjadi {$validated['email']}.");
    }

    /**
     * Password acak yang mudah dibacakan: tanpa karakter ambigu (0/O, 1/l/I)
     * dan dijamin memuat minimal satu huruf besar, huruf kecil, dan angka.
     */
    private function generateReadablePassword(int $length = 10): string
    {
        $upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower = 'abcdefghijkmnopqrstuvwxyz';
        $digit = '23456789';
        $pool = $upper.$lower.$digit;

        $chars = [
            $upper[random_int(0, strlen($upper) - 1)],
            $lower[random_int(0, strlen($lower) - 1)],
            $digit[random_int(0, strlen($digit) - 1)],
        ];

        for ($i = count($chars); $i < $length; $i++) {
            $chars[] = $pool[random_int(0, strlen($pool) - 1)];
        }

        shuffle($chars);

        return implode('', $chars);
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
        $kecamatan = $request->get('kecamatan');
        $kelurahan = $request->get('kelurahan');

        // Desa/kelurahan selalu dibaca dalam konteks kecamatan yang dipilih.
        // Kalau kecamatan diganti tapi desa lama ikut terbawa di query string,
        // pasangan itu tidak akan pernah cocok, jadi desanya diabaikan saja.
        if ($kecamatan && $kelurahan && ! $this->kelurahanAdaDiKecamatan($kelurahan, $kecamatan)) {
            $kelurahan = null;
        }

        // Filter selain status; dipakai ulang untuk daftar sekaligus penghitung
        // chip, supaya angka di chip selalu cocok dengan hasil saat diklik.
        $baseFilter = fn ($query) => $query
            ->when($search, function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->whereLike('nama', "%{$search}%")
                        ->orWhereLike('kode_posyandu', "%{$search}%")
                        ->orWhereLike('kota', "%{$search}%")
                        ->orWhereLike('kecamatan', "%{$search}%")
                        ->orWhereLike('kelurahan', "%{$search}%");
                });
            })
            ->when($kecamatan, fn ($q) => $q->where('kecamatan', $kecamatan))
            ->when($kelurahan, fn ($q) => $q->where('kelurahan', $kelurahan));

        $posyandu = Posyandu::query()
            ->tap($baseFilter)
            ->when($status, fn ($query) => $query->where('status', $status))
            ->withCount(['petugas', 'anak'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $kecamatanList = $this->wilayahOptions('kecamatan');

        // Pilihan desa dipersempit ke kecamatan terpilih supaya dropdown-nya
        // tidak memuat seluruh desa dari kecamatan lain.
        $kelurahanList = $this->wilayahOptions('kelurahan', $kecamatan);

        $statusCounts = [
            'active' => Posyandu::query()->tap($baseFilter)->where('status', 'active')->count(),
            'inactive' => Posyandu::query()->tap($baseFilter)->where('status', '!=', 'active')->count(),
        ];

        return view('super-admin.posyandu.index', compact(
            'posyandu',
            'search',
            'status',
            'kecamatan',
            'kelurahan',
            'kecamatanList',
            'kelurahanList',
            'statusCounts'
        ));
    }

    /**
     * Daftar nilai wilayah unik (kecamatan / kelurahan) untuk dropdown filter,
     * opsional dibatasi pada satu kecamatan.
     */
    private function wilayahOptions(string $column, ?string $kecamatan = null)
    {
        return Posyandu::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->when($kecamatan, fn ($query) => $query->where('kecamatan', $kecamatan))
            ->distinct()
            ->orderBy($column)
            ->pluck($column);
    }

    private function kelurahanAdaDiKecamatan(string $kelurahan, string $kecamatan): bool
    {
        return Posyandu::where('kecamatan', $kecamatan)
            ->where('kelurahan', $kelurahan)
            ->exists();
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
            ->when($search, fn ($q) => $q->whereLike('nama', "%{$search}%"))
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


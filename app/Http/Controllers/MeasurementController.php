<?php

namespace App\Http\Controllers;

use App\Models\Anak;
use App\Models\Measurement;
use App\Services\AntropometriService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MeasurementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $posyanduId = $user->petugasProfile?->posyandu_id;
        $hasDateFilter = $request->filled('from') || $request->filled('to');
        $hasCategoryFilter = $request->filled('stunting_category') || $request->filled('gender');
        $hasMeasurementFilter = $hasDateFilter || $request->filled('stunting_category');

        $applyMeasurementFilters = fn ($query) => $this->applyDateFilters($query, $request)
            ->when($request->filled('stunting_category'), fn ($q) => $q->where('stunting_category', $request->stunting_category));

        $latestMeasuredAtSubquery = Measurement::query()
            ->select('measured_at')
            ->whereColumn('anak_id', 'anak.id')
            ->tap($applyMeasurementFilters)
            ->latest('measured_at')
            ->limit(1);

        $query = Anak::query()
            ->when(
                $posyanduId,
                fn (Builder $query) => $query->where('posyandu_id', $posyanduId),
                fn (Builder $query) => $query->whereRaw('1 = 0')
            )
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = strtolower($request->search);
                $query->where(function($q) use ($search) {
                    $q->whereRaw('lower(nama) like ?', ['%' . $search . '%'])
                      ->orWhereRaw('lower(nik_anak) like ?', ['%' . $search . '%']);
                });
            })
            ->when($request->filled('gender'), fn (Builder $query) => $query->where('jenis_kelamin', $request->gender))
            ->whereHas('measurements', $applyMeasurementFilters)
            ->withCount([
                'measurements as filtered_measurements_count' => $applyMeasurementFilters,
            ])
            ->orderByDesc($latestMeasuredAtSubquery)
            ->orderBy('nama');

        if ($hasMeasurementFilter) {
            $query->with([
                'measurements' => fn ($query) => $applyMeasurementFilters($query)
                    ->latest('measured_at'),
            ]);
        } else {
            $query->with(['latestMeasurement', 'latestPhotoMeasurement']);
        }

        $anakList = $query->paginate(10)->withQueryString();

        return view('measurements.index', [
            'anakList' => $anakList,
            'hasDateFilter' => $hasMeasurementFilter,
            'hasCategoryFilter' => $hasCategoryFilter,
        ]);
    }

    public function create(Request $request)
    {
        $selectedAnakId = $request->query('anak_id') ?: $request->session()->getOldInput('anak_id');
        $selectedAnak = null;

        if ($selectedAnakId) {
            $selectedAnak = Anak::with('posyandu')->findOrFail($selectedAnakId);
            $this->ensureCanAccessAnak($selectedAnak);
        }

        $formToken = (string) Str::uuid();
        $request->session()->put('measurement_form_token', $formToken);

        return view('measurements.create', compact('selectedAnak', 'formToken'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'anak_id' => 'required|exists:anak,id',
            'height_cm' => 'required|numeric|min:30|max:150',
            'weight_kg' => 'required|numeric|min:1|max:50',
            'manual_height_cm' => 'nullable|numeric|min:30|max:150',
            'manual_weight_kg' => 'nullable|numeric|min:1|max:50',
            'photo' => 'nullable|image|max:5120',
            'measured_at' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:1000',
            'form_token' => 'required|string',
        ]);

        $formToken = $validated['form_token'];

        // Atomic lock: a second request bearing the same token (double-click,
        // double-tap, or a network retry) will fail to acquire this and be
        // treated as a duplicate instead of inserting a second row.
        $lock = Cache::lock("measurement-submit:{$formToken}", 30);

        if (! $lock->get()) {
            return redirect()->route('measurements.create')
                ->with('error', 'Data sedang diproses, mohon tunggu sebentar.');
        }

        try {
            if ($request->session()->get('measurement_form_token') !== $formToken) {
                return redirect()->route('measurements.create')
                    ->with('error', 'Data ini sudah tersimpan sebelumnya.');
            }

            $request->session()->forget('measurement_form_token');

            return $this->storeMeasurement($request, $validated);
        } finally {
            $lock->release();
        }
    }

    private function storeMeasurement(Request $request, array $validated)
    {
        $anak = Anak::with('posyandu')->findOrFail($validated['anak_id']);
        $this->ensureCanAccessAnak($anak);

        $measuredAt = Carbon::parse($validated['measured_at']);
        $birthDate = Carbon::parse($anak->tanggal_lahir);

        if ($measuredAt->lt($birthDate)) {
            throw ValidationException::withMessages([
                'measured_at' => 'Tanggal pengukuran tidak boleh mendahului tanggal lahir anak.',
            ]);
        }

        $ageMonths = $birthDate->diffInMonths($measuredAt);

        $zScore = Measurement::calculateZScore($validated['height_cm'], (int) $ageMonths, $anak->jenis_kelamin);
        $stuntingCategory = Measurement::getStuntingCategory($zScore);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('measurements', 'r2');
        } elseif ($request->filled('photo_base64')) {
            // Handle base64 photo from camera capture
            $imageData = $request->input('photo_base64');
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageData = base64_decode($imageData);
            $filename = 'measurements/'.uniqid().'.jpg';
            Storage::disk('r2')->put($filename, $imageData);
            $photoPath = $filename;
        }

        $posePhotoPath = null;
        if ($request->filled('pose_photo_base64')) {
            $poseImageData = $request->input('pose_photo_base64');
            $poseImageData = preg_replace('/^data:image\/\w+;base64,/', '', $poseImageData);
            $poseImageData = base64_decode($poseImageData);
            $poseFilename = 'measurements/pose_'.uniqid().'.jpg';
            Storage::disk('r2')->put($poseFilename, $poseImageData);
            $posePhotoPath = $poseFilename;
        }

        Measurement::create([
            'user_id' => Auth::id(),
            'anak_id' => $anak->id,
            'child_name' => $anak->nama,
            'parent_name' => $anak->nama_ibu ?: $anak->nama_ayah,
            'posyandu_name' => $anak->posyandu?->nama ?? Auth::user()?->petugasProfile?->posyandu_name,
            'address' => $anak->alamat,
            'birth_date' => $anak->tanggal_lahir,
            'gender' => $anak->jenis_kelamin,
            'height_cm' => $validated['height_cm'],
            'weight_kg' => $validated['weight_kg'],
            'manual_height_cm' => $validated['manual_height_cm'] ?? null,
            'manual_weight_kg' => $validated['manual_weight_kg'] ?? null,
            'z_score' => $zScore,
            'stunting_category' => $stuntingCategory,
            'photo_path' => $photoPath,
            'pose_photo_path' => $posePhotoPath,
            'measured_at' => $validated['measured_at'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('measurements.anak.show', $anak)
            ->with('success', 'Pengukuran berhasil disimpan!');
    }

    public function showAnak(Anak $anak, Request $request)
    {
        $this->ensureCanAccessAnak($anak);

        $anak->load([
            'posyandu',
            'latestMeasurement',
            'latestPhotoMeasurement',
            'measurements' => fn ($query) => $query
                ->with(['anak', 'user.petugasProfile'])
                ->when($request->filled('stunting_category'), fn ($q) => $q->where('stunting_category', $request->stunting_category))
                ->tap(fn ($q) => $this->applyDateFilters($q, $request))
                ->orderBy('measured_at'),
        ]);

        if ($request->filled('gizi_status')) {
            $anak->setRelation('measurements', $anak->measurements->filter(function ($measurement) use ($request) {
                $antro = $measurement->antropometriLengkap();
                if (! $antro) {
                    return false;
                }

                $hasIssue = collect(['bb_u', 'pb_tb_u', 'bb_pb_tb', 'imt_u'])
                    ->contains(fn ($key) => ($antro[$key]['severity'] ?? 'normal') !== 'normal');

                return $request->gizi_status === 'bermasalah' ? $hasIssue : ! $hasIssue;
            })->values());
        }

        $hasHistoryFilter = $request->filled('from') || $request->filled('to')
            || $request->filled('stunting_category') || $request->filled('gizi_status');

        return view('measurements.anak-show', [
            'anak' => $anak,
            'hasHistoryFilter' => $hasHistoryFilter,
            'latestMeasurement' => $anak->latestMeasurement,
        ]);
    }

    public function show(Measurement $measurement)
    {
        $this->ensureCanViewMeasurement($measurement);
        $measurement->loadMissing(['anak', 'user.petugasProfile']);

        return view('measurements.show', compact('measurement'));
    }

    public function destroy(Measurement $measurement)
    {
        if ($measurement->user_id !== Auth::id()) {
            abort(403);
        }

        if ($measurement->photo_path) {
            Storage::disk('r2')->delete($measurement->photo_path);
        }
        
        if ($measurement->pose_photo_path) {
            Storage::disk('r2')->delete($measurement->pose_photo_path);
        }

        $measurement->delete();

        return redirect()->to(
            $measurement->anak_id
                ? route('measurements.anak.show', $measurement->anak_id)
                : route('measurements.index')
        )
            ->with('success', 'Pengukuran berhasil dihapus!');
    }

    public function searchAnak(Request $request)
    {
        $q = strtolower(trim($request->query('q')));
        if (strlen($q) < 3) {
            return response()->json([]);
        }

        $query = Anak::query()
            ->select([
                'id',
                'nama',
                'nik_anak',
                'nama_ayah',
                'nama_ibu',
                'alamat',
                'tanggal_lahir',
                'jenis_kelamin',
                'posyandu_id',
            ])
            ->where(function($b) use ($q) {
            $b->whereRaw('lower(nik_anak) like ?', ['%' . $q . '%'])
              ->orWhereRaw('lower(nama) like ?', ['%' . $q . '%']);
        });

        if (auth()->user()->isPetugasPosyandu()) {
            $posyanduId = auth()->user()->petugasProfile?->posyandu_id;
            if ($posyanduId) {
                $query->where('posyandu_id', $posyanduId);
            }
        }

        $anak = $query->limit(10)->get();
        return response()->json($anak);
    }

    /**
     * Proxy image to the ML prediction API (Hugging Face Space).
     */
    public function predict(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
        ]);

        $file = $request->file('image');

        $baseUrl = rtrim((string) config('services.stunting.url'), '/');
        $token = config('services.stunting.token');

        if (! $baseUrl) {
            return response()->json([
                'error' => 'Prediction API belum dikonfigurasi. Set STUNTING_API_URL di .env.',
            ], 500);
        }

        try {
            $ch = curl_init($baseUrl . '/predict');
            $cfile = new \CURLFile(
                $file->getPathname(),
                $file->getMimeType(),
                $file->getClientOriginalName()
            );

            $headers = ['Accept: application/json'];
            if ($token) {
                $headers[] = 'Authorization: Bearer ' . $token;
            }

            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => ['file' => $cfile],
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_RETURNTRANSFER => true,
                // Must stay under Heroku's hard 30s router timeout so Laravel
                // can still return a clean JSON error instead of Heroku's H12 page.
                CURLOPT_TIMEOUT => 25,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $startTime = microtime(true);
            $response = curl_exec($ch);
            $durationMs = round((microtime(true) - $startTime) * 1000);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false || $httpCode !== 200) {
                $isTimeout = str_contains($error, 'timed out') || str_contains($error, 'timeout');
                $message = $isTimeout
                    ? 'Model AI sedang "bangun" dari mode tidur (cold start), butuh waktu lebih lama. Silakan coba lagi dalam beberapa detik.'
                    : 'Prediction API tidak tersedia (HTTP ' . $httpCode . '). ' . $error;

                return response()->json(['error' => $message], 502);
            }

            $data = json_decode($response, true);
            $heightCm = isset($data['height_cm']) ? round($data['height_cm'], 2) : null;
            $weightKg = isset($data['weight_kg']) ? round($data['weight_kg'], 2) : null;

            return response()->json([
                'height_cm' => $heightCm,
                'weight_kg' => $weightKg,
                'height_error' => $heightCm === null ? ($data['message'] ?? 'Tinggi badan tidak dapat diprediksi.') : null,
                'weight_error' => $weightKg === null ? ($data['message'] ?? 'Berat badan tidak dapat diprediksi.') : null,
                'duration_ms' => $durationMs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menghubungi Prediction API: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fire-and-forget ping to nudge the HF Space awake from a cold start
     * before the user actually submits a photo for prediction.
     */
    public function warmup()
    {
        $baseUrl = rtrim((string) config('services.stunting.url'), '/');
        if (! $baseUrl) {
            return response()->json(['status' => 'unconfigured']);
        }

        $token = config('services.stunting.token');
        $headers = ['Accept: application/json'];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $ch = curl_init($baseUrl . '/health');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);
        curl_exec($ch);
        curl_close($ch);

        return response()->json(['status' => 'pinged']);
    }

    /**
     * Hitung status gizi lengkap (Z-Score BB/U, PB/U-TB/U, BB/PB-BB/TB, IMT/U) untuk
     * data anak + hasil tinggi/berat badan yang belum disimpan, dipakai oleh form
     * "Pengukuran Baru" agar status gizi langsung tampil begitu hasil prediksi ML keluar.
     */
    public function antropometri(Request $request)
    {
        $validated = $request->validate([
            'gender' => 'required|in:L,P',
            'birth_date' => 'required|date',
            'measured_at' => 'required|date',
            'height_cm' => 'required|numeric|min:30|max:150',
            'weight_kg' => 'required|numeric|min:1|max:50',
        ]);

        $lahir = Carbon::parse($validated['birth_date']);
        $diukur = Carbon::parse($validated['measured_at']);

        if ($diukur->lt($lahir)) {
            return response()->json([
                'error' => 'Tanggal pengukuran tidak boleh mendahului tanggal lahir.',
            ], 422);
        }

        $startTime = microtime(true);

        $umurBulan = $lahir->diffInDays($diukur) / AntropometriService::HARI_PER_BULAN;
        $result = AntropometriService::hitungLengkap(
            $validated['gender'],
            (float) $validated['weight_kg'],
            (float) $validated['height_cm'],
            $umurBulan
        );

        $durationMs = round((microtime(true) - $startTime) * 1000, 2);

        return response()->json($result + ['duration_ms' => $durationMs]);
    }

    private function applyDateFilters($query, Request $request)
    {
        if ($request->filled('from')) {
            $query->whereDate('measured_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('measured_at', '<=', $request->to);
        }

        return $query;
    }

    private function ensureCanAccessAnak(Anak $anak): void
    {
        $user = Auth::user();
        $posyanduId = $user?->petugasProfile?->posyandu_id;

        if (! $user?->isPetugasPosyandu() || ! $posyanduId || (int) $anak->posyandu_id !== (int) $posyanduId) {
            abort(403);
        }
    }

    private function ensureCanViewMeasurement(Measurement $measurement): void
    {
        if ((int) $measurement->user_id === (int) Auth::id()) {
            return;
        }

        $measurement->loadMissing('anak');

        if (! $measurement->anak) {
            abort(403);
        }

        $this->ensureCanAccessAnak($measurement->anak);
    }
}

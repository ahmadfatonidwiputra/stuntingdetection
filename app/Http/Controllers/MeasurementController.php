<?php

namespace App\Http\Controllers;

use App\Models\Measurement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MeasurementController extends Controller
{
    public function index(Request $request)
    {
        $query = Measurement::where('user_id', Auth::id())
            ->orderBy('measured_at', 'desc');

        if ($request->filled('from')) {
            $query->whereDate('measured_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('measured_at', '<=', $request->to);
        }

        $measurements = $query->paginate(10);

        return view('measurements.index', compact('measurements'));
    }

    public function create()
    {
        return view('measurements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'child_name' => 'required|string|max:255',
            'parent_name' => 'required|string|max:255',
            'posyandu_name' => 'nullable|string|max:255',
            'address' => 'required|string|max:500',
            'birth_date' => 'required|date|before_or_equal:today',
            'gender' => 'required|in:L,P',
            'height_cm' => 'required|numeric|min:30|max:150',
            'weight_kg' => 'required|numeric|min:1|max:50',
            'photo' => 'nullable|image|max:5120',
            'measured_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $birthDate = Carbon::parse($validated['birth_date']);
        $measuredAt = Carbon::parse($validated['measured_at']);
        $ageMonths = $birthDate->diffInMonths($measuredAt);

        $zScore = Measurement::calculateZScore($validated['height_cm'], (int) $ageMonths, $validated['gender']);
        $stuntingCategory = Measurement::getStuntingCategory($zScore);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('measurements', 'public');
        } elseif ($request->filled('photo_base64')) {
            // Handle base64 photo from camera capture
            $imageData = $request->input('photo_base64');
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageData = base64_decode($imageData);
            $filename = 'measurements/'.uniqid().'.jpg';
            Storage::disk('public')->put($filename, $imageData);
            $photoPath = $filename;
        }

        $posePhotoPath = null;
        if ($request->filled('pose_photo_base64')) {
            $poseImageData = $request->input('pose_photo_base64');
            $poseImageData = preg_replace('/^data:image\/\w+;base64,/', '', $poseImageData);
            $poseImageData = base64_decode($poseImageData);
            $poseFilename = 'measurements/pose_'.uniqid().'.jpg';
            Storage::disk('public')->put($poseFilename, $poseImageData);
            $posePhotoPath = $poseFilename;
        }

        Measurement::create([
            'user_id' => Auth::id(),
            'child_name' => $validated['child_name'],
            'parent_name' => $validated['parent_name'],
            'posyandu_name' => $validated['posyandu_name'] ?? null,
            'address' => $validated['address'],
            'birth_date' => $validated['birth_date'],
            'gender' => $validated['gender'],
            'height_cm' => $validated['height_cm'],
            'weight_kg' => $validated['weight_kg'],
            'z_score' => $zScore,
            'stunting_category' => $stuntingCategory,
            'photo_path' => $photoPath,
            'pose_photo_path' => $posePhotoPath,
            'measured_at' => $validated['measured_at'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('measurements.index')
            ->with('success', 'Pengukuran berhasil disimpan!');
    }

    public function show(Measurement $measurement)
    {
        if ($measurement->user_id !== Auth::id()) {
            abort(403);
        }

        return view('measurements.show', compact('measurement'));
    }

    public function destroy(Measurement $measurement)
    {
        if ($measurement->user_id !== Auth::id()) {
            abort(403);
        }

        if ($measurement->photo_path) {
            Storage::disk('public')->delete($measurement->photo_path);
        }
        
        if ($measurement->pose_photo_path) {
            Storage::disk('public')->delete($measurement->pose_photo_path);
        }

        $measurement->delete();

        return redirect()->route('measurements.index')
            ->with('success', 'Pengukuran berhasil dihapus!');
    }

    /**
     * Proxy image to the Python ML prediction API.
     */
    public function predict(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
        ]);

        $file = $request->file('image');

        try {
            $ch = curl_init('http://localhost:5001/predict');
            $cfile = new \CURLFile(
                $file->getPathname(),
                $file->getMimeType(),
                $file->getClientOriginalName()
            );

            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => ['image' => $cfile],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_CONNECTTIMEOUT => 10,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response === false || $httpCode !== 200) {
                return response()->json([
                    'error' => 'Prediction API tidak tersedia. Pastikan server Python sedang berjalan. ' . $error,
                ], 502);
            }

            return response()->json(json_decode($response, true));
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal menghubungi Prediction API: ' . $e->getMessage(),
            ], 500);
        }
    }
}

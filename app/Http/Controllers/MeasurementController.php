<?php

namespace App\Http\Controllers;

use App\Models\Measurement;
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
            'height_cm' => 'required|numeric|min:30|max:300',
            'weight_kg' => 'required|numeric|min:1|max:500',
            'photo' => 'nullable|image|max:5120',
            'measured_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $bmi = Measurement::calculateBmi($validated['height_cm'], $validated['weight_kg']);
        $bmiCategory = Measurement::getBmiCategory($bmi);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('measurements', 'public');
        } elseif ($request->filled('photo_base64')) {
            // Handle base64 photo from camera capture
            $imageData = $request->input('photo_base64');
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageData = base64_decode($imageData);
            $filename = 'measurements/' . uniqid() . '.jpg';
            Storage::disk('public')->put($filename, $imageData);
            $photoPath = $filename;
        }

        Measurement::create([
            'user_id' => Auth::id(),
            'height_cm' => $validated['height_cm'],
            'weight_kg' => $validated['weight_kg'],
            'bmi' => $bmi,
            'bmi_category' => $bmiCategory,
            'photo_path' => $photoPath,
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

        $measurement->delete();

        return redirect()->route('measurements.index')
            ->with('success', 'Pengukuran berhasil dihapus!');
    }
}

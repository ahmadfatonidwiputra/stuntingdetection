<?php

namespace App\Http\Controllers;

use App\Models\Measurement;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $measurements = Measurement::where('user_id', $user->id)
            ->orderBy('measured_at', 'desc')
            ->get();

        $totalMeasurements = $measurements->count();
        $latestMeasurement = $measurements->first();
        $avgZScore = $measurements->avg('z_score');
        $avgHeight = $measurements->avg('height_cm');
        $avgWeight = $measurements->avg('weight_kg');

        $stuntingCounts = [
            'Sangat Stunting' => $measurements->where('stunting_category', 'Sangat Stunting')->count(),
            'Stunting' => $measurements->where('stunting_category', 'Stunting')->count(),
            'Normal' => $measurements->where('stunting_category', 'Normal')->count(),
        ];

        // Data for charts (last 10 measurements, reversed for chronological)
        $chartData = $measurements->take(10)->reverse()->values();

        $recentMeasurements = $measurements->take(5);

        return view('dashboard', compact(
            'totalMeasurements',
            'latestMeasurement',
            'avgZScore',
            'avgHeight',
            'avgWeight',
            'chartData',
            'recentMeasurements',
            'stuntingCounts'
        ));
    }
}

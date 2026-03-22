<?php

namespace App\Http\Controllers;

use App\Models\Measurement;
use Illuminate\Http\Request;
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
        $avgBmi = $measurements->avg('bmi');
        $avgHeight = $measurements->avg('height_cm');
        $avgWeight = $measurements->avg('weight_kg');

        // Data for charts (last 10 measurements, reversed for chronological)
        $chartData = $measurements->take(10)->reverse()->values();

        $recentMeasurements = $measurements->take(5);

        return view('dashboard', compact(
            'totalMeasurements',
            'latestMeasurement',
            'avgBmi',
            'avgHeight',
            'avgWeight',
            'chartData',
            'recentMeasurements'
        ));
    }
}

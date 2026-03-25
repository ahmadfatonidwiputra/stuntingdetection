@extends('layouts.main')

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Ringkasan pengukuran tubuh Anda</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="glass-card stat-card blue fade-in">
        <div class="stat-icon blue">📊</div>
        <div class="stat-value">{{ $totalMeasurements }}</div>
        <div class="stat-label">Total Pengukuran</div>
    </div>

    <div class="glass-card stat-card green fade-in">
        <div class="stat-icon green">📏</div>
        <div class="stat-value">{{ $latestMeasurement ? number_format($latestMeasurement->height_cm, 1) : '-' }}</div>
        <div class="stat-label">Tinggi Terakhir (cm)</div>
    </div>

    <div class="glass-card stat-card orange fade-in">
        <div class="stat-icon orange">⚖️</div>
        <div class="stat-value">{{ $latestMeasurement ? number_format($latestMeasurement->weight_kg, 1) : '-' }}</div>
        <div class="stat-label">Berat Terakhir (kg)</div>
    </div>

    <div class="glass-card stat-card purple fade-in">
        <div class="stat-icon purple">💪</div>
        <div class="stat-value">{{ $avgZScore ? number_format($avgZScore, 2) : '-' }}</div>
        <div class="stat-label">Rata-rata Z-Score</div>
    </div>
</div>

<!-- Charts -->
<div class="chart-container">
    <div class="glass-card fade-in">
        <div class="chart-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-blue)" stroke-width="2">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
            Trend Pengukuran
        </div>
        <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <div class="glass-card fade-in">
        <div class="chart-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-purple)" stroke-width="2">
                <path d="M21.21 15.89A10 10 0 1 1 8 2.83"/>
                <path d="M22 12A10 10 0 0 0 12 2v10z"/>
            </svg>
            Status Stunting
        </div>
        <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="bmiChart"></canvas>
        </div>
    </div>
</div>

<!-- Recent Measurements Table -->
<div class="glass-card fade-in">
    <div class="flex-between" style="margin-bottom: 20px;">
        <div class="chart-title" style="margin-bottom: 0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-green)" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Pengukuran Terbaru
        </div>
        <a href="{{ route('measurements.create') }}" class="btn btn-primary btn-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Pengukuran Baru
        </a>
    </div>

    @if($recentMeasurements->count() > 0)
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Tinggi</th>
                        <th>Berat</th>
                        <th>Z-Score</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentMeasurements as $m)
                    <tr>
                        <td>{{ $m->measured_at->format('d M Y') }}</td>
                        <td>{{ number_format($m->height_cm, 1) }} cm</td>
                        <td>{{ number_format($m->weight_kg, 1) }} kg</td>
                        <td>{{ number_format($m->z_score, 2) }}</td>
                        <td>
                            <span class="badge badge-{{ strtolower(str_replace(' ', '-', $m->stunting_category)) }}">
                                {{ $m->stunting_category }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('measurements.show', $m) }}" class="btn btn-secondary btn-sm">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h3>Belum ada pengukuran</h3>
            <p>Mulai ukur berat dan tinggi badan Anda untuk melihat tren kesehatan.</p>
            <a href="{{ route('measurements.create') }}" class="btn btn-primary">Mulai Pengukuran</a>
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartData = @json($chartData);

    // Theme aware color helpers
    const getGridColor = () => document.documentElement.classList.contains('light-theme') ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.05)';
    const getLegendColor = () => document.documentElement.classList.contains('light-theme') ? '#475569' : '#94a3b8';
    const getChartBgColor = () => document.documentElement.classList.contains('light-theme') ? 'rgba(0,0,0,0.03)' : 'rgba(255,255,255,0.05)';

    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: chartData.map(d => {
                const date = new Date(d.measured_at);
                return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
            }),
            datasets: [
                {
                    label: 'Berat (kg)',
                    data: chartData.map(d => d.weight_kg),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                },
                {
                    label: 'Tinggi (cm)',
                    data: chartData.map(d => d.height_cm),
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: {
                    labels: { color: getLegendColor(), font: { family: 'Inter' } }
                }
            },
            scales: {
                x: {
                    grid: { color: getGridColor() },
                    ticks: { color: '#64748b', font: { family: 'Inter' } }
                },
                y: {
                    position: 'left',
                    grid: { color: getGridColor() },
                    ticks: { color: '#3b82f6', font: { family: 'Inter' } },
                    title: { display: true, text: 'Berat (kg)', color: '#3b82f6', font: { family: 'Inter' } }
                },
                y1: {
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: { color: '#8b5cf6', font: { family: 'Inter' } },
                    title: { display: true, text: 'Tinggi (cm)', color: '#8b5cf6', font: { family: 'Inter' } }
                }
            }
        }
    });

    // Stunting Doughnut Chart
    const bmiCtx = document.getElementById('bmiChart').getContext('2d');
    const stuntingCounts = @json($stuntingCounts);
    const bmiLabels = Object.keys(stuntingCounts).filter(k => stuntingCounts[k] > 0);
    const bmiValues = bmiLabels.map(k => stuntingCounts[k]);

    const stuntingColors = {
        'Sangat Stunting': '#ef4444',
        'Stunting': '#f59e0b',
        'Normal': '#10b981'
    };

    const bgColors = bmiLabels.map(l => stuntingColors[l] || '#64748b');

    const bmiChart = new Chart(bmiCtx, {
        type: 'doughnut',
        data: {
            labels: bmiLabels.length > 0 ? bmiLabels : ['Belum ada data'],
            datasets: [{
                data: bmiValues.length > 0 ? bmiValues : [1],
                backgroundColor: bgColors.length > 0 && bmiValues.length > 0 ? bgColors : [getChartBgColor()],
                borderColor: 'transparent',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: getLegendColor(), padding: 16, font: { family: 'Inter' } }
                }
            }
        }
    });

    // Update charts on theme toggle
    window.addEventListener('themeToggled', () => {
        const gridColor = getGridColor();
        const legendColor = getLegendColor();
        const chartBgColor = getChartBgColor();

        // Update trend chart
        trendChart.options.scales.x.grid.color = gridColor;
        trendChart.options.scales.y.grid.color = gridColor;
        trendChart.options.plugins.legend.labels.color = legendColor;
        trendChart.update();

        // Update bmi chart
        if (bmiValues.length === 0) {
            bmiChart.data.datasets[0].backgroundColor = [chartBgColor];
        }
        bmiChart.options.plugins.legend.labels.color = legendColor;
        bmiChart.update();
    });
});
</script>
@endpush
@endsection

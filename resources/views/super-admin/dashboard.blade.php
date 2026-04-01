@extends('layouts.main')

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard Super Admin</h1>
    <p class="page-subtitle">Ringkasan data sistem AI Stunt Detect</p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="glass-card stat-card blue fade-in">
        <div class="stat-icon blue">👥</div>
        <div class="stat-value">{{ $totalPetugas }}</div>
        <div class="stat-label">Total Petugas</div>
    </div>
    <div class="glass-card stat-card orange fade-in">
        <div class="stat-icon orange">⏳</div>
        <div class="stat-value">{{ $pendingCount }}</div>
        <div class="stat-label">Menunggu Verifikasi</div>
    </div>
    <div class="glass-card stat-card green fade-in">
        <div class="stat-icon green">👶</div>
        <div class="stat-value">{{ $totalAnak }}</div>
        <div class="stat-label">Total Anak Tercatat</div>
    </div>
    <div class="glass-card stat-card purple fade-in">
        <div class="stat-icon purple">📊</div>
        <div class="stat-value">{{ $totalPemeriksaan }}</div>
        <div class="stat-label">Total Pemeriksaan</div>
    </div>
</div>

<!-- Charts -->
<div class="chart-container">
    <div class="glass-card fade-in">
        <div class="chart-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-blue)" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>
            </svg>
            Registrasi Petugas (6 Bulan Terakhir)
        </div>
        <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="registrationChart"></canvas>
        </div>
    </div>

    <div class="glass-card fade-in">
        <div class="chart-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-purple)" stroke-width="2">
                <path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>
            </svg>
            Status Petugas
        </div>
        <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

@if($pendingCount > 0)
<!-- Pending Registrations -->
<div class="glass-card fade-in">
    <div class="flex-between" style="margin-bottom: 20px;">
        <div class="chart-title" style="margin-bottom: 0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-orange)" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            Menunggu Verifikasi
        </div>
        <a href="{{ route('super-admin.petugas.index', ['tab' => 'pending']) }}" class="btn btn-primary btn-sm">Lihat Semua →</a>
    </div>

    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Posyandu</th>
                    <th>Wilayah</th>
                    <th>Tanggal Daftar</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentPending as $p)
                <tr>
                    <td>
                        <div style="font-weight: 600;">{{ $p->petugasProfile?->nama_lengkap ?? $p->name }}</div>
                        <div style="font-size: 12px; color: var(--text-muted);">{{ $p->email }}</div>
                    </td>
                    <td>{{ $p->petugasProfile?->posyandu_name ?? '-' }}</td>
                    <td>{{ $p->petugasProfile?->kota ?? '-' }}</td>
                    <td>{{ $p->created_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('super-admin.petugas.show', $p) }}" class="btn btn-secondary btn-sm">Review</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyStats = @json($monthlyStats);

    // Registration Chart
    new Chart(document.getElementById('registrationChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: monthlyStats.map(d => d.month),
            datasets: [{
                label: 'Registrasi',
                data: monthlyStats.map(d => d.count),
                backgroundColor: 'rgba(59, 130, 246, 0.3)',
                borderColor: '#3b82f6',
                borderWidth: 2,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { labels: { color: '#94a3b8', font: { family: 'Inter' } } } },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b', font: { family: 'Inter' } } },
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#64748b', font: { family: 'Inter' }, stepSize: 1 }, beginAtZero: true }
            }
        }
    });

    // Status Doughnut
    new Chart(document.getElementById('statusChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Aktif', 'Pending', 'Ditolak', 'Suspended'],
            datasets: [{
                data: [{{ $activeCount }}, {{ $pendingCount }}, {{ $rejectedCount }}, 0],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#64748b'],
                borderColor: 'transparent',
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, cutout: '65%',
            plugins: { legend: { position: 'bottom', labels: { color: '#94a3b8', padding: 16, font: { family: 'Inter' } } } }
        }
    });
});
</script>
@endpush
@endsection

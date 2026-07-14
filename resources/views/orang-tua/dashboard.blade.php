@extends('layouts.main')

@section('title', 'Dashboard Orang Tua - AI Stunt Detect')

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard Orang Tua</h1>
    <p class="page-subtitle">
        {{ $profile?->hubungan ? ucfirst($profile->hubungan) : 'Orang Tua' }} •
        Pantau tumbuh kembang anak Anda
    </p>
</div>

<!-- Stats Cards -->
<div class="stats-grid">
    <div class="glass-card stat-card blue fade-in">
        <div class="stat-icon blue">👶</div>
        <div class="stat-value">{{ $totalAnak }}</div>
        <div class="stat-label">Anak Terdaftar</div>
    </div>

    <div class="glass-card stat-card green fade-in">
        <div class="stat-icon green">📊</div>
        <div class="stat-value">{{ $anakList->sum(fn($a) => $a['last_measurement'] ? 1 : 0) }}</div>
        <div class="stat-label">Anak dengan Data</div>
    </div>

    @if($lastCheck)
    <div class="glass-card stat-card orange fade-in">
        <div class="stat-icon orange">🏥</div>
        <div class="stat-value" style="font-size: 18px;">{{ \Carbon\Carbon::parse($lastCheck->measured_at)->translatedFormat('d M Y') }}</div>
        <div class="stat-label">Pemeriksaan Terakhir</div>
    </div>
    @endif
</div>

<!-- Daftar Anak -->
<div class="glass-card fade-in">
    <div class="chart-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-blue)" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        Daftar Anak Saya
    </div>

    @if($anakList->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">🔍</div>
            <h3>Belum ada anak terhubung</h3>
            <p>Akun Anda belum terhubung dengan data anak. Hubungi petugas posyandu Anda.</p>
        </div>
    @else
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Jenis Kelamin</th>
                        <th>Umur</th>
                        <th>Posyandu</th>
                        <th>BB / TB</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($anakList as $anak)
                    @php
                        $badgeClass = match(true) {
                            str_contains(strtolower($anak['status']), 'sangat stunting') => 'badge-sangat-stunting',
                            str_contains(strtolower($anak['status']), 'stunting') => 'badge-stunting',
                            str_contains(strtolower($anak['status']), 'normal') => 'badge-normal',
                            default => '',
                        };
                    @endphp
                    <tr>
                        <td style="font-weight: 600;">
                            {{ $anak['jenis_kelamin'] === 'L' ? '👦' : '👧' }} {{ $anak['nama'] }}
                        </td>
                        <td>{{ $anak['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        <td>{{ $anak['umur']['formatted'] }}</td>
                        <td>{{ $anak['posyandu'] ?? '-' }}</td>
                        <td>
                            @if($anak['last_measurement'])
                                {{ $anak['last_measurement']->weight_kg }} kg / {{ $anak['last_measurement']->height_cm }} cm
                            @else
                                -
                            @endif
                        </td>
                        <td><span class="badge {{ $badgeClass }}">{{ $anak['status'] }}</span></td>
                        <td>
                            <a href="{{ route('orang-tua.anak.show', $anak['id']) }}" class="btn btn-secondary btn-sm">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection

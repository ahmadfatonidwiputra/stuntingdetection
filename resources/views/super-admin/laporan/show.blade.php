@extends('layouts.main')

@section('content')
<div style="margin-bottom: 8px;">
    <a href="{{ route('super-admin.laporan.index') }}" style="color: var(--accent-blue); text-decoration: none; font-size: 14px; font-weight: 600;">← Manajemen Laporan</a>
</div>

<div class="page-header">
    <h1 class="page-title">🏥 {{ $posyandu->nama }}</h1>
    <p class="page-subtitle">
        {{ $posyandu->kode_posyandu ? $posyandu->kode_posyandu . ' — ' : '' }}
        {{ collect([$posyandu->kelurahan, $posyandu->kecamatan, $posyandu->kota, $posyandu->provinsi])->filter()->join(', ') ?: '-' }}
    </p>
</div>

<!-- Filter & Download -->
<div class="glass-card fade-in" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('super-admin.laporan.show', $posyandu) }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ $dari }}" class="form-input">
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ $sampai }}" class="form-input">
        </div>
        <button type="submit" class="btn btn-secondary btn-sm">Terapkan Filter</button>
        @if($dari || $sampai)
            <a href="{{ route('super-admin.laporan.show', $posyandu) }}" class="btn btn-secondary btn-sm">Reset</a>
        @endif
        <button type="submit" formaction="{{ route('super-admin.laporan.download', $posyandu) }}" class="btn btn-primary btn-sm" style="margin-left: auto;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; vertical-align: middle; margin-right: 4px;">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Download CSV
        </button>
    </form>
</div>

<!-- Summary -->
<div class="stats-grid">
    <div class="glass-card stat-card blue fade-in">
        <div class="stat-icon blue">📊</div>
        <div class="stat-value">{{ $summary['total'] }}</div>
        <div class="stat-label">Total Pengukuran</div>
    </div>
    <div class="glass-card stat-card green fade-in">
        <div class="stat-icon green">✅</div>
        <div class="stat-value">{{ $summary['normal'] }}</div>
        <div class="stat-label">Normal</div>
    </div>
    <div class="glass-card stat-card orange fade-in">
        <div class="stat-icon orange">⚠️</div>
        <div class="stat-value">{{ $summary['stunting'] }}</div>
        <div class="stat-label">Stunting</div>
    </div>
    <div class="glass-card stat-card purple fade-in">
        <div class="stat-icon purple">🔴</div>
        <div class="stat-value">{{ $summary['sangat_stunting'] }}</div>
        <div class="stat-label">Sangat Stunting</div>
    </div>
</div>

<!-- Data Table -->
<div class="glass-card fade-in">
    @if($measurements->count() > 0)
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Anak</th>
                        <th>TB (cm)</th>
                        <th>BB (kg)</th>
                        <th>Z-Score</th>
                        <th>Kategori</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($measurements as $m)
                    <tr>
                        <td>{{ $m->measured_at->format('d M Y') }}</td>
                        <td>{{ $m->anak?->nama ?? $m->child_name ?? '-' }}</td>
                        <td>{{ number_format((float) $m->height_cm, 1) }}</td>
                        <td>{{ number_format((float) $m->weight_kg, 1) }}</td>
                        <td>{{ number_format((float) $m->z_score, 2) }}</td>
                        <td>
                            @if($m->stunting_category === 'Normal')
                                <span class="badge badge-normal">Normal</span>
                            @elseif($m->stunting_category === 'Stunting')
                                <span class="badge badge-stunting">Stunting</span>
                            @else
                                <span class="badge badge-sangat-stunting">Sangat Stunting</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($measurements->hasPages())
            <div style="margin-top: 24px; display: flex; justify-content: center;">
                {{ $measurements->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <div class="empty-state-icon">📊</div>
            <h3>Belum ada data pengukuran</h3>
            <p>{{ ($dari || $sampai) ? 'Tidak ada data pada rentang tanggal yang dipilih.' : 'Posyandu ini belum memiliki data pengukuran.' }}</p>
        </div>
    @endif
</div>
@endsection

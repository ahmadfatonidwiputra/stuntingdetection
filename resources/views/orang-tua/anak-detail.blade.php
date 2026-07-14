@extends('layouts.landing')

@section('title', '{{ $anak->nama }} - Tumbuh Kembang - AI Stunt Detect')

@push('styles')
<style>
    .detail-main { max-width: 1000px; margin: 0 auto; padding: 100px 24px 60px; }
    .back-btn { display: inline-flex; align-items: center; gap: 6px; color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 600; margin-bottom: 24px; transition: gap 0.2s; }
    .back-btn:hover { gap: 10px; }
    .anak-profile-card {
        background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 20px;
        padding: 28px; margin-bottom: 24px;
        display: flex; align-items: center; gap: 24px; flex-wrap: wrap;
    }
    .profile-avatar {
        width: 80px; height: 80px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        display: flex; align-items: center; justify-content: center; font-size: 40px;
    }
    .profile-name { font-size: 24px; font-weight: 800; margin-bottom: 8px; }
    .profile-meta { display: flex; flex-wrap: wrap; gap: 8px; font-size: 13px; }
    .profile-meta span { background: var(--bg-main); padding: 4px 12px; border-radius: 100px; color: var(--text-secondary); }
    .status-badge-lg {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 8px 20px; border-radius: 100px; font-size: 15px; font-weight: 700;
    }
    .status-normal-lg { background: rgba(16,185,129,0.15); color: #10b981; }
    .status-stunting-lg { background: rgba(239,68,68,0.15); color: var(--danger); }
    .status-berisiko-lg { background: rgba(245,158,11,0.15); color: var(--warning); }

    .chart-card {
        background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 20px;
        padding: 24px; margin-bottom: 24px;
    }
    .chart-title { font-size: 16px; font-weight: 700; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .chart-tabs { display: flex; gap: 8px; margin-bottom: 20px; }
    .chart-tab {
        padding: 6px 16px; border-radius: 100px; font-size: 13px; font-weight: 600;
        cursor: pointer; border: 1px solid var(--glass-border); background: none;
        color: var(--text-secondary); font-family: 'Inter', sans-serif; transition: all 0.2s;
    }
    .chart-tab.active {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white; border-color: transparent;
    }

    .history-card {
        background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 20px;
        padding: 24px; margin-bottom: 24px;
    }
    .history-item {
        display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
        padding: 16px 0; border-bottom: 1px solid var(--glass-border);
    }
    .history-item:last-child { border-bottom: none; }
    .history-date { font-weight: 700; font-size: 14px; min-width: 120px; }
    .history-stats { display: flex; gap: 16px; flex-wrap: wrap; flex: 1; }
    .history-stat { font-size: 13px; }
    .history-stat .label { color: var(--text-muted); }
    .history-stat .val { font-weight: 700; }
    .status-badge-sm {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 4px 12px; border-radius: 100px; font-size: 12px; font-weight: 600;
    }

    .antro-lengkap-card {
        background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 20px;
        padding: 24px; margin-bottom: 24px;
    }
    .antro-status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px;
        margin-top: 16px;
    }
    .antro-status-card {
        padding: 16px;
        border-radius: 14px;
        background: var(--bg-main);
        border: 1px solid var(--glass-border);
        min-width: 0;
    }
    .antro-status-label {
        font-size: 11.5px;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 6px;
    }
    .antro-status-z {
        font-size: 19px;
        font-weight: 800;
        margin-bottom: 6px;
    }
    .severity-pill {
        display: block;
        padding: 5px 10px;
        border-radius: 8px;
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1.4;
    }
    .severity-severe, .severity-severe-high { background: rgba(239, 68, 68, 0.15); color: var(--danger); }
    .severity-moderate, .severity-watch { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
    .severity-normal { background: rgba(16, 185, 129, 0.15); color: var(--accent); }
    .severity-high { background: rgba(236, 72, 153, 0.15); color: #ec4899; }
    .severity-unknown { background: rgba(148, 163, 184, 0.15); color: var(--text-muted); }

    .history-antro-badges {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-top: 8px;
        width: 100%;
    }
    .history-antro-badges .severity-pill {
        display: inline-flex;
    }
</style>
@endpush

@section('content')
<div class="detail-main">
    <a href="{{ route('orang-tua.dashboard') }}" class="back-btn">← Kembali ke Dashboard</a>

    {{-- Profil Anak --}}
    <div class="anak-profile-card">
        <div class="profile-avatar">
            {{ $anak->jenis_kelamin === 'L' ? '👦' : '👧' }}
        </div>
        <div style="flex: 1;">
            <div class="profile-name">{{ $anak->nama }}</div>
            <div class="profile-meta">
                <span>{{ $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                <span>🎂 {{ $anak->tanggal_lahir?->translatedFormat('d F Y') }}</span>
                <span>📅 {{ $anak->umur['formatted'] }}</span>
                @if($anak->posyandu)
                <span>🏥 {{ $anak->posyandu->nama }}</span>
                @endif
                @if($anak->nik_anak)
                <span>NIK: {{ $anak->nik_anak }}</span>
                @endif
            </div>
            @if($measurements->last())
            @php
                $lastStatus = $measurements->last()->stunting_category;
                $statusClass = match(true) {
                    str_contains(strtolower($lastStatus ?? ''), 'normal') => 'status-normal-lg',
                    str_contains(strtolower($lastStatus ?? ''), 'berisiko') => 'status-berisiko-lg',
                    str_contains(strtolower($lastStatus ?? ''), 'stunting') => 'status-stunting-lg',
                    default => '',
                };
            @endphp
            <div style="margin-top: 12px;">
                <span class="status-badge-lg {{ $statusClass }}">
                    Status Terkini: {{ $lastStatus ?? 'Belum ada data' }}
                </span>
            </div>
            @endif
        </div>
    </div>

    {{-- Status Gizi Lengkap (Permenkes No. 2/2020) --}}
    @if($measurements->last())
    <div class="antro-lengkap-card">
        <div class="chart-title">🧮 Status Gizi Lengkap Terkini (Permenkes RI No. 2 Tahun 2020)</div>
        @include('antropometri.partials.status-lengkap', ['statusLengkap' => $measurements->last()->antropometriLengkap()])
    </div>
    @endif

    {{-- Grafik Pertumbuhan --}}
    @if($measurements->count() > 0)
    <div class="chart-card">
        <div class="chart-title">📈 Grafik Pertumbuhan</div>
        <div class="chart-tabs">
            <button class="chart-tab active" onclick="showChart('bb', this)">Berat Badan (BB)</button>
            <button class="chart-tab" onclick="showChart('tb', this)">Tinggi Badan (TB)</button>
        </div>
        <canvas id="chart-bb" style="max-height: 300px;"></canvas>
        <canvas id="chart-tb" style="max-height: 300px; display: none;"></canvas>
    </div>
    @endif

    {{-- Riwayat Pemeriksaan --}}
    <div class="history-card">
        <div class="chart-title">📋 Riwayat Pemeriksaan</div>
        @forelse($measurements->sortByDesc('measured_at') as $pem)
        <div class="history-item">
            <div class="history-date">
                📅 {{ \Carbon\Carbon::parse($pem->measured_at)->translatedFormat('d M Y') }}
            </div>
            @if($pem->photo_path || $pem->pose_photo_path)
            <div style="display: flex; gap: 8px;">
                @if($pem->photo_path)
                <a href="{{ Storage::disk('r2')->url($pem->photo_path) }}" target="_blank" style="width: 60px; height: 60px; border-radius: 10px; overflow: hidden; border: 1px solid var(--glass-border); display: block; position: relative;">
                    <img src="{{ Storage::disk('r2')->url($pem->photo_path) }}" alt="Foto Asli" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); color: white; font-size: 9px; text-align: center; padding: 2px 0;">Asli</div>
                </a>
                @endif
                @if($pem->pose_photo_path)
                <a href="{{ Storage::disk('r2')->url($pem->pose_photo_path) }}" target="_blank" style="width: 60px; height: 60px; border-radius: 10px; overflow: hidden; border: 1px solid var(--glass-border); display: block; position: relative;">
                    <img src="{{ Storage::disk('r2')->url($pem->pose_photo_path) }}" alt="Analisa Pose" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(139,92,246,0.8); color: white; font-size: 9px; text-align: center; padding: 2px 0;">Pose</div>
                </a>
                @endif
            </div>
            @endif
            <div class="history-stats">
                <div class="history-stat">
                    <div class="label">Berat Badan</div>
                    <div class="val">{{ $pem->weight_kg }} kg</div>
                </div>
                <div class="history-stat">
                    <div class="label">Tinggi Badan</div>
                    <div class="val">{{ $pem->height_cm }} cm</div>
                </div>
                @if($pem->z_score !== null)
                <div class="history-stat">
                    <div class="label">Z-Score</div>
                    <div class="val">{{ number_format($pem->z_score, 2) }}</div>
                </div>
                @endif
            </div>
            @php
                $s = $pem->stunting_category;
                $sc = match(true) {
                    str_contains(strtolower($s ?? ''), 'normal') => 'status-normal-lg',
                    str_contains(strtolower($s ?? ''), 'berisiko') => 'status-berisiko-lg',
                    str_contains(strtolower($s ?? ''), 'stunting') => 'status-stunting-lg',
                    default => '',
                };
            @endphp
            @if($s)
            <span class="status-badge-sm {{ $sc }}">{{ $s }}</span>
            @endif
            @php $pemAntro = $pem->antropometriLengkap(); @endphp
            @if($pemAntro)
            <div class="history-antro-badges">
                <span class="severity-pill severity-{{ $pemAntro['bb_u']['severity'] }}">BB/U: {{ $pemAntro['bb_u']['label'] }}</span>
                <span class="severity-pill severity-{{ $pemAntro['bb_pb_tb']['severity'] }}">BB/PB-TB: {{ $pemAntro['bb_pb_tb']['label'] }}</span>
                <span class="severity-pill severity-{{ $pemAntro['imt_u']['severity'] }}">IMT/U: {{ $pemAntro['imt_u']['label'] }}</span>
            </div>
            @endif
        </div>
        @empty
        <div style="text-align: center; padding: 32px; color: var(--text-muted);">
            <div style="font-size: 36px; margin-bottom: 8px;">📊</div>
            Belum ada data pemeriksaan
        </div>
        @endforelse
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = @json($measurements->sortBy('measured_at')->pluck('measured_at')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m/Y')));
    const bbData = @json($measurements->sortBy('measured_at')->pluck('weight_kg'));
    const tbData = @json($measurements->sortBy('measured_at')->pluck('height_cm'));

    const chartConfig = (label, data, color) => ({
        type: 'line',
        data: {
            labels,
            datasets: [{
                label,
                data,
                borderColor: color,
                backgroundColor: color + '20',
                fill: true,
                tension: 0.4,
                pointRadius: 6,
                pointHoverRadius: 8,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
            },
            scales: {
                x: { grid: { color: 'rgba(255,255,255,0.05)' } },
                y: { grid: { color: 'rgba(255,255,255,0.05)' } },
            }
        }
    });

    const bbChart = new Chart(document.getElementById('chart-bb'), chartConfig('Berat Badan (kg)', bbData, '#0ea5e9'));
    const tbChart = new Chart(document.getElementById('chart-tb'), chartConfig('Tinggi Badan (cm)', tbData, '#8b5cf6'));

    function showChart(type, btn) {
        document.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById('chart-bb').style.display = type === 'bb' ? 'block' : 'none';
        document.getElementById('chart-tb').style.display = type === 'tb' ? 'block' : 'none';
    }
</script>
@endpush

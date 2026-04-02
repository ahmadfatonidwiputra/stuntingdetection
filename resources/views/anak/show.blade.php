@extends('layouts.landing')

@section('title', 'Detail Anak - AI Stunt Detect')

@push('styles')
<style>
    .main { max-width: 900px; margin: 0 auto; padding: 100px 24px 60px; }
    .card { background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 20px; padding: 28px; margin-bottom: 20px; }
    .card-title { font-size: 16px; font-weight: 700; margin-bottom: 16px; color: var(--text); }
    .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
    .info-item .label { font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
    .info-item .val { font-size: 15px; font-weight: 600; color: var(--text); margin-top: 2px; }
    .history-item { display: flex; gap: 16px; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--glass-border); flex-wrap: wrap; }
    .history-item:last-child { border-bottom: none; }
    .status-badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 100px; font-size: 12px; font-weight: 600; }
    .status-normal { background: rgba(16,185,129,0.15); color: #10b981; }
    .status-stunting { background: rgba(239,68,68,0.15); color: var(--danger); }
    .status-berisiko { background: rgba(245,158,11,0.15); color: var(--warning); }
    .action-row { display: flex; gap: 10px; flex-wrap: wrap; }
    .btn { padding: 9px 20px; border-radius: 10px; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; border: 1px solid var(--glass-border); color: var(--text-secondary); }
    .btn:hover { background: var(--bg-main); }
    .btn-primary { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white !important; border-color: transparent; }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(14,165,233,0.3); }
</style>
@endpush

@section('content')
<div class="main">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px;">
        <a href="{{ route('anak.index') }}" style="color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 600;">← Kembali</a>
        <div class="action-row">
            <a href="{{ route('anak.edit', $anak) }}" class="btn">✏️ Edit Data</a>
            <a href="{{ route('measurements.create') }}?anak_id={{ $anak->id }}" class="btn btn-primary">+ Input Pemeriksaan</a>
        </div>
    </div>

    {{-- Profil --}}
    <div class="card">
        <div style="display: flex; align-items: center; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-size: 36px; flex-shrink: 0;">
                {{ $anak->jenis_kelamin === 'L' ? '👦' : '👧' }}
            </div>
            <div>
                <div style="font-size: 22px; font-weight: 800; margin-bottom: 4px;">{{ $anak->nama }}</div>
                <div style="color: var(--text-muted); font-size: 14px;">{{ $anak->umur['formatted'] }} • {{ $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
            </div>
        </div>
        <div class="info-grid">
            <div class="info-item"><div class="label">NIK Anak</div><div class="val">{{ $anak->nik_anak ?? '-' }}</div></div>
            <div class="info-item"><div class="label">Tanggal Lahir</div><div class="val">{{ $anak->tanggal_lahir?->translatedFormat('d F Y') }}</div></div>
            <div class="info-item"><div class="label">Posyandu</div><div class="val">{{ $anak->posyandu?->nama ?? '-' }}</div></div>
            <div class="info-item"><div class="label">No. KK</div><div class="val">{{ $anak->no_kk ?? '-' }}</div></div>
        </div>
    </div>

    {{-- Data Orang Tua --}}
    <div class="card">
        <div class="card-title">👨‍👩‍👧 Data Orang Tua</div>
        <div class="info-grid">
            <div class="info-item"><div class="label">Nama Ayah</div><div class="val">{{ $anak->nama_ayah ?? '-' }}</div></div>
            <div class="info-item"><div class="label">NIK Ayah</div><div class="val">{{ $anak->nik_ayah ?? '-' }}</div></div>
            <div class="info-item"><div class="label">Nama Ibu</div><div class="val">{{ $anak->nama_ibu ?? '-' }}</div></div>
            <div class="info-item"><div class="label">NIK Ibu</div><div class="val">{{ $anak->nik_ibu ?? '-' }}</div></div>
            <div class="info-item"><div class="label">No. Telepon</div><div class="val">{{ $anak->no_telepon_ortu ?? '-' }}</div></div>
            <div class="info-item"><div class="label">Email</div><div class="val">{{ $anak->email_ortu ?? '-' }}</div></div>
        </div>
        @if($anak->alamat)
        <div class="info-item" style="margin-top: 14px;"><div class="label">Alamat</div><div class="val">{{ $anak->alamat }}</div></div>
        @endif
    </div>

    {{-- Riwayat Pemeriksaan --}}
    <div class="card">
        <div class="card-title">📋 Riwayat Pemeriksaan ({{ $anak->measurements->count() }})</div>
        @forelse($anak->measurements->sortByDesc('measured_at') as $m)
        @php
            $s = $m->stunting_category;
            $sc = match(true) {
                str_contains(strtolower($s ?? ''), 'normal') => 'status-normal',
                str_contains(strtolower($s ?? ''), 'berisiko') => 'status-berisiko',
                str_contains(strtolower($s ?? ''), 'stunting') => 'status-stunting',
                default => '',
            };
        @endphp
        <div class="history-item">
            <div style="font-weight: 700; min-width: 110px; font-size: 13px;">
                {{ \Carbon\Carbon::parse($m->measured_at)->translatedFormat('d M Y') }}
            </div>
            <div style="display: flex; gap: 16px; flex-wrap: wrap; flex: 1; font-size: 13px;">
                <span>BB: <b>{{ $m->weight_kg }} kg</b></span>
                <span>TB: <b>{{ $m->height_cm }} cm</b></span>
                @if($m->z_score !== null)<span>Z: <b>{{ number_format($m->z_score, 2) }}</b></span>@endif
            </div>
            @if($s)<span class="status-badge {{ $sc }}">{{ $s }}</span>@endif
        </div>
        @empty
        <div style="text-align: center; padding: 24px; color: var(--text-muted);">Belum ada pemeriksaan</div>
        @endforelse
    </div>
</div>
@endsection

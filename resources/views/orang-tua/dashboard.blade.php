@extends('layouts.landing')

@section('title', 'Dashboard Orang Tua - AI Stunt Detect')

@push('styles')
<style>
    .ortu-main { max-width: 1100px; margin: 0 auto; padding: 100px 24px 60px; }
    .ortu-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 32px; flex-wrap: wrap; gap: 12px; }
    .ortu-title { font-size: 26px; font-weight: 800; }
    .ortu-subtitle { color: var(--text-muted); font-size: 14px; margin-top: 4px; }
    .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 32px; }
    .stat-card {
        background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 16px;
        padding: 20px 24px; text-align: center;
    }
    .stat-num { font-size: 36px; font-weight: 900; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .stat-label { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
    .section-title { font-size: 18px; font-weight: 700; margin-bottom: 16px; display: flex; align-items: center; gap: 8px; }
    .anak-card {
        background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 16px;
        padding: 24px; margin-bottom: 16px; transition: all 0.3s;
        display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
    }
    .anak-card:hover { border-color: var(--primary); transform: translateY(-2px); box-shadow: 0 8px 30px rgba(14,165,233,0.1); }
    .anak-avatar {
        width: 60px; height: 60px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        display: flex; align-items: center; justify-content: center; font-size: 28px;
    }
    .anak-info { flex: 1; min-width: 200px; }
    .anak-name { font-size: 18px; font-weight: 700; margin-bottom: 4px; }
    .anak-meta { color: var(--text-muted); font-size: 13px; display: flex; flex-wrap: wrap; gap: 8px; }
    .anak-meta span { background: var(--bg-main); padding: 2px 10px; border-radius: 100px; }
    .status-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 6px 14px; border-radius: 100px; font-size: 13px; font-weight: 600;
    }
    .status-normal { background: rgba(16,185,129,0.15); color: #10b981; }
    .status-stunting { background: rgba(239,68,68,0.15); color: var(--danger); }
    .status-berisiko { background: rgba(245,158,11,0.15); color: var(--warning); }
    .status-unknown { background: rgba(107,114,128,0.15); color: var(--text-muted); }
    .detail-btn {
        padding: 10px 20px; background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white; border-radius: 10px; font-size: 13px; font-weight: 600;
        text-decoration: none; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
        transition: all 0.2s;
    }
    .detail-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(14,165,233,0.3); color: white; }
    .empty-state { text-align: center; padding: 48px 24px; color: var(--text-muted); }
    .last-check-card {
        background: linear-gradient(135deg, rgba(14,165,233,0.1), rgba(139,92,246,0.1));
        border: 1px solid rgba(14,165,233,0.2); border-radius: 16px; padding: 20px 24px;
        margin-bottom: 32px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    }
    .nav-pills { display: flex; gap: 8px; flex-wrap: wrap; }
    .nav-pill {
        padding: 8px 16px; border-radius: 100px; font-size: 13px; font-weight: 600;
        text-decoration: none; transition: all 0.2s; border: 1px solid var(--glass-border); color: var(--text-secondary);
    }
    .nav-pill:hover { background: var(--bg-card); }
    .logout-btn {
        padding: 8px 16px; border-radius: 100px; font-size: 13px; font-weight: 600;
        border: 1px solid var(--glass-border); background: none; color: var(--text-secondary);
        cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.2s;
    }
    .logout-btn:hover { background: rgba(239,68,68,0.1); color: var(--danger); border-color: rgba(239,68,68,0.3); }
</style>
@endpush

@section('content')
<div class="ortu-main">
    {{-- Header --}}
    <div class="ortu-header">
        <div>
            <div class="ortu-title">👨‍👩‍👧 Halo, {{ auth()->user()->name }}!</div>
            <div class="ortu-subtitle">
                {{ $profile?->hubungan ? ucfirst($profile->hubungan) : 'Orang Tua' }} •
                Pantau tumbuh kembang anak Anda
            </div>
        </div>
        <div class="nav-pills">
            <a href="{{ route('orang-tua.dashboard') }}" class="nav-pill" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border-color: transparent;">🏠 Dashboard</a>
            <a href="{{ route('profile.edit') }}" class="nav-pill">👤 Profil</a>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" class="logout-btn">Keluar</button>
            </form>
        </div>
    </div>

    {{-- Stats --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-num">{{ $totalAnak }}</div>
            <div class="stat-label">Anak Terdaftar</div>
        </div>
        <div class="stat-card">
            <div class="stat-num">{{ $anakList->sum(fn($a) => $a['last_measurement'] ? 1 : 0) }}</div>
            <div class="stat-label">Anak dengan Data</div>
        </div>
        @if($lastCheck)
        <div class="stat-card" style="grid-column: span 1;">
            <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 4px;">Pemeriksaan Terakhir</div>
            <div style="font-size: 16px; font-weight: 700;">{{ \Carbon\Carbon::parse($lastCheck->measured_at)->translatedFormat('d M Y') }}</div>
        </div>
        @endif
    </div>

    {{-- Last Check Banner --}}
    @if($lastCheck)
    <div class="last-check-card">
        <div style="font-size: 32px;">🏥</div>
        <div>
            <div style="font-size: 13px; color: var(--text-muted);">Pemeriksaan Terakhir</div>
            <div style="font-weight: 700; font-size: 15px;">
                {{ \Carbon\Carbon::parse($lastCheck->measured_at)->translatedFormat('d F Y') }} •
                BB: {{ $lastCheck->weight_kg }} kg | TB: {{ $lastCheck->height_cm }} cm
            </div>
            <div style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
                {{ $lastCheck->posyandu_name ?? '-' }}
            </div>
        </div>
    </div>
    @endif

    {{-- Daftar Anak --}}
    <div class="section-title">👶 Daftar Anak Saya</div>

    @if($anakList->isEmpty())
        <div class="empty-state">
            <div style="font-size: 48px; margin-bottom: 16px;">🔍</div>
            <div style="font-size: 16px; font-weight: 600; margin-bottom: 8px;">Belum ada anak terhubung</div>
            <div style="font-size: 14px;">Akun Anda belum terhubung dengan data anak. Hubungi petugas posyandu Anda.</div>
        </div>
    @else
        @foreach($anakList as $anak)
        <div class="anak-card">
            <div class="anak-avatar">
                {{ $anak['jenis_kelamin'] === 'L' ? '👦' : '👧' }}
            </div>
            <div class="anak-info">
                <div class="anak-name">{{ $anak['nama'] }}</div>
                <div class="anak-meta">
                    <span>{{ $anak['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                    <span>{{ $anak['umur']['formatted'] }}</span>
                    @if($anak['posyandu'])
                    <span>🏥 {{ $anak['posyandu'] }}</span>
                    @endif
                </div>
                @if($anak['last_measurement'])
                <div style="margin-top: 8px; font-size: 13px; color: var(--text-muted);">
                    BB: <b>{{ $anak['last_measurement']->weight_kg }} kg</b> •
                    TB: <b>{{ $anak['last_measurement']->height_cm }} cm</b>
                </div>
                @endif
            </div>
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                @php
                    $statusClass = match(true) {
                        str_contains(strtolower($anak['status']), 'normal') => 'status-normal',
                        str_contains(strtolower($anak['status']), 'berisiko') => 'status-berisiko',
                        str_contains(strtolower($anak['status']), 'stunting') => 'status-stunting',
                        default => 'status-unknown',
                    };
                    $statusIcon = match(true) {
                        str_contains(strtolower($anak['status']), 'normal') => '✅',
                        str_contains(strtolower($anak['status']), 'berisiko') => '⚠️',
                        str_contains(strtolower($anak['status']), 'stunting') => '🔴',
                        default => '📊',
                    };
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $statusIcon }} {{ $anak['status'] }}</span>
                <a href="{{ route('orang-tua.anak.show', $anak['id']) }}" class="detail-btn">
                    Lihat Detail →
                </a>
            </div>
        </div>
        @endforeach
    @endif
</div>
@endsection

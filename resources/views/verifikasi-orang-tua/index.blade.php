@extends('layouts.landing')

@section('title', 'Verifikasi Orang Tua - AI Stunt Detect')

@push('styles')
<style>
    .main { max-width: 900px; margin: 0 auto; padding: 100px 24px 60px; }
    .page-title { font-size: 24px; font-weight: 800; margin-bottom: 8px; }
    .page-sub { color: var(--text-muted); font-size: 14px; margin-bottom: 28px; }
    .request-card {
        background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 16px;
        padding: 24px; margin-bottom: 16px; transition: border-color 0.2s;
    }
    .request-card:hover { border-color: var(--primary); }
    .ortu-header { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
    .ortu-avatar { width: 52px; height: 52px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
    .ortu-name { font-size: 17px; font-weight: 700; }
    .ortu-meta { color: var(--text-muted); font-size: 13px; margin-top: 2px; }
    .info-row { display: flex; flex-wrap: wrap; gap: 8px 24px; margin-bottom: 14px; font-size: 13px; }
    .info-row span { color: var(--text-secondary); }
    .info-row b { color: var(--text); }
    .divider { border: none; border-top: 1px solid var(--glass-border); margin: 14px 0; }
    .anak-linked { font-size: 13px; background: var(--bg-main); border-radius: 10px; padding: 10px 14px; }
    .anak-linked .anak-name { font-weight: 700; color: var(--text); }
    .action-row { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 16px; }
    .btn-approve {
        padding: 9px 22px; background: linear-gradient(135deg, #10b981, #059669);
        color: white; border: none; border-radius: 10px; font-size: 13px; font-weight: 700;
        cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.2s;
    }
    .btn-approve:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(16,185,129,0.3); }
    .btn-reject {
        padding: 9px 22px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3);
        color: var(--danger); border-radius: 10px; font-size: 13px; font-weight: 700;
        cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.2s;
    }
    .btn-reject:hover { background: rgba(239,68,68,0.2); }
    .empty-state { text-align: center; padding: 60px 24px; color: var(--text-muted); background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 16px; }
    .success-alert { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; color: #10b981; font-size: 14px; }
</style>
@endpush

@section('content')
<div class="main">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 4px;">
        <div class="page-title">🔍 Verifikasi Pendaftaran Orang Tua</div>
        <a href="{{ route('dashboard') }}" style="color: var(--primary); font-size: 14px; font-weight: 600; text-decoration: none;">← Dashboard</a>
    </div>
    <div class="page-sub">{{ $pending->count() }} pendaftaran menunggu verifikasi</div>

    @if(session('success'))
    <div class="success-alert">✅ {{ session('success') }}</div>
    @endif

    @if($pending->isEmpty())
    <div class="empty-state">
        <div style="font-size: 48px; margin-bottom: 14px;">✅</div>
        <div style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Semua Sudah Diverifikasi</div>
        <div style="font-size: 14px;">Tidak ada pendaftaran orang tua yang menunggu verifikasi.</div>
    </div>
    @else
        @foreach($pending as $user)
        @php $profile = $user->orangTuaProfile; @endphp
        <div class="request-card">
            <div class="ortu-header">
                <div class="ortu-avatar">👤</div>
                <div>
                    <div class="ortu-name">{{ $user->name }}</div>
                    <div class="ortu-meta">{{ $user->email }} • Daftar: {{ $user->created_at->translatedFormat('d M Y') }}</div>
                </div>
            </div>

            @if($profile)
            <div class="info-row">
                <span>NIK: <b>{{ $profile->nik }}</b></span>
                <span>No. KK: <b>{{ $profile->no_kk }}</b></span>
                <span>Hubungan: <b>{{ ucfirst($profile->hubungan) }}</b></span>
                @if($profile->no_telepon)<span>Tel: <b>{{ $profile->no_telepon }}</b></span>@endif
            </div>

            @if($profile->anakRelations->count())
            <hr class="divider">
            <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 8px;">Mendaftarkan akses ke anak:</div>
            @foreach($profile->anakRelations as $rel)
            @if($rel->anak)
            <div class="anak-linked">
                <div class="anak-name">{{ $rel->anak->nama }}</div>
                <div style="color: var(--text-muted); font-size: 12px; margin-top: 2px;">
                    NIK: {{ $rel->anak->nik_anak ?? '-' }} •
                    Posyandu: {{ $rel->anak->posyandu?->nama ?? '-' }}
                </div>
            </div>
            @endif
            @endforeach
            @endif
            @endif

            <div class="action-row">
                <form method="POST" action="{{ route('verifikasi-orang-tua.approve', $user->id) }}">
                    @csrf
                    <button type="submit" class="btn-approve" onclick="return confirm('Setujui pendaftaran {{ $user->name }}?')">
                        ✓ Setujui
                    </button>
                </form>
                <form method="POST" action="{{ route('verifikasi-orang-tua.reject', $user->id) }}" onsubmit="return confirm('Tolak pendaftaran {{ $user->name }}?')">
                    @csrf
                    <button type="submit" class="btn-reject">✗ Tolak</button>
                </form>
            </div>
        </div>
        @endforeach
    @endif
</div>
@endsection

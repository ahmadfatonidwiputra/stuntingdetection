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
    <div class="page-sub">{{ $pending->count() }} pendaftaran menunggu verifikasi.</div>

    <!-- Filter Pencarian -->
    <div class="request-card" style="margin-bottom: 24px; padding: 16px 20px;">
        <form method="GET" action="{{ route('verifikasi-orang-tua.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
            <div style="flex: 1; min-width: 250px;">
                <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Cari nama akun, nama profil, email, atau NIK..." style="width: 100%; border: 1px solid var(--glass-border); padding: 10px 14px; border-radius: 10px; background: var(--bg-main); color: var(--text);">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-approve" style="padding: 10px 20px;">
                    🔍 Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('verifikasi-orang-tua.index') }}" class="btn-reject" style="padding: 10px 20px; text-decoration: none; color: var(--text-secondary); background: var(--bg-main); border: 1px solid var(--glass-border);">Reset</a>
                @endif
            </div>
        </form>
    </div>

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

    <div style="margin-top: 48px; margin-bottom: 16px;">
        <div class="page-title" style="font-size: 20px;">✅ Sudah Terverifikasi</div>
        <div class="page-sub">Daftar orang tua yang aktif di posyandu Anda</div>
    </div>

    @if($verified->isEmpty())
    <div class="empty-state">
        <div style="font-size: 40px; margin-bottom: 14px;">👥</div>
        <div style="font-size: 16px; font-weight: 600;">Belum ada orang tua terverifikasi.</div>
    </div>
    @else
        @foreach($verified as $user)
        @php $profile = $user->orangTuaProfile; @endphp
        <div class="request-card" style="{{ $user->status === 'suspended' ? 'opacity: 0.7; filter: grayscale(50%);' : '' }}">
            <div class="ortu-header">
                <div class="ortu-avatar" style="{{ $user->status === 'suspended' ? 'background: #9ca3af;' : 'background: #3b82f6;' }}">👤</div>
                <div>
                    <div class="ortu-name">
                        {{ $user->name }}
                        @if($user->status === 'suspended')
                            <span style="font-size: 11px; background: var(--danger); color: white; padding: 2px 6px; border-radius: 4px; margin-left: 6px; vertical-align: middle;">Nonaktif</span>
                        @endif
                    </div>
                    <div class="ortu-meta">{{ $user->email }} • Terverifikasi</div>
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
            <div style="font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 8px;">Anak Terhubung:</div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                @foreach($profile->anakRelations as $rel)
                @if($rel->anak)
                <div class="anak-linked" style="flex: 1; min-width: 200px;">
                    <div class="anak-name">{{ $rel->anak->nama }}</div>
                </div>
                @endif
                @endforeach
            </div>
            @endif
            @endif

            <div class="action-row" style="margin-top: 20px;">
                <a href="{{ route('verifikasi-orang-tua.edit', $user) }}" class="btn-approve" style="background: var(--bg-main); color: var(--text); border: 1px solid var(--glass-border); text-decoration: none;">
                    ✏️ Edit
                </a>
                
                <form method="POST" action="{{ route('verifikasi-orang-tua.suspend', $user) }}">
                    @csrf
                    <button type="submit" class="btn-reject" style="background: rgba(245,158,11,0.1); border-color: rgba(245,158,11,0.3); color: var(--warning);" onclick="return confirm('Yakin ingin {{ $user->status === 'suspended' ? 'mengaktifkan' : 'menonaktifkan' }} akun ini?')">
                        {{ $user->status === 'suspended' ? '▶ Aktifkan' : '⏸ Nonaktifkan' }}
                    </button>
                </form>
                
                <form method="POST" action="{{ route('verifikasi-orang-tua.destroy', $user) }}" onsubmit="return confirm('PERINGATAN: Yakin hapus akun ini SELAMANYA?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-reject" style="margin-left: auto;">🗑 Hapus Selamanya</button>
                </form>
            </div>
        </div>
        @endforeach
    @endif
</div>
@endsection

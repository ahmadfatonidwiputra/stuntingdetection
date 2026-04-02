@extends('layouts.landing')

@section('title', 'Daftar Anak - AI Stunt Detect')

@push('styles')
<style>
    .anak-main { max-width: 1100px; margin: 0 auto; padding: 100px 24px 60px; }
    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
    .page-title { font-size: 24px; font-weight: 800; }
    .btn-primary {
        padding: 10px 22px; background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600;
        text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s;
    }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(14,165,233,0.3); color: white; }
    .search-bar {
        display: flex; gap: 10px; margin-bottom: 20px;
    }
    .search-input {
        flex: 1; padding: 10px 16px;
        background: var(--bg-card); border: 1px solid var(--glass-border);
        border-radius: 10px; color: var(--text); font-size: 14px;
        font-family: 'Inter', sans-serif; outline: none; transition: all 0.2s;
    }
    .search-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,165,233,0.15); }
    .search-btn {
        padding: 10px 20px; background: var(--primary); color: white; border: none;
        border-radius: 10px; font-family: 'Inter', sans-serif; font-size: 14px;
        font-weight: 600; cursor: pointer; transition: all 0.2s;
    }
    .anak-table { width: 100%; border-collapse: collapse; }
    .anak-table th, .anak-table td { padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--glass-border); }
    .anak-table th { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; background: var(--bg-main); }
    .anak-table tr:hover td { background: rgba(14,165,233,0.03); }
    .table-card { background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 16px; overflow: hidden; }
    .status-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 10px; border-radius: 100px; font-size: 12px; font-weight: 600;
    }
    .status-normal { background: rgba(16,185,129,0.15); color: #10b981; }
    .status-stunting { background: rgba(239,68,68,0.15); color: var(--danger); }
    .status-berisiko { background: rgba(245,158,11,0.15); color: var(--warning); }
    .action-btn {
        padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;
        text-decoration: none; border: 1px solid var(--glass-border); color: var(--text-secondary);
        transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px;
    }
    .action-btn:hover { background: var(--bg-main); }
    .action-btn.danger { color: var(--danger); border-color: rgba(239,68,68,0.3); }
    .action-btn.danger:hover { background: rgba(239,68,68,0.1); }
    .empty-state { text-align: center; padding: 48px 24px; color: var(--text-muted); }
    .success-alert {
        background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3);
        border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; color: #10b981; font-size: 14px;
    }
</style>
@endpush

@section('content')
<div class="anak-main">
    <a href="{{ route('dashboard') }}" style="display: inline-flex; align-items: center; gap: 6px; color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 600; margin-bottom: 20px;">← Kembali ke Dashboard</a>

    <div class="page-header">
        <div>
            <div class="page-title">👶 Daftar Anak</div>
            <div style="color: var(--text-muted); font-size: 14px; margin-top: 4px;">Data anak yang terdaftar di posyandu Anda</div>
        </div>
        <a href="{{ route('anak.create') }}" class="btn-primary">+ Tambah Anak</a>
    </div>

    @if(session('success'))
    <div class="success-alert">✅ {{ session('success') }}</div>
    @endif

    <form class="search-bar" method="GET">
        <input type="text" name="search" class="search-input" value="{{ $search }}" placeholder="Cari nama anak, NIK, atau nama orang tua...">
        <button type="submit" class="search-btn">🔍 Cari</button>
        @if($search)
        <a href="{{ route('anak.index') }}" style="padding: 10px 16px; border: 1px solid var(--glass-border); border-radius: 10px; color: var(--text-secondary); text-decoration: none; font-size: 14px;">✕ Reset</a>
        @endif
    </form>

    <div class="table-card">
        <div style="overflow-x: auto;">
            <table class="anak-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Anak</th>
                        <th>Umur</th>
                        <th>JK</th>
                        <th>Orang Tua</th>
                        <th>Posyandu</th>
                        <th>Status Terakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($anak as $a)
                    @php
                        $last = $a->measurements->first();
                        $status = $last?->stunting_category;
                        $sc = match(true) {
                            str_contains(strtolower($status ?? ''), 'normal') => 'status-normal',
                            str_contains(strtolower($status ?? ''), 'berisiko') => 'status-berisiko',
                            str_contains(strtolower($status ?? ''), 'stunting') => 'status-stunting',
                            default => '',
                        };
                    @endphp
                    <tr>
                        <td style="color: var(--text-muted); font-size: 13px;">{{ $anak->firstItem() + $loop->index }}</td>
                        <td>
                            <div style="font-weight: 600;">{{ $a->nama }}</div>
                            @if($a->nik_anak)
                            <div style="font-size: 12px; color: var(--text-muted);">NIK: {{ $a->nik_anak }}</div>
                            @endif
                        </td>
                        <td style="font-size: 13px;">{{ $a->umur['formatted'] }}</td>
                        <td>
                            <span style="font-size: 18px;">{{ $a->jenis_kelamin === 'L' ? '👦' : '👧' }}</span>
                        </td>
                        <td style="font-size: 13px;">
                            @if($a->nama_ayah || $a->nama_ibu)
                                <div>{{ $a->nama_ayah ?? '-' }}</div>
                                <div style="color: var(--text-muted);">{{ $a->nama_ibu ?? '-' }}</div>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td style="font-size: 13px; color: var(--text-secondary);">{{ $a->posyandu?->nama ?? '-' }}</td>
                        <td>
                            @if($status)
                            <span class="status-badge {{ $sc }}">{{ $status }}</span>
                            @else
                            <span style="color: var(--text-muted); font-size: 12px;">Belum ada</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                <a href="{{ route('anak.show', $a) }}" class="action-btn">👁 Detail</a>
                                <a href="{{ route('anak.edit', $a) }}" class="action-btn">✏️ Edit</a>
                                <form method="POST" action="{{ route('anak.destroy', $a) }}" onsubmit="return confirm('Hapus data anak {{ $a->nama }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="action-btn danger" style="border: none; cursor: pointer; font-family: inherit;">🗑 Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state">
                                <div style="font-size: 40px; margin-bottom: 12px;">👶</div>
                                <div style="font-size: 16px; font-weight: 600; margin-bottom: 6px;">Belum ada data anak</div>
                                <div style="font-size: 14px;">
                                    @if($search)
                                        Tidak ada hasil untuk "{{ $search }}"
                                    @else
                                        <a href="{{ route('anak.create') }}" style="color: var(--primary); font-weight: 600;">+ Tambah Anak Pertama</a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($anak->hasPages())
        <div style="padding: 16px 20px; border-top: 1px solid var(--glass-border);">
            {{ $anak->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.landing')

@section('title', 'Master Posyandu - AI Stunt Detect')

@push('styles')
<style>
    .main { max-width: 1000px; margin: 0 auto; padding: 100px 24px 60px; }
    .page-header { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
    .page-title { font-size: 24px; font-weight: 800; }
    .btn-primary { padding: 10px 22px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s; }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(14,165,233,0.3); color: white; }
    .posyandu-card { background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 16px; padding: 20px 24px; margin-bottom: 12px; display: flex; align-items: center; gap: 16px; flex-wrap: wrap; transition: border-color 0.2s; }
    .posyandu-card:hover { border-color: var(--primary); }
    .pos-icon { font-size: 28px; }
    .pos-info { flex: 1; min-width: 200px; }
    .pos-name { font-size: 16px; font-weight: 700; }
    .pos-meta { color: var(--text-muted); font-size: 13px; margin-top: 4px; display: flex; flex-wrap: wrap; gap: 8px; }
    .pos-stats { display: flex; gap: 16px; font-size: 13px; }
    .pos-stat { text-align: center; padding: 6px 12px; background: var(--bg-main); border-radius: 8px; }
    .pos-stat .num { font-weight: 700; font-size: 16px; color: var(--primary); }
    .pos-stat .lbl { color: var(--text-muted); font-size: 11px; }
    .status-active { background: rgba(16,185,129,0.15); color: #10b981; padding: 3px 10px; border-radius: 100px; font-size: 12px; font-weight: 600; }
    .status-inactive { background: rgba(107,114,128,0.15); color: var(--text-muted); padding: 3px 10px; border-radius: 100px; font-size: 12px; font-weight: 600; }
    .action-btn { padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none; border: 1px solid var(--glass-border); color: var(--text-secondary); transition: all 0.2s; display: inline-flex; align-items: center; gap: 4px; }
    .action-btn:hover { background: var(--bg-main); }
    .action-btn.danger { color: var(--danger); border-color: rgba(239,68,68,0.3); }
    .action-btn.danger:hover { background: rgba(239,68,68,0.1); }
    .success-alert { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; color: #10b981; font-size: 14px; }
    .filter-card { background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 16px; padding: 18px 20px; margin-bottom: 20px; }
    .filter-form { display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; }
    .form-group { margin-bottom: 0; flex: 1; min-width: 160px; }
    .form-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; }
    .form-input { width: 100%; padding: 10px 14px; box-sizing: border-box; background: var(--bg-main); border: 1px solid var(--glass-border); border-radius: 10px; color: var(--text); font-size: 14px; font-family: 'Inter', sans-serif; transition: all 0.2s; outline: none; }
    .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,165,233,0.15); }
    .btn-secondary { padding: 10px 18px; background: none; border: 1px solid var(--glass-border); color: var(--text-secondary); border-radius: 10px; font-size: 14px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; }
</style>
@endpush

@section('content')
<div class="main">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
        <a href="{{ route('super-admin.dashboard') }}" style="color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 600;">← Dashboard</a>
    </div>

    <div class="page-header">
        <div>
            <div class="page-title">🏥 Master Data Posyandu</div>
            <div style="color: var(--text-muted); font-size: 14px; margin-top: 4px;">{{ $posyandu->total() }} posyandu terdaftar</div>
        </div>
        <a href="{{ route('super-admin.posyandu.create') }}" class="btn-primary">+ Tambah Posyandu</a>
    </div>

    @include('partials.toast')
    @include('partials.confirm-modal')

    <div class="filter-card">
        <form method="GET" action="{{ route('super-admin.posyandu.index') }}" class="filter-form">
            <div class="form-group" style="flex: 2;">
                <label class="form-label">Cari</label>
                <input type="text" name="search" class="form-input" value="{{ $search }}" placeholder="Nama, kode, kota, atau kecamatan...">
            </div>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-input">
                    <option value="">Semua Status</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-primary" style="border: none; cursor: pointer; font-family: inherit;">🔍 Cari</button>
                @if($search || $status)
                    <a href="{{ route('super-admin.posyandu.index') }}" class="btn-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>

    @forelse($posyandu as $p)
    <div class="posyandu-card">
        <div class="pos-icon">🏥</div>
        <div class="pos-info">
            <div class="pos-name">{{ $p->nama }}</div>
            <div class="pos-meta">
                @if($p->kode_posyandu)<span>{{ $p->kode_posyandu }}</span>@endif
                @if($p->kota)<span>📍 {{ $p->kota }}, {{ $p->provinsi }}</span>@endif
                @if($p->no_telepon)<span>📞 {{ $p->no_telepon }}</span>@endif
            </div>
        </div>
        <div class="pos-stats">
            <div class="pos-stat">
                <div class="num">{{ $p->petugas_count }}</div>
                <div class="lbl">Petugas</div>
            </div>
            <div class="pos-stat">
                <div class="num">{{ $p->anak_count }}</div>
                <div class="lbl">Anak</div>
            </div>
        </div>
        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
            <span class="{{ $p->status === 'active' ? 'status-active' : 'status-inactive' }}">
                {{ $p->status === 'active' ? '✓ Aktif' : '— Nonaktif' }}
            </span>
            <div style="display: flex; gap: 6px;">
                <a href="{{ route('super-admin.posyandu.edit', $p) }}" class="action-btn">✏️ Edit</a>
                <form method="POST" action="{{ route('super-admin.posyandu.destroy', $p) }}" data-confirm="Hapus posyandu {{ $p->nama }}? Tindakan ini tidak dapat dibatalkan.">
                    @csrf @method('DELETE')
                    <button type="submit" class="action-btn danger" style="border: none; cursor: pointer; font-family: inherit;">🗑 Hapus</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div style="text-align: center; padding: 60px; background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 16px; color: var(--text-muted);">
        <div style="font-size: 48px; margin-bottom: 12px;">🏥</div>
        @if($search || $status)
            <div style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Tidak ada posyandu yang cocok</div>
            <p style="margin-bottom: 12px;">Coba ubah kata kunci pencarian atau filter status.</p>
            <a href="{{ route('super-admin.posyandu.index') }}" style="color: var(--primary); font-weight: 600;">Reset Filter</a>
        @else
            <div style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Belum ada posyandu</div>
            <a href="{{ route('super-admin.posyandu.create') }}" style="color: var(--primary); font-weight: 600;">+ Tambah Posyandu Pertama</a>
        @endif
    </div>
    @endforelse

    @if($posyandu->hasPages())
    <div style="margin-top: 16px;">{{ $posyandu->links() }}</div>
    @endif
</div>
@endsection

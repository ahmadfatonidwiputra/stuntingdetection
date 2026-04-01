@extends('layouts.main')

@section('content')
<div class="page-header">
    <div class="flex-between">
        <div>
            <h1 class="page-title">Manajemen Petugas Posyandu</h1>
            <p class="page-subtitle">Kelola petugas posyandu yang terdaftar di sistem</p>
        </div>
    </div>
</div>

<!-- Tab Navigation -->
<div class="glass-card fade-in" style="padding: 0; margin-bottom: 24px;">
    <div style="display: flex; border-bottom: 1px solid var(--border-glass); overflow-x: auto;">
        <a href="{{ route('super-admin.petugas.index', ['tab' => 'pending', 'search' => $search]) }}"
           style="padding: 16px 24px; font-size: 14px; font-weight: 600; text-decoration: none; white-space: nowrap; border-bottom: 2px solid {{ $tab === 'pending' ? 'var(--accent-orange)' : 'transparent' }}; color: {{ $tab === 'pending' ? 'var(--accent-orange)' : 'var(--text-muted)' }}; transition: all 0.2s;">
            ⏳ Pending <span style="background: {{ $tab === 'pending' ? 'rgba(245,158,11,0.15)' : 'var(--bg-glass)' }}; padding: 2px 10px; border-radius: 12px; font-size: 12px; margin-left: 6px;">{{ $counts['pending'] }}</span>
        </a>
        <a href="{{ route('super-admin.petugas.index', ['tab' => 'active', 'search' => $search]) }}"
           style="padding: 16px 24px; font-size: 14px; font-weight: 600; text-decoration: none; white-space: nowrap; border-bottom: 2px solid {{ $tab === 'active' ? 'var(--accent-green)' : 'transparent' }}; color: {{ $tab === 'active' ? 'var(--accent-green)' : 'var(--text-muted)' }}; transition: all 0.2s;">
            ✅ Aktif <span style="background: {{ $tab === 'active' ? 'rgba(16,185,129,0.15)' : 'var(--bg-glass)' }}; padding: 2px 10px; border-radius: 12px; font-size: 12px; margin-left: 6px;">{{ $counts['active'] }}</span>
        </a>
        <a href="{{ route('super-admin.petugas.index', ['tab' => 'rejected', 'search' => $search]) }}"
           style="padding: 16px 24px; font-size: 14px; font-weight: 600; text-decoration: none; white-space: nowrap; border-bottom: 2px solid {{ $tab === 'rejected' ? 'var(--accent-red)' : 'transparent' }}; color: {{ $tab === 'rejected' ? 'var(--accent-red)' : 'var(--text-muted)' }}; transition: all 0.2s;">
            ❌ Ditolak <span style="background: {{ $tab === 'rejected' ? 'rgba(239,68,68,0.15)' : 'var(--bg-glass)' }}; padding: 2px 10px; border-radius: 12px; font-size: 12px; margin-left: 6px;">{{ $counts['rejected'] }}</span>
        </a>
    </div>

    <!-- Search -->
    <div style="padding: 16px 24px;">
        <form method="GET" action="{{ route('super-admin.petugas.index') }}" style="display: flex; gap: 12px;">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ $search }}" class="form-input" placeholder="Cari nama, email, posyandu, atau kota..." style="flex: 1;">
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            @if($search)
                <a href="{{ route('super-admin.petugas.index', ['tab' => $tab]) }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="glass-card fade-in">
    @if($petugas->count() > 0)
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Petugas</th>
                        <th>Posyandu</th>
                        <th>Wilayah</th>
                        <th>Status</th>
                        <th>Tanggal Daftar</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($petugas as $p)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $p->petugasProfile?->nama_lengkap ?? $p->name }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $p->email }}</div>
                        </td>
                        <td>{{ $p->petugasProfile?->posyandu_name ?? '-' }}</td>
                        <td>
                            <div>{{ $p->petugasProfile?->kota ?? '-' }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $p->petugasProfile?->provinsi }}</div>
                        </td>
                        <td>
                            @if($p->status === 'active')
                                <span class="badge badge-normal">Aktif</span>
                            @elseif($p->status === 'pending')
                                <span class="badge badge-stunting">Pending</span>
                            @elseif($p->status === 'rejected')
                                <span class="badge badge-sangat-stunting">Ditolak</span>
                            @elseif($p->status === 'suspended')
                                <span class="badge" style="background: rgba(100,116,139,0.15); color: #94a3b8;">Suspended</span>
                            @endif
                        </td>
                        <td>{{ $p->created_at->format('d M Y') }}</td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                <a href="{{ route('super-admin.petugas.show', $p) }}" class="btn btn-secondary btn-sm">Detail</a>

                                @if($p->status === 'pending')
                                    <form method="POST" action="{{ route('super-admin.petugas.approve', $p) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="background: rgba(16,185,129,0.15); color: var(--accent-green); border: 1px solid rgba(16,185,129,0.3);">Approve</button>
                                    </form>
                                @endif

                                @if($p->status === 'rejected' || $p->status === 'suspended')
                                    <form method="POST" action="{{ route('super-admin.petugas.reactivate', $p) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm" style="background: rgba(16,185,129,0.15); color: var(--accent-green); border: 1px solid rgba(16,185,129,0.3);">Aktifkan</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($petugas->hasPages())
            <div style="margin-top: 24px; display: flex; justify-content: center;">
                {{ $petugas->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <div class="empty-state-icon">
                @if($tab === 'pending') ⏳ @elseif($tab === 'active') ✅ @else ❌ @endif
            </div>
            <h3>Tidak ada data petugas {{ $tab }}</h3>
            <p>{{ $search ? 'Tidak ditemukan hasil untuk pencarian "' . $search . '"' : 'Belum ada petugas dengan status ini.' }}</p>
        </div>
    @endif
</div>
@endsection

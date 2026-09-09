@extends('layouts.main')

@section('content')
<div class="page-header">
    <div class="flex-between">
        <div>
            <h1 class="page-title">Manajemen Posyandu</h1>
            <p class="page-subtitle">Master data posyandu yang terdaftar di sistem</p>
        </div>
        <a href="{{ route('super-admin.posyandu.create') }}" class="btn btn-primary btn-sm">+ Tambah Posyandu</a>
    </div>
</div>

<!-- Filter & Search -->
<div class="glass-card fade-in" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('super-admin.posyandu.index') }}" id="filterPosyandu" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 2; min-width: 220px;">
            <label class="form-label">Cari</label>
            <input type="text" name="search" value="{{ $search }}" class="form-input" placeholder="Nama posyandu, kode, kota, kecamatan, atau desa...">
        </div>
        @include('super-admin.partials.filter-wilayah')
        <div style="flex: 1; min-width: 150px;">
            <label class="form-label">Status</label>
            <select name="status" class="form-input">
                <option value="">Semua Status</option>
                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
            @if($search || $status || $kecamatan || $kelurahan)
                <a href="{{ route('super-admin.posyandu.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Quick status chips -->
<div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px;">
    @foreach(['active' => 'Aktif', 'inactive' => 'Nonaktif'] as $key => $label)
        @php
            $isActive = $status === $key;
        @endphp
        <a href="{{ route('super-admin.posyandu.index', array_filter(['status' => $isActive ? null : $key, 'search' => $search, 'kecamatan' => $kecamatan, 'kelurahan' => $kelurahan])) }}"
           class="badge {{ $key === 'active' ? 'badge-normal' : '' }}"
           style="{{ $isActive ? 'outline: 2px solid currentColor;' : '' }} {{ $key === 'active' ? '' : 'background: rgba(100,116,139,0.15); color: #94a3b8;' }} text-decoration: none; cursor: pointer;">
            {{ $label }}: {{ $statusCounts[$key] }}
        </a>
    @endforeach
</div>

<!-- Data Table -->
<div class="glass-card fade-in">
    @if($posyandu->count() > 0)
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Posyandu</th>
                        <th>Kecamatan</th>
                        <th>Desa / Kelurahan</th>
                        <th>Kota / Kabupaten</th>
                        <th style="white-space: nowrap;">Petugas / Anak</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posyandu as $p)
                    <tr>
                        <td style="font-weight: 600;">
                            {{ $p->nama }}
                            {{-- Kode & telepon jadi baris kecil di sini supaya tabel tidak kelebaran. --}}
                            @if($p->kode_posyandu || $p->no_telepon)
                                <div style="font-weight: 400; font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                                    {{ collect([$p->kode_posyandu, $p->no_telepon])->filter()->implode(' · ') }}
                                </div>
                            @endif
                        </td>
                        <td style="font-size: 13px;">{{ $p->kecamatan ?? '-' }}</td>
                        <td style="font-size: 13px;">{{ $p->kelurahan ?? '-' }}</td>
                        <td style="font-size: 13px; color: var(--text-secondary);">{{ $p->kota ?? '-' }}</td>
                        <td style="font-weight: 700; white-space: nowrap;">
                            {{ $p->petugas_count }}
                            <span style="font-weight: 400; color: var(--text-muted);">/</span>
                            {{ $p->anak_count }}
                        </td>
                        <td>
                            @if($p->status === 'active')
                                <span class="badge badge-normal">Aktif</span>
                            @else
                                <span class="badge" style="background: rgba(100,116,139,0.15); color: #94a3b8;">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="{{ route('super-admin.posyandu.edit', $p) }}" class="btn btn-secondary btn-sm" style="white-space: nowrap;">Edit</a>
                                <form method="POST" action="{{ route('super-admin.posyandu.destroy', $p) }}" data-confirm="Hapus posyandu {{ $p->nama }}? Tindakan ini tidak dapat dibatalkan.">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" style="white-space: nowrap;">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="margin-top: 20px; display: flex; flex-direction: column; align-items: center; gap: 10px;">
            <div style="font-size: 13px; color: var(--text-muted);">
                Menampilkan {{ $posyandu->firstItem() ?? 0 }}–{{ $posyandu->lastItem() ?? 0 }} dari {{ $posyandu->total() }} posyandu
            </div>
            {{ $posyandu->links() }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">🏥</div>
            @if($search || $status || $kecamatan || $kelurahan)
                <h3>Tidak ada posyandu yang cocok</h3>
                <p>Coba ubah kata kunci pencarian atau filter wilayah yang dipilih.</p>
                <a href="{{ route('super-admin.posyandu.index') }}" class="btn btn-secondary btn-sm" style="margin-top: 12px;">Reset Filter</a>
            @else
                <h3>Belum ada posyandu</h3>
                <p>Tambahkan posyandu pertama untuk mulai mengelola data.</p>
                <a href="{{ route('super-admin.posyandu.create') }}" class="btn btn-primary btn-sm" style="margin-top: 12px;">+ Tambah Posyandu</a>
            @endif
        </div>
    @endif
</div>
@endsection

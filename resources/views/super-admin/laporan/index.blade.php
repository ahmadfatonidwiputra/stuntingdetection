@extends('layouts.main')

@section('content')
<div class="page-header">
    <h1 class="page-title">Manajemen Laporan</h1>
    <p class="page-subtitle">Lihat dan unduh data pengukuran per posyandu</p>
</div>

<!-- Search -->
<div class="glass-card fade-in" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('super-admin.laporan.index') }}" style="display: flex; gap: 12px;">
        <input type="text" name="search" value="{{ $search }}" class="form-input" placeholder="Cari nama posyandu..." style="flex: 1;">
        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        @if($search)
            <a href="{{ route('super-admin.laporan.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        @endif
    </form>
</div>

<!-- Data Table -->
<div class="glass-card fade-in">
    @if($posyanduList->count() > 0)
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Posyandu</th>
                        <th>Wilayah</th>
                        <th style="text-align: center;">Jumlah Anak</th>
                        <th style="text-align: center;">Jumlah Pengukuran</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posyanduList as $p)
                    <tr>
                        <td>
                            <a href="{{ route('super-admin.laporan.show', $p) }}" style="font-weight: 600; color: var(--text); text-decoration: none;">
                                🏥 {{ $p->nama }}
                            </a>
                            @if($p->kode_posyandu)
                                <div style="font-size: 12px; color: var(--text-muted);">{{ $p->kode_posyandu }}</div>
                            @endif
                        </td>
                        <td>
                            <div>{{ $p->kota ?? '-' }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $p->provinsi }}</div>
                        </td>
                        <td style="text-align: center;">{{ $p->anak_count }}</td>
                        <td style="text-align: center;">{{ $p->measurements_count }}</td>
                        <td style="text-align: right;">
                            <a href="{{ route('super-admin.laporan.show', $p) }}" class="btn btn-secondary btn-sm">Lihat Data</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($posyanduList->hasPages())
            <div style="margin-top: 24px; display: flex; justify-content: center;">
                {{ $posyanduList->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <div class="empty-state-icon">📊</div>
            <h3>Tidak ada data posyandu</h3>
            <p>{{ $search ? 'Tidak ditemukan hasil untuk pencarian "' . $search . '"' : 'Belum ada posyandu terdaftar.' }}</p>
        </div>
    @endif
</div>
@endsection

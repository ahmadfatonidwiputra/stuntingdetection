@extends('layouts.main')

@section('content')
<div class="page-header">
    <div class="flex-between">
        <div>
            <h1 class="page-title">Data Anak</h1>
            <p class="page-subtitle">Seluruh anak yang terdaftar di semua posyandu</p>
        </div>
    </div>
</div>

<!-- Filter & Search -->
<div class="glass-card fade-in" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('super-admin.anak.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 2; min-width: 220px;">
            <label class="form-label">Cari</label>
            <input type="text" name="search" value="{{ $search }}" class="form-input" placeholder="Nama anak, NIK, atau nama orang tua...">
        </div>
        <div style="flex: 1; min-width: 160px;">
            <label class="form-label">Status Gizi</label>
            <select name="status" class="form-input">
                <option value="">Semua Status</option>
                @foreach(['Normal', 'Stunting', 'Sangat Stunting', 'Belum Diukur'] as $s)
                    <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex: 1; min-width: 160px;">
            <label class="form-label">Posyandu</label>
            <select name="posyandu_id" class="form-input">
                <option value="">Semua Posyandu</option>
                @foreach($posyanduList as $p)
                    <option value="{{ $p->id }}" {{ (string) $posyanduId === (string) $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex: 1; min-width: 140px;">
            <label class="form-label">Jenis Kelamin</label>
            <select name="jenis_kelamin" class="form-input">
                <option value="">Semua</option>
                <option value="L" {{ $jenisKelamin === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ $jenisKelamin === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
            @if($search || $status || $posyanduId || $jenisKelamin)
                <a href="{{ route('super-admin.anak.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Quick status chips -->
<div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px;">
    @foreach($statusCounts as $label => $count)
        @php
            $active = $status === $label;
            $sc = match($label) {
                'Normal' => 'badge-normal',
                'Stunting' => 'badge-stunting',
                'Sangat Stunting' => 'badge-sangat-stunting',
                default => '',
            };
        @endphp
        <a href="{{ route('super-admin.anak.index', array_filter(['status' => $active ? null : $label, 'search' => $search, 'posyandu_id' => $posyanduId, 'jenis_kelamin' => $jenisKelamin])) }}"
           class="badge {{ $sc }}" style="{{ $active ? 'outline: 2px solid currentColor;' : '' }} {{ $sc ? '' : 'background: rgba(100,116,139,0.15); color: #94a3b8;' }} text-decoration: none; cursor: pointer;">
            {{ $label }}: {{ $count }}
        </a>
    @endforeach
</div>

<!-- Data Table -->
<div class="glass-card fade-in">
    @if($anak->count() > 0)
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama Anak</th>
                        <th>NIK</th>
                        <th>Umur / JK</th>
                        <th>Orang Tua</th>
                        <th>Posyandu</th>
                        <th>Status Gizi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($anak as $a)
                    @php
                        $cat = $a->latestMeasurement?->stunting_category;
                        $sc = match($cat) {
                            'Normal' => 'badge-normal',
                            'Stunting' => 'badge-stunting',
                            'Sangat Stunting' => 'badge-sangat-stunting',
                            default => '',
                        };
                    @endphp
                    <tr>
                        <td style="font-weight: 600;">{{ $a->nama }}</td>
                        <td style="font-size: 13px; color: var(--text-secondary);">{{ $a->nik_anak ?? '-' }}</td>
                        <td style="font-size: 13px;">
                            {{ $a->umur['formatted'] }}
                            <div style="color: var(--text-muted);">{{ $a->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                        </td>
                        <td style="font-size: 13px;">
                            <div>{{ $a->nama_ayah ?? '-' }}</div>
                            <div style="color: var(--text-muted);">{{ $a->nama_ibu ?? '-' }}</div>
                        </td>
                        <td style="font-size: 13px; color: var(--text-secondary);">{{ $a->posyandu?->nama ?? '-' }}</td>
                        <td>
                            @if($cat)
                                <span class="badge {{ $sc }}">{{ $cat }}</span>
                            @else
                                <span class="badge" style="background: rgba(100,116,139,0.15); color: #94a3b8;">Belum Diukur</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($anak->hasPages())
            <div style="margin-top: 24px; display: flex; justify-content: center;">
                {{ $anak->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <div class="empty-state-icon">👶</div>
            <h3>Tidak ada data anak</h3>
            <p>{{ $search || $status || $posyanduId || $jenisKelamin ? 'Tidak ditemukan hasil untuk filter yang dipilih.' : 'Belum ada anak yang terdaftar di sistem.' }}</p>
        </div>
    @endif
</div>
@endsection

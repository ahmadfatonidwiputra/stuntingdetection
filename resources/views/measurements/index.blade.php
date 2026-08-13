@extends('layouts.main')

@section('content')
<div class="page-header flex-between">
    <div>
        <h1 class="page-title">Riwayat Pengukuran</h1>
        <p class="page-subtitle">Kelompok perkembangan anak berdasarkan Nama dan NIK di posyandu Anda</p>
    </div>
    <a href="{{ route('measurements.create') }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Pengukuran Baru
    </a>
</div>

<!-- Filter -->
<div class="glass-card fade-in" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('measurements.index') }}" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="margin-bottom: 0; flex: 2; min-width: 200px;">
            <label class="form-label">Cari Nama / NIK</label>
            <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Cari nama atau NIK anak...">
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="from" class="form-input" value="{{ request('from') }}">
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="to" class="form-input" value="{{ request('to') }}">
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 170px;">
            <label class="form-label">Kategori Stunting</label>
            <select name="stunting_category" class="form-input">
                <option value="">Semua Kategori</option>
                <option value="Normal" {{ request('stunting_category') === 'Normal' ? 'selected' : '' }}>Normal</option>
                <option value="Stunting" {{ request('stunting_category') === 'Stunting' ? 'selected' : '' }}>Stunting</option>
                <option value="Sangat Stunting" {{ request('stunting_category') === 'Sangat Stunting' ? 'selected' : '' }}>Sangat Stunting</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
            <label class="form-label">Jenis Kelamin</label>
            <select name="gender" class="form-input">
                <option value="">Semua</option>
                <option value="L" {{ request('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ request('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Filter
            </button>
            @if(request('from') || request('to') || request('search') || request('stunting_category') || request('gender'))
                <a href="{{ route('measurements.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Measurements Table -->
<div class="glass-card fade-in">
    @if($anakList->count() > 0)
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Anak</th>
                        <th>Jumlah Pengukuran</th>
                        <th>Pengukuran Terakhir</th>
                        <th>Status Terakhir</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($anakList as $index => $anak)
                    @php
                        $latestMeasurement = $hasDateFilter ? $anak->measurements->first() : $anak->latestMeasurement;
                        $photoMeasurement = $hasDateFilter
                            ? $anak->measurements->first(fn ($measurement) => filled($measurement->photo_path))
                            : $anak->latestPhotoMeasurement;
                    @endphp
                    <tr>
                        <td style="color: var(--text-muted);">{{ $anakList->firstItem() + $index }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                @if($photoMeasurement?->photo_path)
                                    <div style="width: 52px; height: 52px; border-radius: 14px; overflow: hidden; background: var(--bg-glass); border: 1px solid var(--border-glass); flex-shrink: 0;">
                                        <img src="{{ Storage::disk('r2')->url($photoMeasurement->photo_path) }}" alt="Foto {{ $anak->nama }}" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                @else
                                    <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.14), rgba(124, 58, 237, 0.14)); border: 1px dashed var(--border-glass); color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                                        👶
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight: 700;">{{ $anak->nama }}</div>
                                    <div style="font-size: 12px; color: var(--text-muted);">NIK: {{ $anak->nik_anak ?: '-' }}</div>
                                    @if($photoMeasurement?->photo_path)
                                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                            Foto dari pengukuran {{ $photoMeasurement->measured_at->format('d M Y') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>{{ $anak->filtered_measurements_count }}</strong>
                            <span style="color: var(--text-muted); font-size: 12px;">catatan</span>
                        </td>
                        <td>
                            @if($latestMeasurement)
                                <div>{{ $latestMeasurement->measured_at->format('d M Y') }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">
                                    TB {{ number_format($latestMeasurement->height_cm, 2) }} cm • BB {{ number_format($latestMeasurement->weight_kg, 2) }} kg
                                </div>
                            @else
                                <span style="color: var(--text-muted); font-size: 12px;">-</span>
                            @endif
                        </td>
                        <td>
                            @if($latestMeasurement)
                                <span class="badge badge-{{ strtolower(str_replace(' ', '-', $latestMeasurement->stunting_category)) }}">
                                    {{ $latestMeasurement->stunting_category }}
                                </span>
                            @else
                                <span style="color: var(--text-muted); font-size: 12px;">-</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="{{ route('measurements.anak.show', $anak) }}" class="btn btn-secondary btn-sm">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    Lihat Perkembangan
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            {{ $anakList->links('vendor.pagination.custom') }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">👶</div>
            <h3>Belum ada riwayat per anak</h3>
            <p>{{ request('from') || request('to') || request('stunting_category') || request('gender') ? 'Tidak ada pengukuran anak yang cocok dengan filter yang dipilih.' : 'Mulai catat pengukuran anak untuk melihat perkembangan mereka di sini.' }}</p>
            <a href="{{ route('measurements.create') }}" class="btn btn-primary">Mulai Pengukuran</a>
        </div>
    @endif
</div>
@endsection

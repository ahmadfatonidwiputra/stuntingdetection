@extends('layouts.main')

@section('content')
<div class="page-header flex-between">
    <div>
        <h1 class="page-title">Riwayat Pengukuran</h1>
        <p class="page-subtitle">Semua catatan pengukuran berat dan tinggi badan Anda</p>
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
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="from" class="form-input" value="{{ request('from') }}">
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="to" class="form-input" value="{{ request('to') }}">
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Filter
            </button>
            @if(request('from') || request('to'))
                <a href="{{ route('measurements.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Measurements Table -->
<div class="glass-card fade-in">
    @if($measurements->count() > 0)
        <div style="overflow-x: auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Tinggi</th>
                        <th>Berat</th>
                        <th>BMI</th>
                        <th>Status</th>
                        <th>Foto</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($measurements as $index => $m)
                    <tr>
                        <td style="color: var(--text-muted);">{{ $measurements->firstItem() + $index }}</td>
                        <td>
                            <div>{{ $m->measured_at->format('d M Y') }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $m->measured_at->format('H:i') }}</div>
                        </td>
                        <td><strong>{{ number_format($m->height_cm, 1) }}</strong> <span style="color: var(--text-muted);">cm</span></td>
                        <td><strong>{{ number_format($m->weight_kg, 1) }}</strong> <span style="color: var(--text-muted);">kg</span></td>
                        <td><strong>{{ number_format($m->bmi, 1) }}</strong></td>
                        <td>
                            <span class="badge badge-{{ strtolower($m->bmi_category) }}">
                                {{ $m->bmi_category }}
                            </span>
                        </td>
                        <td>
                            @if($m->photo_path)
                                <div style="width: 40px; height: 40px; border-radius: 8px; overflow: hidden; background: var(--bg-glass);">
                                    <img src="{{ asset('storage/' . $m->photo_path) }}" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            @else
                                <span style="color: var(--text-muted); font-size: 12px;">-</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="{{ route('measurements.show', $m) }}" class="btn btn-secondary btn-sm">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    Detail
                                </a>
                                <form method="POST" action="{{ route('measurements.destroy', $m) }}" onsubmit="return confirm('Hapus pengukuran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            {{ $measurements->withQueryString()->links('vendor.pagination.custom') }}
        </div>
    @else
        <div class="empty-state">
            <div class="empty-state-icon">📋</div>
            <h3>Belum ada catatan pengukuran</h3>
            <p>Mulai catat pengukuran berat dan tinggi badan Anda.</p>
            <a href="{{ route('measurements.create') }}" class="btn btn-primary">Mulai Pengukuran</a>
        </div>
    @endif
</div>
@endsection

@extends('layouts.main')

@section('content')
<div class="page-header">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
        <a href="{{ route('measurements.index') }}" class="btn btn-secondary btn-sm" style="padding: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
        </a>
        <h1 class="page-title">Detail Pengukuran</h1>
    </div>
    <p class="page-subtitle">{{ $measurement->measured_at->format('d F Y, H:i') }}</p>
</div>

<div class="detail-grid">
    <!-- Photo -->
    <div class="glass-card fade-in">
        <div class="chart-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-blue)" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
            </svg>
            Foto Pengukuran
        </div>
        @if($measurement->photo_path)
            <div class="detail-photo">
                <img src="{{ asset('storage/' . $measurement->photo_path) }}" alt="Foto pengukuran">
            </div>
        @else
            <div class="empty-state" style="padding: 40px;">
                <div class="empty-state-icon">📷</div>
                <p>Tidak ada foto</p>
            </div>
        @endif
    </div>

    <!-- Details -->
    <div class="glass-card fade-in">
        <div class="chart-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-green)" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            Informasi Pengukuran
        </div>

        <div class="detail-info">
            <div class="measurement-result" style="margin-top: 0;">
                <div class="result-card">
                    <div class="result-value">{{ number_format($measurement->height_cm, 1) }}</div>
                    <div class="result-unit">Tinggi (cm)</div>
                </div>
                <div class="result-card">
                    <div class="result-value" style="background: var(--gradient-2); -webkit-background-clip: text; background-clip: text;">{{ number_format($measurement->weight_kg, 1) }}</div>
                    <div class="result-unit">Berat (kg)</div>
                </div>
            </div>

            <div style="text-align: center; padding: 20px; background: var(--bg-glass); border-radius: var(--radius-sm); border: 1px solid var(--border-glass);">
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Body Mass Index (BMI)</div>
                <div style="font-size: 42px; font-weight: 800; background: var(--gradient-3); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
                    {{ number_format($measurement->bmi, 1) }}
                </div>
                <span class="badge badge-{{ strtolower($measurement->bmi_category) }}" style="margin-top: 8px; font-size: 14px; padding: 6px 16px;">
                    {{ $measurement->bmi_category }}
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-row-label">Tanggal Pengukuran</span>
                <span class="detail-row-value">{{ $measurement->measured_at->format('d F Y') }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-row-label">Waktu</span>
                <span class="detail-row-value">{{ $measurement->measured_at->format('H:i') }} WIB</span>
            </div>

            @if($measurement->notes)
            <div>
                <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 8px; font-weight: 600;">Catatan:</div>
                <div style="padding: 14px; background: var(--bg-glass); border-radius: var(--radius-sm); border: 1px solid var(--border-glass); font-size: 14px; line-height: 1.6;">
                    {{ $measurement->notes }}
                </div>
            </div>
            @endif

            <!-- BMI Info -->
            <div style="padding: 16px; border-radius: var(--radius-sm); background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.15);">
                <div style="font-size: 12px; color: var(--accent-blue); font-weight: 600; margin-bottom: 8px;">Kategori BMI:</div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap; font-size: 11px;">
                    <span class="badge badge-kurus">&lt; 18.5 Kurus</span>
                    <span class="badge badge-normal">18.5 - 24.9 Normal</span>
                    <span class="badge badge-gemuk">25 - 29.9 Gemuk</span>
                    <span class="badge badge-obesitas">&ge; 30 Obesitas</span>
                </div>
            </div>

            <div style="display: flex; gap: 12px;">
                <a href="{{ route('measurements.index') }}" class="btn btn-secondary" style="flex: 1; justify-content: center;">
                    Kembali
                </a>
                <form method="POST" action="{{ route('measurements.destroy', $measurement) }}" onsubmit="return confirm('Yakin hapus pengukuran ini?')" style="flex: 1;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" style="width: 100%; justify-content: center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                        </svg>
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

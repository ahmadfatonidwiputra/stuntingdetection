@extends('layouts.main')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Laporan Pengukuran</h1>
        <p class="page-subtitle">Download laporan data pengukuran posyandu <strong style="color: var(--accent-blue);">{{ $posyanduName }}</strong></p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

    {{-- Laporan Bulanan --}}
    <div class="glass-card fade-in">
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 24px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(59, 130, 246, 0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent-blue)" stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div>
                <h2 style="font-size: 17px; font-weight: 700; margin-bottom: 2px;">Laporan Per Bulan</h2>
                <p style="font-size: 13px; color: var(--text-muted);">Download data pengukuran dalam satu bulan</p>
            </div>
        </div>

        <form action="{{ route('laporan.download.bulanan') }}" method="GET">
            <div class="form-group">
                <label class="form-label">Bulan</label>
                <select name="bulan" class="form-input" required>
                    @php
                        $bulanList = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
                            4 => 'April', 5 => 'Mei', 6 => 'Juni',
                            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
                            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                        ];
                    @endphp
                    @foreach($bulanList as $num => $nama)
                        <option value="{{ $num }}" {{ $num == now()->month ? 'selected' : '' }}>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Tahun</label>
                <select name="tahun" class="form-input" required>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Download Laporan Bulanan
            </button>
        </form>
    </div>

    {{-- Laporan Tahunan --}}
    <div class="glass-card fade-in">
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 24px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="var(--accent-green)" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                    <line x1="16" y1="13" x2="8" y2="13"/>
                    <line x1="16" y1="17" x2="8" y2="17"/>
                    <polyline points="10 9 9 9 8 9"/>
                </svg>
            </div>
            <div>
                <h2 style="font-size: 17px; font-weight: 700; margin-bottom: 2px;">Laporan Per Tahun</h2>
                <p style="font-size: 13px; color: var(--text-muted);">Download seluruh data pengukuran dalam satu tahun</p>
            </div>
        </div>

        <form action="{{ route('laporan.download.tahunan') }}" method="GET">
            <div class="form-group">
                <label class="form-label">Tahun</label>
                <select name="tahun" class="form-input" required>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ $year == $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div style="background: rgba(16, 185, 129, 0.06); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 13px; color: var(--text-secondary);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--accent-green)" stroke-width="2" style="display: inline; margin-right: 6px; vertical-align: middle;">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Laporan tahunan mencakup semua data pengukuran dari Januari hingga Desember tahun yang dipilih.
            </div>

            <button type="submit" class="btn" style="width: 100%; background: linear-gradient(135deg, #10b981, #3b82f6); color: white; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Download Laporan Tahunan
            </button>
        </form>
    </div>

</div>

{{-- Info Card --}}
<div class="glass-card fade-in" style="margin-top: 24px;">
    <div style="display: flex; align-items: flex-start; gap: 14px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(245, 158, 11, 0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent-orange)" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div>
            <h3 style="font-size: 15px; font-weight: 600; margin-bottom: 8px;">Informasi Laporan</h3>
            <ul style="font-size: 13px; color: var(--text-muted); line-height: 1.8; list-style: none; padding: 0;">
                <li>• File laporan berformat <strong style="color: var(--text-secondary);">CSV</strong> dan dapat dibuka dengan Microsoft Excel atau Google Sheets.</li>
                <li>• Laporan hanya mencakup data pengukuran di <strong style="color: var(--accent-blue);">{{ $posyanduName }}</strong>.</li>
                <li>• Data mencakup: nama anak, NIK, tanggal lahir, hasil pengukuran TB/BB, Z-Score, dan kategori stunting.</li>
                <li>• Laporan dilengkapi ringkasan distribusi kategori stunting (Normal, Stunting, Sangat Stunting).</li>
            </ul>
        </div>
    </div>
</div>

@endsection

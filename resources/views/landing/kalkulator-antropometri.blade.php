@extends('layouts.landing')

@section('title', 'Kalkulator Antropometri Anak - AI Stunt Detect')

@push('styles')
<style>
    .kalk-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .kalk-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .kalk-field label {
        font-size: 13px;
        font-weight: 600;
        color: var(--text-secondary);
    }

    .kalk-field input,
    .kalk-field select {
        padding: 13px 16px;
        border-radius: 10px;
        border: 1px solid var(--glass-border);
        background: var(--bg-card);
        color: var(--text);
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        outline: none;
        transition: all 0.2s ease;
    }

    .kalk-field input:focus,
    .kalk-field select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.15);
    }

    .kalk-gender-toggle {
        display: flex;
        gap: 10px;
    }

    .kalk-gender-toggle label {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 13px 16px;
        border-radius: 10px;
        border: 1px solid var(--glass-border);
        background: var(--bg-card);
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: var(--text-secondary);
        transition: all 0.2s ease;
    }

    .kalk-gender-toggle input {
        display: none;
    }

    .kalk-gender-toggle input:checked + span {
        color: var(--primary);
    }

    .kalk-gender-toggle label:has(input:checked) {
        border-color: var(--primary);
        background: rgba(14, 165, 233, 0.1);
        color: var(--primary);
    }

    .kalk-error {
        color: var(--danger);
        font-size: 12px;
        margin-top: -2px;
    }

    .kalk-status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 18px;
        margin-top: 8px;
    }

    .kalk-status-card {
        padding: 20px;
        border-radius: 14px;
        min-width: 0;
    }

    .kalk-status-label {
        font-size: 12.5px;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 8px;
    }

    .kalk-status-z {
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 10px;
    }

    .severity-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: normal;
        text-align: left;
    }

    .severity-severe, .severity-severe-high { background: rgba(239, 68, 68, 0.15); color: var(--danger); }
    .severity-moderate, .severity-watch { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
    .severity-normal { background: rgba(16, 185, 129, 0.15); color: var(--accent); }
    .severity-high { background: rgba(236, 72, 153, 0.15); color: #ec4899; }
    .severity-unknown { background: var(--glass-hover); color: var(--text-muted); }

    .kalk-kategori-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }

    .kalk-kategori-table th {
        text-align: left;
        padding: 10px 14px;
        color: var(--text-muted);
        font-size: 11.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 1px solid var(--glass-border);
    }

    .kalk-kategori-table td {
        padding: 12px 14px;
        border-bottom: 1px solid var(--glass-border);
        color: var(--text-secondary);
    }

    .kalk-kategori-table tr:last-child td {
        border-bottom: none;
    }

    .kalk-note {
        font-size: 13px;
        color: var(--text-muted);
        line-height: 1.7;
        padding: 16px 20px;
        border-radius: 12px;
        background: var(--bg-card);
        border: 1px solid var(--glass-border);
        margin-top: 24px;
    }

    @media (max-width: 640px) {
        .kalk-form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<section style="padding-top: 120px;" class="section">
    <div class="section-header fade-up">
        <div class="section-badge">🧮 Kalkulator Gratis</div>
        <h1 class="section-title">Kalkulator Antropometri Anak</h1>
        <p class="section-subtitle">
            Cek status gizi anak Anda (BB/U, PB/U atau TB/U, BB/PB atau BB/TB, dan IMT/U) secara instan berdasarkan
            Standar Antropometri Anak &mdash; Peraturan Menteri Kesehatan RI No. 2 Tahun 2020. Tidak perlu mendaftar,
            data Anda tidak disimpan.
        </p>
    </div>

    <div class="glass fade-up" style="padding: 36px; max-width: 760px; margin: 0 auto 40px;">
        <form method="GET" action="{{ route('kalkulator-antropometri') }}">
            <input type="hidden" name="hitung" value="1">

            <div class="kalk-field" style="margin-bottom: 20px;">
                <label>Jenis Kelamin</label>
                <div class="kalk-gender-toggle">
                    <label>
                        <input type="radio" name="jenis_kelamin" value="L" {{ old('jenis_kelamin', $old['jenis_kelamin'] ?? '') === 'L' ? 'checked' : '' }}>
                        <span>👦 Laki-laki</span>
                    </label>
                    <label>
                        <input type="radio" name="jenis_kelamin" value="P" {{ old('jenis_kelamin', $old['jenis_kelamin'] ?? '') === 'P' ? 'checked' : '' }}>
                        <span>👧 Perempuan</span>
                    </label>
                </div>
                @error('jenis_kelamin')<div class="kalk-error">{{ $message }}</div>@enderror
            </div>

            <div class="kalk-form-grid" style="margin-bottom: 20px;">
                <div class="kalk-field">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $old['tanggal_lahir'] ?? '') }}" max="{{ now()->format('Y-m-d') }}" required>
                    @error('tanggal_lahir')<div class="kalk-error">{{ $message }}</div>@enderror
                </div>
                <div class="kalk-field">
                    <label>Tanggal Pengukuran (opsional)</label>
                    <input type="date" name="tanggal_ukur" value="{{ old('tanggal_ukur', $old['tanggal_ukur'] ?? '') }}" max="{{ now()->format('Y-m-d') }}" placeholder="Default: hari ini">
                    @error('tanggal_ukur')<div class="kalk-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="kalk-form-grid" style="margin-bottom: 28px;">
                <div class="kalk-field">
                    <label>Berat Badan (kg)</label>
                    <input type="number" step="0.1" min="1" max="50" name="berat_kg" value="{{ old('berat_kg', $old['berat_kg'] ?? '') }}" placeholder="Contoh: 9.5" required>
                    @error('berat_kg')<div class="kalk-error">{{ $message }}</div>@enderror
                </div>
                <div class="kalk-field">
                    <label>Panjang/Tinggi Badan (cm)</label>
                    <input type="number" step="0.1" min="30" max="150" name="tinggi_cm" value="{{ old('tinggi_cm', $old['tinggi_cm'] ?? '') }}" placeholder="Contoh: 72.5" required>
                    @error('tinggi_cm')<div class="kalk-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;">Hitung Status Gizi →</button>
        </form>
    </div>

    @if($hasil)
        <div class="glass fade-up" style="padding: 36px; max-width: 900px; margin: 0 auto 40px;">
            <h3 style="font-size: 19px; font-weight: 700; margin-bottom: 6px;">Hasil Perhitungan</h3>
            <p style="color: var(--text-muted); font-size: 13.5px; margin-bottom: 20px;">
                Umur anak saat pengukuran: <strong>{{ $hasil['umur_bulan'] }} bulan</strong>
                &middot; Indeks panjang/tinggi &amp; berat menggunakan acuan
                <strong>{{ $hasil['pakai_tb'] ? 'TB/U & BB/TB (diukur berdiri)' : 'PB/U & BB/PB (diukur telentang)' }}</strong>
                @if($hasil['imt']) &middot; IMT: <strong>{{ $hasil['imt'] }} kg/m&sup2;</strong> @endif
            </p>

            <div class="kalk-status-grid">
                <div class="kalk-status-card glass">
                    <div class="kalk-status-label">BB/U (Berat Badan menurut Umur)</div>
                    <div class="kalk-status-z">{{ $hasil['bb_u']['z'] !== null ? number_format($hasil['bb_u']['z'], 2) : '-' }} SD</div>
                    <span class="severity-pill severity-{{ $hasil['bb_u']['severity'] }}">{{ $hasil['bb_u']['label'] }}</span>
                </div>
                <div class="kalk-status-card glass">
                    <div class="kalk-status-label">PB/U atau TB/U (Panjang/Tinggi menurut Umur)</div>
                    <div class="kalk-status-z">{{ $hasil['pb_tb_u']['z'] !== null ? number_format($hasil['pb_tb_u']['z'], 2) : '-' }} SD</div>
                    <span class="severity-pill severity-{{ $hasil['pb_tb_u']['severity'] }}">{{ $hasil['pb_tb_u']['label'] }}</span>
                </div>
                <div class="kalk-status-card glass">
                    <div class="kalk-status-label">BB/PB atau BB/TB</div>
                    <div class="kalk-status-z">{{ $hasil['bb_pb_tb']['z'] !== null ? number_format($hasil['bb_pb_tb']['z'], 2) : '-' }} SD</div>
                    <span class="severity-pill severity-{{ $hasil['bb_pb_tb']['severity'] }}">{{ $hasil['bb_pb_tb']['label'] }}</span>
                </div>
                <div class="kalk-status-card glass">
                    <div class="kalk-status-label">IMT/U (Indeks Massa Tubuh menurut Umur)</div>
                    <div class="kalk-status-z">{{ $hasil['imt_u']['z'] !== null ? number_format($hasil['imt_u']['z'], 2) : '-' }} SD</div>
                    <span class="severity-pill severity-{{ $hasil['imt_u']['severity'] }}">{{ $hasil['imt_u']['label'] }}</span>
                </div>
            </div>

            <div class="kalk-note">
                Hasil ini adalah estimasi mandiri untuk edukasi, bukan diagnosis medis. Untuk pemantauan berkala,
                grafik pertumbuhan, dan konsultasi lebih lanjut, silakan kunjungi posyandu terdekat atau
                <a href="{{ route('register.orang-tua') }}" style="color: var(--primary);">daftar sebagai orang tua</a>
                di AI Stunt Detect untuk memantau tumbuh kembang anak secara berkelanjutan.
            </div>
        </div>
    @endif

    <div class="glass fade-up" style="padding: 36px; max-width: 900px; margin: 0 auto;">
        <h3 style="font-size: 19px; font-weight: 700; margin-bottom: 4px;">Kategori &amp; Ambang Batas Status Gizi</h3>
        <p style="color: var(--text-muted); font-size: 13.5px; margin-bottom: 20px;">Berlaku untuk anak umur 0–60 bulan, sesuai Lampiran I Permenkes RI No. 2 Tahun 2020.</p>

        <div style="display: grid; gap: 28px;">
            @foreach($kategori as $cat)
                <div>
                    <h4 style="font-size: 14.5px; font-weight: 700; margin-bottom: 10px;">{{ $cat['indeks'] }}</h4>
                    <div style="overflow-x: auto;">
                        <table class="kalk-kategori-table">
                            <thead>
                                <tr>
                                    <th>Kategori Status Gizi</th>
                                    <th>Ambang Batas (Z-Score)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cat['baris'] as $baris)
                                    <tr>
                                        <td><span class="severity-pill severity-{{ $baris['severity'] }}">{{ $baris['label'] }}</span></td>
                                        <td>{{ $baris['z'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="kalk-note">
            Sumber data acuan: WHO Child Growth Standards (2006) &amp; WHO Growth Reference (2007), yang menjadi
            dasar penyusunan Standar Antropometri Anak &mdash; Peraturan Menteri Kesehatan RI No. 2 Tahun 2020.
        </div>
    </div>
</section>
@endsection

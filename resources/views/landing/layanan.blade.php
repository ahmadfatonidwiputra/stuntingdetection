@extends('layouts.landing')

@section('title', 'Layanan Posyandu - AI Stunt Detect')

@section('content')
<section style="padding-top: 120px;" class="section">
    <div class="section-header fade-up">
        <div class="section-badge">🏥 Layanan</div>
        <h1 class="section-title">Layanan Posyandu</h1>
        <p class="section-subtitle">Posyandu (Pos Pelayanan Terpadu) adalah pusat kegiatan masyarakat untuk mendapatkan pelayanan kesehatan dasar, terutama untuk ibu dan anak.</p>
    </div>

    <!-- Services -->
    <h2 style="font-size: 28px; font-weight: 800; margin-bottom: 24px;" class="fade-up">Layanan yang Tersedia</h2>
    <div class="grid-3" style="margin-bottom: 60px;">
        <div class="glass fade-up" style="padding: 32px;">
            <div style="font-size: 40px; margin-bottom: 16px;">📏</div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 10px;">Pengukuran Pertumbuhan</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Pengukuran tinggi badan, berat badan, dan lingkar kepala anak secara rutin setiap bulan.</p>
        </div>
        <div class="glass fade-up" style="padding: 32px;">
            <div style="font-size: 40px; margin-bottom: 16px;">🤖</div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 10px;">Deteksi Stunting AI</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Analisis otomatis status gizi anak menggunakan kecerdasan buatan berdasarkan standar WHO dan Kemenkes.</p>
        </div>
        <div class="glass fade-up" style="padding: 32px;">
            <div style="font-size: 40px; margin-bottom: 16px;">💉</div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 10px;">Imunisasi</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Pemberian imunisasi dasar lengkap untuk anak sesuai jadwal yang ditentukan Kementerian Kesehatan.</p>
        </div>
        <div class="glass fade-up" style="padding: 32px;">
            <div style="font-size: 40px; margin-bottom: 16px;">🍎</div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 10px;">Konsultasi Gizi</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Edukasi dan konsultasi pola makan sehat untuk ibu hamil, ibu menyusui, dan balita agar tumbuh kembang optimal.</p>
        </div>
        <div class="glass fade-up" style="padding: 32px;">
            <div style="font-size: 40px; margin-bottom: 16px;">🤰</div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 10px;">Pemeriksaan Ibu Hamil</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Pemeriksaan kehamilan rutin, penimbangan, tekanan darah, dan konsultasi kesehatan ibu hamil.</p>
        </div>
        <div class="glass fade-up" style="padding: 32px;">
            <div style="font-size: 40px; margin-bottom: 16px;">💊</div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 10px;">Pemberian Vitamin</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Distribusi vitamin A, tablet tambah darah, dan suplemen gizi lainnya untuk ibu dan anak.</p>
        </div>
    </div>

    <!-- Schedule -->
    <h2 style="font-size: 28px; font-weight: 800; margin-bottom: 24px;" class="fade-up">Jadwal Pelayanan Posyandu</h2>
    <div class="glass fade-up" style="padding: 36px; margin-bottom: 60px;">
        <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7; margin-bottom: 24px;">
            Posyandu umumnya dilaksanakan sebulan sekali di masing-masing wilayah. Jadwal pelayanan mengikuti sistem 5 meja:
        </p>
        <div style="display: grid; gap: 16px;">
            <div style="display: flex; gap: 16px; align-items: flex-start; padding: 16px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--glass-border);">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0; color: white;">1</div>
                <div>
                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Pendaftaran</h4>
                    <p style="color: var(--text-muted); font-size: 13px;">Pencatatan data kunjungan ibu dan anak</p>
                </div>
            </div>
            <div style="display: flex; gap: 16px; align-items: flex-start; padding: 16px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--glass-border);">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0; color: white;">2</div>
                <div>
                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Penimbangan & Pengukuran</h4>
                    <p style="color: var(--text-muted); font-size: 13px;">Berat badan, tinggi badan, dan lingkar kepala anak</p>
                </div>
            </div>
            <div style="display: flex; gap: 16px; align-items: flex-start; padding: 16px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--glass-border);">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0; color: white;">3</div>
                <div>
                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Pencatatan & Analisis AI</h4>
                    <p style="color: var(--text-muted); font-size: 13px;">Input data ke sistem AI Stunt Detect untuk analisis Z-Score otomatis</p>
                </div>
            </div>
            <div style="display: flex; gap: 16px; align-items: flex-start; padding: 16px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--glass-border);">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0; color: white;">4</div>
                <div>
                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Penyuluhan & Edukasi</h4>
                    <p style="color: var(--text-muted); font-size: 13px;">Konsultasi gizi dan pola asuh oleh kader dan tenaga kesehatan</p>
                </div>
            </div>
            <div style="display: flex; gap: 16px; align-items: flex-start; padding: 16px; background: rgba(255,255,255,0.03); border-radius: 12px; border: 1px solid var(--glass-border);">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0; color: white;">5</div>
                <div>
                    <h4 style="font-size: 15px; font-weight: 700; margin-bottom: 4px;">Pelayanan Kesehatan</h4>
                    <p style="color: var(--text-muted); font-size: 13px;">Imunisasi, vitamin, dan rujukan ke puskesmas jika diperlukan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="glass fade-up" style="padding: 48px; text-align: center; background: linear-gradient(135deg, rgba(16,185,129,0.08), rgba(14,165,233,0.08));">
        <h2 style="font-size: 24px; font-weight: 800; margin-bottom: 12px;">Anda Petugas Posyandu?</h2>
        <p style="color: var(--text-secondary); font-size: 15px; margin-bottom: 24px;">Daftarkan diri Anda dan mulai gunakan AI Stunt Detect untuk pemantauan pertumbuhan anak di posyandu Anda.</p>
        <a href="{{ route('register.petugas') }}" class="btn-primary">Daftar Sebagai Petugas →</a>
    </div>
</section>
@endsection

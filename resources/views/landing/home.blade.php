@extends('layouts.landing')

@section('title', 'AI Stunt Detect - Deteksi Dini Stunting Berbasis AI')

@section('content')
<!-- Hero Section -->
<section style="min-height: 100vh; display: flex; align-items: center; padding: 120px 24px 80px;">
    <div style="max-width: 1200px; margin: 0 auto; width: 100%;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;">
            <div class="fade-up">
                <div class="section-badge">🤖 Didukung Kecerdasan Buatan</div>
                <h1 style="font-size: 52px; font-weight: 900; line-height: 1.15; margin-bottom: 24px;">
                    Deteksi Dini
                    <span style="background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Stunting</span>
                    untuk Masa Depan Anak
                </h1>
                <p style="font-size: 17px; color: var(--text-secondary); line-height: 1.8; margin-bottom: 36px; max-width: 500px;">
                    Sistem pemantauan pertumbuhan anak berbasis AI yang membantu petugas posyandu mendeteksi stunting lebih awal dan akurat menggunakan standar WHO.
                </p>
                <div class="hero-buttons" style="display: flex; gap: 16px; flex-wrap: wrap;">
                    <a href="{{ route('tentang-stunting') }}" class="btn-primary">
                        Pelajari Stunting →
                    </a>
                </div>
            </div>
            <div class="fade-up" style="animation-delay: 0.2s;">
                <div style="position: relative;">
                    <div style="background: linear-gradient(135deg, rgba(14,165,233,0.15), rgba(139,92,246,0.15)); border: 1px solid rgba(255,255,255,0.08); border-radius: 24px; padding: 40px; backdrop-filter: blur(20px);">
                        <!-- Stats preview -->
                        <div style="text-align: center; margin-bottom: 30px;">
                            <div style="font-size: 72px; margin-bottom: 8px;">📊</div>
                            <h3 style="font-size: 20px; font-weight: 700; margin-bottom: 8px;">Pemantauan Real-time</h3>
                            <p style="color: var(--text-muted); font-size: 14px;">Z-Score & Klasifikasi WHO</p>
                        </div>
                        <div class="hero-stats-grid">
                            <div style="text-align: center; padding: 16px; background: rgba(16,185,129,0.1); border-radius: 12px; border: 1px solid rgba(16,185,129,0.2);">
                                <div style="font-size: 24px; font-weight: 800; color: #10b981;">Normal</div>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Z ≥ -2</div>
                            </div>
                            <div style="text-align: center; padding: 16px; background: rgba(245,158,11,0.1); border-radius: 12px; border: 1px solid rgba(245,158,11,0.2);">
                                <div style="font-size: 24px; font-weight: 800; color: #f59e0b;">Stunting</div>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">-3 ≤ Z < -2</div>
                            </div>
                            <div style="text-align: center; padding: 16px; background: rgba(239,68,68,0.1); border-radius: 12px; border: 1px solid rgba(239,68,68,0.2);">
                                <div style="font-size: 24px; font-weight: 800; color: #ef4444;">Severe</div>
                                <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Z < -3</div>
                            </div>
                        </div>
                    </div>
                    <!-- Floating accent -->
                    <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--accent)); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 32px; box-shadow: 0 8px 30px rgba(14,165,233,0.4); animation: float 3s ease-in-out infinite;">🧒</div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .hero-stats-grid {
        display: grid; 
        grid-template-columns: 1fr 1fr 1fr; 
        gap: 16px;
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    @media (max-width: 768px) {
        section:first-of-type > div > div { grid-template-columns: 1fr !important; }
        section:first-of-type h1 { 
            font-size: 32px !important; 
            line-height: 1.2 !important;
            word-break: break-word; /* Prevents long words from breaking container */
        }
        .hero-stats-grid {
            grid-template-columns: 1fr; /* Stack the 3 cards vertically on mobile */
        }
    }
</style>

<!-- Stats Section -->
<section style="padding: 0 24px 80px;">
    <div style="max-width: 1200px; margin: 0 auto;">
        <div class="grid-4">
            <div class="glass fade-up" style="padding: 28px; text-align: center;">
                <div style="font-size: 36px; font-weight: 900; background: linear-gradient(135deg, var(--danger), #f97316); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">21,6%</div>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 6px;">Prevalensi Stunting Indonesia (2022)</div>
            </div>
            <div class="glass fade-up" style="padding: 28px; text-align: center;">
                <div style="font-size: 36px; font-weight: 900; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">5,3 Jt</div>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 6px;">Balita Stunting di Indonesia</div>
            </div>
            <div class="glass fade-up" style="padding: 28px; text-align: center;">
                <div style="font-size: 36px; font-weight: 900; background: linear-gradient(135deg, var(--accent), #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">14%</div>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 6px;">Target Prevalensi 2024</div>
            </div>
            <div class="glass fade-up" style="padding: 28px; text-align: center;">
                <div style="font-size: 36px; font-weight: 900; background: linear-gradient(135deg, var(--warning), var(--danger)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">0-60</div>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 6px;">Bulan Usia Kritis Anak</div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section">
    <div class="section-header fade-up">
        <div class="section-badge">Fitur Utama</div>
        <h2 class="section-title">Mengapa AI Stunt Detect?</h2>
        <p class="section-subtitle">Teknologi kecerdasan buatan yang membantu petugas posyandu bekerja lebih efisien dan akurat dalam memantau pertumbuhan anak.</p>
    </div>

    <div class="grid-3">
        <div class="glass fade-up" style="padding: 36px;">
            <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(14,165,233,0.15); display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 20px;">🤖</div>
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 10px;">Deteksi AI Otomatis</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Kalkulasi Z-Score otomatis berdasarkan standar WHO/Kemenkes. Klasifikasi stunting secara real-time tanpa perhitungan manual.</p>
        </div>
        <div class="glass fade-up" style="padding: 36px;">
            <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(139,92,246,0.15); display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 20px;">📊</div>
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 10px;">Dashboard Interaktif</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Visualisasi data pertumbuhan anak dengan grafik tren, statistik, dan laporan lengkap yang mudah dipahami.</p>
        </div>
        <div class="glass fade-up" style="padding: 36px;">
            <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(16,185,129,0.15); display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 20px;">📱</div>
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 10px;">Responsif & Mobile</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Akses dari perangkat apapun. Desain responsif yang nyaman digunakan di smartphone maupun komputer.</p>
        </div>
        <div class="glass fade-up" style="padding: 36px;">
            <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(245,158,11,0.15); display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 20px;">👥</div>
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 10px;">Multi-Role System</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Sistem peran terstruktur: Super Admin mengelola petugas, Petugas Posyandu mencatat data anak dengan akuntabilitas jelas.</p>
        </div>
        <div class="glass fade-up" style="padding: 36px;">
            <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(239,68,68,0.15); display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 20px;">🔒</div>
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 10px;">Aman & Terverifikasi</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Sistem verifikasi petugas oleh Super Admin. Data anak terlindungi dengan autentikasi dan otorisasi berlapis.</p>
        </div>
        <div class="glass fade-up" style="padding: 36px;">
            <div style="width: 56px; height: 56px; border-radius: 14px; background: rgba(236,72,153,0.15); display: flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 20px;">📋</div>
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 10px;">Riwayat Lengkap</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Simpan seluruh riwayat pemeriksaan anak. Pantau perkembangan dari waktu ke waktu dengan data historis lengkap.</p>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section style="padding: 80px 24px;">
    <div style="max-width: 800px; margin: 0 auto;">
        <div class="glass fade-up" style="padding: 60px; text-align: center; background: linear-gradient(135deg, rgba(14,165,233,0.08), rgba(139,92,246,0.08));">
            <h2 style="font-size: 32px; font-weight: 800; margin-bottom: 16px;">Siap Memulai?</h2>
            <p style="color: var(--text-secondary); font-size: 16px; margin-bottom: 32px; max-width: 500px; margin-left: auto; margin-right: auto; line-height: 1.7;">
                Daftarkan posyandu Anda dan mulai pantau pertumbuhan anak-anak di wilayah Anda dengan teknologi AI.
            </p>
            <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('register.petugas') }}" class="btn-primary">Daftar Sekarang</a>
                <a href="{{ route('login') }}" class="btn-outline">Sudah Punya Akun? Masuk</a>
            </div>
        </div>
    </div>
</section>
@endsection

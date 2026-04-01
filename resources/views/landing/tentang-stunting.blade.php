@extends('layouts.landing')

@section('title', 'Tentang Stunting - AI Stunt Detect')

@section('content')
<section style="padding-top: 120px;" class="section">
    <div class="section-header fade-up">
        <div class="section-badge">📖 Edukasi</div>
        <h1 class="section-title">Apa Itu Stunting?</h1>
        <p class="section-subtitle">Stunting adalah gangguan pertumbuhan kronis pada anak akibat kekurangan gizi dalam waktu lama, menyebabkan anak terlalu pendek untuk usianya.</p>
    </div>

    <!-- Definition -->
    <div class="glass fade-up" style="padding: 40px; margin-bottom: 40px; border-left: 4px solid var(--primary);">
        <h2 style="font-size: 22px; font-weight: 700; margin-bottom: 12px; color: var(--primary);">Definisi Stunting</h2>
        <p style="color: var(--text-secondary); font-size: 15px; line-height: 1.8;">
            Menurut WHO, stunting adalah kondisi di mana tinggi badan anak berada di bawah minus dua standar deviasi (-2 SD) dari median standar pertumbuhan anak WHO. Stunting merupakan masalah gizi kronis yang disebabkan oleh asupan gizi yang kurang dalam waktu lama, umumnya terjadi pada 1.000 hari pertama kehidupan (HPK).
        </p>
    </div>

    <!-- Causes -->
    <h2 style="font-size: 28px; font-weight: 800; margin-bottom: 24px;" class="fade-up">Penyebab Stunting</h2>
    <div class="grid-2" style="margin-bottom: 60px;">
        <div class="glass fade-up" style="padding: 32px;">
            <div style="font-size: 32px; margin-bottom: 16px;">🍼</div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 10px;">Kurang Gizi Kronis</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Asupan gizi yang tidak memadai selama kehamilan dan dua tahun pertama kehidupan anak. Kekurangan protein, zink, zat besi, dan vitamin A.</p>
        </div>
        <div class="glass fade-up" style="padding: 32px;">
            <div style="font-size: 32px; margin-bottom: 16px;">🦠</div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 10px;">Infeksi Berulang</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Diare kronis, infeksi saluran napas, dan penyakit menular lainnya yang mengganggu penyerapan nutrisi dan pertumbuhan anak.</p>
        </div>
        <div class="glass fade-up" style="padding: 32px;">
            <div style="font-size: 32px; margin-bottom: 16px;">🚰</div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 10px;">Sanitasi & Kebersihan</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Akses air bersih yang terbatas, sanitasi buruk, dan kurangnya praktek hygiene yang baik meningkatkan risiko infeksi.</p>
        </div>
        <div class="glass fade-up" style="padding: 32px;">
            <div style="font-size: 32px; margin-bottom: 16px;">🤰</div>
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 10px;">Kesehatan Ibu</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Gizi ibu saat hamil dan menyusui sangat berpengaruh. Anemia, KEK (Kurang Energi Kronis), dan kurangnya asupan nutrisi saat hamil meningkatkan risiko.</p>
        </div>
    </div>

    <!-- Impacts -->
    <h2 style="font-size: 28px; font-weight: 800; margin-bottom: 24px;" class="fade-up">Dampak Stunting</h2>
    <div class="grid-3" style="margin-bottom: 60px;">
        <div class="glass fade-up" style="padding: 32px; border-top: 3px solid var(--danger);">
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 10px; color: var(--danger);">Jangka Pendek</h3>
            <ul style="color: var(--text-secondary); font-size: 14px; line-height: 2; list-style: none;">
                <li>⚠️ Gangguan perkembangan otak</li>
                <li>⚠️ Kecerdasan menurun</li>
                <li>⚠️ Gangguan pertumbuhan fisik</li>
                <li>⚠️ Gangguan metabolisme tubuh</li>
            </ul>
        </div>
        <div class="glass fade-up" style="padding: 32px; border-top: 3px solid var(--warning);">
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 10px; color: var(--warning);">Jangka Panjang</h3>
            <ul style="color: var(--text-secondary); font-size: 14px; line-height: 2; list-style: none;">
                <li>⚠️ Postur tubuh pendek (tidak optimal)</li>
                <li>⚠️ Risiko penyakit degeneratif</li>
                <li>⚠️ Kemampuan belajar menurun</li>
                <li>⚠️ Produktivitas kerja rendah</li>
            </ul>
        </div>
        <div class="glass fade-up" style="padding: 32px; border-top: 3px solid var(--secondary);">
            <h3 style="font-size: 17px; font-weight: 700; margin-bottom: 10px; color: var(--secondary);">Dampak Ekonomi</h3>
            <ul style="color: var(--text-secondary); font-size: 14px; line-height: 2; list-style: none;">
                <li>⚠️ Penurunan GDP 2-3%</li>
                <li>⚠️ Biaya kesehatan meningkat</li>
                <li>⚠️ Produktivitas tenaga kerja rendah</li>
                <li>⚠️ Siklus kemiskinan berlanjut</li>
            </ul>
        </div>
    </div>

    <!-- Prevention -->
    <h2 style="font-size: 28px; font-weight: 800; margin-bottom: 24px;" class="fade-up">Pencegahan Stunting</h2>
    <div class="grid-2" style="margin-bottom: 60px;">
        <div class="glass fade-up" style="padding: 32px;">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16,185,129,0.15); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">1️⃣</div>
                <h3 style="font-size: 17px; font-weight: 700;">1.000 Hari Pertama Kehidupan</h3>
            </div>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Perhatikan asupan gizi sejak masa kehamilan hingga anak berusia 2 tahun. Berikan ASI eksklusif 6 bulan pertama, lalu MPASI bergizi seimbang.</p>
        </div>
        <div class="glass fade-up" style="padding: 32px;">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(14,165,233,0.15); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">2️⃣</div>
                <h3 style="font-size: 17px; font-weight: 700;">Pemantauan Rutin di Posyandu</h3>
            </div>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Bawa anak ke posyandu secara rutin untuk penimbangan dan pengukuran tinggi badan. Deteksi dini membantu penanganan lebih cepat.</p>
        </div>
        <div class="glass fade-up" style="padding: 32px;">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(139,92,246,0.15); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">3️⃣</div>
                <h3 style="font-size: 17px; font-weight: 700;">Pola Makan Bergizi Seimbang</h3>
            </div>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Pastikan anak mendapat protein hewani (telur, ikan, ayam, susu), sayuran, buah-buahan, dan karbohidrat yang cukup setiap hari.</p>
        </div>
        <div class="glass fade-up" style="padding: 32px;">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245,158,11,0.15); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0;">4️⃣</div>
                <h3 style="font-size: 17px; font-weight: 700;">Sanitasi & Air Bersih</h3>
            </div>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">Jaga kebersihan lingkungan, gunakan air bersih, cuci tangan sebelum makan dan setelah BAB, serta kelola sampah dengan baik.</p>
        </div>
    </div>

    <!-- Z-Score Classification -->
    <div class="glass fade-up" style="padding: 40px; background: linear-gradient(135deg, rgba(14,165,233,0.05), rgba(139,92,246,0.05));">
        <h2 style="font-size: 22px; font-weight: 700; margin-bottom: 20px; text-align: center;">Klasifikasi Status Gizi (Tinggi Badan menurut Umur)</h2>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; min-width: 500px;">
                <thead>
                    <tr>
                        <th style="padding: 14px 20px; text-align: left; border-bottom: 1px solid var(--glass-border); font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted);">Kategori</th>
                        <th style="padding: 14px 20px; text-align: left; border-bottom: 1px solid var(--glass-border); font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted);">Z-Score (TB/U)</th>
                        <th style="padding: 14px 20px; text-align: left; border-bottom: 1px solid var(--glass-border); font-size: 13px; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted);">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);"><span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: rgba(239,68,68,0.15); color: #ef4444;">Sangat Stunting</span></td>
                        <td style="padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04); font-weight: 600;">Z-Score < -3 SD</td>
                        <td style="padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04); color: var(--text-secondary); font-size: 14px;">Sangat pendek untuk usia. Perlu penanganan segera.</td>
                    </tr>
                    <tr>
                        <td style="padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04);"><span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: rgba(245,158,11,0.15); color: #f59e0b;">Stunting</span></td>
                        <td style="padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04); font-weight: 600;">-3 SD ≤ Z-Score < -2 SD</td>
                        <td style="padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.04); color: var(--text-secondary); font-size: 14px;">Pendek untuk usia. Perlu pemantauan intensif.</td>
                    </tr>
                    <tr>
                        <td style="padding: 14px 20px;"><span style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; background: rgba(16,185,129,0.15); color: #10b981;">Normal</span></td>
                        <td style="padding: 14px 20px; font-weight: 600;">Z-Score ≥ -2 SD</td>
                        <td style="padding: 14px 20px; color: var(--text-secondary); font-size: 14px;">Tinggi badan sesuai dengan usia. Pertumbuhan normal.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@extends('layouts.main')

@push('styles')
<style>
    .antro-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .antro-tab {
        padding: 10px 18px;
        border-radius: 999px;
        border: 1px solid var(--border-glass);
        background: transparent;
        color: var(--text-secondary);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Inter', sans-serif;
    }

    .antro-tab:hover {
        color: var(--text-primary);
        background: var(--bg-glass);
    }

    .antro-tab.active {
        background: var(--gradient-1);
        color: white;
        border-color: transparent;
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
    }

    .antro-section {
        display: none;
    }

    .antro-section.active {
        display: block;
    }

    .antro-subtabs {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .antro-subtab {
        padding: 7px 14px;
        border-radius: 8px;
        border: 1px solid var(--border-glass);
        background: var(--bg-glass);
        color: var(--text-secondary);
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: 'Inter', sans-serif;
        white-space: nowrap;
    }

    .antro-subtab.active {
        background: var(--accent-blue);
        color: white;
        border-color: transparent;
    }

    .antro-subsection {
        display: none;
    }

    .antro-subsection.active {
        display: block;
    }

    .antro-table-wrap {
        max-height: 520px;
        overflow-y: auto;
        overflow-x: auto;
        border: 1px solid var(--border-glass);
        border-radius: var(--radius-sm);
    }

    .antro-table thead th {
        position: sticky;
        top: 0;
        background: var(--bg-secondary);
        z-index: 2;
    }

    .antro-table td, .antro-table th {
        text-align: center;
        white-space: nowrap;
    }

    .antro-table td:first-child, .antro-table th:first-child {
        text-align: left;
        position: sticky;
        left: 0;
        background: var(--bg-card);
        z-index: 1;
    }

    .kategori-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .kategori-grid > .glass-card {
        min-width: 0;
    }

    .kategori-grid table.data-table th,
    .kategori-grid table.data-table td {
        white-space: normal;
        word-break: break-word;
        vertical-align: top;
    }

    .kategori-grid table.data-table td:last-child {
        white-space: nowrap;
    }

    .kategori-grid .severity-pill {
        display: block;
        white-space: normal;
        text-align: left;
        border-radius: 8px;
        line-height: 1.4;
        padding: 6px 10px;
    }

    .severity-pill {
        display: inline-flex;
        align-items: center;
        padding: 3px 10px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 700;
        white-space: nowrap;
    }

    .severity-severe, .severity-severe-high { background: rgba(239, 68, 68, 0.15); color: var(--accent-red); }
    .severity-moderate, .severity-watch { background: rgba(245, 158, 11, 0.15); color: var(--accent-orange); }
    .severity-normal { background: rgba(16, 185, 129, 0.15); color: var(--accent-green); }
    .severity-high { background: rgba(236, 72, 153, 0.15); color: var(--accent-pink); }
    .severity-unknown { background: var(--bg-glass); color: var(--text-muted); }

    .status-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }

    .status-card {
        padding: 18px;
        border-radius: var(--radius-sm);
        background: var(--bg-glass);
        border: 1px solid var(--border-glass);
        min-width: 0;
    }

    .status-card .severity-pill {
        white-space: normal;
        text-align: left;
    }

    .status-card-label {
        font-size: 12px;
        color: var(--text-muted);
        margin-bottom: 8px;
        font-weight: 600;
    }

    .status-card-z {
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 6px;
    }

    .antro-note {
        font-size: 12.5px;
        color: var(--text-muted);
        background: var(--bg-glass);
        border: 1px solid var(--border-glass);
        border-radius: var(--radius-sm);
        padding: 14px 16px;
        margin-top: 16px;
        line-height: 1.6;
    }

    .anak-picker {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .antro-search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 50;
        background: var(--bg-card);
        border: 1px solid var(--border-glass);
        border-radius: var(--radius-sm);
        margin-top: 6px;
        max-height: 260px;
        overflow-y: auto;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    }

    .antro-search-result-item {
        padding: 10px 16px;
        cursor: pointer;
        transition: background 0.15s ease;
        border-bottom: 1px solid var(--border-glass);
    }

    .antro-search-result-item:last-child {
        border-bottom: none;
    }

    .antro-search-result-item:hover,
    .antro-search-result-item.is-active {
        background: var(--bg-glass);
    }

    .antro-search-result-name {
        font-weight: 600;
        font-size: 14px;
        color: var(--text-primary);
    }

    .antro-search-result-meta {
        font-size: 12px;
        color: var(--text-muted);
        margin-top: 2px;
    }

    @media (max-width: 768px) {
        .antro-table-wrap { max-height: 420px; }
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Antropometri Anak</h1>
    <p class="page-subtitle">Standar Antropometri Anak — Permenkes RI No. 2 Tahun 2020</p>
</div>

<div class="antro-tabs">
    <button type="button" class="antro-tab active" data-antro-section="kategori">Kategori &amp; Ambang Batas</button>
    <button type="button" class="antro-tab" data-antro-section="tabel">Tabel Standar Antropometri</button>
    @if($canPilihAnak)
        <button type="button" class="antro-tab" data-antro-section="grafik">Grafik Pertumbuhan</button>
    @endif
    <button type="button" class="antro-tab" data-antro-section="kenaikan">Kenaikan Berat Badan</button>
</div>

{{-- ═══════════════ 1. KATEGORI & AMBANG BATAS ═══════════════ --}}
<div class="antro-section active" data-antro-section-panel="kategori">
    <div class="kategori-grid">
        @foreach($kategori as $key => $cat)
            <div class="glass-card fade-in">
                <div class="chart-title" style="font-size: 14px;">{{ $cat['indeks'] }}</div>
                <p class="page-subtitle" style="margin-bottom: 16px;">Berlaku untuk umur {{ $cat['usia'] }}</p>
                <table class="data-table">
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
        @endforeach
    </div>
    <div class="antro-note">
        Kategori dan ambang batas di atas mengacu pada Lampiran I Peraturan Menteri Kesehatan RI No. 2 Tahun 2020 tentang Standar Antropometri Anak.
    </div>
</div>

{{-- ═══════════════ 2. TABEL STANDAR ANTROPOMETRI ═══════════════ --}}
<div class="antro-section" data-antro-section-panel="tabel">
    <div class="glass-card fade-in">
        <div class="antro-subtabs" data-antro-gender-tabs>
            <button type="button" class="antro-subtab active" data-antro-gender="L">Anak Laki-laki</button>
            <button type="button" class="antro-subtab" data-antro-gender="P">Anak Perempuan</button>
        </div>

        @foreach(['L' => $tabelBoys, 'P' => $tabelGirls] as $genderKey => $tabel)
            <div class="antro-gender-panel" data-antro-gender-panel="{{ $genderKey }}" style="{{ $genderKey === 'P' ? 'display:none;' : '' }}">
                <div class="antro-subtabs" data-antro-indikator-tabs="{{ $genderKey }}">
                    <button type="button" class="antro-subtab active" data-antro-indikator="bb_u">BB/U</button>
                    <button type="button" class="antro-subtab" data-antro-indikator="pb_tb_u">PB/U atau TB/U</button>
                    <button type="button" class="antro-subtab" data-antro-indikator="bb_pb">BB/PB (0–24 bulan)</button>
                    <button type="button" class="antro-subtab" data-antro-indikator="bb_tb">BB/TB (24–60 bulan)</button>
                    <button type="button" class="antro-subtab" data-antro-indikator="imt_u">IMT/U</button>
                </div>

                <div class="antro-indikator-panel" data-antro-indikator-panel="bb_u">
                    @include('antropometri.partials.tabel-standar', ['rows' => $tabel['bb_u'], 'keyLabel' => 'Umur (bulan)'])
                </div>
                <div class="antro-indikator-panel" data-antro-indikator-panel="pb_tb_u" style="display:none;">
                    @include('antropometri.partials.tabel-standar', ['rows' => $tabel['pb_tb_u'], 'keyLabel' => 'Umur (bulan)'])
                </div>
                <div class="antro-indikator-panel" data-antro-indikator-panel="bb_pb" style="display:none;">
                    @include('antropometri.partials.tabel-standar', ['rows' => $tabel['bb_pb'], 'keyLabel' => 'Panjang Badan (cm)'])
                </div>
                <div class="antro-indikator-panel" data-antro-indikator-panel="bb_tb" style="display:none;">
                    @include('antropometri.partials.tabel-standar', ['rows' => $tabel['bb_tb'], 'keyLabel' => 'Tinggi Badan (cm)'])
                </div>
                <div class="antro-indikator-panel" data-antro-indikator-panel="imt_u" style="display:none;">
                    @include('antropometri.partials.tabel-standar', ['rows' => $tabel['imt_u'], 'keyLabel' => 'Umur (bulan)'])
                </div>
            </div>
        @endforeach

        <div class="antro-note">
            Sumber data: WHO Child Growth Standards (2006) &amp; WHO Growth Reference (2007), yang menjadi dasar penyusunan Standar Antropometri Anak — Permenkes RI No. 2 Tahun 2020.
            PB (panjang badan, diukur telentang) digunakan untuk umur 0–24 bulan; TB (tinggi badan, diukur berdiri) digunakan untuk umur di atas 24 bulan.
        </div>
    </div>
</div>

{{-- ═══════════════ 3. GRAFIK PERTUMBUHAN ═══════════════ --}}
@if($canPilihAnak)
<div class="antro-section" data-antro-section-panel="grafik">
    <div class="glass-card fade-in" style="margin-bottom: 24px;">
        <form method="GET" action="{{ url()->current() }}" id="grafik-anak-form" class="anak-picker" style="position: relative;">
            <label class="form-label" style="margin-bottom:0;">Cari Anak:</label>
            <div style="position: relative; flex: 1; max-width: 360px;">
                <input type="text" id="grafik-search-anak" class="form-input" autocomplete="off"
                       value="{{ $grafik ? $grafik['anak']->nama . ' (' . ($grafik['anak']->nik_anak ?: '-') . ')' : '' }}"
                       placeholder="Ketik nama atau NIK anak...">
                <div id="grafik-search-results" class="antro-search-results" style="display:none;"></div>
            </div>
            <input type="hidden" name="anak_id" id="grafik-anak-id" value="{{ $selectedAnakId }}">
        </form>

        @if($anakOptions->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">👶</div>
                <h3>Belum ada data anak</h3>
                <p>Data anak akan muncul di sini setelah terdaftar.</p>
            </div>
        @elseif(!$grafik)
            <div class="empty-state">
                <div class="empty-state-icon">📈</div>
                <h3>Pilih anak untuk melihat grafik pertumbuhan</h3>
                <p>Grafik akan membandingkan hasil pengukuran anak dengan kurva standar WHO/Permenkes.</p>
            </div>
        @endif
    </div>

    @if($grafik)
        @php $anak = $grafik['anak']; @endphp
        <div class="glass-card fade-in" style="margin-bottom: 24px;">
            <div class="flex-between">
                <div>
                    <div class="chart-title" style="margin-bottom: 4px;">{{ $anak->nama }}</div>
                    <p class="page-subtitle">
                        {{ $anak->jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-laki' }}
                        &middot; Lahir {{ $anak->tanggal_lahir->format('d M Y') }}
                        &middot; Umur saat ini {{ $anak->umur['formatted'] }}
                    </p>
                </div>
            </div>
        </div>

        @if($grafik['status_terkini'])
            @php $st = $grafik['status_terkini']; @endphp
            <div class="status-grid">
                <div class="status-card">
                    <div class="status-card-label">BB/U &middot; {{ $st['berat_kg'] }} kg pada umur {{ $st['umur_bulan'] }} bln</div>
                    <div class="status-card-z">{{ $st['bb_u']['z'] !== null ? number_format($st['bb_u']['z'], 2) : '-' }} SD</div>
                    <span class="severity-pill severity-{{ $st['bb_u']['severity'] }}">{{ $st['bb_u']['label'] }}</span>
                </div>
                <div class="status-card">
                    <div class="status-card-label">PB/U atau TB/U &middot; {{ $st['tinggi_cm'] }} cm</div>
                    <div class="status-card-z">{{ $st['pb_tb_u']['z'] !== null ? number_format($st['pb_tb_u']['z'], 2) : '-' }} SD</div>
                    <span class="severity-pill severity-{{ $st['pb_tb_u']['severity'] }}">{{ $st['pb_tb_u']['label'] }}</span>
                </div>
                <div class="status-card">
                    <div class="status-card-label">BB/PB atau BB/TB</div>
                    <div class="status-card-z">{{ $st['bb_pb_tb']['z'] !== null ? number_format($st['bb_pb_tb']['z'], 2) : '-' }} SD</div>
                    <span class="severity-pill severity-{{ $st['bb_pb_tb']['severity'] }}">{{ $st['bb_pb_tb']['label'] }}</span>
                </div>
                <div class="status-card">
                    <div class="status-card-label">IMT/U &middot; {{ $st['imt'] ?? '-' }} kg/m&sup2;</div>
                    <div class="status-card-z">{{ $st['imt_u']['z'] !== null ? number_format($st['imt_u']['z'], 2) : '-' }} SD</div>
                    <span class="severity-pill severity-{{ $st['imt_u']['severity'] }}">{{ $st['imt_u']['label'] }}</span>
                </div>
            </div>
        @endif

        @if($grafik['penilaian_kenaikan'])
            @php $pk = $grafik['penilaian_kenaikan']; @endphp
            <div class="glass-card fade-in" style="margin-bottom: 24px;">
                <div class="chart-title" style="font-size: 14px;">Penilaian Kenaikan Berat Badan Terakhir</div>
                <div class="detail-row">
                    <span class="detail-row-label">Periode</span>
                    <span class="detail-row-value">{{ $pk['dari_tanggal'] }} &rarr; {{ $pk['ke_tanggal'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-row-label">Kenaikan Berat Badan</span>
                    <span class="detail-row-value">{{ number_format($pk['kenaikan_gram']) }} gram</span>
                </div>
                <div class="detail-row">
                    <span class="detail-row-label">Minimum (KBM) untuk usia tersebut</span>
                    <span class="detail-row-value">{{ $pk['minimum_gram'] !== null ? number_format($pk['minimum_gram']).' gram' : '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-row-label">Status</span>
                    <span class="detail-row-value">
                        <span class="severity-pill severity-{{ str_starts_with($pk['status'], 'Naik') ? 'normal' : 'moderate' }}">{{ $pk['status'] }}</span>
                    </span>
                </div>
            </div>
        @endif

        <div class="chart-container">
            <div class="glass-card fade-in">
                <div class="chart-title">Kurva BB/U (Berat Badan menurut Umur)</div>
                <div style="position: relative; height: 340px; width: 100%;">
                    <canvas id="chartBbU"></canvas>
                </div>
            </div>
            <div class="glass-card fade-in">
                <div class="chart-title">Kurva PB/U atau TB/U (Panjang/Tinggi Badan menurut Umur)</div>
                <div style="position: relative; height: 340px; width: 100%;">
                    <canvas id="chartTbU"></canvas>
                </div>
            </div>
        </div>
    @endif
</div>
@endif

{{-- ═══════════════ 4. KENAIKAN BERAT BADAN (WEIGHT INCREMENT) ═══════════════ --}}
<div class="antro-section" data-antro-section-panel="kenaikan">
    <div class="glass-card fade-in">
        <div class="chart-title" style="font-size: 14px;">Tabel Kenaikan Berat Badan Minimum (KBM)</div>
        <p class="page-subtitle" style="margin-bottom: 16px;">Digunakan untuk menilai apakah kenaikan berat badan anak antar kunjungan sudah mencukupi (status "Naik"/"Tidak Naik" pada KMS).</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Interval Umur (bulan)</th>
                    <th>Kenaikan Berat Badan Minimum</th>
                </tr>
            </thead>
            <tbody>
                @foreach($kenaikanBb as $row)
                    <tr>
                        <td>{{ $row['bulan'] }}</td>
                        <td>{{ number_format($row['minimum_gram']) }} gram</td>
                    </tr>
                @endforeach
                <tr>
                    <td>&ge; 12 (per bulan, rata-rata indikatif)</td>
                    <td>{{ number_format($kenaikanBbDiatas12) }} gram</td>
                </tr>
            </tbody>
        </table>
        <div class="antro-note">
            Tabel Kenaikan Berat Badan Minimum (KBM) di atas merupakan acuan praktis yang lazim digunakan pada Kartu Menuju Sehat (KMS)/Buku KIA untuk memantau kenaikan berat badan anak usia 0–12 bulan.
            Nilai untuk umur &ge; 12 bulan bersifat indikatif karena laju kenaikan berat badan melambat dan bervariasi antar anak.
            Untuk kepastian nilai baku sebagaimana Lampiran Permenkes RI No. 2 Tahun 2020, mohon periksa kembali dokumen resminya.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ── Top-level section tabs ──────────────────────
    const sectionTabs = document.querySelectorAll('.antro-tab');
    const sectionPanels = document.querySelectorAll('[data-antro-section-panel]');

    sectionTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            sectionTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const target = tab.getAttribute('data-antro-section');
            sectionPanels.forEach(function (panel) {
                panel.classList.toggle('active', panel.getAttribute('data-antro-section-panel') === target);
            });
        });
    });

    @if($grafik)
        document.querySelector('.antro-tab[data-antro-section="grafik"]')?.click();
    @endif

    // ── Gender tabs (Tabel Standar) ──────────────────
    const genderTabs = document.querySelectorAll('[data-antro-gender-tabs] .antro-subtab');
    genderTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            const gender = tab.getAttribute('data-antro-gender');
            genderTabs.forEach(t => t.classList.toggle('active', t === tab));
            document.querySelectorAll('[data-antro-gender-panel]').forEach(function (panel) {
                panel.style.display = panel.getAttribute('data-antro-gender-panel') === gender ? 'block' : 'none';
            });
        });
    });

    // ── Indikator tabs (per gender panel) ────────────
    document.querySelectorAll('[data-antro-indikator-tabs]').forEach(function (tabGroup) {
        const panelWrap = tabGroup.closest('.antro-gender-panel');
        tabGroup.querySelectorAll('.antro-subtab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                const indikator = tab.getAttribute('data-antro-indikator');
                tabGroup.querySelectorAll('.antro-subtab').forEach(t => t.classList.toggle('active', t === tab));
                panelWrap.querySelectorAll('[data-antro-indikator-panel]').forEach(function (panel) {
                    panel.style.display = panel.getAttribute('data-antro-indikator-panel') === indikator ? 'block' : 'none';
                });
            });
        });
    });

    // ── Cari Anak (nama atau NIK) ────────────────────
    const grafikAnakData = @json($anakSearchData);
    const searchAnakInput = document.getElementById('grafik-search-anak');
    const searchAnakResults = document.getElementById('grafik-search-results');
    const searchAnakIdInput = document.getElementById('grafik-anak-id');
    const searchAnakForm = document.getElementById('grafik-anak-form');

    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = String(str ?? '');
        return d.innerHTML;
    }

    function renderGrafikAnakResults(matches) {
        if (matches.length === 0) {
            searchAnakResults.innerHTML = '<div class="antro-search-result-item" style="color: var(--text-muted); cursor: default;">Tidak ditemukan anak yang cocok.</div>';
        } else {
            searchAnakResults.innerHTML = matches.map((item, i) => `
                <div class="antro-search-result-item" data-idx="${i}">
                    <div class="antro-search-result-name">${escHtml(item.nama)}</div>
                    <div class="antro-search-result-meta">NIK: ${escHtml(item.nik_anak) || '-'} &middot; ${item.jenis_kelamin === 'P' ? 'Perempuan' : 'Laki-laki'}</div>
                </div>
            `).join('');
            searchAnakResults.querySelectorAll('.antro-search-result-item[data-idx]').forEach(function (el) {
                el.addEventListener('click', function () {
                    const item = matches[Number(el.getAttribute('data-idx'))];
                    searchAnakIdInput.value = item.id;
                    searchAnakInput.value = `${item.nama} (${item.nik_anak || '-'})`;
                    searchAnakResults.style.display = 'none';
                    searchAnakForm.submit();
                });
            });
        }
        searchAnakResults.style.display = 'block';
    }

    if (searchAnakInput) {
        searchAnakInput.addEventListener('input', function () {
            const q = this.value.trim().toLowerCase();
            searchAnakIdInput.value = '';
            if (q.length < 2) { searchAnakResults.style.display = 'none'; return; }

            const matches = grafikAnakData.filter(function (item) {
                return (item.nama && item.nama.toLowerCase().includes(q)) ||
                    (item.nik_anak && item.nik_anak.toLowerCase().includes(q));
            }).slice(0, 20);

            renderGrafikAnakResults(matches);
        });

        searchAnakInput.addEventListener('focus', function () {
            if (this.value.trim().length >= 2) { this.dispatchEvent(new Event('input')); }
        });

        document.addEventListener('click', function (e) {
            if (!searchAnakInput.contains(e.target) && !searchAnakResults.contains(e.target)) {
                searchAnakResults.style.display = 'none';
            }
        });
    }

    @if($grafik)
        const standarBb = @json($grafik['standar_bb']);
        const standarTb = @json($grafik['standar_tb']);
        const titik = @json($grafik['titik']);

        const getGridColor = () => document.documentElement.classList.contains('light-theme') ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.05)';
        const getLegendColor = () => document.documentElement.classList.contains('light-theme') ? '#475569' : '#94a3b8';

        function buildStandarDatasets(standar, colorMedian) {
            return [
                { label: '-3 SD', data: standar.map(r => ({x: r.bulan, y: r.sd3neg})), borderColor: 'rgba(239,68,68,0.5)', borderDash: [4,4], pointRadius: 0, borderWidth: 1.5, fill: false, tension: 0.3 },
                { label: '-2 SD', data: standar.map(r => ({x: r.bulan, y: r.sd2neg})), borderColor: 'rgba(245,158,11,0.6)', borderDash: [4,4], pointRadius: 0, borderWidth: 1.5, fill: false, tension: 0.3 },
                { label: 'Median', data: standar.map(r => ({x: r.bulan, y: r.median})), borderColor: colorMedian, pointRadius: 0, borderWidth: 2, fill: false, tension: 0.3 },
                { label: '+2 SD', data: standar.map(r => ({x: r.bulan, y: r.sd2})), borderColor: 'rgba(245,158,11,0.6)', borderDash: [4,4], pointRadius: 0, borderWidth: 1.5, fill: false, tension: 0.3 },
                { label: '+3 SD', data: standar.map(r => ({x: r.bulan, y: r.sd3})), borderColor: 'rgba(239,68,68,0.5)', borderDash: [4,4], pointRadius: 0, borderWidth: 1.5, fill: false, tension: 0.3 },
            ];
        }

        function baseOptions(yTitle) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'nearest' },
                plugins: { legend: { labels: { color: getLegendColor(), font: { family: 'Inter', size: 11 }, boxWidth: 12 } } },
                scales: {
                    x: { type: 'linear', title: { display: true, text: 'Umur (bulan)', color: getLegendColor() }, grid: { color: getGridColor() }, ticks: { color: '#64748b' } },
                    y: { title: { display: true, text: yTitle, color: getLegendColor() }, grid: { color: getGridColor() }, ticks: { color: '#64748b' } },
                },
            };
        }

        new Chart(document.getElementById('chartBbU').getContext('2d'), {
            type: 'line',
            data: {
                datasets: [
                    ...buildStandarDatasets(standarBb, '#3b82f6'),
                    { label: 'Anak (BB)', data: titik.map(t => ({x: t.umur_bulan, y: t.weight_kg})), borderColor: '#10b981', backgroundColor: '#10b981', pointRadius: 4, borderWidth: 2, showLine: true, tension: 0 },
                ],
            },
            options: baseOptions('Berat Badan (kg)'),
        });

        new Chart(document.getElementById('chartTbU').getContext('2d'), {
            type: 'line',
            data: {
                datasets: [
                    ...buildStandarDatasets(standarTb, '#8b5cf6'),
                    { label: 'Anak (PB/TB)', data: titik.map(t => ({x: t.umur_bulan, y: t.height_cm})), borderColor: '#10b981', backgroundColor: '#10b981', pointRadius: 4, borderWidth: 2, showLine: true, tension: 0 },
                ],
            },
            options: baseOptions('Panjang/Tinggi Badan (cm)'),
        });
    @endif
});
</script>
@endpush

{{--
    Kartu ringkasan Status Gizi Lengkap (BB/U, PB/U-TB/U, BB/PB-BB/TB, IMT/U)
    sesuai Standar Antropometri Anak - Permenkes RI No. 2 Tahun 2020.
    Variabel: $statusLengkap (array|null, hasil dari Measurement::antropometriLengkap()).
--}}
@if($statusLengkap)
    <div class="antro-status-grid">
        <div class="antro-status-card">
            <div class="antro-status-label">BB/U (Berat Badan menurut Umur)</div>
            <div class="antro-status-z">{{ $statusLengkap['bb_u']['z'] !== null ? number_format($statusLengkap['bb_u']['z'], 2) : '-' }} SD</div>
            <span class="severity-pill severity-{{ $statusLengkap['bb_u']['severity'] }}">{{ $statusLengkap['bb_u']['label'] }}</span>
        </div>
        <div class="antro-status-card">
            <div class="antro-status-label">PB/U atau TB/U</div>
            <div class="antro-status-z">{{ $statusLengkap['pb_tb_u']['z'] !== null ? number_format($statusLengkap['pb_tb_u']['z'], 2) : '-' }} SD</div>
            <span class="severity-pill severity-{{ $statusLengkap['pb_tb_u']['severity'] }}">{{ $statusLengkap['pb_tb_u']['label'] }}</span>
        </div>
        <div class="antro-status-card">
            <div class="antro-status-label">BB/PB atau BB/TB</div>
            <div class="antro-status-z">{{ $statusLengkap['bb_pb_tb']['z'] !== null ? number_format($statusLengkap['bb_pb_tb']['z'], 2) : '-' }} SD</div>
            <span class="severity-pill severity-{{ $statusLengkap['bb_pb_tb']['severity'] }}">{{ $statusLengkap['bb_pb_tb']['label'] }}</span>
        </div>
        <div class="antro-status-card">
            <div class="antro-status-label">IMT/U {{ $statusLengkap['imt'] ? '· '.$statusLengkap['imt'].' kg/m'.'²' : '' }}</div>
            <div class="antro-status-z">{{ $statusLengkap['imt_u']['z'] !== null ? number_format($statusLengkap['imt_u']['z'], 2) : '-' }} SD</div>
            <span class="severity-pill severity-{{ $statusLengkap['imt_u']['severity'] }}">{{ $statusLengkap['imt_u']['label'] }}</span>
        </div>
    </div>
    <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 12px;">
        Dihitung sesuai Standar Antropometri Anak — Permenkes RI No. 2 Tahun 2020 (WHO Child Growth Standards).
    </div>
@else
    <div style="font-size: 13px; color: var(--text-muted);">
        Data umur, tinggi, atau berat badan belum lengkap untuk menghitung status gizi.
    </div>
@endif

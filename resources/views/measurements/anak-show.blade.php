@extends('layouts.main')

@push('styles')
<style>
    .growth-chart-card {
        margin-bottom: 24px;
    }

    .growth-chart-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .growth-chart-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .growth-chart-tab {
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid var(--border-glass);
        background: transparent;
        color: var(--text-secondary);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .growth-chart-tab:hover {
        color: var(--text-primary);
        background: var(--bg-glass);
    }

    .growth-chart-tab.active {
        background: var(--gradient-1);
        color: white;
        border-color: transparent;
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.2);
    }

    .growth-chart-meta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 999px;
        background: var(--bg-glass);
        color: var(--text-secondary);
        font-size: 12px;
        border: 1px solid var(--border-glass);
    }

    .growth-chart-wrap {
        position: relative;
        height: 340px;
        width: 100%;
    }

    @media (max-width: 768px) {
        .growth-chart-wrap {
            height: 300px;
        }
    }
</style>
@endpush

@section('content')
@php
    $photoMeasurement = $anak->latestPhotoMeasurement;
    $chartPoints = $anak->measurements->map(fn ($measurement) => [
        'label' => $measurement->measured_at->translatedFormat('d M Y'),
        'height' => (float) $measurement->height_cm,
        'weight' => (float) $measurement->weight_kg,
        'zscore' => $measurement->z_score !== null ? (float) $measurement->z_score : null,
        'status' => $measurement->stunting_category,
    ])->values();
@endphp
<div class="page-header flex-between">
    <div>
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px; flex-wrap: wrap;">
            <a href="{{ route('measurements.index') }}" class="btn btn-secondary btn-sm">← Kembali ke Riwayat</a>
            <span class="badge" style="background: rgba(59, 130, 246, 0.12); color: var(--accent-blue);">
                {{ $anak->nik_anak ?: 'NIK belum tersedia' }}
            </span>
        </div>
        <h1 class="page-title">{{ $anak->nama }}</h1>
        <p class="page-subtitle">Perkembangan pengukuran anak dalam satu halaman</p>
    </div>
    <a href="{{ route('measurements.create', ['anak_id' => $anak->id]) }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Pengukuran Baru
    </a>
</div>

    <div class="detail-grid" style="margin-bottom: 24px;">
    <div class="glass-card fade-in">
        <div class="chart-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-blue)" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            Profil Anak
        </div>

        <div style="display: grid; grid-template-columns: minmax(160px, 220px) 1fr; gap: 20px; margin-top: 16px; align-items: start;">
            <div>
                @if($photoMeasurement?->photo_path)
                    <div style="border-radius: 18px; overflow: hidden; border: 1px solid var(--border-glass); background: var(--bg-glass); aspect-ratio: 4 / 5;">
                        <img src="{{ Storage::disk('r2')->url($photoMeasurement->photo_path) }}" alt="Foto {{ $anak->nama }}" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 8px; text-align: center;">
                        Foto dari pengukuran {{ $photoMeasurement->measured_at->translatedFormat('d M Y') }}
                    </div>
                @else
                    <div style="border-radius: 18px; border: 1px dashed var(--border-glass); background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(124, 58, 237, 0.08)); aspect-ratio: 4 / 5; display: flex; align-items: center; justify-content: center; flex-direction: column; color: var(--text-muted);">
                        <div style="font-size: 48px; line-height: 1;">👶</div>
                        <div style="font-size: 12px; margin-top: 10px;">Belum ada foto pengukuran</div>
                    </div>
                @endif
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px;">
            <div>
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Tanggal Lahir</div>
                <div style="font-weight: 700;">{{ $anak->tanggal_lahir?->translatedFormat('d F Y') ?: '-' }}</div>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Jenis Kelamin</div>
                <div style="font-weight: 700;">{{ $anak->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Usia Saat Ini</div>
                <div style="font-weight: 700;">{{ $anak->umur['formatted'] }}</div>
            </div>
            <div>
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Posyandu</div>
                <div style="font-weight: 700;">{{ $anak->posyandu?->nama ?: '-' }}</div>
            </div>
            </div>
        </div>
    </div>

    <div class="glass-card fade-in">
        <div class="chart-title">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-green)" stroke-width="2">
                <path d="M3 3v18h18"></path>
                <path d="M19 9l-5 5-4-4-3 3"></path>
            </svg>
            Ringkasan Perkembangan
        </div>

        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 16px;">
            <div style="padding: 16px; border-radius: var(--radius-sm); background: var(--bg-glass); border: 1px solid var(--border-glass);">
                <div style="font-size: 12px; color: var(--text-muted);">Total Pengukuran</div>
                <div style="font-size: 28px; font-weight: 800; margin-top: 6px;">{{ $anak->measurements->count() }}</div>
            </div>
            <div style="padding: 16px; border-radius: var(--radius-sm); background: var(--bg-glass); border: 1px solid var(--border-glass);">
                <div style="font-size: 12px; color: var(--text-muted);">Pengukuran Terakhir</div>
                <div style="font-size: 14px; font-weight: 700; margin-top: 6px;">
                    {{ $latestMeasurement?->measured_at?->translatedFormat('d M Y') ?: '-' }}
                </div>
            </div>
            <div style="padding: 16px; border-radius: var(--radius-sm); background: var(--bg-glass); border: 1px solid var(--border-glass);">
                <div style="font-size: 12px; color: var(--text-muted);">Status Terakhir</div>
                @if($latestMeasurement)
                    <span class="badge badge-{{ strtolower(str_replace(' ', '-', $latestMeasurement->stunting_category)) }}" style="margin-top: 8px;">
                        {{ $latestMeasurement->stunting_category }}
                    </span>
                @else
                    <div style="font-size: 14px; font-weight: 700; margin-top: 6px;">-</div>
                @endif
            </div>
        </div>

        @if($anak->measurements->contains(fn ($measurement) => (int) $measurement->user_id !== (int) auth()->id()))
            <div style="margin-top: 16px; padding: 12px 14px; border-radius: var(--radius-sm); background: rgba(59, 130, 246, 0.08); border: 1px solid rgba(59, 130, 246, 0.16); color: var(--text-secondary); font-size: 13px; line-height: 1.6;">
                Riwayat ini menampilkan seluruh pengukuran anak di posyandu yang sama. Catatan dari petugas lain tetap bisa dilihat, tetapi tidak bisa dihapus dari akun Anda.
            </div>
        @endif
    </div>
</div>

@if($latestMeasurement)
<div class="glass-card fade-in" style="margin-bottom: 24px;">
    <div class="chart-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-purple)" stroke-width="2">
            <path d="M3 3v18h18"/>
            <path d="M18.7 8l-5.1 5.1-2.8-2.8L7 14"/>
        </svg>
        Status Gizi Lengkap Terkini (Permenkes RI No. 2 Tahun 2020)
    </div>
    @include('antropometri.partials.status-lengkap', ['statusLengkap' => $latestMeasurement->antropometriLengkap()])
</div>
@endif

@if($anak->measurements->isNotEmpty())
<div class="glass-card fade-in growth-chart-card">
    <div class="growth-chart-toolbar">
        <div>
            <div class="chart-title" style="margin-bottom: 6px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-pink)" stroke-width="2">
                    <path d="M3 3v18h18"></path>
                    <path d="M19 9l-5 5-4-4-3 3"></path>
                </svg>
                Grafik Perkembangan
            </div>
            <div style="font-size: 13px; color: var(--text-muted);">
                Pantau perubahan berat badan, tinggi badan, dan Z-Score anak dari waktu ke waktu.
            </div>
        </div>
        <div class="growth-chart-meta">
            <span>{{ $anak->measurements->count() }} titik data</span>
            <span>•</span>
            <span>{{ $anak->measurements->first()?->measured_at?->translatedFormat('d M Y') }}</span>
            <span>s/d</span>
            <span>{{ $anak->measurements->last()?->measured_at?->translatedFormat('d M Y') }}</span>
        </div>
    </div>

    <div class="growth-chart-tabs">
        <button type="button" class="growth-chart-tab active" data-chart-type="weight">Berat Badan</button>
        <button type="button" class="growth-chart-tab" data-chart-type="height">Tinggi Badan</button>
        <button type="button" class="growth-chart-tab" data-chart-type="zscore">Z-Score</button>
    </div>

    <div class="growth-chart-wrap" style="margin-top: 18px;">
        <canvas id="growthChart"></canvas>
    </div>
</div>
@endif

<div class="glass-card fade-in">
    <div class="chart-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-purple)" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
        </svg>
        Riwayat Pengukuran
    </div>

    <form method="GET" action="{{ route('measurements.anak.show', $anak) }}" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin-top: 16px; padding-bottom: 16px; border-bottom: 1px dashed var(--border-glass);">
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 140px;">
            <label class="form-label" style="font-size: 12px;">Dari Tanggal</label>
            <input type="date" name="from" class="form-input" value="{{ request('from') }}">
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 140px;">
            <label class="form-label" style="font-size: 12px;">Sampai Tanggal</label>
            <input type="date" name="to" class="form-input" value="{{ request('to') }}">
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 160px;">
            <label class="form-label" style="font-size: 12px;">Kategori Stunting</label>
            <select name="stunting_category" class="form-input">
                <option value="">Semua Kategori</option>
                <option value="Normal" {{ request('stunting_category') === 'Normal' ? 'selected' : '' }}>Normal</option>
                <option value="Stunting" {{ request('stunting_category') === 'Stunting' ? 'selected' : '' }}>Stunting</option>
                <option value="Sangat Stunting" {{ request('stunting_category') === 'Sangat Stunting' ? 'selected' : '' }}>Sangat Stunting</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 170px;">
            <label class="form-label" style="font-size: 12px;">Status Gizi (Permenkes)</label>
            <select name="gizi_status" class="form-input">
                <option value="">Semua</option>
                <option value="normal" {{ request('gizi_status') === 'normal' ? 'selected' : '' }}>Semua indikator normal</option>
                <option value="bermasalah" {{ request('gizi_status') === 'bermasalah' ? 'selected' : '' }}>Ada indikator bermasalah</option>
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
            @if($hasHistoryFilter)
                <a href="{{ route('measurements.anak.show', $anak) }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </div>
    </form>

    @if($anak->measurements->isNotEmpty())
        <div style="overflow-x: auto; margin-top: 16px;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Tinggi</th>
                        <th>Berat</th>
                        <th>Z-Score</th>
                        <th>Status</th>
                        <th>BB/U</th>
                        <th>BB/PB atau BB/TB</th>
                        <th>IMT/U</th>
                        <th>Petugas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($anak->measurements as $measurement)
                        @php
                            $canDelete = (int) $measurement->user_id === (int) auth()->id();
                            $antro = $measurement->antropometriLengkap();
                        @endphp
                        <tr>
                            <td style="color: var(--text-muted);">{{ $loop->iteration }}</td>
                            <td>
                                <div>{{ $measurement->measured_at->translatedFormat('d M Y') }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $measurement->measured_at->format('H:i') }}</div>
                            </td>
                            <td>
                                <strong>{{ number_format($measurement->height_cm, 2) }}</strong> <span style="color: var(--text-muted);">cm</span>
                                @if($measurement->manual_height_cm)
                                    <div style="font-size: 11px; color: var(--text-muted);">Manual: {{ number_format($measurement->manual_height_cm, 2) }} cm</div>
                                @endif
                            </td>
                            <td>
                                <strong>{{ number_format($measurement->weight_kg, 2) }}</strong> <span style="color: var(--text-muted);">kg</span>
                                @if($measurement->manual_weight_kg)
                                    <div style="font-size: 11px; color: var(--text-muted);">Manual: {{ number_format($measurement->manual_weight_kg, 2) }} kg</div>
                                @endif
                            </td>
                            <td><strong>{{ number_format($measurement->z_score, 2) }}</strong></td>
                            <td>
                                <span class="badge badge-{{ strtolower(str_replace(' ', '-', $measurement->stunting_category)) }}">
                                    {{ $measurement->stunting_category }}
                                </span>
                            </td>
                            <td>
                                @if($antro)
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ number_format($antro['bb_u']['z'], 2) }} SD</div>
                                    <span class="severity-pill severity-{{ $antro['bb_u']['severity'] }}">{{ $antro['bb_u']['label'] }}</span>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td>
                                @if($antro)
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ $antro['bb_pb_tb']['z'] !== null ? number_format($antro['bb_pb_tb']['z'], 2).' SD' : '-' }}</div>
                                    <span class="severity-pill severity-{{ $antro['bb_pb_tb']['severity'] }}">{{ $antro['bb_pb_tb']['label'] }}</span>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td>
                                @if($antro)
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ $antro['imt'] ?? '-' }} kg/m&sup2; &middot; {{ $antro['imt_u']['z'] !== null ? number_format($antro['imt_u']['z'], 2).' SD' : '-' }}</div>
                                    <span class="severity-pill severity-{{ $antro['imt_u']['severity'] }}">{{ $antro['imt_u']['label'] }}</span>
                                @else
                                    <span style="color: var(--text-muted);">-</span>
                                @endif
                            </td>
                            <td>{{ $measurement->user->petugasProfile?->nama_lengkap ?? $measurement->user->name }}</td>
                            <td>
                                <div style="display: flex; gap: 6px;">
                                    <a href="{{ route('measurements.show', $measurement) }}" class="btn btn-secondary btn-sm">Detail</a>
                                    @if($canDelete)
                                        <form method="POST" action="{{ route('measurements.destroy', $measurement) }}" onsubmit="return confirm('Yakin hapus pengukuran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state" style="margin-top: 16px;">
            <div class="empty-state-icon">📋</div>
            @if($hasHistoryFilter)
                <h3>Tidak ada pengukuran yang cocok dengan filter</h3>
                <p>Coba ubah rentang tanggal atau kategori filter yang dipilih.</p>
            @else
                <h3>Belum ada pengukuran untuk anak ini</h3>
                <p>Tambahkan pengukuran pertama untuk mulai memantau perkembangannya.</p>
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
@if($anak->measurements->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const growthCanvas = document.getElementById('growthChart');
    const chartTabs = Array.from(document.querySelectorAll('.growth-chart-tab'));

    if (!growthCanvas || chartTabs.length === 0 || typeof Chart === 'undefined') {
        return;
    }

    const chartPoints = @json($chartPoints);

    const getGridColor = () => document.documentElement.classList.contains('light-theme') ? 'rgba(15, 23, 42, 0.08)' : 'rgba(255, 255, 255, 0.06)';
    const getTickColor = () => document.documentElement.classList.contains('light-theme') ? '#475569' : '#94a3b8';
    const getTooltipBg = () => document.documentElement.classList.contains('light-theme') ? 'rgba(255,255,255,0.96)' : 'rgba(15,23,42,0.92)';
    const getTooltipTitle = () => document.documentElement.classList.contains('light-theme') ? '#0f172a' : '#f8fafc';
    const getTooltipBody = () => document.documentElement.classList.contains('light-theme') ? '#334155' : '#cbd5e1';

    const chartConfigMap = {
        weight: {
            label: 'Berat Badan (kg)',
            color: '#0ea5e9',
            fill: 'rgba(14, 165, 233, 0.12)',
            unit: 'kg',
            values: chartPoints.map(point => point.weight),
        },
        height: {
            label: 'Tinggi Badan (cm)',
            color: '#8b5cf6',
            fill: 'rgba(139, 92, 246, 0.12)',
            unit: 'cm',
            values: chartPoints.map(point => point.height),
        },
        zscore: {
            label: 'Z-Score',
            color: '#ec4899',
            fill: 'rgba(236, 72, 153, 0.12)',
            unit: '',
            values: chartPoints.map(point => point.zscore),
        },
    };

    const labels = chartPoints.map(point => point.label);
    const ctx = growthCanvas.getContext('2d');
    let growthChart = null;
    let activeType = 'weight';

    function buildChart(type) {
        const metric = chartConfigMap[type];

        if (growthChart) {
            growthChart.destroy();
        }

        const datasets = [{
            label: metric.label,
            data: metric.values,
            borderColor: metric.color,
            backgroundColor: metric.fill,
            fill: true,
            borderWidth: 3,
            tension: 0.35,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: metric.color,
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            spanGaps: true,
        }];

        if (type === 'zscore') {
            datasets.push(
                {
                    label: 'Batas Stunting (-2)',
                    data: labels.map(() => -2),
                    borderColor: 'rgba(245, 158, 11, 0.9)',
                    borderDash: [8, 6],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                },
                {
                    label: 'Batas Sangat Stunting (-3)',
                    data: labels.map(() => -3),
                    borderColor: 'rgba(239, 68, 68, 0.9)',
                    borderDash: [8, 6],
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                }
            );
        }

        growthChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets,
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: {
                        display: type === 'zscore',
                        labels: {
                            color: getTickColor(),
                            font: { family: 'Inter' },
                            usePointStyle: true,
                            boxWidth: 8,
                        }
                    },
                    tooltip: {
                        backgroundColor: getTooltipBg(),
                        titleColor: getTooltipTitle(),
                        bodyColor: getTooltipBody(),
                        borderColor: getGridColor(),
                        borderWidth: 1,
                        padding: 12,
                        callbacks: {
                            label: function (context) {
                                const value = context.parsed.y;
                                if (value === null || value === undefined) {
                                    return context.dataset.label + ': -';
                                }

                                if (type === 'zscore' && context.datasetIndex === 0) {
                                    const point = chartPoints[context.dataIndex];
                                    return `${context.dataset.label}: ${value.toFixed(2)} (${point.status || 'Tanpa status'})`;
                                }

                                if (type === 'zscore') {
                                    return `${context.dataset.label}: ${value.toFixed(1)}`;
                                }

                                return `${context.dataset.label}: ${value.toFixed(1)} ${metric.unit}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: getGridColor() },
                        ticks: { color: getTickColor(), font: { family: 'Inter' } }
                    },
                    y: {
                        grid: { color: getGridColor() },
                        ticks: {
                            color: getTickColor(),
                            font: { family: 'Inter' },
                            callback: function (value) {
                                return type === 'zscore'
                                    ? Number(value).toFixed(1)
                                    : `${Number(value).toFixed(1)} ${metric.unit}`;
                            }
                        },
                        title: {
                            display: true,
                            text: metric.label,
                            color: getTickColor(),
                            font: { family: 'Inter', weight: '600' }
                        }
                    }
                }
            }
        });
    }

    chartTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const selectedType = this.dataset.chartType;

            if (!selectedType || selectedType === activeType) {
                return;
            }

            activeType = selectedType;
            chartTabs.forEach(item => item.classList.remove('active'));
            this.classList.add('active');
            buildChart(activeType);
        });
    });

    window.addEventListener('themeToggled', () => buildChart(activeType));

    buildChart(activeType);
});
</script>
@endif
@endpush

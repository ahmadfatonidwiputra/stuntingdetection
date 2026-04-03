@extends('layouts.main')

@section('content')
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

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-top: 16px;">
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
                        <th>Petugas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($anak->measurements as $measurement)
                        @php
                            $canDelete = (int) $measurement->user_id === (int) auth()->id();
                        @endphp
                        <tr>
                            <td style="color: var(--text-muted);">{{ $loop->iteration }}</td>
                            <td>
                                <div>{{ $measurement->measured_at->translatedFormat('d M Y') }}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $measurement->measured_at->format('H:i') }}</div>
                            </td>
                            <td><strong>{{ number_format($measurement->height_cm, 1) }}</strong> <span style="color: var(--text-muted);">cm</span></td>
                            <td><strong>{{ number_format($measurement->weight_kg, 1) }}</strong> <span style="color: var(--text-muted);">kg</span></td>
                            <td><strong>{{ number_format($measurement->z_score, 2) }}</strong></td>
                            <td>
                                <span class="badge badge-{{ strtolower(str_replace(' ', '-', $measurement->stunting_category)) }}">
                                    {{ $measurement->stunting_category }}
                                </span>
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
            <h3>Belum ada pengukuran untuk anak ini</h3>
            <p>Tambahkan pengukuran pertama untuk mulai memantau perkembangannya.</p>
        </div>
    @endif
</div>
@endsection

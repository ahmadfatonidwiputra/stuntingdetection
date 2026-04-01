@extends('layouts.main')

@section('content')
<div class="page-header">
    <div class="flex-between">
        <div>
            <h1 class="page-title">Detail Petugas</h1>
            <p class="page-subtitle">Informasi lengkap petugas posyandu</p>
        </div>
        <a href="{{ route('super-admin.petugas.index') }}" class="btn btn-secondary">← Kembali</a>
    </div>
</div>

<div class="detail-grid">
    <!-- Left: Profile Info -->
    <div>
        <div class="glass-card fade-in" style="margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 24px;">
                <div style="width: 64px; height: 64px; border-radius: 16px; background: var(--gradient-4); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 700; flex-shrink: 0;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 style="font-size: 20px; font-weight: 700;">{{ $user->petugasProfile?->nama_lengkap ?? $user->name }}</h2>
                    <div style="color: var(--text-muted); font-size: 14px;">{{ $user->email }}</div>
                    <div style="margin-top: 6px;">
                        @if($user->status === 'active')
                            <span class="badge badge-normal">✅ Aktif</span>
                        @elseif($user->status === 'pending')
                            <span class="badge badge-stunting">⏳ Menunggu Verifikasi</span>
                        @elseif($user->status === 'rejected')
                            <span class="badge badge-sangat-stunting">❌ Ditolak</span>
                        @elseif($user->status === 'suspended')
                            <span class="badge" style="background: rgba(100,116,139,0.15); color: #94a3b8;">⛔ Suspended</span>
                        @endif
                    </div>
                </div>
            </div>

            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 16px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Data Pribadi</h3>
            <div class="detail-info">
                <div class="detail-row">
                    <span class="detail-row-label">NIK</span>
                    <span class="detail-row-value">{{ $user->petugasProfile?->nik ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-row-label">No. Telepon</span>
                    <span class="detail-row-value">{{ $user->petugasProfile?->no_telepon ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-row-label">Tanggal Daftar</span>
                    <span class="detail-row-value">{{ $user->created_at->format('d M Y, H:i') }}</span>
                </div>
                @if($user->petugasProfile?->verified_at)
                <div class="detail-row">
                    <span class="detail-row-label">Diverifikasi Pada</span>
                    <span class="detail-row-value">{{ $user->petugasProfile->verified_at->format('d M Y, H:i') }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="glass-card fade-in">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 16px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Data Posyandu</h3>
            <div class="detail-info">
                <div class="detail-row">
                    <span class="detail-row-label">Nama Posyandu</span>
                    <span class="detail-row-value">{{ $user->petugasProfile?->posyandu_name ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-row-label">Alamat</span>
                    <span class="detail-row-value">{{ $user->petugasProfile?->posyandu_address ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-row-label">Kelurahan</span>
                    <span class="detail-row-value">{{ $user->petugasProfile?->kelurahan ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-row-label">Kecamatan</span>
                    <span class="detail-row-value">{{ $user->petugasProfile?->kecamatan ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-row-label">Kota / Kabupaten</span>
                    <span class="detail-row-value">{{ $user->petugasProfile?->kota ?? '-' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-row-label">Provinsi</span>
                    <span class="detail-row-value">{{ $user->petugasProfile?->provinsi ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Actions & Stats -->
    <div>
        <!-- Stats -->
        <div class="glass-card fade-in" style="margin-bottom: 24px;">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 16px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Statistik</h3>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div style="text-align: center; padding: 20px; background: var(--bg-glass); border-radius: var(--radius-sm); border: 1px solid var(--border-glass);">
                    <div style="font-size: 28px; font-weight: 800; color: var(--accent-blue);">{{ $childCount }}</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Anak Tercatat</div>
                </div>
                <div style="text-align: center; padding: 20px; background: var(--bg-glass); border-radius: var(--radius-sm); border: 1px solid var(--border-glass);">
                    <div style="font-size: 28px; font-weight: 800; color: var(--accent-purple);">{{ $measurementCount }}</div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Pemeriksaan</div>
                </div>
            </div>
        </div>

        <!-- Document -->
        @if($user->petugasProfile?->document_path)
        <div class="glass-card fade-in" style="margin-bottom: 24px;">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 16px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">📄 Dokumen</h3>
            <a href="{{ Storage::url($user->petugasProfile->document_path) }}" target="_blank" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                Lihat Dokumen →
            </a>
        </div>
        @endif

        <!-- Rejection Reason -->
        @if($user->status === 'rejected' && $user->petugasProfile?->rejection_reason)
        <div class="glass-card fade-in" style="margin-bottom: 24px; border-left: 3px solid var(--accent-red);">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 12px; color: var(--accent-red);">❌ Alasan Penolakan</h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">{{ $user->petugasProfile->rejection_reason }}</p>
        </div>
        @endif

        <!-- Actions -->
        <div class="glass-card fade-in">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 16px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">Aksi</h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">

                @if($user->status === 'pending')
                    <form method="POST" action="{{ route('super-admin.petugas.approve', $user) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm" style="width: 100%; justify-content: center; background: rgba(16,185,129,0.15); color: var(--accent-green); border: 1px solid rgba(16,185,129,0.3);">✅ Setujui Petugas</button>
                    </form>

                    <!-- Reject with reason -->
                    <button onclick="document.getElementById('rejectModal').style.display='flex'" class="btn btn-danger btn-sm" style="width: 100%; justify-content: center;">❌ Tolak Registrasi</button>
                @endif

                @if($user->status === 'active')
                    <form method="POST" action="{{ route('super-admin.petugas.suspend', $user) }}" onsubmit="return confirm('Yakin ingin men-suspend akun ini?')">
                        @csrf
                        <button type="submit" class="btn btn-sm" style="width: 100%; justify-content: center; background: rgba(245,158,11,0.15); color: var(--accent-orange); border: 1px solid rgba(245,158,11,0.3);">⛔ Suspend Akun</button>
                    </form>
                @endif

                @if($user->status === 'rejected' || $user->status === 'suspended')
                    <form method="POST" action="{{ route('super-admin.petugas.reactivate', $user) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm" style="width: 100%; justify-content: center; background: rgba(16,185,129,0.15); color: var(--accent-green); border: 1px solid rgba(16,185,129,0.3);">✅ Aktifkan Kembali</button>
                    </form>
                @endif

                <form method="POST" action="{{ route('super-admin.petugas.destroy', $user) }}" onsubmit="return confirm('PERINGATAN: Aksi ini akan menghapus akun petugas beserta seluruh data terkait. Lanjutkan?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" style="width: 100%; justify-content: center;">🗑️ Hapus Akun</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; padding: 24px;">
    <div class="glass-card" style="max-width: 500px; width: 100%; background: var(--bg-secondary);">
        <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Tolak Registrasi</h3>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">Berikan alasan penolakan yang jelas agar petugas dapat memperbaiki kekurangan.</p>

        <form method="POST" action="{{ route('super-admin.petugas.reject', $user) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Alasan Penolakan *</label>
                <textarea name="rejection_reason" class="form-textarea" required placeholder="Contoh: Dokumen surat tugas belum dilampirkan. Mohon upload SK pengangkatan dari kelurahan." style="min-height: 120px;"></textarea>
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('rejectModal').style.display='none'" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-danger btn-sm">Tolak Registrasi</button>
            </div>
        </form>
    </div>
</div>
@endsection

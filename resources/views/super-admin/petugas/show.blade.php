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
        <!-- Password Baru (hanya tampil sekali setelah reset) -->
        @if(session('new_password'))
        <div class="glass-card fade-in" style="margin-bottom: 24px; border-left: 3px solid var(--accent-green);">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 8px; color: var(--accent-green);">🔑 Password Baru</h3>
            <p style="color: var(--text-muted); font-size: 13px; line-height: 1.6; margin-bottom: 14px;">
                Catat atau salin sekarang, lalu serahkan ke petugas. Password ini <strong>hanya ditampilkan satu kali</strong> dan tidak bisa dilihat lagi setelah halaman dimuat ulang.
            </p>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <code style="flex: 1; min-width: 160px; padding: 12px 14px; background: var(--bg-glass); border: 1px solid var(--border-glass); border-radius: var(--radius-sm); font-size: 18px; font-weight: 700; letter-spacing: 2px; font-family: ui-monospace, Menlo, Consolas, monospace;">{{ session('new_password') }}</code>
                <button type="button" class="btn btn-secondary btn-sm" data-copy="{{ session('new_password') }}">📋 Salin</button>
            </div>
        </div>
        @endif

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
            
            @php
                $ext = pathinfo($user->petugasProfile->document_path, PATHINFO_EXTENSION);
            @endphp
            
            @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                <div style="width: 100%; border-radius: 8px; overflow: hidden; margin-bottom: 12px; border: 1px solid var(--border-glass);">
                    <img src="{{ Storage::disk('r2')->url($user->petugasProfile->document_path) }}" alt="Dokumen" style="width: 100%; height: auto; display: block;">
                </div>
            @else
                <div style="width: 100%; border-radius: 8px; overflow: hidden; margin-bottom: 12px; border: 1px solid var(--border-glass); height: 400px;">
                    <iframe src="{{ Storage::disk('r2')->url($user->petugasProfile->document_path) }}" width="100%" height="100%" style="border: none;"></iframe>
                </div>
            @endif
            
            <a href="{{ Storage::disk('r2')->url($user->petugasProfile->document_path) }}" target="_blank" class="btn btn-secondary" style="width: 100%; justify-content: center;">
                Buka di Tab Baru →
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

        <!-- Kredensial Akun -->
        <div class="glass-card fade-in" style="margin-bottom: 24px;">
            <h3 style="font-size: 14px; font-weight: 700; margin-bottom: 16px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">🔐 Kredensial Akun</h3>
            <div class="detail-info">
                <div class="detail-row">
                    <span class="detail-row-label">Username (Login)</span>
                    <span class="detail-row-value" style="display: flex; align-items: center; gap: 8px; justify-content: flex-end;">
                        <span style="word-break: break-all;">{{ $user->email }}</span>
                        <button type="button" class="btn btn-secondary btn-sm" style="padding: 4px 10px;" data-copy="{{ $user->email }}">📋</button>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-row-label">Password</span>
                    <span class="detail-row-value" style="letter-spacing: 3px;">••••••••••</span>
                </div>
                <div class="detail-row">
                    <span class="detail-row-label">Data Diperbarui</span>
                    <span class="detail-row-value">{{ $user->updated_at?->format('d M Y, H:i') ?? '-' }}</span>
                </div>
            </div>

            <p style="margin-top: 14px; color: var(--text-muted); font-size: 12px; line-height: 1.7;">
                ℹ️ Password disimpan sebagai <strong>hash bcrypt</strong> — tidak dapat ditampilkan kembali oleh siapa pun, termasuk super admin. Jika petugas lupa password, buatkan password baru lewat tombol di bawah.
            </p>

            <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 16px;">
                <button type="button" onclick="document.getElementById('resetPasswordModal').style.display='flex'" class="btn btn-sm" style="width: 100%; justify-content: center; background: rgba(59,130,246,0.15); color: var(--accent-blue); border: 1px solid rgba(59,130,246,0.3);">🔑 Reset Password</button>
                <button type="button" onclick="document.getElementById('usernameModal').style.display='flex'" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center;">✏️ Ubah Username</button>
            </div>
        </div>

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
                    <form method="POST" action="{{ route('super-admin.petugas.suspend', $user) }}" data-confirm="Yakin ingin men-suspend akun {{ $user->name }}?">
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

                <form method="POST" action="{{ route('super-admin.petugas.destroy', $user) }}" data-confirm="PERINGATAN: Aksi ini akan menghapus akun petugas {{ $user->name }} beserta seluruh data terkait. Tindakan ini tidak dapat dibatalkan.">
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

<!-- Reset Password Modal -->
<div id="resetPasswordModal" style="display: {{ $errors->hasAny(['password', 'mode']) ? 'flex' : 'none' }}; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; padding: 24px;">
    <div class="glass-card" style="max-width: 500px; width: 100%; background: var(--bg-secondary);">
        <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Reset Password Petugas</h3>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">
            Password lama akan langsung diganti dan sesi "ingat saya" milik petugas dibatalkan. Password baru ditampilkan sekali setelah proses selesai.
        </p>

        <form method="POST" action="{{ route('super-admin.petugas.reset-password', $user) }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Metode</label>
                <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border: 1px solid var(--border-glass); border-radius: var(--radius-sm); cursor: pointer; margin-bottom: 8px;">
                    <input type="radio" name="mode" value="auto" style="margin-top: 3px;" {{ old('mode', 'auto') === 'auto' ? 'checked' : '' }} onchange="toggleManualPassword()">
                    <span>
                        <span style="font-weight: 600; font-size: 14px;">Buat otomatis</span>
                        <span style="display: block; color: var(--text-muted); font-size: 12px; margin-top: 2px;">Sistem membuat password acak 10 karakter yang mudah dibacakan.</span>
                    </span>
                </label>
                <label style="display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border: 1px solid var(--border-glass); border-radius: var(--radius-sm); cursor: pointer;">
                    <input type="radio" name="mode" value="manual" style="margin-top: 3px;" {{ old('mode') === 'manual' ? 'checked' : '' }} onchange="toggleManualPassword()">
                    <span>
                        <span style="font-weight: 600; font-size: 14px;">Tentukan sendiri</span>
                        <span style="display: block; color: var(--text-muted); font-size: 12px; margin-top: 2px;">Ketik password baru secara manual (minimal 8 karakter).</span>
                    </span>
                </label>
                @error('mode') <div class="form-error" style="color: var(--accent-red); font-size: 12px; margin-top: 6px;">{{ $message }}</div> @enderror
            </div>

            <div id="manualPasswordFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Password Baru *</label>
                    <input type="text" name="password" class="form-input" autocomplete="new-password" placeholder="Minimal 8 karakter">
                    @error('password') <div class="form-error" style="color: var(--accent-red); font-size: 12px; margin-top: 6px;">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Ulangi Password Baru *</label>
                    <input type="text" name="password_confirmation" class="form-input" autocomplete="new-password" placeholder="Ketik ulang password baru">
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" onclick="document.getElementById('resetPasswordModal').style.display='none'" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-sm" style="background: rgba(59,130,246,0.15); color: var(--accent-blue); border: 1px solid rgba(59,130,246,0.3);">Reset Password</button>
            </div>
        </form>
    </div>
</div>

<!-- Ubah Username Modal -->
<div id="usernameModal" style="display: {{ $errors->has('email') ? 'flex' : 'none' }}; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 999; align-items: center; justify-content: center; padding: 24px;">
    <div class="glass-card" style="max-width: 500px; width: 100%; background: var(--bg-secondary);">
        <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Ubah Username</h3>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">
            Username adalah alamat email yang dipakai petugas untuk masuk. Pastikan alamat baru benar — petugas tidak bisa login dengan username lama setelah diubah.
        </p>

        <form method="POST" action="{{ route('super-admin.petugas.update-username', $user) }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Username Saat Ini</label>
                <input type="text" class="form-input" value="{{ $user->email }}" disabled style="opacity: 0.6;">
            </div>
            <div class="form-group">
                <label class="form-label">Username Baru *</label>
                <input type="email" name="email" class="form-input" required value="{{ old('email') }}" placeholder="email@contoh.com" autocomplete="off">
                @error('email') <div class="form-error" style="color: var(--accent-red); font-size: 12px; margin-top: 6px;">{{ $message }}</div> @enderror
            </div>
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" onclick="document.getElementById('usernameModal').style.display='none'" class="btn btn-secondary btn-sm">Batal</button>
                <button type="submit" class="btn btn-sm" style="background: rgba(59,130,246,0.15); color: var(--accent-blue); border: 1px solid rgba(59,130,246,0.3);">Simpan Username</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Field password manual hanya relevan saat metode "manual" dipilih.
    function toggleManualPassword() {
        var manual = document.querySelector('input[name="mode"][value="manual"]');
        document.getElementById('manualPasswordFields').style.display = manual && manual.checked ? 'block' : 'none';
    }
    toggleManualPassword();

    // Tombol salin (username / password baru). navigator.clipboard butuh HTTPS,
    // jadi disediakan fallback execCommand untuk akses lewat HTTP.
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-copy]');
        if (!btn) return;

        var text = btn.getAttribute('data-copy');
        var done = function () {
            var original = btn.innerHTML;
            btn.innerHTML = '✅ Tersalin';
            setTimeout(function () { btn.innerHTML = original; }, 1500);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done);
            return;
        }

        var tmp = document.createElement('textarea');
        tmp.value = text;
        tmp.style.position = 'fixed';
        tmp.style.opacity = '0';
        document.body.appendChild(tmp);
        tmp.select();
        try { document.execCommand('copy'); done(); } catch (err) { /* diabaikan */ }
        document.body.removeChild(tmp);
    });
</script>
@endsection

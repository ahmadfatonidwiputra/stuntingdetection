@extends('layouts.landing')

@section('title', 'Registrasi Petugas Posyandu - AI Stunt Detect')

@push('styles')
<style>
    .reg-container { max-width: 720px; margin: 0 auto; padding-top: 120px; padding-bottom: 60px; }
    .reg-header { text-align: center; margin-bottom: 40px; }
    .reg-form { background: var(--bg-card); backdrop-filter: blur(20px); border: 1px solid var(--glass-border); border-radius: 20px; padding: 40px; }
    .form-section { margin-bottom: 32px; }
    .form-section-title { font-size: 16px; font-weight: 700; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid var(--glass-border); display: flex; align-items: center; gap: 10px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; }
    .form-label .required { color: var(--danger); }
    .form-input, .form-select, .form-textarea {
        width: 100%; padding: 12px 16px;
        background: var(--bg-main); border: 1px solid var(--glass-border);
        border-radius: 10px; color: var(--text); font-size: 14px;
        font-family: 'Inter', sans-serif; transition: all 0.2s; outline: none;
    }
    .form-input:focus, .form-select:focus, .form-textarea:focus {
        border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,165,233,0.15);
    }
    .form-select { appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-position: right 12px center; background-repeat: no-repeat; background-size: 16px; padding-right: 40px; }
    .form-select option { background: var(--bg-main); color: var(--text); }
    .form-textarea { min-height: 80px; resize: vertical; }
    .form-error { color: var(--danger); font-size: 12px; margin-top: 6px; }
    .form-hint { color: var(--text-muted); font-size: 12px; margin-top: 4px; }
    .file-upload {
        border: 2px dashed var(--glass-border); border-radius: 12px;
        padding: 28px; text-align: center; cursor: pointer; transition: all 0.3s;
    }
    .file-upload:hover { border-color: var(--primary); background: rgba(14,165,233,0.05); }
    .file-upload input { display: none; }
    .file-upload-icon { font-size: 32px; margin-bottom: 8px; }
    .file-upload-text { color: var(--text-secondary); font-size: 13px; }
    .file-upload-name { color: var(--primary); font-size: 13px; font-weight: 600; margin-top: 8px; display: none; }
    .checkbox-row { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 24px; }
    .checkbox-row input[type="checkbox"] {
        width: 20px; height: 20px; border-radius: 6px; flex-shrink: 0; margin-top: 2px;
        accent-color: var(--primary);
    }
    .checkbox-row label { color: var(--text-secondary); font-size: 13px; line-height: 1.6; }
    .submit-btn {
        width: 100%; padding: 14px; border-radius: 12px; border: none;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white; font-size: 15px; font-weight: 700; cursor: pointer;
        font-family: 'Inter', sans-serif;
        box-shadow: 0 4px 20px rgba(14,165,233,0.3);
        transition: all 0.3s;
    }
    .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(14,165,233,0.4); }
    .submit-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
    @media (max-width: 640px) {
        .form-row { grid-template-columns: 1fr; }
        .reg-form { padding: 24px; }
    }
</style>
@endpush

@section('content')
<div class="reg-container" style="padding-left: 24px; padding-right: 24px;">
    <div class="reg-header fade-up">
        <div class="section-badge">📝 Registrasi</div>
        <h1 style="font-size: 32px; font-weight: 800; margin-bottom: 12px;">Daftar Sebagai Petugas Posyandu</h1>
        <p style="color: var(--text-secondary); font-size: 15px;">Isi formulir di bawah ini untuk mendaftar. Akun Anda akan diverifikasi oleh Super Admin sebelum dapat digunakan.</p>
    </div>

    @if($errors->any())
    <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
        <div style="font-weight: 600; color: var(--danger); margin-bottom: 8px;">Terjadi kesalahan:</div>
        <ul style="color: var(--danger); font-size: 13px; margin-left: 16px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('register.petugas') }}" enctype="multipart/form-data" class="reg-form fade-up">
        @csrf

        <!-- Data Akun -->
        <div class="form-section">
            <div class="form-section-title">🔑 Data Akun</div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Username <span class="required">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="Username untuk login" required>
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Email <span class="required">*</span></label>
                    <input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="email@contoh.com" required>
                    @error('email') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Password <span class="required">*</span></label>
                    <input type="password" name="password" class="form-input" placeholder="Minimal 8 karakter" required>
                    @error('password') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password <span class="required">*</span></label>
                    <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password" required>
                </div>
            </div>
        </div>

        <!-- Data Pribadi -->
        <div class="form-section">
            <div class="form-section-title">👤 Data Pribadi</div>
            <div class="form-group">
                <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                <input type="text" name="nama_lengkap" class="form-input" value="{{ old('nama_lengkap') }}" placeholder="Nama lengkap sesuai KTP" required>
                @error('nama_lengkap') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">NIK <span class="required">*</span></label>
                    <input type="text" name="nik" class="form-input" value="{{ old('nik') }}" placeholder="16 digit NIK" maxlength="16" required>
                    <div class="form-hint">Nomor Induk Kependudukan (16 digit)</div>
                    @error('nik') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="no_telepon" class="form-input" value="{{ old('no_telepon') }}" placeholder="08xx-xxxx-xxxx">
                    @error('no_telepon') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <!-- Data Posyandu -->
        <div class="form-section">
            <div class="form-section-title">🏥 Data Posyandu</div>
            <div class="form-group">
                <label class="form-label">Nama Posyandu <span class="required">*</span></label>
                <input type="text" name="posyandu_name" class="form-input" value="{{ old('posyandu_name') }}" placeholder="Contoh: Posyandu Melati" required>
                @error('posyandu_name') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label class="form-label">Alamat Posyandu</label>
                <textarea name="posyandu_address" class="form-textarea" placeholder="Alamat lengkap posyandu">{{ old('posyandu_address') }}</textarea>
                @error('posyandu_address') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kelurahan/Desa</label>
                    <input type="text" name="kelurahan" class="form-input" value="{{ old('kelurahan') }}" placeholder="Kelurahan">
                </div>
                <div class="form-group">
                    <label class="form-label">Kecamatan</label>
                    <input type="text" name="kecamatan" class="form-input" value="{{ old('kecamatan') }}" placeholder="Kecamatan">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Kota/Kabupaten</label>
                    <input type="text" name="kota" class="form-input" value="{{ old('kota') }}" placeholder="Kota/Kabupaten">
                </div>
                <div class="form-group">
                    <label class="form-label">Provinsi</label>
                    <input type="text" name="provinsi" class="form-input" value="{{ old('provinsi') }}" placeholder="Provinsi">
                </div>
            </div>
        </div>

        <!-- Upload Dokumen -->
        <div class="form-section">
            <div class="form-section-title">📄 Dokumen</div>
            <div class="form-group">
                <label class="form-label">Surat Tugas / SK Pengangkatan</label>
                <label class="file-upload" id="fileUpload">
                    <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" onchange="showFileName(this)">
                    <div class="file-upload-icon">📎</div>
                    <div class="file-upload-text">Klik untuk upload dokumen (PDF, JPG, PNG — maks 5MB)</div>
                    <div class="file-upload-name" id="fileName"></div>
                </label>
                <div class="form-hint">Upload surat tugas atau SK pengangkatan sebagai kader posyandu (opsional, namun mempercepat verifikasi)</div>
                @error('document') <div class="form-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <!-- Agreement -->
        <div class="checkbox-row">
            <input type="checkbox" name="agreement" id="agreement" required>
            <label for="agreement">Saya menyatakan data yang saya isi adalah benar dan saya bersedia menunggu proses verifikasi oleh Super Admin. Saya memahami bahwa akun saya baru dapat digunakan setelah disetujui.</label>
        </div>
        @error('agreement') <div class="form-error" style="margin-bottom: 16px;">{{ $message }}</div> @enderror

        <button type="submit" class="submit-btn">Daftar Sekarang</button>

        <p style="text-align: center; margin-top: 20px; color: var(--text-muted); font-size: 14px;">
            Sudah punya akun? <a href="{{ route('login') }}" style="color: var(--primary); text-decoration: none; font-weight: 600;">Masuk di sini</a>
        </p>
    </form>
</div>

<script>
function showFileName(input) {
    const nameEl = document.getElementById('fileName');
    if (input.files.length > 0) {
        nameEl.textContent = '📄 ' + input.files[0].name;
        nameEl.style.display = 'block';
    }
}
</script>
@endsection

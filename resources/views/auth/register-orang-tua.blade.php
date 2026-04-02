@extends('layouts.landing')

@section('title', 'Daftar Sebagai Orang Tua - AI Stunt Detect')

@push('styles')
<style>
    .register-container { max-width: 600px; margin: 0 auto; padding-top: 120px; padding-bottom: 60px; padding-left: 24px; padding-right: 24px; }
    .register-card {
        background: var(--bg-card); backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border); border-radius: 20px; padding: 40px;
    }
    .section-header { text-align: center; margin-bottom: 32px; }
    .form-section { border-bottom: 1px solid var(--glass-border); padding-bottom: 24px; margin-bottom: 24px; }
    .form-section:last-of-type { border-bottom: none; }
    .form-section h2 { font-size: 14px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 16px; }
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; }
    .form-input {
        width: 100%; padding: 11px 14px;
        background: var(--bg-main); border: 1px solid var(--glass-border);
        border-radius: 10px; color: var(--text); font-size: 14px;
        font-family: 'Inter', sans-serif; transition: all 0.2s; outline: none; box-sizing: border-box;
    }
    .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,165,233,0.15); }
    .form-error { color: var(--danger); font-size: 12px; margin-top: 4px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-hint { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
    .submit-btn {
        width: 100%; padding: 14px; border-radius: 12px; border: none;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white; font-size: 15px; font-weight: 700; cursor: pointer;
        font-family: 'Inter', sans-serif;
        box-shadow: 0 4px 20px rgba(14,165,233,0.3); transition: all 0.3s; margin-top: 8px;
    }
    .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(14,165,233,0.4); }
    .info-box {
        background: rgba(14,165,233,0.07); border: 1px solid rgba(14,165,233,0.2);
        border-radius: 12px; padding: 14px 16px; margin-bottom: 24px;
        color: var(--text-secondary); font-size: 13px; line-height: 1.6;
    }
    @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }

    /* Select2 Theme Overrides */
    .select2-container--default .select2-selection--single {
        background: var(--bg-main); border: 1px solid var(--glass-border);
        border-radius: 10px; height: 44px; outline: none; display: flex; align-items: center;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: var(--text); font-size: 14px; padding-left: 14px; padding-right: 32px; width: 100%;
    }
    .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: var(--text-muted); font-size: 14px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 44px; right: 10px; display: flex; align-items: center;
    }
    .select2-dropdown {
        background: var(--bg-main); border: 1px solid var(--glass-border);
        border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.5);
    }
    .select2-search--dropdown .select2-search__field {
        background: rgba(255,255,255,0.05); color: var(--text); border: 1px solid var(--glass-border);
        border-radius: 6px; padding: 6px 10px;
    }
    .select2-container--default .select2-results__option {
        color: var(--text); font-size: 14px; padding: 10px 14px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected],
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background: var(--primary); color: white;
    }
    .select2-container--default .select2-results__option[aria-selected=true] {
        background: rgba(14,165,233,0.2);
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
<div class="register-container">
    <div class="register-card fade-up">
        <div class="section-header">
            <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 16px; background: linear-gradient(135deg, #10b981, #059669); display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 4px 20px rgba(16,185,129,0.3);">👨‍👩‍👧</div>
            <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 8px;">Daftar Sebagai Orang Tua</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Pantau tumbuh kembang anak Anda secara online</p>
        </div>

        <div class="info-box">
            💡 <strong>Penting:</strong> Anak Anda harus sudah terdaftar di posyandu terlebih dahulu. Siapkan <strong>NIK anak</strong> dan <strong>Nomor Kartu Keluarga (KK)</strong> sebelum mendaftar. Akun Anda akan diverifikasi oleh petugas posyandu.
        </div>

        @if($errors->any())
            <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; color: var(--danger); font-size: 13px;">
                <ul style="margin: 0; padding-left: 16px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.orang-tua') }}">
            @csrf

            {{-- Informasi Akun --}}
            <div class="form-section">
                <h2>🔐 Informasi Akun</h2>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap (untuk akun) <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" class="form-input" value="{{ old('name') }}" placeholder="Nama Anda" required>
                    @error('name') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Email <span style="color:var(--danger)">*</span></label>
                        <input type="email" name="email" class="form-input" value="{{ old('email') }}" placeholder="email@contoh.com" required>
                        @error('email') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" name="no_telepon" class="form-input" value="{{ old('no_telepon') }}" placeholder="08xxxxxxxxxx">
                        @error('no_telepon') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Password <span style="color:var(--danger)">*</span></label>
                        <input type="password" name="password" class="form-input" placeholder="Min. 8 karakter" required>
                        @error('password') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Konfirmasi Password <span style="color:var(--danger)">*</span></label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="Ulangi password" required>
                    </div>
                </div>
            </div>

            {{-- Data Orang Tua --}}
            <div class="form-section">
                <h2>👤 Data Orang Tua / Wali</h2>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-input" value="{{ old('nama_lengkap') }}" placeholder="Sesuai KTP" required>
                    @error('nama_lengkap') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">NIK (16 digit) <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="nik" class="form-input" value="{{ old('nik') }}" placeholder="3201XXXXXXXXXXXX" maxlength="16" required>
                        @error('nik') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hubungan <span style="color:var(--danger)">*</span></label>
                        <select name="hubungan" class="form-input" required>
                            <option value="">-- Pilih --</option>
                            <option value="ayah" {{ old('hubungan') == 'ayah' ? 'selected' : '' }}>Ayah</option>
                            <option value="ibu" {{ old('hubungan') == 'ibu' ? 'selected' : '' }}>Ibu</option>
                            <option value="wali" {{ old('hubungan') == 'wali' ? 'selected' : '' }}>Wali</option>
                        </select>
                        @error('hubungan') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            {{-- Validasi Data Anak --}}
            <div class="form-section">
                <h2>👶 Validasi Data Anak</h2>
                <p class="form-hint" style="margin-bottom: 12px; color: var(--text-secondary);">Pilih Posyandu lalu masukkan data anak yang <strong>sudah terdaftar</strong> untuk verifikasi.</p>
                <div class="form-group">
                    <label class="form-label">Posyandu Tempat Anak Terdaftar <span style="color:var(--danger)">*</span></label>
                    <select name="posyandu_id" class="posyandu-select2" style="width: 100%;" required>
                        <option value="">-- Cari Nama Posyandu --</option>
                        @foreach($posyandus as $p)
                            <option value="{{ $p->id }}" {{ old('posyandu_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->nama }} - {{ $p->kelurahan }}
                            </option>
                        @endforeach
                    </select>
                    @error('posyandu_id') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">NIK Anak (16 digit) <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="nik_anak" class="form-input" value="{{ old('nik_anak') }}" placeholder="NIK anak Anda" maxlength="16" required>
                        @error('nik_anak') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor KK (16 digit) <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="no_kk" class="form-input" value="{{ old('no_kk') }}" placeholder="3201XXXXXXXXXXXX" maxlength="16" required>
                        @error('no_kk') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nama Anak <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="nama_anak" class="form-input" value="{{ old('nama_anak') }}" placeholder="Nama anak sesuai data posyandu" required>
                        @error('nama_anak') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nama Ibu Kandung <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="nama_ibu" class="form-input" value="{{ old('nama_ibu') }}" placeholder="Nama ibu sesuai pada pendataan" required>
                        @error('nama_ibu') <div class="form-error">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <button type="submit" class="submit-btn">Daftar Sekarang →</button>

            <p style="text-align: center; margin-top: 20px; color: var(--text-muted); font-size: 13px;">
                Sudah punya akun? <a href="{{ route('login') }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">Login di sini</a>
            </p>
            <p style="text-align: center; margin-top: 8px; color: var(--text-muted); font-size: 13px;">
                Daftar sebagai <a href="{{ route('register.petugas') }}" style="color: var(--primary); font-weight: 500; text-decoration: none;">Petugas Posyandu →</a>
            </p>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.posyandu-select2').select2({
            placeholder: '-- Cari Nama Posyandu --',
            allowClear: true,
            language: {
                noResults: function() {
                    return "Posyandu tidak ditemukan";
                }
            }
        });
    });
</script>
@endpush

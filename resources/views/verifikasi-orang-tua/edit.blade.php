@extends('layouts.landing')

@section('title', 'Edit Data Orang Tua - AI Stunt Detect')

@push('styles')
<style>
    .main { max-width: 600px; margin: 0 auto; padding: 100px 24px 60px; }
    .form-card {
        background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 16px;
        padding: 32px;
    }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; }
    .form-input {
        width: 100%; padding: 12px 14px;
        background: var(--bg-main); border: 1px solid var(--glass-border);
        border-radius: 10px; color: var(--text); font-size: 14px;
        font-family: inherit; transition: all 0.2s; box-sizing: border-box;
    }
    .form-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(14,165,233,0.15); }
    .btn-submit {
        width: 100%; padding: 14px; background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white; border: none; border-radius: 10px; font-size: 15px; font-weight: 700;
        cursor: pointer; cursor: pointer; transition: all 0.2s; margin-top: 10px;
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(14,165,233,0.3); }
    .back-btn { color: var(--text-secondary); text-decoration: none; font-size: 14px; font-weight: 600; display: inline-block; margin-bottom: 24px; }
    .form-error { color: var(--danger); font-size: 12px; margin-top: 4px; }
</style>
@endpush

@section('content')
<div class="main">
    <a href="{{ route('verifikasi-orang-tua.index') }}" class="back-btn">← Kembali ke Daftar</a>
    <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 8px;">Edit Data Orang Tua</h1>
    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 24px;">Merevisi profil orang tua terdaftar</p>

    <div class="form-card">
        <form method="POST" action="{{ route('verifikasi-orang-tua.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Nama Lengkap (Sesuai KTP)</label>
                <input type="text" name="nama_lengkap" class="form-input" value="{{ old('nama_lengkap', $user->orangTuaProfile?->nama_lengkap) }}" required>
                @error('nama_lengkap') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">NIK (16 Digit)</label>
                <input type="text" name="nik" class="form-input" value="{{ old('nik', $user->orangTuaProfile?->nik) }}" maxlength="16" required>
                @error('nik') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Nomor KK (16 Digit)</label>
                <input type="text" name="no_kk" class="form-input" value="{{ old('no_kk', $user->orangTuaProfile?->no_kk) }}" maxlength="16" required>
                @error('no_kk') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Hubungan dengan Anak</label>
                <select name="hubungan" class="form-input" required>
                    <option value="" disabled>-- Pilih Hubungan --</option>
                    <option value="ayah" {{ old('hubungan', $user->orangTuaProfile?->hubungan) === 'ayah' ? 'selected' : '' }}>Ayah</option>
                    <option value="ibu" {{ old('hubungan', $user->orangTuaProfile?->hubungan) === 'ibu' ? 'selected' : '' }}>Ibu</option>
                    <option value="wali" {{ old('hubungan', $user->orangTuaProfile?->hubungan) === 'wali' ? 'selected' : '' }}>Wali</option>
                </select>
                @error('hubungan') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">No. Telepon (Opsional)</label>
                <input type="text" name="no_telepon" class="form-input" value="{{ old('no_telepon', $user->orangTuaProfile?->no_telepon) }}" maxlength="15">
                @error('no_telepon') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="btn-submit">Simpan Perubahan</button>
        </form>
    </div>
</div>
@endsection

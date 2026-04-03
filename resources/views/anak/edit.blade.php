@extends('layouts.landing')

@section('title', 'Edit Data Anak - AI Stunt Detect')

@push('styles')
<style>
    .form-main { max-width: 760px; margin: 0 auto; padding: 100px 24px 60px; }
    .form-card { background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 20px; padding: 36px; }
    .form-card-title { font-size: 22px; font-weight: 800; margin-bottom: 28px; display: flex; align-items: center; gap: 10px; }
    .section-label { font-size: 12px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin: 20px 0 14px; padding-top: 20px; border-top: 1px solid var(--glass-border); }
    .section-label:first-of-type { border-top: none; margin-top: 0; padding-top: 0; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-group { margin-bottom: 14px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; }
    .form-input {
        width: 100%; padding: 11px 14px; box-sizing: border-box;
        background: var(--bg-main); border: 1px solid var(--glass-border);
        border-radius: 10px; color: var(--text); font-size: 14px;
        font-family: 'Inter', sans-serif; transition: all 0.2s; outline: none;
    }
    .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,165,233,0.15); }
    .form-error { color: var(--danger); font-size: 12px; margin-top: 4px; }
    .submit-row { display: flex; align-items: center; gap: 12px; margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--glass-border); }
    .btn-primary { padding: 12px 28px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.2s; }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(14,165,233,0.3); }
    .btn-secondary { padding: 12px 24px; background: none; border: 1px solid var(--glass-border); color: var(--text-secondary); border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif; text-decoration: none; transition: all 0.2s; }
    @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="form-main">
    <a href="{{ route('anak.show', $anak) }}" style="display: inline-flex; align-items: center; gap: 6px; color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 600; margin-bottom: 20px;">← Kembali</a>

    <div class="form-card">
        <div class="form-card-title">✏️ Edit Data — {{ $anak->nama }}</div>

        @if($errors->any())
        <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; color: var(--danger); font-size: 13px;">
            <ul style="margin: 0; padding-left: 16px;">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('anak.update', $anak) }}">
            @csrf @method('PUT')

            <div class="section-label">🏥 Posyandu</div>
            @if(auth()->user()->role === 'super_admin')
            <div class="form-group">
                <label class="form-label">Posyandu <span style="color:var(--danger)">*</span></label>
                <select name="posyandu_id" class="form-input" required>
                    <option value="">-- Pilih Posyandu --</option>
                    @foreach($posyandu as $p)
                    <option value="{{ $p->id }}" {{ old('posyandu_id', $anak->posyandu_id) == $p->id ? 'selected' : '' }}>{{ $p->nama }} — {{ $p->kota }}</option>
                    @endforeach
                </select>
                @error('posyandu_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>
            @else
            <div class="form-group">
                <label class="form-label">Posyandu</label>
                <input type="text" class="form-input" value="{{ auth()->user()->petugasProfile?->posyandu_name ?? '-' }}" disabled>
                <input type="hidden" name="posyandu_id" value="{{ $anak->posyandu_id }}">
            </div>
            @endif

            <div class="section-label">👶 Data Anak</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="nama" class="form-input" value="{{ old('nama', $anak->nama) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">NIK Anak (16 digit) <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="nik_anak" class="form-input" value="{{ old('nik_anak', $anak->nik_anak) }}" maxlength="16" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tempat Lahir <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="tempat_lahir" class="form-input" value="{{ old('tempat_lahir', $anak->tempat_lahir) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Lahir <span style="color:var(--danger)">*</span></label>
                    <input type="date" name="tanggal_lahir" class="form-input" value="{{ old('tanggal_lahir', $anak->tanggal_lahir?->format('Y-m-d')) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Jenis Kelamin <span style="color:var(--danger)">*</span></label>
                    <select name="jenis_kelamin" class="form-input" required>
                        <option value="L" {{ old('jenis_kelamin', $anak->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $anak->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="section-label">👨‍👩‍👧 Data Orang Tua</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">No. KK (16 digit) <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="no_kk" class="form-input" value="{{ old('no_kk', $anak->no_kk) }}" maxlength="16" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Ayah Kandung <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="nama_ayah" class="form-input" value="{{ old('nama_ayah', $anak->nama_ayah) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Ibu Kandung <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="nama_ibu" class="form-input" value="{{ old('nama_ibu', $anak->nama_ibu) }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">NIK Ayah</label>
                    <input type="text" name="nik_ayah" class="form-input" value="{{ old('nik_ayah', $anak->nik_ayah) }}" maxlength="16">
                </div>
                <div class="form-group">
                    <label class="form-label">NIK Ibu</label>
                    <input type="text" name="nik_ibu" class="form-input" value="{{ old('nik_ibu', $anak->nik_ibu) }}" maxlength="16">
                </div>
                <div class="form-group">
                    <label class="form-label">No. Telepon Orang Tua</label>
                    <input type="text" name="no_telepon_ortu" class="form-input" value="{{ old('no_telepon_ortu', $anak->no_telepon_ortu) }}">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Alamat Lengkap <span style="color:var(--danger)">*</span></label>
                <textarea name="alamat" class="form-input" rows="2" required>{{ old('alamat', $anak->alamat) }}</textarea>
            </div>

            <div class="submit-row">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('anak.show', $anak) }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

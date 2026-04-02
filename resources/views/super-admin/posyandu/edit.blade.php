@extends('layouts.landing')

@section('title', 'Edit Posyandu - AI Stunt Detect')

@push('styles')
<style>
    .form-main { max-width: 640px; margin: 0 auto; padding: 100px 24px 60px; }
    .form-card { background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 20px; padding: 36px; }
    .form-card-title { font-size: 22px; font-weight: 800; margin-bottom: 28px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-group { margin-bottom: 14px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; }
    .form-input { width: 100%; padding: 11px 14px; box-sizing: border-box; background: var(--bg-main); border: 1px solid var(--glass-border); border-radius: 10px; color: var(--text); font-size: 14px; font-family: 'Inter', sans-serif; transition: all 0.2s; outline: none; }
    .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,165,233,0.15); }
    .form-error { color: var(--danger); font-size: 12px; margin-top: 4px; }
    .submit-row { display: flex; align-items: center; gap: 12px; margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--glass-border); }
    .btn-primary { padding: 12px 28px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer; font-family: 'Inter', sans-serif; }
    .btn-secondary { padding: 12px 24px; background: none; border: 1px solid var(--glass-border); color: var(--text-secondary); border-radius: 10px; font-size: 14px; font-weight: 600; text-decoration: none; }
    @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="form-main">
    <a href="{{ route('super-admin.posyandu.index') }}" style="display: inline-flex; align-items: center; gap: 6px; color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 600; margin-bottom: 20px;">← Kembali</a>

    <div class="form-card">
        <div class="form-card-title">✏️ Edit — {{ $posyandu->nama }}</div>

        @if($errors->any())
        <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; color: var(--danger); font-size: 13px;">
            <ul style="margin: 0; padding-left: 16px;">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('super-admin.posyandu.update', $posyandu) }}">
            @csrf @method('PUT')

            <div class="form-grid">
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Nama Posyandu <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="nama" class="form-input" value="{{ old('nama', $posyandu->nama) }}" required>
                    @error('nama') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Kode Posyandu</label>
                    <input type="text" name="kode_posyandu" class="form-input" value="{{ old('kode_posyandu', $posyandu->kode_posyandu) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="no_telepon" class="form-input" value="{{ old('no_telepon', $posyandu->no_telepon) }}">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-input" rows="2">{{ old('alamat', $posyandu->alamat) }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Kelurahan</label>
                    <input type="text" name="kelurahan" class="form-input" value="{{ old('kelurahan', $posyandu->kelurahan) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Kecamatan</label>
                    <input type="text" name="kecamatan" class="form-input" value="{{ old('kecamatan', $posyandu->kecamatan) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Kota</label>
                    <input type="text" name="kota" class="form-input" value="{{ old('kota', $posyandu->kota) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Provinsi</label>
                    <input type="text" name="provinsi" class="form-input" value="{{ old('provinsi', $posyandu->provinsi) }}">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Status <span style="color:var(--danger)">*</span></label>
                    <select name="status" class="form-input" required>
                        <option value="active" {{ old('status', $posyandu->status) === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ old('status', $posyandu->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="submit-row">
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
                <a href="{{ route('super-admin.posyandu.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

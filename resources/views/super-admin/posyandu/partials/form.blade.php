{{-- Form isian posyandu, dipakai bersama oleh halaman tambah & edit. --}}
@if($errors->any())
<div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: var(--radius-sm); padding: 14px 16px; margin-bottom: 20px; color: var(--accent-red); font-size: 13px;">
    <ul style="margin: 0; padding-left: 16px;">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ $action }}">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
            <label class="form-label">Nama Posyandu *</label>
            <input type="text" name="nama" class="form-input" value="{{ old('nama', $posyandu?->nama) }}" placeholder="cth. Posyandu Melati 1" required>
            @error('nama') <div style="color: var(--accent-red); font-size: 12px; margin-top: 6px;">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Kode Posyandu</label>
            <input type="text" name="kode_posyandu" class="form-input" value="{{ old('kode_posyandu', $posyandu?->kode_posyandu) }}" placeholder="POS-XXX-001">
            @error('kode_posyandu') <div style="color: var(--accent-red); font-size: 12px; margin-top: 6px;">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">No. Telepon</label>
            <input type="text" name="no_telepon" class="form-input" value="{{ old('no_telepon', $posyandu?->no_telepon) }}" placeholder="0361...">
            @error('no_telepon') <div style="color: var(--accent-red); font-size: 12px; margin-top: 6px;">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-textarea" rows="2" placeholder="Jl. ..." style="min-height: 70px;">{{ old('alamat', $posyandu?->alamat) }}</textarea>
            @error('alamat') <div style="color: var(--accent-red); font-size: 12px; margin-top: 6px;">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Desa / Kelurahan</label>
            <input type="text" name="kelurahan" class="form-input" value="{{ old('kelurahan', $posyandu?->kelurahan) }}">
            @error('kelurahan') <div style="color: var(--accent-red); font-size: 12px; margin-top: 6px;">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Kecamatan</label>
            <input type="text" name="kecamatan" class="form-input" value="{{ old('kecamatan', $posyandu?->kecamatan) }}">
            @error('kecamatan') <div style="color: var(--accent-red); font-size: 12px; margin-top: 6px;">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Kota / Kabupaten</label>
            <input type="text" name="kota" class="form-input" value="{{ old('kota', $posyandu?->kota) }}">
            @error('kota') <div style="color: var(--accent-red); font-size: 12px; margin-top: 6px;">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Provinsi</label>
            <input type="text" name="provinsi" class="form-input" value="{{ old('provinsi', $posyandu?->provinsi) }}">
            @error('provinsi') <div style="color: var(--accent-red); font-size: 12px; margin-top: 6px;">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 0;">
            <label class="form-label">Status *</label>
            <select name="status" class="form-input" required>
                <option value="active" {{ old('status', $posyandu?->status ?? 'active') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ old('status', $posyandu?->status) === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            @error('status') <div style="color: var(--accent-red); font-size: 12px; margin-top: 6px;">{{ $message }}</div> @enderror
        </div>
    </div>

    <div style="display: flex; gap: 12px; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-glass);">
        <button type="submit" class="btn btn-primary btn-sm">{{ $submitLabel }}</button>
        <a href="{{ route('super-admin.posyandu.index') }}" class="btn btn-secondary btn-sm">Batal</a>
    </div>
</form>

<div class="glass-card fade-in">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border-glass);">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(16,185,129,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent-green)" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div>
            <h2 style="font-size: 16px; font-weight: 700; margin-bottom: 2px;">Data Orang Tua</h2>
            <p style="font-size: 13px; color: var(--text-muted);">Perbarui data diri, hubungan keluarga, dan kontak Anda</p>
        </div>
    </div>

    <form method="post" action="{{ route('profile.orang-tua.update') }}">
        @csrf
        @method('patch')

        <div class="form-group">
            <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
            <input id="nama_lengkap" name="nama_lengkap" type="text" class="form-input" value="{{ old('nama_lengkap', $orangTuaProfile->nama_lengkap) }}" required />
            @error('nama_lengkap')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="hubungan" class="form-label">Hubungan dengan Anak</label>
            <select id="hubungan" name="hubungan" class="form-input" required>
                @foreach(['ayah' => 'Ayah', 'ibu' => 'Ibu', 'wali' => 'Wali'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('hubungan', $orangTuaProfile->hubungan) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('hubungan')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="no_telepon" class="form-label">No. Telepon</label>
            <input id="no_telepon" name="no_telepon" type="text" class="form-input" value="{{ old('no_telepon', $orangTuaProfile->no_telepon) }}" />
            @error('no_telepon')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="alamat" class="form-label">Alamat</label>
            <textarea id="alamat" name="alamat" class="form-input" rows="3">{{ old('alamat', $orangTuaProfile->alamat) }}</textarea>
            @error('alamat')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
            NIK: <b>{{ $orangTuaProfile->nik }}</b> · No. KK: <b>{{ $orangTuaProfile->no_kk }}</b>
            <br>NIK dan No. KK terhubung dengan data verifikasi, hubungi petugas posyandu jika perlu diubah.
        </div>

        <div style="display: flex; align-items: center; gap: 16px;">
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                Simpan Perubahan
            </button>

            @if (session('status') === 'orang-tua-profile-updated')
                <span style="font-size: 13px; color: var(--accent-green); display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    Berhasil disimpan!
                </span>
            @endif
        </div>
    </form>
</div>

@extends('layouts.landing')

@section('title', 'Tambah Data Anak - AI Stunt Detect')

@push('styles')
<style>
    .form-main { max-width: 760px; margin: 0 auto; padding: 100px 24px 60px; }
    .form-card { background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 20px; padding: 36px; }
    .form-card-title { font-size: 22px; font-weight: 800; margin-bottom: 28px; display: flex; align-items: center; gap: 10px; }
    .section-label { font-size: 12px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; padding-top: 20px; border-top: 1px solid var(--glass-border); }
    .section-label:first-of-type { border-top: none; padding-top: 0; }
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
    .search-results {
        position: absolute; top: 100%; left: 0; right: 0; z-index: 50;
        background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 12px;
        margin-top: 4px; max-height: 240px; overflow-y: auto; box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    }
    .search-result-item {
        padding: 12px 16px; cursor: pointer; transition: background 0.15s; border-bottom: 1px solid var(--glass-border);
    }
    .search-result-item:last-child { border-bottom: none; }
    .search-result-item:hover { background: var(--bg-main); }
    .autofill-box {
        background: rgba(16,185,129,0.08); border: 1px solid rgba(16,185,129,0.25);
        border-radius: 12px; padding: 14px; margin-bottom: 16px; font-size: 13px;
        color: var(--text-secondary); display: none;
    }
    .submit-row { display: flex; align-items: center; gap: 12px; margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--glass-border); }
    .btn-primary {
        padding: 12px 28px; background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 700;
        cursor: pointer; font-family: 'Inter', sans-serif; transition: all 0.2s;
    }
    .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(14,165,233,0.3); }
    .btn-secondary {
        padding: 12px 24px; background: none; border: 1px solid var(--glass-border);
        color: var(--text-secondary); border-radius: 10px; font-size: 14px; font-weight: 600;
        cursor: pointer; font-family: 'Inter', sans-serif; text-decoration: none; transition: all 0.2s;
    }
    .btn-secondary:hover { background: var(--bg-main); }
    @media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="form-main">
    <a href="{{ route('anak.index') }}" style="display: inline-flex; align-items: center; gap: 6px; color: var(--primary); text-decoration: none; font-size: 14px; font-weight: 600; margin-bottom: 20px;">← Kembali ke Daftar Anak</a>

    <div class="form-card">
        <div class="form-card-title">👶 Tambah Data Anak Baru</div>

        @if($errors->any())
        <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; color: var(--danger); font-size: 13px;">
            <ul style="margin: 0; padding-left: 16px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('anak.store') }}">
            @csrf

            {{-- Posyandu --}}
            <div class="section-label">🏥 Posyandu</div>
            <div class="form-group">
                <label class="form-label">Posyandu <span style="color:var(--danger)">*</span></label>
                <select name="posyandu_id" class="form-input" required>
                    <option value="">-- Pilih Posyandu --</option>
                    @foreach($posyandu as $p)
                        <option value="{{ $p->id }}" {{ (old('posyandu_id', $defaultPosyanduId) == $p->id) ? 'selected' : '' }}>
                            {{ $p->nama }} — {{ $p->kota }}
                        </option>
                    @endforeach
                </select>
                @error('posyandu_id') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            {{-- Cari Orang Tua --}}
            <div class="section-label">👨‍👩‍👧 Data Orang Tua</div>
            <div class="form-group" style="position: relative;">
                <label class="form-label">Cari Nama Orang Tua (ketik min. 3 karakter)</label>
                <input type="text" id="search-ortu" class="form-input" placeholder="Ketik nama ayah atau ibu...">
                <div id="search-results" class="search-results" style="display:none;"></div>
            </div>
            <div class="autofill-box" id="autofill-info">
                ✅ Data orang tua berhasil diisi otomatis. Anda boleh mengubahnya jika perlu.
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">No. Kartu Keluarga (16 digit) <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="no_kk" id="no_kk" class="form-input" value="{{ old('no_kk') }}" maxlength="16" placeholder="3201..." required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Ayah Kandung <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="nama_ayah" id="nama_ayah" class="form-input" value="{{ old('nama_ayah') }}" placeholder="Nama ayah kandung" required>
                    @error('nama_ayah') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Ibu Kandung <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="nama_ibu" id="nama_ibu" class="form-input" value="{{ old('nama_ibu') }}" placeholder="Nama ibu kandung" required>
                </div>
                <div class="form-group">
                    <label class="form-label">NIK Ayah (16 digit)</label>
                    <input type="text" name="nik_ayah" id="nik_ayah" class="form-input" value="{{ old('nik_ayah') }}" maxlength="16" placeholder="3201...">
                </div>
                <div class="form-group">
                    <label class="form-label">NIK Ibu (16 digit)</label>
                    <input type="text" name="nik_ibu" id="nik_ibu" class="form-input" value="{{ old('nik_ibu') }}" maxlength="16" placeholder="3201...">
                </div>
                <div class="form-group">
                    <label class="form-label">No. Telepon Orang Tua</label>
                    <input type="text" name="no_telepon_ortu" id="no_telepon_ortu" class="form-input" value="{{ old('no_telepon_ortu') }}" placeholder="08xxx">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Alamat Lengkap <span style="color:var(--danger)">*</span></label>
                <textarea name="alamat" id="alamat_ortu" class="form-input" rows="2" placeholder="Alamat tempat tinggal" required>{{ old('alamat') }}</textarea>
            </div>

            {{-- Data Anak --}}
            <div class="section-label">👶 Data Anak</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="nama" class="form-input" value="{{ old('nama') }}" placeholder="Nama lengkap anak" required>
                    @error('nama') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">NIK Anak (16 digit) <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="nik_anak" class="form-input" value="{{ old('nik_anak') }}" maxlength="16" placeholder="NIK pada KK" required>
                    @error('nik_anak') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Tempat Lahir <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="tempat_lahir" class="form-input" value="{{ old('tempat_lahir') }}" placeholder="Tempat lahir anak" required>
                    @error('tempat_lahir') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Tanggal Lahir <span style="color:var(--danger)">*</span></label>
                    <input type="date" name="tanggal_lahir" class="form-input" value="{{ old('tanggal_lahir') }}" required>
                    @error('tanggal_lahir') <div class="form-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Jenis Kelamin <span style="color:var(--danger)">*</span></label>
                    <select name="jenis_kelamin" class="form-input" required>
                        <option value="">-- Pilih --</option>
                        <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <div class="form-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="submit-row">
                <button type="submit" class="btn-primary">Simpan Data Anak</button>
                <a href="{{ route('anak.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const searchInput = document.getElementById('search-ortu');
    const searchResults = document.getElementById('search-results');
    const autofillInfo = document.getElementById('autofill-info');
    let searchTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        const q = this.value.trim();
        if (q.length < 3) { searchResults.style.display = 'none'; return; }

        searchTimer = setTimeout(async () => {
            try {
                const res = await fetch(`/anak-search-orang-tua?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                renderResults(data, q);
            } catch (e) { console.error(e); }
        }, 300);
    });

    function renderResults(data, q) {
        if (data.length === 0) {
            searchResults.innerHTML = `<div class="search-result-item" style="color: var(--text-muted)">Tidak ditemukan. Isi manual di bawah ini.</div>`;
        } else {
            searchResults.innerHTML = data.map((item, i) => `
                <div class="search-result-item" onclick="selectOrtu(${i})">
                    <div style="font-weight: 600; font-size: 14px;">
                        ${item.nama_ayah ? '👨 Ayah: ' + item.nama_ayah : ''}
                        ${item.nama_ayah && item.nama_ibu ? ' &nbsp;|&nbsp; ' : ''}
                        ${item.nama_ibu ? '👩 Ibu: ' + item.nama_ibu : ''}
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                        No. KK: ${item.no_kk || '-'} • Tel: ${item.no_telepon || '-'}
                    </div>
                </div>
            `).join('');
            window._ortuData = data;
        }
        searchResults.style.display = 'block';
    }

    function selectOrtu(i) {
        const d = window._ortuData[i];
        document.getElementById('nama_ayah').value = d.nama_ayah || '';
        document.getElementById('nik_ayah').value = d.nik_ayah || '';
        document.getElementById('nama_ibu').value = d.nama_ibu || '';
        document.getElementById('nik_ibu').value = d.nik_ibu || '';
        document.getElementById('no_kk').value = d.no_kk || '';
        document.getElementById('no_telepon_ortu').value = d.no_telepon || '';
        document.getElementById('alamat_ortu').value = d.alamat || '';
        searchResults.style.display = 'none';
        searchInput.value = '';
        autofillInfo.style.display = 'block';
    }

    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
</script>
@endpush

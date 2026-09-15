@extends('layouts.main')

@section('content')
@php
    $selectedParentName = $selectedAnak?->nama_ibu ?: $selectedAnak?->nama_ayah;
    $selectedSearchLabel = $selectedAnak
        ? trim($selectedAnak->nama . ' (' . ($selectedAnak->nik_anak ?: '-') . ')')
        : '';
@endphp
<div class="page-header flex-between">
    <div>
        <h1 class="page-title">Pengukuran Baru</h1>
        <p class="page-subtitle">Ukur tinggi badan menggunakan kamera dan catat berat badan</p>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-success" style="background: rgba(239, 68, 68, 0.15); border-color: rgba(239, 68, 68, 0.3); color: var(--accent-red);">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="15" y1="9" x2="9" y2="15"/>
            <line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

@push('styles')
<style>
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
</style>
@endpush

<form method="POST" action="{{ route('measurements.store') }}" enctype="multipart/form-data" id="measurementForm">
    @csrf
    <input type="hidden" name="form_token" value="{{ $formToken }}">
    <input type="hidden" name="anak_id" id="anakIdInput" value="{{ old('anak_id', $selectedAnak?->id) }}">

    <div class="grid-2">
        <!-- Left: Camera & Photo -->
        @include('measurements.partials.camera-card')

        <!-- Right: Form Input -->
        <div class="glass-card fade-in">
            <div class="chart-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-green)" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Data Pengukuran
            </div>

            <div class="form-group" style="position: relative; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px dashed var(--glass-border);">
                <label class="form-label" style="color: var(--accent-blue);">🔍 Cari Data Anak (Ketik NIK atau Nama)</label>
                <input type="text" id="search-anak" class="form-input" value="{{ $selectedSearchLabel }}" placeholder="Masukkan minimal 3 karakter..." style="border-color: rgba(59, 130, 246, 0.3);">
                <div id="search-results" class="search-results" style="display:none;"></div>
                <div class="autofill-box" id="autofill-info" style="margin-top: 10px; display: {{ old('anak_id', $selectedAnak?->id) ? 'block' : 'none' }};">
                    ✅ Data anak berhasil dipilih. Identitas anak akan diambil otomatis dari data master.
                </div>
                @error('anak_id')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">NIK Anak</label>
                <input type="text" id="nikAnakInput" class="form-input" value="{{ $selectedAnak?->nik_anak }}" placeholder="Otomatis terisi saat memilih data anak di atas" readonly style="background-color: var(--bg-color); cursor: default;">
            </div>

            <div class="form-group">
                <label class="form-label">Nama Anak</label>
                <input type="text" name="child_name" id="childNameInput" class="form-input" value="{{ $selectedAnak?->nama }}" placeholder="Terisi otomatis dari data anak" readonly style="background-color: var(--bg-color); cursor: default;">
            </div>

            <div class="form-group">
                <label class="form-label">Nama Ibu Kandung</label>
                <input type="text" name="parent_name" id="parentNameInput" class="form-input" value="{{ $selectedParentName }}" placeholder="Terisi otomatis dari data anak" readonly style="background-color: var(--bg-color); cursor: default;">
            </div>

            <div class="form-group">
                <label class="form-label">Alamat</label>
                <textarea name="address" id="addressInput" class="form-textarea" placeholder="Terisi otomatis dari data anak" readonly style="background-color: var(--bg-color); cursor: default;">{{ $selectedAnak?->alamat }}</textarea>
            </div>

            @if(auth()->user()->isPetugasPosyandu() && auth()->user()->petugasProfile?->posyandu_name)
            <div class="form-group">
                <label class="form-label">Nama Posyandu/Puskesmas</label>
                <input type="text" class="form-input" value="{{ auth()->user()->petugasProfile->posyandu_name }}" disabled>
                <input type="hidden" name="posyandu_name" value="{{ auth()->user()->petugasProfile->posyandu_name }}">
            </div>
            @else
            <div class="form-group">
                <label class="form-label">Nama Posyandu/Puskesmas</label>
                <input type="text" name="posyandu_name" class="form-input" value="{{ old('posyandu_name') }}" placeholder="Masukkan nama tempat pengukuran (opsional)">
                @error('posyandu_name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            @endif

            <div class="form-group">
                <label class="form-label">Tanggal Lahir Anak</label>
                <input type="date" name="birth_date" id="birthDateInput" class="form-input" value="{{ $selectedAnak?->tanggal_lahir?->format('Y-m-d') }}" max="{{ date('Y-m-d') }}" readonly style="background-color: var(--bg-color); cursor: default;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Jenis Kelamin</label>
                <div style="display: flex; gap: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="gender" value="L" {{ $selectedAnak?->jenis_kelamin == 'L' ? 'checked' : '' }} disabled> Laki-laki
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="gender" value="P" {{ $selectedAnak?->jenis_kelamin == 'P' ? 'checked' : '' }} disabled> Perempuan
                    </label>
                </div>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 6px;">Data identitas anak mengikuti data master yang dipilih.</p>
            </div>

            <div class="form-group">
                <label class="form-label">Tinggi Badan (cm) *</label>
                <input type="number" name="height_cm" id="heightInput" class="form-input" step="0.01" min="30" max="300"
                       value="{{ old('height_cm') }}" placeholder="Masukkan tinggi badan atau gunakan estimasi" required>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Hasil estimasi ML akan otomatis mengisi field ini</p>
                @error('height_cm')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Berat Badan (kg) *</label>
                <input type="number" name="weight_kg" id="weightInput" class="form-input" step="0.01" min="1" max="500"
                       value="{{ old('weight_kg') }}" placeholder="Masukkan berat badan atau gunakan estimasi" required>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Hasil estimasi ML akan otomatis mengisi field ini</p>
                @error('weight_kg')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Pengukuran *</label>
                <input type="date" name="measured_at" id="measuredAtInput" class="form-input" value="{{ old('measured_at', date('Y-m-d')) }}" required>
                @error('measured_at')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Usia Saat Diukur</label>
                <input type="text" id="ageDisplay" class="form-input" style="background-color: var(--bg-color); cursor: default;" readonly placeholder="Pilih tanggal lahir & pengukuran">
            </div>

            <div class="form-group">
                <label class="form-label">Catatan (opsional)</label>
                <textarea name="notes" class="form-textarea" placeholder="Tambahkan catatan...">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>



            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 14px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                Simpan Pengukuran
            </button>
        </div>
    </div>
</form>

@push('scripts')
@include('measurements.partials.measurement-script')

<script>

// Anak Autocomplete
const searchAnakInput = document.getElementById('search-anak');
const searchAnakResults = document.getElementById('search-results');
const autofillAnakInfo = document.getElementById('autofill-info');
let anakSearchTimer;

if (searchAnakInput) {
    searchAnakInput.addEventListener('input', function() {
        clearTimeout(anakSearchTimer);
        const q = this.value.trim();
        if (q.length < 3) { searchAnakResults.style.display = 'none'; return; }

        anakSearchTimer = setTimeout(async () => {
            try {
                const res = await fetch(`/measurements-search-anak?q=${encodeURIComponent(q)}`);
                const data = await res.json();
                renderAnakResults(data, q);
            } catch (e) { console.error(e); }
        }, 300);
    });

    document.addEventListener('click', (e) => {
        if (!searchAnakInput.contains(e.target) && !searchAnakResults.contains(e.target)) {
            searchAnakResults.style.display = 'none';
        }
    });
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = String(str ?? '');
    return d.innerHTML;
}

function renderAnakResults(data, q) {
    if (data.length === 0) {
        searchAnakResults.innerHTML = `<div class="search-result-item" style="color: var(--text-muted)">Tidak ditemukan anak yang cocok.</div>`;
    } else {
        searchAnakResults.innerHTML = data.map((item, i) => `
            <div class="search-result-item" onclick="selectAnak(${i})">
                <div style="font-weight: 600; font-size: 14px;">${escHtml(item.nama)}</div>
                <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                    NIK: ${escHtml(item.nik_anak) || '-'} • Ortu: ${escHtml(item.nama_ibu) || escHtml(item.nama_ayah) || '-'}
                </div>
            </div>
        `).join('');
        window._anakData = data;
    }
    searchAnakResults.style.display = 'block';
}

function selectAnak(i) {
    const d = window._anakData[i];

    const anakIdInput = document.getElementById('anakIdInput');
    const nikAnakInput = document.getElementById('nikAnakInput');
    const childNameInput = document.getElementById('childNameInput');
    const parentNameInput = document.getElementById('parentNameInput');
    const addressInput = document.getElementById('addressInput');

    if (anakIdInput) anakIdInput.value = d.id || '';
    if (nikAnakInput) nikAnakInput.value = d.nik_anak || '';
    if (childNameInput) childNameInput.value = d.nama || '';
    if (parentNameInput) parentNameInput.value = d.nama_ibu || d.nama_ayah || '';
    if (addressInput) addressInput.value = d.alamat || '';
    
    if (d.tanggal_lahir) {
        const dateStr = new Date(d.tanggal_lahir).toISOString().split('T')[0];
        document.getElementById('birthDateInput').value = dateStr;
    }
    
    if (d.jenis_kelamin) {
        const rad = document.querySelector(`input[name="gender"][value="${d.jenis_kelamin}"]`);
        if (rad) rad.checked = true;
    }

    if (typeof calculateAge === 'function') { calculateAge(); }
    if (typeof refreshAntropometri === 'function') { refreshAntropometri(); }

    searchAnakResults.style.display = 'none';
    searchAnakInput.value = `${d.nama || ''} (${d.nik_anak || '-'})`;
    autofillAnakInfo.style.display = 'block';
    
    setTimeout(() => { autofillAnakInfo.style.display = 'none'; }, 5000);
}
</script>
@endpush
@endsection

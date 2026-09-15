@extends('layouts.main')

@section('content')
@php
    $anak = $measurement->anak;
    $birthDate = $anak?->tanggal_lahir ?? $measurement->birth_date;
    $gender = $anak?->jenis_kelamin ?? $measurement->gender;
    $backUrl = $measurement->anak_id
        ? route('measurements.anak.show', $measurement->anak_id)
        : route('measurements.index');
@endphp
<div class="page-header">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
        <a href="{{ $backUrl }}" class="btn btn-secondary btn-sm" style="padding: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"/>
                <polyline points="12 19 5 12 12 5"/>
            </svg>
        </a>
        <h1 class="page-title">Edit Pengukuran</h1>
    </div>
    <p class="page-subtitle">{{ $measurement->child_name }} &middot; dicatat {{ $measurement->measured_at->translatedFormat('d F Y, H:i') }}</p>
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

<form method="POST" action="{{ route('measurements.update', $measurement) }}" enctype="multipart/form-data" id="measurementForm">
    @csrf
    @method('PUT')

    <div class="grid-2">
        <!-- Left: Camera & Photo -->
        @include('measurements.partials.camera-card', [
            'existingMeasurement' => $measurement,
            'manualSectionVisible' => true,
            'manualHeightValue' => old('manual_height_cm', $measurement->manual_height_cm),
            'manualWeightValue' => old('manual_weight_kg', $measurement->manual_weight_kg),
        ])

        <!-- Right: Form Input -->
        <div class="glass-card fade-in">
            <div class="chart-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-green)" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Data Pengukuran
            </div>

            <div style="margin-bottom: 20px; padding: 12px 14px; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.25); border-radius: 10px; font-size: 12px; color: var(--text-secondary);">
                Identitas anak tidak bisa diubah dari sini. Untuk memperbaiki data anak, gunakan menu Data Anak.
            </div>

            <div class="form-group">
                <label class="form-label">NIK Anak</label>
                <input type="text" class="form-input" value="{{ $anak?->nik_anak }}" readonly style="background-color: var(--bg-color); cursor: default;">
            </div>

            <div class="form-group">
                <label class="form-label">Nama Anak</label>
                <input type="text" class="form-input" value="{{ $measurement->child_name }}" readonly style="background-color: var(--bg-color); cursor: default;">
            </div>

            <div class="form-group">
                <label class="form-label">Nama Orang Tua</label>
                <input type="text" class="form-input" value="{{ $measurement->parent_name }}" readonly style="background-color: var(--bg-color); cursor: default;">
            </div>

            <div class="form-group">
                <label class="form-label">Nama Posyandu/Puskesmas</label>
                <input type="text" class="form-input" value="{{ $measurement->posyandu_name }}" readonly style="background-color: var(--bg-color); cursor: default;">
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Lahir Anak</label>
                <input type="date" id="birthDateInput" class="form-input" value="{{ $birthDate?->format('Y-m-d') }}" readonly style="background-color: var(--bg-color); cursor: default;">
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Jenis Kelamin</label>
                <div style="display: flex; gap: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="gender" value="L" {{ $gender === 'L' ? 'checked' : '' }} disabled> Laki-laki
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="gender" value="P" {{ $gender === 'P' ? 'checked' : '' }} disabled> Perempuan
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Tinggi Badan (cm) *</label>
                <input type="number" name="height_cm" id="heightInput" class="form-input" step="0.01" min="30" max="300"
                       value="{{ old('height_cm', $measurement->height_cm) }}" required>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Isi manual, atau ukur ulang otomatis lewat foto di sebelah kiri</p>
                @error('height_cm')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Berat Badan (kg) *</label>
                <input type="number" name="weight_kg" id="weightInput" class="form-input" step="0.01" min="1" max="500"
                       value="{{ old('weight_kg', $measurement->weight_kg) }}" required>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Isi manual, atau ukur ulang otomatis lewat foto di sebelah kiri</p>
                @error('weight_kg')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Pengukuran *</label>
                <input type="date" name="measured_at" id="measuredAtInput" class="form-input"
                       value="{{ old('measured_at', $measurement->measured_at->format('Y-m-d')) }}"
                       max="{{ date('Y-m-d') }}" required>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Mengubah tanggal akan menghitung ulang usia anak, Z-Score, dan status gizinya.</p>
                @error('measured_at')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Usia Saat Diukur</label>
                <input type="text" id="ageDisplay" class="form-input" style="background-color: var(--bg-color); cursor: default;" readonly>
            </div>

            <div class="form-group">
                <label class="form-label">Catatan (opsional)</label>
                <textarea name="notes" class="form-textarea" placeholder="Tambahkan catatan...">{{ old('notes', $measurement->notes) }}</textarea>
                @error('notes')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div style="display: flex; gap: 12px;">
                <a href="{{ $backUrl }}" class="btn btn-secondary" style="flex: 1; justify-content: center; padding: 14px;">Batal</a>
                <button type="submit" class="btn btn-primary" style="flex: 2; justify-content: center; padding: 14px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
@include('measurements.partials.measurement-script', [
    'resetValues' => [
        'height' => (string) old('height_cm', $measurement->height_cm),
        'weight' => (string) old('weight_kg', $measurement->weight_kg),
        'manual_height' => (string) old('manual_height_cm', $measurement->manual_height_cm),
        'manual_weight' => (string) old('manual_weight_kg', $measurement->manual_weight_kg),
    ],
    'initialMlValues' => [
        'height' => (float) $measurement->height_cm,
        'weight' => (float) $measurement->weight_kg,
    ],
    'keepManualVisible' => true,
])

<script>
// Tampilkan usia & status gizi tersimpan begitu halaman dibuka, tanpa menunggu
// petugas mengubah apa pun.
calculateAge();
refreshAntropometri();
</script>
@endpush
@endsection

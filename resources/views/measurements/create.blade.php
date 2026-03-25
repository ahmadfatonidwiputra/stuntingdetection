@extends('layouts.main')

@section('content')
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

<form method="POST" action="{{ route('measurements.store') }}" enctype="multipart/form-data" id="measurementForm">
    @csrf

    <div class="grid-2">
        <!-- Left: Camera & Photo -->
        <div class="glass-card fade-in">
            <div class="chart-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-blue)" stroke-width="2">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                </svg>
                Kamera & Estimasi ML
            </div>

            <div class="camera-container" id="cameraContainer">
                <video id="cameraVideo" autoplay playsinline style="display:block;"></video>
                <canvas id="cameraCanvas" style="display:none;"></canvas>
                <img id="capturedPhoto" style="display:none;" />
                <div class="loading-overlay" id="loadingOverlay" style="display:none;">
                    <div class="spinner"></div>
                    <div class="loading-text">Menganalisis pose...</div>
                </div>
            </div>

            <div class="camera-controls">
                <button type="button" class="camera-btn camera-btn-capture" id="btnStartCamera" onclick="startCamera()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                    Aktifkan Kamera
                </button>
                <button type="button" class="camera-btn camera-btn-capture" id="btnCapture" onclick="capturePhoto()" style="display:none;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    Ambil Foto
                </button>
                <button type="button" class="camera-btn camera-btn-reset" id="btnReset" onclick="resetCamera()" style="display:none;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="1 4 1 10 7 10"/>
                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
                    </svg>
                    Ulangi
                </button>
            </div>

            <div style="margin-top: 16px;">
                <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">Atau upload foto:</p>
                <input type="file" name="photo" accept="image/*" class="form-input" id="photoUpload" onchange="handlePhotoUpload(event)">
            </div>

            <input type="hidden" name="photo_base64" id="photoBase64">
            <input type="hidden" name="pose_photo_base64" id="posePhotoBase64">

            <!-- Estimation Result -->
            <div class="measurement-result" id="estimationResult" style="display:none; gap: 12px;">
                <div class="result-card">
                    <div class="result-value" id="estimatedHeight">-</div>
                    <div class="result-unit">Estimasi Tinggi (cm)</div>
                </div>
                <div class="result-card">
                    <div class="result-value" id="estimatedWeight">-</div>
                    <div class="result-unit">Estimasi Berat (kg)</div>
                </div>
            </div>

            <div style="margin-top: 12px; padding: 12px; background: rgba(59, 130, 246, 0.1); border-radius: 10px; border: 1px solid rgba(59, 130, 246, 0.2);">
                <p style="font-size: 12px; color: var(--accent-blue); margin-bottom: 4px; font-weight: 600;">💡 Tips untuk hasil akurat:</p>
                <ul style="font-size: 11px; color: var(--text-muted); list-style: disc; padding-left: 16px; line-height: 1.8;">
                    <li>Berdiri tegak menghadap kamera, seluruh tubuh terlihat dari kepala hingga kaki</li>
                    <li>Pastikan pencahayaan cukup terang</li>
                    <li>Masukkan tinggi referensi (tinggi sebenarnya) jika diketahui untuk kalibrasi</li>
                    <li>Jarak kamera ±2-3 meter dari subjek</li>
                </ul>
            </div>
        </div>

        <!-- Right: Form Input -->
        <div class="glass-card fade-in">
            <div class="chart-title">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-green)" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                Data Pengukuran
            </div>

            <div class="form-group">
                <label class="form-label">Nama Anak *</label>
                <input type="text" name="child_name" class="form-input" value="{{ old('child_name') }}" placeholder="Masukkan nama anak" required>
                @error('child_name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Nama Orang Tua *</label>
                <input type="text" name="parent_name" class="form-input" value="{{ old('parent_name') }}" placeholder="Masukkan nama orang tua" required>
                @error('parent_name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Alamat *</label>
                <textarea name="address" class="form-textarea" placeholder="Masukkan alamat lengkap" required>{{ old('address') }}</textarea>
                @error('address')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Nama Posyandu/Puskesmas</label>
                <input type="text" name="posyandu_name" class="form-input" value="{{ old('posyandu_name') }}" placeholder="Masukkan nama tempat pengukuran (opsional)">
                @error('posyandu_name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Lahir Anak *</label>
                <input type="date" name="birth_date" id="birthDateInput" class="form-input" value="{{ old('birth_date') }}" max="{{ date('Y-m-d') }}" required>
                @error('birth_date')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Jenis Kelamin *</label>
                <div style="display: flex; gap: 16px;">
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="gender" value="L" {{ old('gender') == 'L' ? 'checked' : '' }} required> Laki-laki
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px;">
                        <input type="radio" name="gender" value="P" {{ old('gender') == 'P' ? 'checked' : '' }} required> Perempuan
                    </label>
                </div>
                @error('gender')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tinggi Badan (cm) *</label>
                <input type="number" name="height_cm" id="heightInput" class="form-input" step="0.1" min="30" max="300"
                       value="{{ old('height_cm') }}" placeholder="Masukkan tinggi badan atau gunakan estimasi" required>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Hasil estimasi ML akan otomatis mengisi field ini</p>
                @error('height_cm')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Berat Badan (kg) *</label>
                <input type="number" name="weight_kg" id="weightInput" class="form-input" step="0.1" min="1" max="500"
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
<script>
let stream = null;
const PREDICT_URL = '{{ route("measurements.predict") }}';
const CSRF_TOKEN = '{{ csrf_token() }}';

// Hitung usia
const birthDateInput = document.getElementById('birthDateInput');
const measuredAtInput = document.getElementById('measuredAtInput');
const ageDisplay = document.getElementById('ageDisplay');

function calculateAge() {
    if (!birthDateInput.value || !measuredAtInput.value) {
        ageDisplay.value = '';
        return;
    }

    const start = new Date(birthDateInput.value);
    const end = new Date(measuredAtInput.value);

    if (end < start) {
        ageDisplay.value = 'Tanggal pengukuran tidak boleh mendahului tanggal lahir';
        return;
    }

    let years = end.getFullYear() - start.getFullYear();
    let months = end.getMonth() - start.getMonth();
    let days = end.getDate() - start.getDate();

    if (days < 0) {
        months -= 1;
        const prevMonth = new Date(end.getFullYear(), end.getMonth(), 0);
        days += prevMonth.getDate();
    }

    if (months < 0) {
        years -= 1;
        months += 12;
    }

    let ageString = [];
    if (years > 0) ageString.push(years + ' tahun');
    if (months > 0) ageString.push(months + ' bulan');
    if (days > 0 || (years === 0 && months === 0)) ageString.push(days + ' hari');
    
    ageDisplay.value = ageString.join(' ');
}

if(birthDateInput && measuredAtInput && ageDisplay) {
    birthDateInput.addEventListener('change', calculateAge);
    measuredAtInput.addEventListener('change', calculateAge);
    document.addEventListener('DOMContentLoaded', calculateAge);
}

// Camera functions
async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 960 } }
        });
        const video = document.getElementById('cameraVideo');
        video.srcObject = stream;
        video.style.display = 'block';
        document.getElementById('capturedPhoto').style.display = 'none';
        document.getElementById('cameraCanvas').style.display = 'none';
        document.getElementById('btnStartCamera').style.display = 'none';
        document.getElementById('btnCapture').style.display = 'inline-flex';
        document.getElementById('btnReset').style.display = 'none';
    } catch (err) {
        alert('Tidak bisa mengakses kamera: ' + err.message);
    }
}

async function capturePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const ctx = canvas.getContext('2d');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    ctx.drawImage(video, 0, 0);

    // Stop camera
    if (stream) {
        stream.getTracks().forEach(t => t.stop());
    }

    // Show captured photo
    const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
    document.getElementById('capturedPhoto').src = dataUrl;
    document.getElementById('capturedPhoto').style.display = 'block';
    document.getElementById('cameraVideo').style.display = 'none';
    document.getElementById('photoBase64').value = dataUrl;

    document.getElementById('btnCapture').style.display = 'none';
    document.getElementById('btnReset').style.display = 'inline-flex';

    // Convert canvas to blob and send to ML API
    canvas.toBlob(blob => {
        sendToMLApi(blob);
    }, 'image/jpeg', 0.8);
}

function handlePhotoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('capturedPhoto').src = e.target.result;
        document.getElementById('capturedPhoto').style.display = 'block';
        document.getElementById('cameraVideo').style.display = 'none';
    };
    reader.readAsDataURL(file);

    // Send the file directly to ML API
    sendToMLApi(file);
}

async function sendToMLApi(imageBlob) {
    const loading = document.getElementById('loadingOverlay');
    loading.style.display = 'flex';
    document.querySelector('#loadingOverlay .loading-text').textContent = 'Menganalisis dengan ML model...';

    try {
        const formData = new FormData();
        formData.append('image', imageBlob, 'photo.jpg');
        formData.append('_token', CSRF_TOKEN);

        const response = await fetch(PREDICT_URL, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            const errData = await response.json().catch(() => ({}));
            throw new Error(errData.error || 'Server error ' + response.status);
        }

        const data = await response.json();

        // Fill pose photo base64
        if (data.pose_image_base64) {
            document.getElementById('posePhotoBase64').value = data.pose_image_base64;
        }
        if (data.height_cm !== null && data.height_cm !== undefined) {
            document.getElementById('estimatedHeight').textContent = data.height_cm;
            document.getElementById('heightInput').value = data.height_cm;
        } else {
            document.getElementById('estimatedHeight').textContent = '-';
            if (data.height_error) {
                console.warn('Height estimation error:', data.height_error);
            }
        }

        // Fill weight
        if (data.weight_kg !== null && data.weight_kg !== undefined) {
            document.getElementById('estimatedWeight').textContent = data.weight_kg;
            document.getElementById('weightInput').value = data.weight_kg;
        } else {
            document.getElementById('estimatedWeight').textContent = '-';
            if (data.weight_error) {
                console.warn('Weight estimation error:', data.weight_error);
            }
        }

        document.getElementById('estimationResult').style.display = 'flex';
    } catch (err) {
        console.error('ML API error:', err);
        alert('Error saat prediksi ML: ' + err.message + '\n\nPastikan server Python (predict_api.py) sedang berjalan.\nSilakan masukkan tinggi & berat badan secara manual.');
    } finally {
        loading.style.display = 'none';
    }
}

function resetCamera() {
    document.getElementById('capturedPhoto').style.display = 'none';
    document.getElementById('cameraCanvas').style.display = 'none';
    document.getElementById('photoBase64').value = '';
    document.getElementById('estimationResult').style.display = 'none';
    document.getElementById('btnReset').style.display = 'none';
    document.getElementById('btnStartCamera').style.display = 'inline-flex';
    document.getElementById('cameraVideo').style.display = 'block';
    document.getElementById('photoUpload').value = '';
    document.getElementById('heightInput').value = '';
    document.getElementById('weightInput').value = '';
    document.getElementById('posePhotoBase64').value = '';
}
</script>
@endpush
@endsection

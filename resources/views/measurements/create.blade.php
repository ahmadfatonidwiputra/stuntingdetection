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
                Kamera & Estimasi Tinggi
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

            <!-- Estimation Result -->
            <div class="measurement-result" id="estimationResult" style="display:none;">
                <div class="result-card">
                    <div class="result-value" id="estimatedHeight">-</div>
                    <div class="result-unit">Estimasi Tinggi (cm)</div>
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
                <label class="form-label">Tinggi Badan (cm) *</label>
                <input type="number" name="height_cm" id="heightInput" class="form-input" step="0.1" min="30" max="300"
                       value="{{ old('height_cm') }}" placeholder="Masukkan tinggi badan atau gunakan estimasi" required>
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Hasil estimasi kamera akan otomatis mengisi field ini</p>
                @error('height_cm')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Berat Badan (kg) *</label>
                <input type="number" name="weight_kg" id="weightInput" class="form-input" step="0.1" min="1" max="500"
                       value="{{ old('weight_kg') }}" placeholder="Masukkan berat badan" required>
                @error('weight_kg')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Tanggal Pengukuran *</label>
                <input type="date" name="measured_at" class="form-input" value="{{ old('measured_at', date('Y-m-d')) }}" required>
                @error('measured_at')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Catatan (opsional)</label>
                <textarea name="notes" class="form-textarea" placeholder="Tambahkan catatan...">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <!-- BMI Preview -->
            <div class="glass-card" id="bmiPreview" style="background: var(--bg-glass); display: none; margin-bottom: 20px;">
                <div style="text-align: center;">
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;">Preview BMI</div>
                    <div id="bmiPreviewValue" style="font-size: 36px; font-weight: 800; background: var(--gradient-1); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">-</div>
                    <div id="bmiPreviewCategory" class="badge" style="margin-top: 8px;">-</div>
                </div>
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
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest/wasm/vision_wasm_internal.js"></script>
<script>
let stream = null;
let mediaPipeLoaded = false;
let poseLandmarker = null;

// BMI live preview
const heightInput = document.getElementById('heightInput');
const weightInput = document.getElementById('weightInput');

function updateBmiPreview() {
    const h = parseFloat(heightInput.value);
    const w = parseFloat(weightInput.value);
    const preview = document.getElementById('bmiPreview');
    const valueEl = document.getElementById('bmiPreviewValue');
    const catEl = document.getElementById('bmiPreviewCategory');

    if (h > 0 && w > 0) {
        const hm = h / 100;
        const bmi = (w / (hm * hm)).toFixed(1);
        let category, catClass;

        if (bmi < 18.5) { category = 'Kurus'; catClass = 'badge-kurus'; }
        else if (bmi < 25) { category = 'Normal'; catClass = 'badge-normal'; }
        else if (bmi < 30) { category = 'Gemuk'; catClass = 'badge-gemuk'; }
        else { category = 'Obesitas'; catClass = 'badge-obesitas'; }

        valueEl.textContent = bmi;
        catEl.textContent = category;
        catEl.className = 'badge ' + catClass;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

heightInput.addEventListener('input', updateBmiPreview);
weightInput.addEventListener('input', updateBmiPreview);

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

    // Estimate height
    estimateHeight(canvas, ctx);
}

function handlePhotoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const img = new Image();
        img.onload = function() {
            const canvas = document.getElementById('cameraCanvas');
            const ctx = canvas.getContext('2d');
            canvas.width = img.width;
            canvas.height = img.height;
            ctx.drawImage(img, 0, 0);

            document.getElementById('capturedPhoto').src = e.target.result;
            document.getElementById('capturedPhoto').style.display = 'block';
            document.getElementById('cameraVideo').style.display = 'none';

            estimateHeight(canvas, ctx);
        };
        img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

async function estimateHeight(canvas, ctx) {
    const loading = document.getElementById('loadingOverlay');
    loading.style.display = 'flex';

    try {
        // Use MediaPipe Pose Detection via CDN
        const { PoseLandmarker, FilesetResolver, DrawingUtils } = await import(
            'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest'
        );

        const vision = await FilesetResolver.forVisionTasks(
            'https://cdn.jsdelivr.net/npm/@mediapipe/tasks-vision@latest/wasm'
        );

        const landmarker = await PoseLandmarker.createFromOptions(vision, {
            baseOptions: {
                modelAssetPath: 'https://storage.googleapis.com/mediapipe-models/pose_landmarker/pose_landmarker_lite/float16/1/pose_landmarker_lite.task',
                delegate: 'GPU'
            },
            runningMode: 'IMAGE',
            numPoses: 1
        });

        // Create an image element for detection
        const imgEl = new Image();
        imgEl.src = canvas.toDataURL();
        await new Promise(resolve => imgEl.onload = resolve);

        const result = landmarker.detect(imgEl);

        if (result.landmarks && result.landmarks.length > 0) {
            const landmarks = result.landmarks[0];

            // Key points: nose(0), left_ankle(27), right_ankle(28), left_eye(1), right_eye(4)
            // Top of head estimation: use midpoint above nose
            const nose = landmarks[0];
            const leftEye = landmarks[1];
            const rightEye = landmarks[4];
            const leftAnkle = landmarks[27];
            const rightAnkle = landmarks[28];
            const leftHeel = landmarks[29];
            const rightHeel = landmarks[30];

            // Get the highest and lowest points
            const headY = nose.y - (nose.y - ((leftEye.y + rightEye.y) / 2)) * 2.5; // estimate top of head
            const feetY = Math.max(leftHeel.y, rightHeel.y, leftAnkle.y, rightAnkle.y);

            // Height in normalized coordinates
            const heightRatio = feetY - headY;

            // Estimate real height using body proportion heuristics
            // Average human body is about 7.5 head heights
            // We estimate using the ratio of body to image
            const pixelHeight = heightRatio * canvas.height;

            // Use a calibration factor - assume average camera setup
            // This is a rough estimate; real-world calibration would need known reference
            const estimatedHeightCm = Math.round(pixelHeight * 0.28 * 10) / 10; // rough scaling

            // Clamp to reasonable range
            const clampedHeight = Math.max(50, Math.min(250, estimatedHeightCm));

            document.getElementById('estimatedHeight').textContent = clampedHeight.toFixed(1);
            document.getElementById('estimationResult').style.display = 'flex';
            document.getElementById('heightInput').value = clampedHeight.toFixed(1);
            updateBmiPreview();

            // Draw landmarks on canvas
            canvas.style.display = 'block';
            document.getElementById('capturedPhoto').style.display = 'none';

            const drawingUtils = new DrawingUtils(ctx);
            drawingUtils.drawLandmarks(landmarks, {
                radius: 3,
                color: '#3b82f6',
                lineWidth: 1
            });
            drawingUtils.drawConnectors(landmarks, PoseLandmarker.POSE_CONNECTIONS, {
                color: '#8b5cf6',
                lineWidth: 2
            });
        } else {
            alert('Tidak dapat mendeteksi pose tubuh. Pastikan seluruh tubuh terlihat dari kepala hingga kaki.');
        }

        landmarker.close();
    } catch (err) {
        console.error('Estimation error:', err);
        alert('Error saat estimasi: ' + err.message + '. Silakan masukkan tinggi badan secara manual.');
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
}
</script>
@endpush
@endsection

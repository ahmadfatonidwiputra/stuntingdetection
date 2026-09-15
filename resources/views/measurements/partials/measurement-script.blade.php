@php
    // Skrip kamera + estimasi ML + status gizi, dipakai bersama oleh form
    // "Pengukuran Baru" dan "Edit Pengukuran".
    //   $resetValues   : nilai yang dikembalikan tombol "Ulangi" (kosong saat membuat baru,
    //                    nilai tersimpan saat mengedit)
    //   $initialMlValues : tinggi/berat acuan untuk menghitung selisih ukur manual
    //   $keepManualVisible : biarkan blok ukur manual tetap tampil setelah "Ulangi"
    $resetValues = $resetValues ?? ['height' => '', 'weight' => '', 'manual_height' => '', 'manual_weight' => ''];
    $initialMlValues = $initialMlValues ?? ['height' => null, 'weight' => null];
    $keepManualVisible = $keepManualVisible ?? false;
@endphp
<script>
const RESET_VALUES = @json($resetValues);
const KEEP_MANUAL_VISIBLE = @json($keepManualVisible);

let stream = null;
let lastMlHeight = @json($initialMlValues['height']);
let lastMlWeight = @json($initialMlValues['weight']);
const PREDICT_URL = '{{ route("measurements.predict") }}';
const WARMUP_URL = '{{ route("measurements.warmup") }}';
const ANTROPOMETRI_URL = '{{ route("measurements.antropometri") }}';
const CSRF_TOKEN = '{{ csrf_token() }}';

// Nudge the ML API awake as soon as this page loads, so it's hopefully
// no longer cold-starting by the time the user submits a photo.
fetch(WARMUP_URL, {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
}).catch(() => {});

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

// Decode a user-picked file into something drawable on a canvas.
// Tried in order because each step fails on a different class of file/browser:
//   1. createImageBitmap() decodes the File directly - no URL, so it is immune
//      to CSP img-src rules, and it handles every format the browser knows.
//   2. a data: URL from FileReader - works when a blob: URL would be blocked.
//   3. a blob: URL - cheapest on memory, last resort for old browsers.
async function decodeImageFile(file) {
    if (window.createImageBitmap) {
        try {
            return await createImageBitmap(file);
        } catch (e) {
            console.warn('createImageBitmap gagal, fallback ke <img>:', e);
        }
    }

    const loadVia = (src) => new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => resolve(img);
        img.onerror = () => reject(new Error('Browser tidak bisa membaca format gambar ini.'));
        img.src = src;
    });

    try {
        const dataUrl = await new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = () => reject(new Error('Gagal membaca isi file.'));
            reader.readAsDataURL(file);
        });
        return await loadVia(dataUrl);
    } catch (e) {
        console.warn('Decode via data URL gagal, fallback ke object URL:', e);
    }

    const objectUrl = URL.createObjectURL(file);
    try {
        return await loadVia(objectUrl);
    } finally {
        URL.revokeObjectURL(objectUrl);
    }
}

async function handlePhotoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    let img;
    try {
        img = await decodeImageFile(file);
    } catch (err) {
        console.error('Gagal decode foto:', file.name, file.type, err);

        // iPhone HEIC/HEIF is the usual culprit: the file picker offers it but
        // Chrome/Firefox cannot decode it, so say what to do about it.
        const name = (file.name || '').toLowerCase();
        const isHeic = /\.(heic|heif)$/.test(name) || /heic|heif/.test(file.type || '');
        alert(isHeic
            ? 'Format foto HEIC (bawaan iPhone) belum didukung browser ini.\n\nUbah ke JPG/PNG dulu, atau di iPhone: Pengaturan > Kamera > Format > Paling Kompatibel.'
            : 'Gagal membaca foto. Silakan pilih file gambar lain (JPG atau PNG).');
        event.target.value = '';
        return;
    }

    // Downscale gallery photos to the same size as camera captures.
    // Full-resolution phone photos (several MB) were slow enough on the
    // ML API to trip Heroku's 30s request timeout (503).
    const MAX_DIM = 1280;
    let width = img.width;
    let height = img.height;
    if (width > MAX_DIM || height > MAX_DIM) {
        const scale = MAX_DIM / Math.max(width, height);
        width = Math.round(width * scale);
        height = Math.round(height * scale);
    }

    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    canvas.getContext('2d').drawImage(img, 0, 0, width, height);
    if (typeof img.close === 'function') img.close();

    const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
    document.getElementById('capturedPhoto').src = dataUrl;
    document.getElementById('capturedPhoto').style.display = 'block';
    document.getElementById('cameraVideo').style.display = 'none';
    document.getElementById('photoBase64').value = dataUrl;

    canvas.toBlob(blob => {
        sendToMLApi(blob);
    }, 'image/jpeg', 0.8);
}

async function sendToMLApi(imageBlob, isRetry = false) {
    const loading = document.getElementById('loadingOverlay');
    loading.style.display = 'flex';
    document.querySelector('#loadingOverlay .loading-text').textContent = isRetry
        ? 'Model AI baru saja bangun, mencoba lagi...'
        : 'Menganalisis dengan ML model...';

    try {
        const formData = new FormData();
        formData.append('image', imageBlob, 'photo.jpg');
        formData.append('_token', CSRF_TOKEN);

        // Use XHR (not fetch) so we can measure how long it takes to upload
        // the image bytes to the server, separate from the model's processing time.
        const uploadStart = performance.now();
        let uploadDurationMs = null;

        const { httpStatus, ok, data } = await new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', PREDICT_URL);
            xhr.setRequestHeader('X-CSRF-TOKEN', CSRF_TOKEN);
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.upload.addEventListener('load', () => {
                uploadDurationMs = performance.now() - uploadStart;
            });

            xhr.onload = () => {
                let parsed = {};
                try {
                    parsed = JSON.parse(xhr.responseText);
                } catch (e) {}
                resolve({
                    httpStatus: xhr.status,
                    ok: xhr.status >= 200 && xhr.status < 300,
                    data: parsed
                });
            };
            xhr.onerror = () => reject(new Error('Gagal mengupload gambar ke server.'));

            xhr.send(formData);
        });

        if (!ok) {
            // First failure is often a cold-start on the ML API side; retry once automatically.
            if (!isRetry) {
                return sendToMLApi(imageBlob, true);
            }
            throw new Error(data.error || 'Server error ' + httpStatus);
        }

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

        const durationEl = document.getElementById('predictionDuration');
        if (data.duration_ms !== null && data.duration_ms !== undefined) {
            durationEl.textContent = '⏱ Waktu prediksi model: ' + (data.duration_ms / 1000).toFixed(2) + ' detik';
        } else {
            durationEl.textContent = '';
        }

        const uploadDurationEl = document.getElementById('uploadDuration');
        if (uploadDurationMs !== null) {
            uploadDurationEl.textContent = '📤 Waktu upload gambar: ' + (uploadDurationMs / 1000).toFixed(2) + ' detik';
        } else {
            uploadDurationEl.textContent = '';
        }

        document.getElementById('estimationResult').style.display = 'flex';
        document.getElementById('manualCompareSection').style.display = 'block';
        lastMlHeight = data.height_cm ?? null;
        lastMlWeight = data.weight_kg ?? null;
        updateManualCompare();

        if (data.height_cm !== null && data.height_cm !== undefined && data.weight_kg !== null && data.weight_kg !== undefined) {
            fetchAntropometri(data.height_cm, data.weight_kg);
        }
    } catch (err) {
        console.error('ML API error:', err);
        alert('Error saat prediksi ML: ' + err.message + '\n\nLayanan prediksi mungkin sedang tidak tersedia.\nSilakan masukkan tinggi & berat badan secara manual.');
    } finally {
        loading.style.display = 'none';
    }
}

// Hitung status gizi (Z-Score BB/U, PB/U-TB/U, BB/PB-BB/TB, IMT/U) berdasarkan
// data anak yang sudah dipilih (tanggal lahir & jenis kelamin) + hasil tinggi/berat dari model.
async function fetchAntropometri(heightCm, weightKg) {
    const resultBox = document.getElementById('antropometriResult');
    const grid = document.getElementById('antropometriGrid');
    const durationEl = document.getElementById('antropometriDuration');

    const genderRadio = document.querySelector('input[name="gender"]:checked');
    const birthDate = birthDateInput.value;
    const measuredAt = measuredAtInput.value;

    resultBox.style.display = 'block';

    if (!genderRadio || !birthDate || !measuredAt) {
        grid.innerHTML = '<div style="font-size: 13px; color: var(--text-muted);">Pilih data anak terlebih dahulu untuk menghitung status gizi.</div>';
        durationEl.textContent = '';
        return;
    }

    try {
        const response = await fetch(ANTROPOMETRI_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                gender: genderRadio.value,
                birth_date: birthDate,
                measured_at: measuredAt,
                height_cm: heightCm,
                weight_kg: weightKg
            })
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || 'Gagal menghitung status gizi.');
        }

        const cards = [
            { key: 'bb_u', label: 'BB/U (Berat Badan menurut Umur)' },
            { key: 'pb_tb_u', label: 'PB/U atau TB/U' },
            { key: 'bb_pb_tb', label: 'BB/PB atau BB/TB' },
            { key: 'imt_u', label: 'IMT/U' + (result.imt ? ' · ' + result.imt + ' kg/m²' : '') },
        ];

        grid.innerHTML = cards.map(function (c) {
            const indikator = result[c.key];
            const zText = (indikator && indikator.z !== null && indikator.z !== undefined)
                ? Number(indikator.z).toFixed(2) + ' SD'
                : '-';
            const severity = indikator ? indikator.severity : 'unknown';
            const label = indikator ? indikator.label : 'Tidak dapat dihitung';

            return '<div class="antro-status-card">'
                + '<div class="antro-status-label">' + c.label + '</div>'
                + '<div class="antro-status-z">' + zText + '</div>'
                + '<span class="severity-pill severity-' + severity + '">' + label + '</span>'
                + '</div>';
        }).join('');

        durationEl.textContent = (result.duration_ms !== null && result.duration_ms !== undefined)
            ? '⏱ Waktu hitung status gizi: ' + result.duration_ms + ' ms'
            : '';
    } catch (err) {
        console.error('Antropometri error:', err);
        grid.innerHTML = '<div style="font-size: 13px; color: var(--text-muted);">' + err.message + '</div>';
        durationEl.textContent = '';
    }
}

// Compare the petugas's manual measurement against the ML estimate as they type,
// so they can see the discrepancy immediately instead of only after saving.
function updateManualCompare() {
    const resultEl = document.getElementById('manualCompareResult');
    const manualHeight = parseFloat(document.getElementById('manualHeightInput').value);
    const manualWeight = parseFloat(document.getElementById('manualWeightInput').value);

    const parts = [];
    if (!isNaN(manualHeight) && lastMlHeight !== null) {
        const diff = (manualHeight - lastMlHeight).toFixed(2);
        parts.push(`Selisih tinggi: ${diff > 0 ? '+' : ''}${diff} cm`);
    }
    if (!isNaN(manualWeight) && lastMlWeight !== null) {
        const diff = (manualWeight - lastMlWeight).toFixed(2);
        parts.push(`Selisih berat: ${diff > 0 ? '+' : ''}${diff} kg`);
    }
    resultEl.textContent = parts.join(' • ');
}

document.getElementById('manualHeightInput').addEventListener('input', updateManualCompare);
document.getElementById('manualWeightInput').addEventListener('input', updateManualCompare);

// "Ulangi" membuang foto/hasil ML yang baru diambil dan mengembalikan isian ke
// RESET_VALUES: kosong pada form pengukuran baru, nilai tersimpan saat mengedit
// sehingga membatalkan pengukuran ulang tidak ikut menghapus data lama.
function resetCamera() {
    document.getElementById('capturedPhoto').style.display = 'none';
    document.getElementById('cameraCanvas').style.display = 'none';
    document.getElementById('photoBase64').value = '';
    document.getElementById('estimationResult').style.display = 'none';
    document.getElementById('manualCompareSection').style.display = KEEP_MANUAL_VISIBLE ? 'block' : 'none';
    document.getElementById('btnReset').style.display = 'none';
    document.getElementById('btnStartCamera').style.display = 'inline-flex';
    document.getElementById('cameraVideo').style.display = 'block';
    document.getElementById('photoUpload').value = '';
    document.getElementById('heightInput').value = RESET_VALUES.height ?? '';
    document.getElementById('weightInput').value = RESET_VALUES.weight ?? '';
    document.getElementById('manualHeightInput').value = RESET_VALUES.manual_height ?? '';
    document.getElementById('manualWeightInput').value = RESET_VALUES.manual_weight ?? '';
    document.getElementById('manualCompareResult').textContent = '';
    document.getElementById('posePhotoBase64').value = '';
    lastMlHeight = RESET_VALUES.height !== '' && RESET_VALUES.height !== null ? parseFloat(RESET_VALUES.height) : null;
    lastMlWeight = RESET_VALUES.weight !== '' && RESET_VALUES.weight !== null ? parseFloat(RESET_VALUES.weight) : null;
    updateManualCompare();
    refreshAntropometri();
}

// Status gizi ikut berubah begitu tinggi/berat/tanggal pengukuran diubah manual,
// bukan hanya setelah prediksi ML, supaya petugas langsung melihat dampak koreksinya.
let antropometriTimer;
function refreshAntropometri() {
    clearTimeout(antropometriTimer);
    antropometriTimer = setTimeout(() => {
        const height = parseFloat(document.getElementById('heightInput').value);
        const weight = parseFloat(document.getElementById('weightInput').value);
        const genderPicked = document.querySelector('input[name="gender"]:checked');
        // Tanpa data anak (jenis kelamin & tanggal lahir) Z-Score tidak bisa dihitung,
        // jadi diamkan saja sampai anaknya dipilih.
        if (isNaN(height) || isNaN(weight) || !genderPicked || !birthDateInput.value || !measuredAtInput.value) {
            document.getElementById('antropometriResult').style.display = 'none';
            return;
        }
        fetchAntropometri(height, weight);
    }, 500);
}

['heightInput', 'weightInput', 'measuredAtInput'].forEach(function (id) {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', refreshAntropometri);
});

// Prevent duplicate rows from double-clicking/double-tapping submit while
// the request is in flight (server-side lock in MeasurementController@store
// guards against the same thing at the network/race-condition level).
document.getElementById('measurementForm').addEventListener('submit', function (event) {
    const submitBtn = event.target.querySelector('button[type="submit"]');
    if (submitBtn.disabled) {
        event.preventDefault();
        return;
    }
    submitBtn.disabled = true;
    submitBtn.textContent = 'Menyimpan...';
});
</script>


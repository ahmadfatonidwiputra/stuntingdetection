@php
    // Dipakai bersama oleh form "Pengukuran Baru" dan "Edit Pengukuran".
    //   $existingMeasurement  : pengukuran yang sedang diedit (null saat membuat baru)
    //   $manualSectionVisible : tampilkan blok ukur manual tanpa menunggu hasil ML
    $existingMeasurement = $existingMeasurement ?? null;
    $manualSectionVisible = $manualSectionVisible ?? false;
    $manualHeightValue = $manualHeightValue ?? old('manual_height_cm');
    $manualWeightValue = $manualWeightValue ?? old('manual_weight_kg');
@endphp
<div class="glass-card fade-in">
    <div class="chart-title">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--accent-blue)" stroke-width="2">
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
            <circle cx="12" cy="13" r="4"/>
        </svg>
        Kamera & Estimasi ML
    </div>

    @if($existingMeasurement && ($existingMeasurement->photo_path || $existingMeasurement->pose_photo_path))
        <div style="margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px dashed var(--glass-border);">
            <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px; font-weight: 600;">🖼 Foto tersimpan saat ini</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
                @if($existingMeasurement->photo_path)
                    <div>
                        <p style="font-size: 11px; color: var(--text-muted); text-align: center; margin-bottom: 6px;">Foto Asli</p>
                        <img src="{{ $existingMeasurement->photo_url }}" alt="Foto pengukuran tersimpan"
                             style="width: 100%; border-radius: 10px; border: 1px solid var(--glass-border);"
                             onerror="this.src='{{ $existingMeasurement->photo_url_fallback }}';this.onerror=function(){this.style.display='none'}">
                    </div>
                @endif
                @if($existingMeasurement->pose_photo_path)
                    <div>
                        <p style="font-size: 11px; color: var(--text-muted); text-align: center; margin-bottom: 6px;">MediaPipe Pose</p>
                        <img src="{{ $existingMeasurement->pose_photo_url }}" alt="Foto pose ML tersimpan"
                             style="width: 100%; border-radius: 10px; border: 1px solid var(--glass-border);"
                             onerror="this.src='{{ $existingMeasurement->pose_photo_url_fallback }}';this.onerror=function(){this.style.display='none'}">
                    </div>
                @endif
            </div>
            <p style="font-size: 11px; color: var(--text-muted); margin-top: 8px;">Ambil atau upload foto baru di bawah untuk mengukur ulang otomatis. Foto lama baru diganti setelah perubahan disimpan.</p>
        </div>
    @endif

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
        <input type="file" accept="image/*" class="form-input" id="photoUpload" onchange="handlePhotoUpload(event)">
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
        <p id="predictionDuration" style="flex-basis: 100%; font-size: 11px; color: var(--text-muted); text-align: center; margin: 0;"></p>
        <p id="uploadDuration" style="flex-basis: 100%; font-size: 11px; color: var(--text-muted); text-align: center; margin: 0;"></p>
    </div>

    <!-- Ukur Manual untuk Perbandingan dengan hasil ML -->
    <div id="manualCompareSection" style="display:{{ $manualSectionVisible ? 'block' : 'none' }}; margin-top: 16px; padding-top: 16px; border-top: 1px dashed var(--glass-border);">
        <p style="font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 10px;">📏 Ukur Manual untuk Perbandingan (opsional)</p>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Tinggi Manual (cm)</label>
                <input type="number" name="manual_height_cm" id="manualHeightInput" class="form-input" step="0.01" min="30" max="150" value="{{ $manualHeightValue }}" placeholder="Hasil ukur alat manual">
                @error('manual_height_cm')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label" style="font-size: 12px;">Berat Manual (kg)</label>
                <input type="number" name="manual_weight_kg" id="manualWeightInput" class="form-input" step="0.01" min="1" max="50" value="{{ $manualWeightValue }}" placeholder="Hasil timbangan manual">
                @error('manual_weight_kg')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <p id="manualCompareResult" style="font-size: 12px; color: var(--text-muted); margin-top: 8px;"></p>
    </div>

    <!-- Status Gizi (Z-Score BB/U, PB/U-TB/U, BB/PB-BB/TB, IMT/U) -->
    <div id="antropometriResult" style="display:none; margin-top: 16px;">
        <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px; font-weight: 600;">📊 Status Gizi (berdasarkan data anak terpilih)</p>
        <div class="antro-status-grid" id="antropometriGrid"></div>
        <p id="antropometriDuration" style="font-size: 11px; color: var(--text-muted); text-align: center; margin: 8px 0 0;"></p>
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

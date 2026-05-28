<div class="glass-card fade-in" style="border-color: rgba(239,68,68,0.2);">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid rgba(239,68,68,0.15);">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(239,68,68,0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent-red)" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
            </svg>
        </div>
        <div>
            <h2 style="font-size: 16px; font-weight: 700; margin-bottom: 2px; color: var(--accent-red);">Hapus Akun</h2>
            <p style="font-size: 13px; color: var(--text-muted);">Tindakan ini tidak dapat dibatalkan</p>
        </div>
    </div>

    <div style="background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.15); border-radius: 10px; padding: 14px 16px; margin-bottom: 20px;">
        <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.6;">
            Setelah akun dihapus, semua data dan informasi terkait akan <strong style="color: var(--accent-red);">dihapus secara permanen</strong>. Pastikan Anda telah mengunduh data penting sebelum melanjutkan.
        </p>
    </div>

    <button type="button" onclick="document.getElementById('delete-modal').classList.add('active')" class="btn btn-danger">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="3 6 5 6 21 6"/>
            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
        </svg>
        Hapus Akun Saya
    </button>
</div>

{{-- Delete Confirmation Modal --}}
<div id="delete-modal" style="
    display: none;
    position: fixed; inset: 0; z-index: 1000;
    background: rgba(0,0,0,0.7);
    backdrop-filter: blur(6px);
    align-items: center;
    justify-content: center;
    padding: 24px;
">
    <div style="
        background: var(--bg-secondary);
        border: 1px solid rgba(239,68,68,0.3);
        border-radius: 20px;
        padding: 32px;
        max-width: 460px;
        width: 100%;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        animation: modalIn 0.25s ease;
    ">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="width: 56px; height: 56px; border-radius: 16px; background: rgba(239,68,68,0.15); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; border: 1px solid rgba(239,68,68,0.3);">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--accent-red)" stroke-width="2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">Hapus Akun?</h3>
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.6;">
                Semua data Anda akan dihapus secara permanen. Masukkan password untuk konfirmasi.
            </p>
        </div>

        <form method="post" action="{{ route('profile.destroy') }}">
            @csrf
            @method('delete')

            <div class="form-group">
                <label for="modal_password" class="form-label">Password</label>
                <input
                    id="modal_password"
                    name="password"
                    type="password"
                    class="form-input"
                    style="{{ $errors->userDeletion->has('password') ? 'border-color: var(--accent-red);' : '' }}"
                    placeholder="Masukkan password Anda"
                    autofocus
                />
                @error('password', 'userDeletion')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 8px;">
                <button type="button" onclick="document.getElementById('delete-modal').classList.remove('active')" class="btn btn-secondary">
                    Batal
                </button>
                <button type="submit" class="btn btn-danger">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                    </svg>
                    Ya, Hapus Akun
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    #delete-modal.active { display: flex; }
    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.95) translateY(-10px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>

<script>
    document.getElementById('delete-modal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });

    @if($errors->userDeletion->isNotEmpty())
        document.getElementById('delete-modal').classList.add('active');
    @endif
</script>

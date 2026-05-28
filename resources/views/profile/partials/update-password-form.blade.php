<div class="glass-card fade-in">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--border-glass);">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(139,92,246,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--accent-purple)" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </div>
        <div>
            <h2 style="font-size: 16px; font-weight: 700; margin-bottom: 2px;">Ubah Password</h2>
            <p style="font-size: 13px; color: var(--text-muted);">Gunakan password yang panjang dan acak agar akun lebih aman</p>
        </div>
    </div>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="form-group">
            <label for="update_password_current_password" class="form-label">Password Saat Ini</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-input" autocomplete="current-password" placeholder="Masukkan password saat ini" />
            @error('current_password', 'updatePassword')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="update_password_password" class="form-label">Password Baru</label>
            <input id="update_password_password" name="password" type="password" class="form-input" autocomplete="new-password" placeholder="Masukkan password baru" />
            @error('password', 'updatePassword')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="update_password_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-input" autocomplete="new-password" placeholder="Ulangi password baru" />
            @error('password_confirmation', 'updatePassword')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div style="display: flex; align-items: center; gap: 16px;">
            <button type="submit" class="btn" style="background: linear-gradient(135deg, #8b5cf6, #ec4899); color: white; box-shadow: 0 4px 15px rgba(139,92,246,0.3);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                Perbarui Password
            </button>

            @if (session('status') === 'password-updated')
                <span style="font-size: 13px; color: var(--accent-green); display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    Password berhasil diperbarui!
                </span>
            @endif
        </div>
    </form>
</div>

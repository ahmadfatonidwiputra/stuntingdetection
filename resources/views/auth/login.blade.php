@extends('layouts.landing')

@section('title', 'Masuk - AI Stunt Detect')

@push('styles')
<style>
    .login-container { max-width: 480px; margin: 0 auto; padding-top: 140px; padding-bottom: 60px; }
    .login-card {
        background: var(--bg-card); backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border); border-radius: 20px; padding: 40px;
    }
    .login-header { text-align: center; margin-bottom: 32px; }
    .form-group { margin-bottom: 20px; }
    .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px; }
    .form-input {
        width: 100%; padding: 12px 16px;
        background: var(--bg-main); border: 1px solid var(--glass-border);
        border-radius: 10px; color: var(--text); font-size: 14px;
        font-family: 'Inter', sans-serif; transition: all 0.2s; outline: none;
    }
    .form-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(14,165,233,0.15); }
    .form-error { color: var(--danger); font-size: 12px; margin-top: 6px; }
    .checkbox-label { display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-size: 13px; cursor: pointer; }
    .checkbox-label input { accent-color: var(--primary); width: 16px; height: 16px; }
    .login-btn {
        width: 100%; padding: 14px; border-radius: 12px; border: none;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white; font-size: 15px; font-weight: 700; cursor: pointer;
        font-family: 'Inter', sans-serif;
        box-shadow: 0 4px 20px rgba(14,165,233,0.3); transition: all 0.3s;
    }
    .login-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(14,165,233,0.4); }
    .login-footer { display: flex; align-items: center; justify-content: space-between; margin-top: 16px; flex-wrap: wrap; gap: 8px; }
    .login-link { color: var(--primary); font-size: 13px; text-decoration: none; font-weight: 500; transition: color 0.2s; }
    .login-link:hover { color: #38bdf8; }
    .success-banner {
        background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3);
        border-radius: 12px; padding: 16px; margin-bottom: 24px;
        color: #10b981; font-size: 14px; line-height: 1.6;
    }
</style>
@endpush

@section('content')
<div class="login-container" style="padding-left: 24px; padding-right: 24px;">
    <div class="login-card fade-up">
        <div class="login-header">
            <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 16px; background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center; font-size: 28px; box-shadow: 0 4px 20px rgba(14,165,233,0.3);">🏥</div>
            <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 8px;">Masuk ke AI Stunt Detect</h1>
            <p style="color: var(--text-muted); font-size: 14px;">Login dengan akun petugas atau super admin</p>
        </div>

        @if(session('status'))
            <div class="success-banner">✅ {{ session('status') }}</div>
        @endif

        @if($needsInitialSuperadmin ?? false)
            <div style="background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.3); border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                <div style="font-weight: 600; color: var(--warning); margin-bottom: 4px;">⚠️ Setup Awal</div>
                <div style="color: var(--warning); font-size: 13px;">Belum ada akun. <a href="{{ route('register') }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">Buat Super Admin pertama →</a></div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-input" placeholder="email@contoh.com" required autofocus autocomplete="username">
                @error('email') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input id="password" type="password" name="password" class="form-input" placeholder="Masukkan password" required autocomplete="current-password">
                @error('password') <div class="form-error">{{ $message }}</div> @enderror
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember">
                    Ingat saya
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="login-link">Lupa password?</a>
                @endif
            </div>

            <button type="submit" class="login-btn">Masuk</button>

            <div class="login-footer">
                <span style="color: var(--text-muted); font-size: 13px;">Belum punya akun?</span>
                <a href="{{ route('register.petugas') }}" class="login-link">Daftar sebagai Petugas →</a>
            </div>
        </form>
    </div>
</div>
@endsection

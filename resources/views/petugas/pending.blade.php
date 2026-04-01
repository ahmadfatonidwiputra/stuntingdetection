@extends('layouts.landing')

@section('title', 'Menunggu Verifikasi - AI Stunt Detect')

@section('content')
<section style="min-height: 80vh; display: flex; align-items: center; padding: 120px 24px;">
    <div style="max-width: 560px; margin: 0 auto; text-align: center;">
        <div class="fade-up">
            <div style="width: 100px; height: 100px; margin: 0 auto 32px; border-radius: 24px; background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.2); display: flex; align-items: center; justify-content: center; font-size: 48px;">
                ⏳
            </div>
            <h1 style="font-size: 32px; font-weight: 800; margin-bottom: 16px;">Menunggu Verifikasi</h1>
            <p style="color: var(--text-secondary); font-size: 16px; line-height: 1.8; margin-bottom: 32px;">
                Akun Anda telah berhasil didaftarkan dan sedang dalam proses verifikasi oleh Super Admin. Anda akan dapat mengakses dashboard setelah akun Anda disetujui.
            </p>

            <div class="glass" style="padding: 24px; text-align: left; margin-bottom: 32px;">
                <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 16px;">Langkah selanjutnya:</h3>
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(16,185,129,0.15); display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">✅</div>
                        <span style="color: var(--text-secondary); font-size: 14px; line-height: 1.6;">Registrasi berhasil dilakukan</span>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(245,158,11,0.15); display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; animation: pulse 2s infinite;">🔄</div>
                        <span style="color: var(--text-secondary); font-size: 14px; line-height: 1.6;">Super Admin sedang mereview data Anda</span>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: flex-start;">
                        <div style="width: 28px; height: 28px; border-radius: 8px; background: rgba(100,116,139,0.15); display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0;">⏸️</div>
                        <span style="color: var(--text-muted); font-size: 14px; line-height: 1.6;">Akun disetujui → Anda bisa login ke dashboard</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-outline" style="border: 1px solid rgba(255,255,255,0.1);">Keluar</button>
                </form>
                <a href="{{ route('home') }}" class="btn-primary">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</section>

<style>
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
</style>
@endsection

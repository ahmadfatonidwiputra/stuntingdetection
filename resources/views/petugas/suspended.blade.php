@extends('layouts.landing')

@section('title', 'Akun Disuspend - AI Stunt Detect')

@section('content')
<section style="min-height: 80vh; display: flex; align-items: center; padding: 120px 24px;">
    <div style="max-width: 560px; margin: 0 auto; text-align: center;">
        <div class="fade-up">
            <div style="width: 100px; height: 100px; margin: 0 auto 32px; border-radius: 24px; background: rgba(100,116,139,0.1); border: 1px solid rgba(100,116,139,0.2); display: flex; align-items: center; justify-content: center; font-size: 48px;">
                ⛔
            </div>
            <h1 style="font-size: 32px; font-weight: 800; margin-bottom: 16px;">Akun Disuspend</h1>
            <p style="color: var(--text-secondary); font-size: 16px; line-height: 1.8; margin-bottom: 32px;">
                Akun Anda telah disuspend oleh Super Admin. Anda tidak dapat mengakses dashboard saat ini. Silakan hubungi Super Admin untuk informasi lebih lanjut.
            </p>

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
@endsection

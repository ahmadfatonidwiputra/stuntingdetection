@extends('layouts.landing')

@section('title', 'Pendaftaran Ditolak - AI Stunt Detect')

@section('content')
<section style="min-height: 80vh; display: flex; align-items: center; padding: 120px 24px;">
    <div style="max-width: 560px; margin: 0 auto; text-align: center;">
        <div class="fade-up">
            <div style="width: 100px; height: 100px; margin: 0 auto 32px; border-radius: 24px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); display: flex; align-items: center; justify-content: center; font-size: 48px;">
                ❌
            </div>
            <h1 style="font-size: 32px; font-weight: 800; margin-bottom: 16px;">Pendaftaran Ditolak</h1>
            <p style="color: var(--text-secondary); font-size: 16px; line-height: 1.8; margin-bottom: 32px;">
                Maaf, pendaftaran akun Anda sebagai petugas posyandu tidak disetujui oleh Super Admin.
            </p>

            @if($reason)
            <div class="glass" style="padding: 24px; text-align: left; margin-bottom: 32px; border-left: 3px solid #ef4444;">
                <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 10px; color: #ef4444;">Alasan Penolakan:</h3>
                <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.7;">{{ $reason }}</p>
            </div>
            @endif

            <div class="glass" style="padding: 24px; text-align: left; margin-bottom: 32px;">
                <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 12px;">Apa yang bisa dilakukan?</h3>
                <ul style="color: var(--text-secondary); font-size: 14px; line-height: 2; margin-left: 16px;">
                    <li>Hubungi Super Admin untuk informasi lebih lanjut</li>
                    <li>Perbaiki dokumen atau data yang kurang sesuai</li>
                    <li>Daftarkan kembali dengan data yang lebih lengkap</li>
                </ul>
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
@endsection

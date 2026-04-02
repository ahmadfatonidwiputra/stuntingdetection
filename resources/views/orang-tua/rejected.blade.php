@extends('layouts.landing')

@section('title', 'Pendaftaran Ditolak - AI Stunt Detect')

@section('content')
<div style="max-width: 520px; margin: 0 auto; padding: 160px 24px 60px; text-align: center;">
    <div style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 24px; padding: 48px 40px;">
        <div style="font-size: 64px; margin-bottom: 16px;">❌</div>
        <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 12px; color: var(--danger);">Pendaftaran Ditolak</h1>
        <p style="color: var(--text-muted); font-size: 15px; line-height: 1.7; margin-bottom: 28px;">
            Maaf, pendaftaran akun Anda ditolak. Silakan hubungi petugas posyandu untuk informasi lebih lanjut
            atau daftar ulang dengan data yang benar.
        </p>
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('register.orang-tua') }}" style="padding: 12px 24px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border-radius: 10px; font-size: 14px; font-weight: 600; text-decoration: none;">Daftar Ulang</a>
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
                <button type="submit" style="padding: 12px 24px; background: var(--bg-card); border: 1px solid var(--glass-border); color: var(--text); border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif;">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layouts.landing')

@section('title', 'Menunggu Verifikasi - AI Stunt Detect')

@section('content')
<div style="max-width: 520px; margin: 0 auto; padding: 160px 24px 60px; text-align: center;">
    <div style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 24px; padding: 48px 40px;">
        <div style="font-size: 64px; margin-bottom: 16px;">⏳</div>
        <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 12px;">Menunggu Verifikasi</h1>
        <p style="color: var(--text-muted); font-size: 15px; line-height: 1.7; margin-bottom: 28px;">
            Terima kasih telah mendaftar! Akun Anda sedang dalam proses verifikasi oleh petugas posyandu.
            Anda akan dapat login setelah diverifikasi.
        </p>
        <div style="background: rgba(245,158,11,0.1); border: 1px solid rgba(245,158,11,0.25); border-radius: 12px; padding: 14px 16px; margin-bottom: 28px; font-size: 13px; color: var(--warning); text-align: left;">
            <div style="font-weight: 700; margin-bottom: 4px;">📋 Proses Verifikasi:</div>
            <ol style="margin: 0; padding-left: 16px; line-height: 1.8;">
                <li>Petugas posyandu terkait akan memeriksa data Anda</li>
                <li>Biasanya selesai dalam 1-2 hari kerja</li>
                <li>Anda akan mendapatkan notifikasi via email</li>
            </ol>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="padding: 12px 28px; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif;">
                Keluar
            </button>
        </form>
    </div>
</div>
@endsection

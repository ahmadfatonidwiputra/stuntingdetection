@extends('layouts.main')

@section('content')

<div class="page-header flex-between">
    <div>
        <h1 class="page-title">Profil Akun</h1>
        <p class="page-subtitle">Kelola informasi akun dan keamanan Anda</p>
    </div>
</div>

{{-- Profile Summary Card --}}
<div class="glass-card fade-in" style="margin-bottom: 24px; display: flex; align-items: center; gap: 20px; flex-wrap: wrap;">
    <div style="width: 64px; height: 64px; border-radius: 18px; background: linear-gradient(135deg, #ec4899, #8b5cf6); display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 800; color: white; flex-shrink: 0;">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    <div style="flex: 1; min-width: 0;">
        <div style="font-size: 20px; font-weight: 700; margin-bottom: 4px;">{{ $user->name }}</div>
        <div style="font-size: 13px; color: var(--text-muted);">{{ $user->email }}</div>
    </div>
    <div>
        @if($user->isSuperAdmin())
            <span class="badge" style="background: rgba(139,92,246,0.15); color: #8b5cf6; padding: 6px 16px; font-size: 13px;">Super Admin</span>
        @elseif($user->isPetugasPosyandu())
            <span class="badge" style="background: rgba(59,130,246,0.15); color: var(--accent-blue); padding: 6px 16px; font-size: 13px;">
                Petugas Posyandu
            </span>
        @else
            <span class="badge" style="background: rgba(16,185,129,0.15); color: var(--accent-green); padding: 6px 16px; font-size: 13px;">Orang Tua</span>
        @endif
    </div>
</div>

<div style="display: grid; gap: 24px; max-width: 720px;">

    {{-- Update Profile Info --}}
    @include('profile.partials.update-profile-information-form')

    {{-- Data Orang Tua --}}
    @if($orangTuaProfile)
        @include('profile.partials.update-orang-tua-profile-form')
    @endif

    {{-- Update Password --}}
    @include('profile.partials.update-password-form')

    {{-- Delete Account --}}
    @include('profile.partials.delete-user-form')

</div>

@endsection

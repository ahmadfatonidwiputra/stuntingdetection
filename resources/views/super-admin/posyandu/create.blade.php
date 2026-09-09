@extends('layouts.main')

@section('content')
<div class="page-header">
    <div class="flex-between">
        <div>
            <h1 class="page-title">Tambah Posyandu</h1>
            <p class="page-subtitle">Daftarkan posyandu baru ke master data</p>
        </div>
        <a href="{{ route('super-admin.posyandu.index') }}" class="btn btn-secondary btn-sm">← Kembali</a>
    </div>
</div>

<div class="glass-card fade-in" style="max-width: 760px;">
    @include('super-admin.posyandu.partials.form', [
        'action' => route('super-admin.posyandu.store'),
        'method' => 'POST',
        'posyandu' => null,
        'submitLabel' => 'Simpan',
    ])
</div>
@endsection

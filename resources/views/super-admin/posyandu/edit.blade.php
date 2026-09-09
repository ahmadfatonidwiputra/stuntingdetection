@extends('layouts.main')

@section('content')
<div class="page-header">
    <div class="flex-between">
        <div>
            <h1 class="page-title">Edit Posyandu</h1>
            <p class="page-subtitle">{{ $posyandu->nama }}</p>
        </div>
        <a href="{{ route('super-admin.posyandu.index') }}" class="btn btn-secondary btn-sm">← Kembali</a>
    </div>
</div>

<div class="glass-card fade-in" style="max-width: 760px;">
    @include('super-admin.posyandu.partials.form', [
        'action' => route('super-admin.posyandu.update', $posyandu),
        'method' => 'PUT',
        'posyandu' => $posyandu,
        'submitLabel' => 'Simpan Perubahan',
    ])
</div>
@endsection

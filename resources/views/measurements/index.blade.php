@extends('layouts.main')

@push('styles')
<style>
    .sortable-th a {
        display: inline-flex; align-items: center; gap: 4px;
        color: inherit; text-decoration: none; cursor: pointer;
    }
    .sortable-th a:hover { color: var(--text-primary); }
    .sortable-th .sort-arrow { font-size: 9px; color: var(--accent-blue); }
</style>
@endpush

@section('content')
<div class="page-header flex-between">
    <div>
        <h1 class="page-title">Riwayat Pengukuran</h1>
        <p class="page-subtitle">Kelompok perkembangan anak berdasarkan Nama dan NIK di posyandu Anda</p>
    </div>
    <a href="{{ route('measurements.create') }}" class="btn btn-primary">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Pengukuran Baru
    </a>
</div>

<!-- Filter -->
<div class="glass-card fade-in" style="margin-bottom: 24px;">
    <form method="GET" action="{{ route('measurements.index') }}" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="margin-bottom: 0; flex: 2; min-width: 200px;">
            <label class="form-label">Cari Nama / NIK</label>
            <input type="text" name="search" class="form-input" value="{{ request('search') }}" placeholder="Cari nama atau NIK anak...">
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="from" class="form-input" value="{{ request('from') }}">
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="to" class="form-input" value="{{ request('to') }}">
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 170px;">
            <label class="form-label">Kategori Stunting</label>
            <select name="stunting_category" class="form-input">
                <option value="">Semua Kategori</option>
                <option value="Normal" {{ request('stunting_category') === 'Normal' ? 'selected' : '' }}>Normal</option>
                <option value="Stunting" {{ request('stunting_category') === 'Stunting' ? 'selected' : '' }}>Stunting</option>
                <option value="Sangat Stunting" {{ request('stunting_category') === 'Sangat Stunting' ? 'selected' : '' }}>Sangat Stunting</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
            <label class="form-label">Jenis Kelamin</label>
            <select name="gender" class="form-input">
                <option value="">Semua</option>
                <option value="L" {{ request('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ request('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>
        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary btn-sm">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                Filter
            </button>
            @if(request('from') || request('to') || request('search') || request('stunting_category') || request('gender'))
                <a href="{{ route('measurements.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
        </div>
    </form>
</div>

<!-- Measurements Table -->
<div class="glass-card fade-in" id="measurementsTableRegion">
    @include('measurements.partials.index-table')
</div>
@endsection

@push('scripts')
<script>
(function () {
    const region = document.getElementById('measurementsTableRegion');
    if (!region) return;

    function loadTable(url, pushState) {
        region.style.opacity = '0.5';
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(res => {
                if (!res.ok) throw new Error('Request gagal');
                return res.text();
            })
            .then(html => {
                region.innerHTML = html;
                region.style.opacity = '1';
                if (pushState) window.history.pushState({ measurementsUrl: url }, '', url);
            })
            .catch(() => {
                window.location.href = url;
            });
    }

    // Intercept clicks on sort headers and pagination links (same page, only the
    // query string changes) so they swap the table in place instead of reloading.
    region.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (!link || !region.contains(link)) return;
        if (link.pathname !== window.location.pathname) return;

        e.preventDefault();
        loadTable(link.href, true);
    });

    window.addEventListener('popstate', function () {
        loadTable(window.location.href, false);
    });
})();
</script>
@endpush

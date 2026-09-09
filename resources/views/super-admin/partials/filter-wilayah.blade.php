{{--
    Dua dropdown filter wilayah (kecamatan + desa), dipakai bersama oleh menu
    Manajemen Posyandu dan Manajemen Laporan. Harus berada di dalam <form> yang
    punya field bernama "kelurahan", karena mengganti kecamatan akan mereset
    pilihan desa lalu submit ulang agar daftar desanya ikut menyesuaikan.
--}}
<div style="flex: 1; min-width: 170px;">
    <label class="form-label">Kecamatan</label>
    <select name="kecamatan" class="form-input" onchange="this.form.kelurahan.value = ''; this.form.submit();">
        <option value="">Semua Kecamatan</option>
        @foreach($kecamatanList as $k)
            <option value="{{ $k }}" {{ $kecamatan === $k ? 'selected' : '' }}>{{ $k }}</option>
        @endforeach
    </select>
</div>
<div style="flex: 1; min-width: 170px;">
    <label class="form-label">Desa / Kelurahan</label>
    <select name="kelurahan" class="form-input" {{ $kelurahanList->isEmpty() ? 'disabled' : '' }}>
        <option value="">{{ $kelurahanList->isEmpty() ? 'Tidak ada data desa' : 'Semua Desa' }}</option>
        @foreach($kelurahanList as $d)
            <option value="{{ $d }}" {{ $kelurahan === $d ? 'selected' : '' }}>{{ $d }}</option>
        @endforeach
    </select>
</div>

{{-- Tabel standar antropometri: baris diurutkan berdasarkan key numerik (umur bulan atau panjang/tinggi cm) --}}
<div class="antro-table-wrap">
    <table class="data-table antro-table">
        <thead>
            <tr>
                <th>{{ $keyLabel }}</th>
                <th>-3 SD</th>
                <th>-2 SD</th>
                <th>-1 SD</th>
                <th>Median</th>
                <th>+1 SD</th>
                <th>+2 SD</th>
                <th>+3 SD</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $key => $row)
                <tr>
                    <td>{{ is_numeric($key) && (float) $key == (int) $key ? (int) $key : $key }}{{ $keySuffix ?? '' }}</td>
                    <td>{{ number_format($row['SD3neg'], 1) }}</td>
                    <td>{{ number_format($row['SD2neg'], 1) }}</td>
                    <td>{{ number_format($row['SD1neg'], 1) }}</td>
                    <td><strong>{{ number_format($row['SD0'], 1) }}</strong></td>
                    <td>{{ number_format($row['SD1'], 1) }}</td>
                    <td>{{ number_format($row['SD2'], 1) }}</td>
                    <td>{{ number_format($row['SD3'], 1) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

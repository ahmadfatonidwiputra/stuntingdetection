@php
    $historySortLink = function (string $field) use ($sortField, $sortDirection, $anak) {
        $nextDirection = ($sortField === $field && $sortDirection === 'asc') ? 'desc' : 'asc';
        $query = array_merge(request()->except(['sort', 'direction']), [
            'sort' => $field,
            'direction' => $nextDirection,
        ]);

        return [
            'url' => route('measurements.anak.show', $anak) . '?' . http_build_query($query),
            'arrow' => $sortField === $field ? ($sortDirection === 'asc' ? '▲' : '▼') : '',
        ];
    };
@endphp

@if($anak->measurements->isNotEmpty())
    <div style="overflow-x: auto; margin-top: 16px;">
        <table class="data-table">
            <thead>
                @php
                    $sortTanggal = $historySortLink('tanggal');
                    $sortTinggi = $historySortLink('tinggi');
                    $sortBerat = $historySortLink('berat');
                    $sortZscore = $historySortLink('zscore');
                    $sortStatus = $historySortLink('status');
                    $sortBbU = $historySortLink('bb_u');
                    $sortBbPbTb = $historySortLink('bb_pb_tb');
                    $sortImtU = $historySortLink('imt_u');
                    $sortPetugas = $historySortLink('petugas');
                @endphp
                <tr>
                    <th>No</th>
                    <th class="sortable-th"><a href="{{ $sortTanggal['url'] }}">Tanggal <span class="sort-arrow">{{ $sortTanggal['arrow'] }}</span></a></th>
                    <th class="sortable-th"><a href="{{ $sortTinggi['url'] }}">Tinggi <span class="sort-arrow">{{ $sortTinggi['arrow'] }}</span></a></th>
                    <th class="sortable-th"><a href="{{ $sortBerat['url'] }}">Berat <span class="sort-arrow">{{ $sortBerat['arrow'] }}</span></a></th>
                    <th class="sortable-th"><a href="{{ $sortZscore['url'] }}">Z-Score <span class="sort-arrow">{{ $sortZscore['arrow'] }}</span></a></th>
                    <th class="sortable-th"><a href="{{ $sortStatus['url'] }}">Status <span class="sort-arrow">{{ $sortStatus['arrow'] }}</span></a></th>
                    <th class="sortable-th"><a href="{{ $sortBbU['url'] }}">BB/U <span class="sort-arrow">{{ $sortBbU['arrow'] }}</span></a></th>
                    <th class="sortable-th"><a href="{{ $sortBbPbTb['url'] }}">BB/PB atau BB/TB <span class="sort-arrow">{{ $sortBbPbTb['arrow'] }}</span></a></th>
                    <th class="sortable-th"><a href="{{ $sortImtU['url'] }}">IMT/U <span class="sort-arrow">{{ $sortImtU['arrow'] }}</span></a></th>
                    <th class="sortable-th"><a href="{{ $sortPetugas['url'] }}">Petugas <span class="sort-arrow">{{ $sortPetugas['arrow'] }}</span></a></th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($anak->measurements as $measurement)
                    @php
                        $canDelete = (int) $measurement->user_id === (int) auth()->id();
                        $antro = $measurement->antropometriLengkap();
                    @endphp
                    <tr>
                        <td style="color: var(--text-muted);">{{ $loop->iteration }}</td>
                        <td>
                            <div>{{ $measurement->measured_at->translatedFormat('d M Y') }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">{{ $measurement->measured_at->format('H:i') }}</div>
                        </td>
                        <td>
                            <strong>{{ number_format($measurement->height_cm, 2) }}</strong> <span style="color: var(--text-muted);">cm</span>
                            @if($measurement->manual_height_cm)
                                <div style="font-size: 11px; color: var(--text-muted);">Manual: {{ number_format($measurement->manual_height_cm, 2) }} cm</div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ number_format($measurement->weight_kg, 2) }}</strong> <span style="color: var(--text-muted);">kg</span>
                            @if($measurement->manual_weight_kg)
                                <div style="font-size: 11px; color: var(--text-muted);">Manual: {{ number_format($measurement->manual_weight_kg, 2) }} kg</div>
                            @endif
                        </td>
                        <td><strong>{{ number_format($measurement->z_score, 2) }}</strong></td>
                        <td>
                            <span class="badge badge-{{ strtolower(str_replace(' ', '-', $measurement->stunting_category)) }}">
                                {{ $measurement->stunting_category }}
                            </span>
                        </td>
                        <td>
                            @if($antro)
                                <div style="font-size: 11px; color: var(--text-muted);">{{ number_format($antro['bb_u']['z'], 2) }} SD</div>
                                <span class="severity-pill severity-{{ $antro['bb_u']['severity'] }}">{{ $antro['bb_u']['label'] }}</span>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td>
                            @if($antro)
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $antro['bb_pb_tb']['z'] !== null ? number_format($antro['bb_pb_tb']['z'], 2).' SD' : '-' }}</div>
                                <span class="severity-pill severity-{{ $antro['bb_pb_tb']['severity'] }}">{{ $antro['bb_pb_tb']['label'] }}</span>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td>
                            @if($antro)
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $antro['imt'] ?? '-' }} kg/m&sup2; &middot; {{ $antro['imt_u']['z'] !== null ? number_format($antro['imt_u']['z'], 2).' SD' : '-' }}</div>
                                <span class="severity-pill severity-{{ $antro['imt_u']['severity'] }}">{{ $antro['imt_u']['label'] }}</span>
                            @else
                                <span style="color: var(--text-muted);">-</span>
                            @endif
                        </td>
                        <td>{{ $measurement->user->petugasProfile?->nama_lengkap ?? $measurement->user->name }}</td>
                        <td>
                            <div style="display: flex; gap: 6px;">
                                <a href="{{ route('measurements.show', $measurement) }}" class="btn btn-secondary btn-sm">Detail</a>
                                @if($canDelete)
                                    <form method="POST" action="{{ route('measurements.destroy', $measurement) }}" onsubmit="return confirm('Yakin hapus pengukuran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="empty-state" style="margin-top: 16px;">
        <div class="empty-state-icon">📋</div>
        @if($hasHistoryFilter)
            <h3>Tidak ada pengukuran yang cocok dengan filter</h3>
            <p>Coba ubah rentang tanggal atau kategori filter yang dipilih.</p>
        @else
            <h3>Belum ada pengukuran untuk anak ini</h3>
            <p>Tambahkan pengukuran pertama untuk mulai memantau perkembangannya.</p>
        @endif
    </div>
@endif

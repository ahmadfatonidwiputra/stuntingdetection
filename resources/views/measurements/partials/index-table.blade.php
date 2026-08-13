@php
    $sortLink = function (string $field) use ($sortField, $sortDirection) {
        $nextDirection = ($sortField === $field && $sortDirection === 'asc') ? 'desc' : 'asc';
        $query = array_merge(request()->except(['sort', 'direction', 'page']), [
            'sort' => $field,
            'direction' => $nextDirection,
        ]);

        return [
            'url' => request()->url() . '?' . http_build_query($query),
            'arrow' => $sortField === $field ? ($sortDirection === 'asc' ? '▲' : '▼') : '',
        ];
    };
@endphp

@if($anakList->count() > 0)
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                @php
                    $sortNama = $sortLink('nama');
                    $sortJumlah = $sortLink('jumlah');
                    $sortTanggal = $sortLink('tanggal');
                    $sortStatus = $sortLink('status');
                @endphp
                <tr>
                    <th>No</th>
                    <th class="sortable-th"><a href="{{ $sortNama['url'] }}">Anak <span class="sort-arrow">{{ $sortNama['arrow'] }}</span></a></th>
                    <th class="sortable-th"><a href="{{ $sortJumlah['url'] }}">Jumlah Pengukuran <span class="sort-arrow">{{ $sortJumlah['arrow'] }}</span></a></th>
                    <th class="sortable-th"><a href="{{ $sortTanggal['url'] }}">Pengukuran Terakhir <span class="sort-arrow">{{ $sortTanggal['arrow'] }}</span></a></th>
                    <th class="sortable-th"><a href="{{ $sortStatus['url'] }}">Status Terakhir <span class="sort-arrow">{{ $sortStatus['arrow'] }}</span></a></th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($anakList as $index => $anak)
                @php
                    $latestMeasurement = $hasDateFilter ? $anak->measurements->first() : $anak->latestMeasurement;
                    $photoMeasurement = $hasDateFilter
                        ? $anak->measurements->first(fn ($measurement) => filled($measurement->photo_path))
                        : $anak->latestPhotoMeasurement;
                @endphp
                <tr>
                    <td style="color: var(--text-muted);">{{ $anakList->firstItem() + $index }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            @if($photoMeasurement?->photo_path)
                                <div style="width: 52px; height: 52px; border-radius: 14px; overflow: hidden; background: var(--bg-glass); border: 1px solid var(--border-glass); flex-shrink: 0;">
                                    <img src="{{ Storage::disk('r2')->url($photoMeasurement->photo_path) }}" alt="Foto {{ $anak->nama }}" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            @else
                                <div style="width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.14), rgba(124, 58, 237, 0.14)); border: 1px dashed var(--border-glass); color: var(--text-muted); display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0;">
                                    👶
                                </div>
                            @endif
                            <div>
                                <div style="font-weight: 700;">{{ $anak->nama }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);">NIK: {{ $anak->nik_anak ?: '-' }}</div>
                                @if($photoMeasurement?->photo_path)
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                                        Foto dari pengukuran {{ $photoMeasurement->measured_at->format('d M Y') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <strong>{{ $anak->filtered_measurements_count }}</strong>
                        <span style="color: var(--text-muted); font-size: 12px;">catatan</span>
                    </td>
                    <td>
                        @if($latestMeasurement)
                            <div>{{ $latestMeasurement->measured_at->format('d M Y') }}</div>
                            <div style="font-size: 11px; color: var(--text-muted);">
                                TB {{ number_format($latestMeasurement->height_cm, 2) }} cm • BB {{ number_format($latestMeasurement->weight_kg, 2) }} kg
                            </div>
                        @else
                            <span style="color: var(--text-muted); font-size: 12px;">-</span>
                        @endif
                    </td>
                    <td>
                        @if($latestMeasurement)
                            <span class="badge badge-{{ strtolower(str_replace(' ', '-', $latestMeasurement->stunting_category)) }}">
                                {{ $latestMeasurement->stunting_category }}
                            </span>
                        @else
                            <span style="color: var(--text-muted); font-size: 12px;">-</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <a href="{{ route('measurements.anak.show', $anak) }}" class="btn btn-secondary btn-sm">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                Lihat Perkembangan
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        {{ $anakList->links('vendor.pagination.custom') }}
    </div>
@else
    <div class="empty-state">
        <div class="empty-state-icon">👶</div>
        <h3>Belum ada riwayat per anak</h3>
        <p>{{ request('from') || request('to') || request('stunting_category') || request('gender') ? 'Tidak ada pengukuran anak yang cocok dengan filter yang dipilih.' : 'Mulai catat pengukuran anak untuk melihat perkembangan mereka di sini.' }}</p>
        <a href="{{ route('measurements.create') }}" class="btn btn-primary">Mulai Pengukuran</a>
    </div>
@endif

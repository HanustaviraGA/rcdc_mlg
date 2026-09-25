@php
    $number = static fn ($value) => $value === null ? '—' : rtrim(rtrim(number_format($value, 4, ',', '.'), '0'), ',');
@endphp
@forelse($rows as $row)
    <tr data-code="{{ $row['code'] }}" data-year="{{ $row['year'] }}" data-month="{{ $row['month'] }}">
        <td>{{ $loop->iteration }}</td>
        <td>{{ $row['year'] ?? '—' }}</td>
        <td class="text-nowrap">{{ $row['month'] ? \Carbon\Carbon::create(2026, $row['month'], 1)->locale('id')->translatedFormat('F') : 'Belum ditentukan' }}</td>
        <td><div class="fw-bold">{{ $row['name'] }}</div><div class="text-muted">{{ $row['code'] }}</div></td>
        <td title="{{ $row['program_name'] }}">{{ $row['program'] }}</td>
        <td>{{ $row['rank'] ?? '—' }}</td>
        <td>{{ $row['faculty'] ?? '—' }}</td>
        @foreach(['non_scopus', 'scopus', 'titles', 'first_author', 'score'] as $field)<td class="text-end">{{ $number($row[$field]) }}</td>@endforeach
        <td>{{ $row['score_source'] ?? '—' }}</td>
        @foreach(['rectorate_non_scopus', 'rectorate_scopus', 'rtto_non_scopus', 'rtto_scopus'] as $field)<td class="text-end">{{ $number($row[$field]) }}</td>@endforeach
        @foreach(['grant_chair', 'grant_member'] as $field)<td class="text-end" title="{{ $row['grant_years'] ? 'Tahun anggaran: '.$row['grant_years'] : '' }}">{{ $number($row[$field]) }}</td>@endforeach
    </tr>
@empty
    <tr><td colspan="19" class="text-center py-8">Tidak ada data KPI yang sesuai. Periksa pencarian dan filter, atau upload laporan bulanan.</td></tr>
@endforelse

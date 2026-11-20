<p class="fw-bold">Sheet {{ trim($summary['sheet']) }}: {{ count($summary['programs']) }} prodi, {{ count($summary['years']) }} tahun, {{ $summary['selected'] }} isian prodi/tahun.</p>
@if($summary['mismatches'])
    <div class="alert alert-warning">
        <p class="mb-2">Ada {{ $summary['mismatches'] }} isian S2 + S3 yang berbeda dari Jumlah FM. Angka akan diimpor sesuai file.</p>
        <details>
            <summary>Lihat perbedaan</summary>
            <ul class="mb-0 mt-2">
                @foreach($summary['warnings'] as $warning)<li>{{ $warning }}</li>@endforeach
            </ul>
        </details>
    </div>
@endif
<div class="table-responsive">
    <table class="table table-bordered align-middle fs-7 roadmap-table">
        <thead class="bg-light fw-bold">
            <tr>
                <th scope="col">Prodi</th>
                @foreach($summary['years'] as $year)
                    <th scope="col">Jumlah FM {{ $year }}</th><th scope="col">S2 {{ $year }}</th><th scope="col">S3 {{ $year }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach(collect($records)->groupBy('prodi') as $prodi => $entries)
                <tr>
                    <th scope="row">{{ $prodi }}</th>
                    @foreach($entries as $entry)
                        @php($mismatch = $entry['jumlah_s2'] + $entry['jumlah_s3'] !== $entry['jumlah_fm'])
                        @foreach(['jumlah_fm', 'jumlah_s2', 'jumlah_s3'] as $field)
                            <td @class(['bg-light-warning' => $mismatch])>{{ $entry[$field] }}</td>
                        @endforeach
                    @endforeach
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-light fw-bold">
            <tr>
                <th scope="row">Total</th>
                @foreach($summary['years'] as $year)
                    @foreach(['jumlah_fm', 'jumlah_s2', 'jumlah_s3'] as $field)
                        <td>{{ collect($records)->where('year', $year)->sum($field) }}</td>
                    @endforeach
                @endforeach
            </tr>
        </tfoot>
    </table>
</div>

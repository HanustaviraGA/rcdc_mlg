{{-- Tab 2: CBDC Scopus titles in the last FM upload (Import Rectorate) of the quarter. --}}
@php($number = fn ($value) => $value === null ? '—' : rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ','))
@if($data['batch'])
    <p class="text-muted mb-5">
        Sumber: upload Publikasi FM bulan {{ \Carbon\Carbon::create($data['batch']->year, $data['batch']->month, 1)->locale('id')->translatedFormat('F Y') }}
        ({{ $data['batch']->filename }}), upload terakhir pada {{ $data['label'] }}. Realisasi adalah jumlah judul Scopus (Tipe Publikasi Scopus dan
        Submitted Scopus FM) dosen CBDC; judul yang ditulis bersama beberapa dosen CBDC dihitung satu kali. Dosen CBDC adalah dosen dengan
        Jurusan Binaan CBDC pada sheet KPI atau Gugus Binaan Character Building pada Data Center.
    </p>
@else
    <div class="alert alert-secondary mb-5">Belum ada upload Publikasi FM pada {{ $data['label'] }}. Unggah melalui Import Rectorate, atau pilih kuartal lain.</div>
@endif
@include('characterbuilding::summary', ['summary' => $data['summary'], 'periodLabel' => $data['label'],
    'unit' => 'Judul Scopus', 'realizationNote' => 'dari '.$data['lecturers']->count().' dosen CBDC.'])
@if($data['batch'])
    <h4 class="mb-3">Dosen CBDC ({{ $data['lecturers']->count() }})</h4>
    @if($data['lecturers']->isEmpty())
        <p class="text-muted">Tidak ada dosen CBDC pada upload ini.</p>
    @else
        <div class="table-responsive mb-8">
            <table class="table table-row-bordered table-row-gray-200 align-middle fs-7">
                <thead><tr class="fw-bold text-muted">
                    <th>Kode</th><th>Nama dosen</th><th>JJA</th><th class="text-end">Judul Scopus</th><th class="text-end">First author</th>
                    <th class="text-end">Bobot Scopus</th><th class="text-end">Bobot Non Scopus</th><th class="text-end">Skor KPI</th>
                </tr></thead>
                <tbody>
                    @foreach($data['lecturers'] as $row)
                        <tr>
                            <td>{{ $row['code'] }}</td><td>{{ $row['name'] }}</td><td>{{ $row['rank'] ?? '—' }}</td>
                            <td class="text-end fw-bold">{{ $row['titles'] ?? '—' }}</td><td class="text-end">{{ $row['first_author'] ?? '—' }}</td>
                            <td class="text-end">{{ $number($row['scopus']) }}</td><td class="text-end">{{ $number($row['non_scopus']) }}</td>
                            <td class="text-end">{{ $number($row['score']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    <h4 class="mb-3">Publikasi dosen CBDC ({{ $data['publications']->count() }})</h4>
    @if($data['publications']->isEmpty())
        <p class="text-muted mb-0">Belum ada publikasi Scopus FM atau Non Scopus FM dosen CBDC pada upload ini.</p>
    @else
        <div class="table-responsive">
            <table class="table table-row-bordered table-row-gray-200 align-middle fs-7 mb-0">
                <thead><tr class="fw-bold text-muted"><th>Dosen</th><th>Judul</th><th>Submitted</th><th>Tipe publikasi</th><th>Jenis</th><th>Quartile</th><th>First author</th><th>Dihitung</th></tr></thead>
                <tbody>
                    @foreach($data['publications'] as $row)
                        <tr>
                            <td class="text-nowrap">{{ $row['name'] }}<div class="text-muted fs-8">{{ $row['code'] }}</div></td>
                            <td style="min-width: 280px">{{ $row['title'] ?: '—' }}</td><td>{{ $row['submitted'] ?: '—' }}</td>
                            <td>{{ $row['type'] ?: '—' }}</td><td>{{ $row['kind'] ?: '—' }}</td><td>{{ $row['quartile'] ?: '—' }}</td><td>{{ $row['first_author'] ?: '—' }}</td>
                            <td>@if($row['scopus'])<span class="badge bg-success-subtle text-success">Judul Scopus</span>@else<span class="text-muted">—</span>@endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endif

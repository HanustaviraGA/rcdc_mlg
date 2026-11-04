{{-- Tab 4: CBDC comdev activities from the ImportPKM upload of the semester holding most of the quarter. --}}
@php($number = fn ($value) => $value === null ? '—' : rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ','))
<div class="alert alert-light-primary border border-primary-subtle mb-5">
    Data Community Development diunggah per semester. {{ $data['label'] }} memakai semester <strong data-cb-semester>{{ $data['semester'] }}</strong>
    ({{ $data['semester_months'] }}), yaitu semester yang mencakup sebagian besar bulan pada kuartal tersebut.
</div>
@if($data['batch'])
    <p class="text-muted mb-5">
        Sumber: Import Data PKM {{ $data['semester'] }} ({{ $data['batch']->filename }}).
        @if($data['source'] === 'Keterlibatan')
            Sheet Rasio tidak memuat jumlah kegiatan CBDC, sehingga realisasi adalah jumlah kegiatan yang dilaporkan dosen CBDC pada sheet Keterlibatan.
        @else
            Realisasi adalah jumlah kegiatan CBDC pada sheet Rasio.
        @endif
        Dosen CBDC adalah dosen dengan Gugus Binaan Character Building pada data dosen.
    </p>
@else
    <div class="alert alert-secondary mb-5">Belum ada upload Community Development untuk semester {{ $data['semester'] }}. Unggah melalui Import Data PKM, atau pilih kuartal lain.</div>
@endif
@include('characterbuilding::summary', ['summary' => $data['summary'], 'periodLabel' => $data['label'],
    'unit' => 'Kegiatan', 'realizationNote' => $data['source'] === 'Keterlibatan' ? 'dilaporkan dosen CBDC.' : 'CBDC pada sheet Rasio.'])
@if($data['batch'])
    @if($data['program'])
        <div class="d-flex flex-wrap gap-8 border rounded p-5 mb-6">
            <div><div class="text-muted fs-7">Jumlah FM CBDC (Rasio)</div><div class="fs-3 fw-bold">{{ $data['program']['faculty'] ?? '—' }}</div></div>
            <div><div class="text-muted fs-7">Jumlah kegiatan</div><div class="fs-3 fw-bold">{{ $data['program']['activities'] ?? '—' }}</div></div>
            <div><div class="text-muted fs-7">Rasio kegiatan per FM</div><div class="fs-3 fw-bold">{{ $number($data['program']['ratio']) }}</div></div>
        </div>
    @else
        <p class="text-muted">Sheet Rasio pada upload ini tidak memuat baris CBDC.</p>
    @endif
    <h4 class="mb-3">Dosen CBDC ({{ $data['lecturers']->count() }})</h4>
    @if($data['lecturers']->isEmpty())
        <p class="text-muted mb-0">Tidak ada dosen CBDC pada sheet Keterlibatan upload ini.</p>
    @else
        <div class="table-responsive">
            <table class="table table-row-bordered table-row-gray-200 align-middle fs-7 mb-0">
                <thead><tr class="fw-bold text-muted"><th>Kode</th><th>Nama dosen</th><th class="text-end">Kegiatan dilaporkan</th><th class="text-end">Skor KPI Community Development</th><th>Keterangan</th></tr></thead>
                <tbody>
                    @foreach($data['lecturers'] as $row)
                        <tr>
                            <td>{{ $row['code'] }}</td><td>{{ $row['name'] }}</td><td class="text-end fw-bold">{{ $row['activities'] ?? '—' }}</td>
                            <td class="text-end">{{ $number($row['score']) }}</td><td>{{ $row['status'] ?: '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endif

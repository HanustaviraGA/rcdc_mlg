<div id="monthly-import">
    <div class="card card-bordered mb-6">
        <div class="card-body">
            <h2 class="mb-3">Upload laporan bulanan</h2>
            <p class="text-muted">Tiga sumber per bulan: publikasi FM, publikasi mahasiswa, dan hibah penelitian.</p>
            <div class="alert alert-primary">Pilih tahun dan bulan laporan, lalu unggah satu atau beberapa file. Upload ulang mengganti sumber sejenis pada bulan yang sama. Seluruh file yang dipilih harus lolos validasi sebelum disimpan.</div>
            <div id="monthly-import-errors" class="alert alert-danger" role="alert" hidden></div>
            <div id="monthly-import-summary" aria-live="polite">@include('importrectorate::summary', ['summaries' => session('monthly_summaries', [])])</div>
            <form id="monthly-import-form" action="{{ route('importrectorate.upload') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row g-5 mb-6">
                    <div class="col-md-6"><label class="form-label required" for="upload-year">Tahun laporan</label><input class="form-control" id="upload-year" name="year" type="number" min="2000" max="2100" required value="{{ old('year', now()->year) }}"></div>
                    <div class="col-md-6"><label class="form-label required" for="upload-month">Bulan laporan</label><select class="form-select" id="upload-month" name="month" required>@foreach(range(1, 12) as $month)<option value="{{ $month }}" @selected((int) old('month', now()->month) === $month)>{{ \Carbon\Carbon::create(2026, $month, 1)->locale('id')->translatedFormat('F') }}</option>@endforeach</select></div>
                </div>
                <div class="mb-6"><label class="form-label fw-bold" for="fm_file">Publikasi FM (.xlsx)</label><input class="form-control" id="fm_file" name="fm_file" type="file" accept=".xlsx"><div class="form-text">Sheet MALANG (atau Raw) dan KPI. Termasuk daftar dosen, bobot Rectorate / RTTO, first author, dan skor KPI.</div></div>
                <div class="mb-6"><label class="form-label fw-bold" for="mhs_file">Publikasi MHS (.xlsx)</label><input class="form-control" id="mhs_file" name="mhs_file" type="file" accept=".xlsx"><div class="form-text">Membaca sheet MALANG, TITLE, dan LIST untuk publikasi mahasiswa.</div></div>
                <div class="mb-6"><label class="form-label fw-bold" for="research_file">Hibah penelitian (.xlsx)</label><input class="form-control" id="research_file" name="research_file" type="file" accept=".xlsx"><div class="form-text">Mengutamakan sheet MALANG, lalu Detail, dengan Lokasi Kampus = Binus @Malang. Tahun Anggaran mengikuti isi file; tahun dan bulan laporan mengikuti pilihan di atas.</div></div>
                <p class="text-muted">Maksimal 20 MB per file. Pilih minimal satu file.</p>
                <div class="d-flex flex-wrap gap-3"><button class="btn btn-primary" id="monthly-import-submit" type="submit">Upload dan import</button><a class="btn btn-light-primary" href="{{ url('/dashboard/perhitungankpi') }}">Data KPI dosen</a><a class="btn btn-light-primary" href="{{ url('/dashboard/exportreport') }}">Generate laporan</a></div>
            </form>
        </div>
    </div>
    <div class="card card-bordered"><div class="card-body"><h3 class="mb-5">Kelengkapan sumber bulanan</h3><div id="monthly-import-history">@include('importrectorate::history')</div></div></div>
</div>
@include('importrectorate::javascript')

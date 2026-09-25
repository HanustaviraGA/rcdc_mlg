<div class="card card-bordered" id="imported-kpi">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between gap-3 mb-5">
            <div><h2>Data KPI dosen</h2><p class="text-muted mb-0">Seluruh data hasil import langsung ditampilkan. Satu baris untuk setiap dosen dan bulan laporan.</p></div>
            <a class="btn btn-light-primary align-self-start" href="{{ url('/dashboard/importrectorate') }}">Upload laporan bulanan</a>
        </div>
        <form id="kpi-filters" class="row g-4 mb-5">
            @csrf
            <div class="col-12 col-lg-4"><label class="form-label" for="kpi-search">Cari dosen</label><input type="search" class="form-control" id="kpi-search" name="search" maxlength="255" placeholder="Nama, kode dosen, atau program studi" autocomplete="off"></div>
            <div class="col-6 col-lg-2"><label class="form-label" for="kpi-year">Tahun</label><select class="form-select" id="kpi-year" name="year"><option value="">Semua tahun</option>@foreach($years as $year)<option value="{{ $year }}">{{ $year }}</option>@endforeach</select></div>
            <div class="col-6 col-lg-2"><label class="form-label" for="kpi-month">Bulan</label><select class="form-select" id="kpi-month" name="month"><option value="">Semua bulan</option>@foreach(range(1, 12) as $month)<option value="{{ $month }}">{{ \Carbon\Carbon::create(2026, $month, 1)->locale('id')->translatedFormat('F') }}</option>@endforeach</select></div>
            <div class="col-8 col-lg-3"><label class="form-label" for="kpi-program">Program studi</label><select class="form-select" id="kpi-program" name="prodi"><option value="">Semua program studi</option>@foreach($programs as $program)<option value="{{ $program }}">{{ $program }}</option>@endforeach</select></div>
            <div class="col-4 col-lg-1 d-flex align-items-end"><button class="btn btn-light w-100 px-2" type="reset">Reset</button></div>
        </form>
        <div id="kpi-error" class="alert alert-danger" role="alert" hidden></div>
        <p id="kpi-count" class="fw-bold" role="status">{{ $rows->count() }} baris dari {{ $rows->pluck('code')->unique()->count() }} dosen</p>
        <div class="table-responsive" tabindex="0" aria-label="Data KPI dosen, geser untuk melihat seluruh kolom">
            <table class="table table-striped table-row-bordered align-middle" style="min-width:1900px">
                <thead><tr class="fw-bold">
                    <th>No.</th><th>Tahun</th><th>Bulan</th><th style="min-width:230px">Dosen</th><th>Prodi</th><th>JJA</th><th>Faculty</th>
                    <th>Non Scopus</th><th>Scopus</th><th>Judul Scopus</th><th>First author</th><th>Skor KPI</th><th>Sumber skor</th>
                    <th>Non Scopus Rectorate</th><th>Scopus Rectorate</th><th>Non Scopus RTTO</th><th>Scopus RTTO</th><th>Hibah ketua</th><th>Hibah anggota</th>
                </tr></thead>
                <tbody id="table_kpi">@include('perhitungankpi::rows')</tbody>
            </table>
        </div>
        <p class="text-muted mt-5 mb-2">Bobot akhir adalah nilai tertinggi Rectorate / RTTO per kategori. Skor mengikuti RTTO dari sheet KPI. Untuk file lama tanpa sheet KPI, skor dihitung oleh sistem dari publikasi bulan tersebut.</p>
        <p class="text-muted mb-0">Tanda &mdash; berarti data belum tersedia. Hibah menghitung proyek unik per peran dari file bulan yang sama, termasuk seluruh tahun anggaran di dalamnya. Import hibah lama tanpa bulan laporan ditampilkan terpisah.</p>
    </div>
</div>
@include('perhitungankpi::javascript')

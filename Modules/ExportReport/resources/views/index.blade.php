<div class="card my-5"><div class="card-body">
    <h2>Laporan publikasi Scopus FM &amp; mahasiswa</h2>
    <p class="text-muted">Generate data dari periode terpilih, periksa dan ubah isi laporan, lalu unduh Word sesuai template. Perubahan hanya berlaku pada draft laporan.</p>
    <div class="row g-4 align-items-end">
        <div class="col-md-3"><label class="form-label" for="reportYear">Tahun</label><input class="form-control" type="number" id="reportYear" min="2000" max="2100" value="{{ now()->year }}"></div>
        <div class="col-md-3"><label class="form-label" for="reportMonth">Bulan</label><select class="form-select" id="reportMonth">@foreach(range(1, 12) as $month)<option value="{{ $month }}" @selected($month === now()->month)>{{ \Carbon\Carbon::create(2026, $month, 1)->locale('id')->translatedFormat('F') }}</option>@endforeach</select></div>
        <div class="col-md-6 d-flex gap-3"><button class="btn btn-primary" id="generateReport" type="button">Generate</button><a class="btn btn-light-primary" href="{{ url('/dashboard/importrectorate') }}">Upload 3 sumber bulanan</a></div>
    </div>
    <div id="reportMessage" role="status" aria-live="polite" class="mt-4" style="white-space:pre-wrap"></div>
</div></div>
<div id="reportEditor" hidden>
    <div class="card mb-5"><div class="card-body">
        <h3>Pratinjau dan edit laporan</h3>
        <p class="text-muted">Semua sel tabel dapat diedit. Grafik mengikuti Target dan Realization pada rekap. Jika mengubah rincian, sesuaikan rekap dan skor yang terkait. Sel kosong berarti data belum tersedia. Draft tersimpan selama sesi login ini.</p>
        <label for="reportTitle" class="form-label">Judul</label><input id="reportTitle" class="form-control mb-3">
        <label for="reportPeriod" class="form-label">Periode pada dokumen</label><input id="reportPeriod" class="form-control mb-4">
        <div class="d-flex gap-3"><button id="saveReport" class="btn btn-light-primary" type="button">Simpan draft</button><button id="downloadReport" class="btn btn-primary" type="button">Unduh Word</button></div>
        <div id="reportSources" class="mt-4 text-muted"></div>
    </div></div>
    <div id="reportCharts" class="row g-4 mb-5"></div>
    <div id="reportTables"></div>
</div>
@include('exportreport::javascript')

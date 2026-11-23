<div id="pkmImport" data-preview-url="{{ route('importpkm.preview') }}" data-upload-url="{{ route('importpkm.upload') }}" data-csrf="{{ csrf_token() }}">
    <div class="card card-bordered mb-5">
        <div class="card-header"><h3 class="card-title">Pilih Jenis Import PkM / ComDev</h3></div>
        <div class="card-body">
            <div class="mb-0">
                <label for="pkmImportType" class="form-label fw-bold">Jenis Import</label>
                <select id="pkmImportType" class="form-select" style="max-width: 450px;">
                    <option value="semester" selected>Import Community Development FM (Per Semester - Rasio &amp; Keterlibatan)</option>
                    <option value="periodic">Import Community Development FM Periodik (Rentang Tanggal - Keterlibatan Saja)</option>
                    <option value="partners">Import Mitra Binaan / UMKM</option>
                </select>
            </div>
        </div>
    </div>

    <form id="pkmImportForm" class="card card-bordered mb-5" enctype="multipart/form-data">
        <div class="card-header"><h3 class="card-title" id="pkmFormTitle">Import Community Development FM</h3></div>
        <div class="card-body">
            <p class="text-muted" id="pkmInstructions">Unggah workbook berisi data <strong>Rasio</strong> dan <strong>Keterlibatan</strong>. Sheet bernama Rasio dan Keterlibatan dipilih otomatis; sheet lain dapat dipilih pada pratinjau. Petakan kolom pada kedua sheet sebelum mengimpor.</p>
            
            <input type="hidden" name="type" id="pkmTypeInput" value="semester">

            <div id="semesterFields" class="row g-5 mb-5">
                <div class="col-md-6"><label for="pkmAcademicYear" class="required form-label fw-bold">Tahun akademik</label>
                    <select name="academic_year" id="pkmAcademicYear" class="form-select">
                        @foreach($academicYears as $year)
                            <option value="{{ $year }}/{{ $year + 1 }}" @selected($year == $defaultYear)>{{ $year }}/{{ $year + 1 }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6"><label for="pkmSemester" class="required form-label fw-bold">Semester</label><select name="period" id="pkmSemester" class="form-select"><option value="Ganjil">Ganjil</option><option value="Genap">Genap</option></select></div>
            </div>

            <div id="periodicFields" class="row g-5 mb-5" style="display: none;">
                <div class="col-md-6"><label for="pkmStartDate" class="required form-label fw-bold">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="pkmStartDate" class="form-control" value="{{ date('Y-m-01') }}">
                </div>
                <div class="col-md-6"><label for="pkmEndDate" class="required form-label fw-bold">Tanggal Selesai</label>
                    <input type="date" name="end_date" id="pkmEndDate" class="form-control" value="{{ date('Y-m-t') }}">
                </div>
            </div>

            <p class="mb-5">Periode / Tanggal upload: <strong id="pkmPeriodLabel"></strong></p>
            <label for="pkmWorkbook" class="required form-label fw-bold">File Excel (.xlsx)</label>
            <input type="file" accept=".xlsx" required name="pkm" id="pkmWorkbook" class="form-control mb-3">
            <p class="text-muted fs-7">Jumlah kegiatan pada rincian dosen menggunakan kolom H di Keterlibatan, yaitu kegiatan yang dilaporkan ke Binusmaya. Nama dan prodi dosen diambil dari Modules/Dosen berdasarkan kode dosen.</p>
            <div id="pkmPreviewStatus" class="mb-3" role="status" aria-live="polite"></div>
            <button type="button" id="pkmPreviewRetry" class="btn btn-sm btn-light-primary mb-4" hidden>Coba baca file kembali</button>
            <div id="pkmMappingPanel" hidden>
                <div id="mappingSectionRasio">
                    @foreach(['rasio' => 'Rasio'] as $key => $name)
                        <section class="border rounded p-5 mb-5" aria-labelledby="pkm-{{ $key }}-title">
                            <h4 id="pkm-{{ $key }}-title">Pemetaan sheet {{ $name }}</h4>
                            <div class="mb-3" style="max-width: 420px">
                                <label for="pkm-{{ $key }}-sheet" class="form-label fw-bold">Sheet untuk {{ $name }}</label>
                                <select id="pkm-{{ $key }}-sheet" class="form-select form-select-sm" data-sheet-for="{{ $key }}"></select>
                            </div>
                            <p class="text-muted fs-7" id="pkm-{{ $key }}-info"></p>
                            <div class="table-responsive"><table class="table table-row-bordered align-middle"><thead><tr><th>Kolom sistem</th><th>Kolom Excel</th><th>Contoh nilai</th></tr></thead><tbody id="pkm-{{ $key }}-mapping"></tbody></table></div>
                            <details class="mt-4"><summary class="text-primary">Lihat pratinjau data</summary><div class="table-responsive mt-3"><table class="table table-row-bordered fs-7"><thead id="pkm-{{ $key }}-headers"></thead><tbody id="pkm-{{ $key }}-preview"></tbody></table></div></details>
                        </section>
                    @endforeach
                </div>
                <div id="mappingSectionKeterlibatan">
                    @foreach(['keterlibatan' => 'Keterlibatan'] as $key => $name)
                        <section class="border rounded p-5 mb-5" aria-labelledby="pkm-{{ $key }}-title">
                            <h4 id="pkm-{{ $key }}-title">Pemetaan sheet {{ $name }}</h4>
                            <div class="mb-3" style="max-width: 420px">
                                <label for="pkm-{{ $key }}-sheet" class="form-label fw-bold">Sheet untuk {{ $name }}</label>
                                <select id="pkm-{{ $key }}-sheet" class="form-select form-select-sm" data-sheet-for="{{ $key }}"></select>
                            </div>
                            <p class="text-muted fs-7" id="pkm-{{ $key }}-info"></p>
                            <div class="table-responsive"><table class="table table-row-bordered align-middle"><thead><tr><th>Kolom sistem</th><th>Kolom Excel</th><th>Contoh nilai</th></tr></thead><tbody id="pkm-{{ $key }}-mapping"></tbody></table></div>
                            <details class="mt-4"><summary class="text-primary">Lihat pratinjau data</summary><div class="table-responsive mt-3"><table class="table table-row-bordered fs-7"><thead id="pkm-{{ $key }}-headers"></thead><tbody id="pkm-{{ $key }}-preview"></tbody></table></div></details>
                        </section>
                    @endforeach
                </div>
                <p id="pkmMappingStatus" role="status" aria-live="polite"></p>
                <button type="button" id="pkmMappingConfirm" class="btn btn-light-primary me-3" disabled>Konfirmasi pemetaan</button>
            </div>
            <button type="submit" id="pkmImportSubmit" class="btn btn-primary mt-4" disabled>Import data</button>
            <div id="pkmImportErrors" class="alert alert-danger mt-5" role="alert" style="white-space: pre-line" hidden></div>
            <div id="pkmImportSummary" class="alert alert-success mt-5" role="status" style="white-space: pre-line" hidden></div>
        </div>
    </form>
    <div id="pkmHistoryCard" class="card card-bordered"><div class="card-header"><h3 class="card-title">Riwayat import Community Development (Per Semester)</h3></div><div class="card-body" id="pkmImportHistory">@include('importpkm::history')</div></div>
    <div id="periodicHistoryCard" class="card card-bordered" style="display: none;"><div class="card-header"><h3 class="card-title">Riwayat import Community Development (Periodik)</h3></div><div class="card-body" id="periodicImportHistory">@include('importpkm::periodic_history')</div></div>
</div>

<div id="partnerImportContainer" style="display: none;">
    @include('importpkm::partner_import')
</div>
@include('importpkm::javascript')

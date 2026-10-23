<div id="pkmImport" data-preview-url="{{ route('importpkm.preview') }}" data-upload-url="{{ route('importpkm.upload') }}" data-csrf="{{ csrf_token() }}">
    <form id="pkmImportForm" class="card card-bordered mb-5" enctype="multipart/form-data">
        <div class="card-header"><h3 class="card-title">Import Community Development FM</h3></div>
        <div class="card-body">
            <p class="text-muted">Unggah workbook dengan sheet <strong>Rasio</strong> dan <strong>Keterlibatan</strong>. Petakan kolom pada kedua sheet sebelum mengimpor.</p>
            <div class="row g-5 mb-5">
                <div class="col-md-6"><label for="pkmAcademicYear" class="required form-label fw-bold">Tahun akademik</label>
                    <select required name="academic_year" id="pkmAcademicYear" class="form-select">
                        @foreach($academicYears as $year)
                            <option value="{{ $year }}/{{ $year + 1 }}" @selected($year == $defaultYear)>{{ $year }}/{{ $year + 1 }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6"><label for="pkmSemester" class="required form-label fw-bold">Semester</label><select required name="period" id="pkmSemester" class="form-select"><option value="Ganjil">Ganjil</option><option value="Genap">Genap</option></select></div>
            </div>
            <p class="mb-5">Periode upload: <strong id="pkmPeriodLabel"></strong></p>
            <label for="pkmWorkbook" class="required form-label fw-bold">File Excel (.xlsx)</label>
            <input type="file" accept=".xlsx" required name="pkm" id="pkmWorkbook" class="form-control mb-3">
            <p class="text-muted fs-7">Rekap prodi menggunakan sheet Rasio. Jumlah kegiatan pada rincian dosen menggunakan kolom H di Keterlibatan, yaitu kegiatan yang dilaporkan ke Binusmaya. Nama dan prodi dosen diambil dari Modules/Dosen berdasarkan kode dosen.</p>
            <p class="text-muted fs-7">Unggah ulang akan mengganti kedua sheet untuk tahun akademik dan semester yang dipilih. Data periode lainnya tetap tersimpan.</p>
            <div id="pkmPreviewStatus" class="mb-3" role="status" aria-live="polite"></div>
            <button type="button" id="pkmPreviewRetry" class="btn btn-sm btn-light-primary mb-4" hidden>Coba baca file kembali</button>
            <div id="pkmMappingPanel" hidden>
                @foreach(['rasio' => 'Rasio', 'keterlibatan' => 'Keterlibatan'] as $key => $name)
                    <section class="border rounded p-5 mb-5" aria-labelledby="pkm-{{ $key }}-title">
                        <h4 id="pkm-{{ $key }}-title">Pemetaan sheet {{ $name }}</h4>
                        <p class="text-muted fs-7" id="pkm-{{ $key }}-info"></p>
                        <div class="table-responsive"><table class="table table-row-bordered align-middle"><thead><tr><th>Kolom sistem</th><th>Kolom Excel</th><th>Contoh nilai</th></tr></thead><tbody id="pkm-{{ $key }}-mapping"></tbody></table></div>
                        <details class="mt-4"><summary class="text-primary">Lihat pratinjau data</summary><div class="table-responsive mt-3"><table class="table table-row-bordered fs-7"><thead id="pkm-{{ $key }}-headers"></thead><tbody id="pkm-{{ $key }}-preview"></tbody></table></div></details>
                    </section>
                @endforeach
                <p id="pkmMappingStatus" role="status" aria-live="polite"></p>
                <button type="button" id="pkmMappingConfirm" class="btn btn-light-primary me-3" disabled>Konfirmasi pemetaan</button>
            </div>
            <button type="submit" id="pkmImportSubmit" class="btn btn-primary mt-4" disabled>Import data</button>
            <div id="pkmImportErrors" class="alert alert-danger mt-5" role="alert" style="white-space: pre-line" hidden></div>
            <div id="pkmImportSummary" class="alert alert-success mt-5" role="status" style="white-space: pre-line" hidden></div>
        </div>
    </form>
    <div class="card card-bordered"><div class="card-header"><h3 class="card-title">Riwayat import per periode</h3></div><div class="card-body" id="pkmImportHistory">@include('importpkm::history')</div></div>
</div>
@include('importpkm::javascript')

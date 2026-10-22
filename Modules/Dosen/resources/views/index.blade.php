<div class="row table_data mb-5" data-roleable="false" data-role="Company-Read">
    <div class=" col-12" id="tableCourseContainer">
        <div class="card card-bordered">
            <div class="card-body">
                <div class="row mb-5">
                    <div class="col-12 col-xl d-flex align-items-center justify-content-between ">
                        <div class=" d-flex align-items-center">
                            <p> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                    <path d="M15 2.28572V3.71428C15 4.97322 11.8646 6 8 6C4.13541 6 1 4.97322 1 3.71428V2.28572C1 1.02678 4.13541 0 8 0C11.8646 0 15 1.02678 15 2.28572ZM15 5.5V8.71428C15 9.97322 11.8646 11 8 11C4.13541 11 1 9.97322 1 8.71428V5.5C2.50391 6.53572 5.2565 7.01788 8 7.01788C10.7435 7.01788 13.4961 6.53572 15 5.5ZM15 10.5V13.7143C15 14.9732 11.8646 16 8 16C4.13541 16 1 14.9732 1 13.7143V10.5C2.50391 11.5357 5.2565 12.0179 8 12.0179C10.7435 12.0179 13.4961 11.5357 15 10.5Z" fill="black" />
                                </svg></p>
                            <h3 class="mt-1 mb-5 ms-5">Dosen</h3>
                        </div>
                        <div class=" col-lg-6 d-none d-xl-block">
                            <p class="mt-4 mb-5 ms-5 text-end">Pencarian :</p>
                        </div>
                        <div class="col-12 col-xl-4 col-md-6 col-lg-6 d-flex align-items-center ms-5 mt-5 mt-md-0">
                            <label for="customSearchInput" class="text-muted las la-search position-absolute ms-3 fs-2 mt-1"></label>
                            <input type="search" class="form-control form-control ps-10 bg-gray-100" id="customSearchInput" placeholder="Cari nama">
                        </div>
                    </div>
                    <div class="col-12 col-xl-auto d-flex flex-wrap gap-3 align-items-center mt-5 my-xl-0">
                        <button type="button" class="btn btn-light-primary" onclick="openDosenFilters()" id="dosenFilterButton" aria-haspopup="dialog" aria-controls="modalDosenFilter"><i class="las la-filter fs-2" aria-hidden="true"></i> Filter</button>
                        <div data-roleable="false" data-role="editdosen">
                            <button type="button" class="btn btn-primary" onclick="$('#modalHakakses').modal('show')" id="toggleFormButton"><i class="las la-plus fs-2"></i> Perbarui Data</button>
                        </div>
                    </div>
                </div>
                <p id="dosenHistoricalNotice" class="text-muted d-none">Data periode historis hanya dapat dilihat. Edit, hapus, dan pengaturan visibilitas tidak tersedia.</p>
                <p id="dosenFilterError" class="text-danger d-none" role="alert"></p>
                <p class="text-muted">Centang <strong>Sembunyikan profil</strong> untuk menyembunyikan dosen dari beranda dan daftar/detail dosen di halaman depan. Hapus centang untuk menampilkannya kembali.</p>
                <div class="table-responsive border-2 border-top">
                    <table class="table table-striped table-row-bordered align-middle rounded tdFirstCenter" id="tableCourse">
                        <thead>
                            <tr class="fw-bolder text-dark">
                                <th style="width: 100px !important;">Kode Dosen</th>
                                <th>Sembunyikan profil</th>
                                <th style="width: 400px !important;">Nama Dosen</th>
                                <th style="width: 200px !important;">Program Studi</th>
                                <th>Faculty Type</th>
                                <th>JJA</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="table_dosen">
                            <tr id="package_empty">
                                <td colspan="7" id="empty-message-package" class="text-center">Tidak ada Data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="prodi" value="All">
<input type="hidden" id="dosenFilterYear" value="{{ now()->year }}">
<input type="hidden" id="dosenFilterMonth" value="">
<div class="modal fade" id="modalDosenFilter" tabindex="-1" aria-labelledby="dosenFilterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="formDosenFilter" onsubmit="applyDosenFilters(event)">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="dosenFilterTitle">Filter Data Dosen</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-5">
                        <label for="dosenFilterYearInput" class="form-label">Tahun data</label>
                        <input type="number" id="dosenFilterYearInput" class="form-control" min="2000" max="2100" value="{{ now()->year }}" list="dosenAvailableYears" required>
                        <datalist id="dosenAvailableYears">
                            @foreach(($years ?? collect()) as $year)
                                <option value="{{ $year }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="mb-5">
                        <label for="dosenFilterMonthInput" class="form-label">Bulan data</label>
                        <select id="dosenFilterMonthInput" class="form-select">
                            <option value="">Terbaru dalam tahun terpilih</option>
                            @foreach([1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $number => $label)
                                <option value="{{ $number }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="dosenFilterProdiInput" class="form-label">Program studi</label>
                        <select id="dosenFilterProdiInput" class="form-select">
                            <option value="All">Semua Prodi</option>
                            @foreach($program as $prodi)
                                @if($prodi->nama_gugus_binaan)
                                    <option value="{{ $prodi->nama_gugus_binaan }}">{{ $prodi->nama_gugus_binaan }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light me-auto" onclick="resetDosenFilters()">Reset</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Terapkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modalHakakses" tabindex="-1" aria-labelledby="dosenImportTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form action="javascript:onAdd()" method="post" id="formHakakses" name="formHakakses" autocomplete="off" enctype="multipart/form-data" class="d-flex flex-column overflow-hidden">
                @csrf
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="dosenImportTitle">Perbarui Data</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="fv-row mb-7 fv-plugins-icon-container">
                        <div class="row g-3 mb-5">
                            <div class="col-md-6">
                                <label for="dosenImportYear" class="required form-label">Tahun</label>
                                <input type="number" id="dosenImportYear" name="year" class="form-control" min="2000" max="2100" value="{{ now()->year }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="dosenImportMonth" class="required form-label">Bulan</label>
                                <select id="dosenImportMonth" name="month" class="form-select" required>
                                    @foreach([1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $number => $label)
                                        <option value="{{ $number }}" @selected($number === now()->month)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <label for="dosen" class="required form-label">Data Center (.xlsx)</label>
                        <input type="file" accept=".xlsx" required id="dosen" name="dosen" class="form-control form-control-outline" onchange="previewDosenWorkbook()">
                        <p class="text-muted mt-3">Membaca sheet Malang (atau FM Malang / FM BINUS Malang). Periksa contoh data dan konfirmasikan pemetaan sebelum mengunggah. Hanya Kode Dosen dan Nama Dosen yang wajib dipetakan; kolom opsional boleh tidak dipetakan atau memiliki nilai kosong dan tidak menghalangi unggahan. Kolom opsional yang tidak dipetakan memakai data snapshot periode yang sama atau sebelumnya dalam tahun terpilih, jika tersedia. Unggahan historis tidak mengubah master tahun berjalan.</p>
                        <p id="dosenPreviewStatus" role="status" aria-live="polite"></p>
                        <button type="button" id="dosenPreviewRetry" class="btn btn-sm btn-light-primary d-none mb-3" onclick="previewDosenWorkbook()">Coba pratinjau lagi</button>
                        <div id="dosenMappingPanel" class="d-none">
                            <p id="dosenSheetInfo" class="fw-bold"></p>
                            <h2 class="fs-5">Pemetaan kolom</h2>
                            <div class="table-responsive mb-4" style="max-height: 360px">
                                <table class="table table-bordered align-middle">
                                    <thead><tr><th>Kolom tujuan</th><th>Kolom file</th><th>Contoh nilai</th></tr></thead>
                                    <tbody id="dosenMappingRows"></tbody>
                                </table>
                            </div>
                            <h2 class="fs-5">Pratinjau (maksimal 5 baris)</h2>
                            <div class="table-responsive mb-4" style="max-height: 260px">
                                <table class="table table-bordered table-striped">
                                    <thead><tr id="dosenPreviewHeaders"></tr></thead>
                                    <tbody id="dosenPreviewRows"></tbody>
                                </table>
                            </div>
                            <p id="dosenMappingStatus" role="status" aria-live="polite"></p>
                            <button type="button" id="dosenMappingConfirm" class="btn btn-light-primary" onclick="confirmDosenMapping()" disabled>Konfirmasi pemetaan</button>
                        </div>
                        <pre id="dosenImportSummary" class="mt-3" style="white-space:pre-wrap" aria-live="polite"></pre>
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" id="dosenImportSubmit" class="btn btn-primary" disabled>Unggah</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('dosen::modal')
<script src="{{ asset('assets/backoffice/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
@include('dosen::javascript')
@include('dosen::import_javascript')

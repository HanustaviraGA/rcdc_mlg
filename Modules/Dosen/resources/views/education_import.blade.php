<div class="modal fade" id="modalDosenEducation" tabindex="-1" aria-labelledby="dosenEducationTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form action="javascript:importDosenEducationWorkbook()" method="post" id="formDosenEducation" enctype="multipart/form-data" class="d-flex flex-column overflow-hidden">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="dosenEducationTitle">Update Status Pendidikan</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-5">
                        <div class="col-md-6">
                            <label for="dosenEducationYear" class="required form-label">Tahun</label>
                            <input type="number" id="dosenEducationYear" name="year" class="form-control" min="2000" max="2100" value="{{ now()->year }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="dosenEducationMonth" class="required form-label">Bulan</label>
                            <select id="dosenEducationMonth" name="month" class="form-select" required>
                                @foreach([1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $number => $label)
                                    <option value="{{ $number }}" @selected($number === now()->month)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <label for="dosenEducationFile" class="required form-label">Status Pendidikan (.xlsx)</label>
                    <input type="file" accept=".xlsx" required id="dosenEducationFile" class="form-control" onchange="previewDosenEducationWorkbook()">
                    <p class="text-muted mt-3">Membaca sheet <strong>Detail Jenjang Pendidikan</strong>. Secara otomatis, Kode Dosen diambil dari kolom A dan Status Pendidikan dari kolom H (Pendidikan), misalnya S3 atau On Going S3. Periksa pratinjau dan konfirmasikan pemetaan sebelum mengunggah. Maksimal 20 MB.</p>
                    <p class="text-muted">Unggah Data Center pada tahun dan bulan terpilih terlebih dahulu. Hanya status pendidikan dosen yang cocok berdasarkan Kode Dosen pada periode tersebut yang diperbarui. Nilai Pendidikan yang kosong mengosongkan status dosen tersebut. Dosen lain dan Pendidikan Terakhir tetap tersimpan.</p>
                    <p id="dosenEducationPreviewStatus" role="status" aria-live="polite"></p>
                    <button type="button" id="dosenEducationPreviewRetry" class="btn btn-sm btn-light-primary d-none mb-3" onclick="previewDosenEducationWorkbook()">Coba pratinjau lagi</button>
                    <div id="dosenEducationMappingPanel" class="d-none">
                        <p id="dosenEducationSheetInfo" class="fw-bold"></p>
                        <h3 class="fs-5">Pemetaan kolom</h3>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle">
                                <thead><tr><th>Kolom tujuan</th><th>Kolom file</th><th>Contoh nilai</th></tr></thead>
                                <tbody id="dosenEducationMappingRows"></tbody>
                            </table>
                        </div>
                        <h3 class="fs-5">Pratinjau (maksimal 5 baris)</h3>
                        <div class="table-responsive mb-4" style="max-height: 260px">
                            <table class="table table-bordered table-striped">
                                <thead><tr id="dosenEducationPreviewHeaders"></tr></thead>
                                <tbody id="dosenEducationPreviewRows"></tbody>
                            </table>
                        </div>
                        <p id="dosenEducationMappingStatus" role="status" aria-live="polite"></p>
                        <button type="button" id="dosenEducationMappingConfirm" class="btn btn-light-primary" onclick="confirmDosenEducationMapping()" disabled>Konfirmasi pemetaan</button>
                    </div>
                    <pre id="dosenEducationSummary" class="mt-3" style="white-space: pre-wrap" aria-live="polite"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" id="dosenEducationSubmit" class="btn btn-primary" disabled>Unggah</button>
                </div>
            </form>
        </div>
    </div>
</div>

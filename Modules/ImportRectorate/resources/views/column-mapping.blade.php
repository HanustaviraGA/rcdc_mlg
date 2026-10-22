{{-- Component: Column Mapping Preview Modal --}}

<div id="column-mapping-modal" class="modal fade" tabindex="-1" aria-labelledby="columnMappingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="columnMappingModalLabel">
                        <i class="bi bi-table me-2"></i>Pemetaan Kolom
                    </h5>
                    <p class="text-muted mb-0 fs-7 mt-1">
                        Sesuaikan kolom file dengan kolom yang dibutuhkan sistem
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">

                {{-- Loading state --}}
                <div id="cm-loading" class="text-center py-10">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <p class="text-muted">Memuat preview file...</p>
                </div>

                {{-- Error state --}}
                <div id="cm-error" class="p-6" hidden>
                    <div class="alert alert-danger mb-0">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        <span id="cm-error-message">Gagal membaca file.</span>
                    </div>
                </div>

                {{-- Content state --}}
                <div id="cm-content" hidden>

                    {{-- File & Sheet Info --}}
                    <div class="p-5 border-bottom bg-light-subtle">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-7">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                                <span id="cm-file-name">-</span>
                            </span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fs-7">
                                <i class="bi bi-layers me-1"></i>Sheet: <strong id="cm-sheet-name">-</strong>
                            </span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 fs-7">
                                <i class="bi bi-columns-gap me-1"></i>
                                <span id="cm-col-count">0</span> kolom ditemukan
                            </span>
                        </div>
                    </div>

                    {{-- Validation Banner --}}
                    <div id="cm-validation-banner" class="px-5 pt-4" hidden>
                        <div id="cm-validation-errors" class="alert alert-danger mb-3" hidden>
                            <h6 class="alert-heading mb-2">
                                <i class="bi bi-x-circle me-1"></i>Pemetaan perlu diperbaiki:
                            </h6>
                            <ul id="cm-validation-error-list" class="mb-0 ps-3"></ul>
                        </div>
                        <div id="cm-validation-warnings" class="alert alert-info mb-3" hidden>
                            <h6 class="alert-heading mb-2">
                                <i class="bi bi-info-circle me-1"></i>Kolom opsional belum dipetakan (tidak menghalangi import):
                            </h6>
                            <p class="mb-2">Boleh dibiarkan kosong jika tidak tersedia. Data tambahan ini tidak akan diisi dari kolom file.</p>
                            <ul id="cm-validation-warning-list" class="mb-0 ps-3"></ul>
                        </div>
                        <div id="cm-validation-ok" class="alert alert-success mb-3" hidden>
                            <i class="bi bi-check-circle me-1"></i>Semua kolom wajib berhasil dipetakan.
                        </div>
                    </div>

                    {{-- Mapping Table --}}
                    <div class="p-5">
                        <h6 class="fw-bold mb-3">Pemetaan Kolom Sistem → Kolom File</h6>
                        <p class="text-muted small mb-4">
                            Sistem secara otomatis mendeteksi kolom yang sesuai. Ubah pemetaan jika ada kolom yang tidak cocok.
                            Kolom dengan tanda <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Wajib</span> harus dipetakan ke kolom sumber yang berbeda.
                            Kolom opsional boleh tidak dipetakan. Konfirmasi pemetaan, lalu klik <strong>Upload dan import</strong> pada halaman utama.
                        </p>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle" id="cm-mapping-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th style="min-width: 200px">Kolom Sistem</th>
                                        <th style="width: 100px" class="text-center">Tipe</th>
                                        <th style="min-width: 220px">Kolom File (Sumber)</th>
                                        <th style="min-width: 200px">Nilai Contoh</th>
                                        <th style="width: 80px" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="cm-mapping-rows">
                                    {{-- Diisi via JavaScript --}}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Preview Data --}}
                    <div class="px-5 pb-5">
                        <h6 class="fw-bold mb-3">
                            <i class="bi bi-eye me-1"></i>Preview Data File
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-2">5 baris pertama</span>
                        </h6>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-bordered table-striped mb-0 small" id="cm-preview-table">
                                <thead class="table-secondary">
                                    <tr id="cm-preview-headers"></tr>
                                </thead>
                                <tbody id="cm-preview-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Batal
                </button>
                <div class="d-flex gap-3 align-items-center">
                    <span id="cm-ready-badge" class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2" hidden>
                        <i class="bi bi-hourglass-split me-1"></i>Periksa mapping sebelum melanjutkan
                    </span>
                    <button type="button" class="btn btn-primary" id="cm-confirm-btn" disabled>
                        <i class="bi bi-check2-circle me-1"></i>Konfirmasi pemetaan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

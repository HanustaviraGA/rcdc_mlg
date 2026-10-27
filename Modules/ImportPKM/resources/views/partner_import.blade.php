<div id="partnerImport" class="mt-8" data-preview-url="{{ route('importpkm.partners.preview') }}" data-upload-url="{{ route('importpkm.partners.upload') }}" data-csrf="{{ csrf_token() }}">
    <form id="partnerImportForm" class="card card-bordered mb-5" enctype="multipart/form-data">
        <div class="card-header"><h3 class="card-title">Import mitra binaan / UMKM</h3><a href="{{ route('community-dashboard') }}" target="_blank" rel="noopener" class="btn btn-sm btn-light-primary align-self-center">Lihat Dashboard Comdev</a></div>
        <div class="card-body">
            <p class="text-muted">Unggah data mitra untuk menghitung jumlah mitra, kluster, skala usaha, dan sebaran area pada Dashboard Comdev.</p>
            <div class="row g-5 mb-5">
                <div class="col-md-4"><label for="partnerDataYear" class="required form-label fw-bold">Tahun data</label><input type="number" min="2000" max="2100" required id="partnerDataYear" class="form-control" value="{{ now()->year }}"></div>
                <div class="col-md-8"><label for="partnerWorkbook" class="required form-label fw-bold">File Excel (.xlsx / .xlsb)</label><input type="file" accept=".xlsx,.xlsb" required id="partnerWorkbook" class="form-control"></div>
            </div>
            <p class="text-muted fs-7">Maksimal 20 MB. Unggah ulang mengganti data mitra pada tahun yang dipilih. Tahun lainnya tetap tersimpan. Tahun data merupakan tahun rekap, berbeda dari tahun bergabung masing-masing mitra.</p>
            <div id="partnerPreviewStatus" role="status" aria-live="polite" class="mb-3"></div>
            <button type="button" id="partnerPreviewRetry" class="btn btn-sm btn-light-primary mb-4" hidden>Coba baca file kembali</button>
            <div id="partnerSheetControls" class="row g-5 mb-5" hidden>
                <div class="col-md-8"><label for="partnerSheet" class="form-label fw-bold">Sheet data mitra</label><select id="partnerSheet" class="form-select"></select></div>
                <div class="col-md-4"><label for="partnerHeaderRow" class="form-label fw-bold">Baris header</label><input id="partnerHeaderRow" type="number" min="1" max="1000" class="form-control"></div>
            </div>
            <section id="partnerMappingPanel" class="border rounded p-5 mb-4" aria-labelledby="partnerMappingTitle" hidden>
                <h4 id="partnerMappingTitle">Pemetaan kolom mitra</h4>
                <p class="text-muted fs-7">Periksa usulan pemetaan dan contoh nilainya. Data pribadi pemilik dan alamat tidak ditampilkan pada dashboard publik.</p>
                <div class="table-responsive"><table class="table table-row-bordered align-middle"><thead><tr><th>Kolom sistem</th><th>Kolom Excel</th><th>Contoh nilai</th></tr></thead><tbody id="partnerMappingRows"></tbody></table></div>
                <details class="mt-4"><summary class="text-primary">Lihat pratinjau data</summary><div class="table-responsive mt-3"><table class="table table-row-bordered fs-7"><thead id="partnerPreviewHeaders"></thead><tbody id="partnerPreviewRows"></tbody></table></div></details>
                <p id="partnerMappingStatus" class="mt-4" role="status" aria-live="polite"></p>
                <button type="button" id="partnerMappingConfirm" class="btn btn-light-primary" disabled>Konfirmasi pemetaan</button>
            </section>
            <button type="submit" id="partnerImportSubmit" class="btn btn-primary" disabled>Import data mitra</button>
            <div id="partnerImportErrors" class="alert alert-danger mt-5" role="alert" style="white-space: pre-line" hidden></div>
            <div id="partnerImportSummary" class="alert alert-success mt-5" role="status" style="white-space: pre-line" hidden></div>
        </div>
    </form>
    <div class="card card-bordered"><div class="card-header"><h3 class="card-title">Riwayat import mitra per tahun</h3></div><div class="card-body" id="partnerImportHistory">@include('importpkm::partner_history')</div></div>
</div>
@include('importpkm::partner_javascript')

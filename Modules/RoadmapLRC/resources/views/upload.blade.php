<div class="modal fade" id="roadmapUploadModal" tabindex="-1" aria-labelledby="roadmapUploadTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="roadmapUploadTitle">Upload Roadmap dari Sheet JJP</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <form id="roadmapUploadForm" data-preview-url="{{ route('roadmaplrc.upload.preview') }}" data-import-url="{{ route('roadmaplrc.upload.import') }}">
                    <label for="roadmapUploadFile" class="form-label required">File roadmap (.xlsx)</label>
                    <input type="file" class="form-control" id="roadmapUploadFile" name="roadmap" accept=".xlsx" required aria-describedby="roadmapUploadHelp">
                    <p id="roadmapUploadHelp" class="text-muted fs-7 mt-3">Maksimal 20 MB. Otomatis membaca sheet JJP, termasuk nama sheet dengan spasi di akhir. Format: PRODI, lalu Jumlah FM beserta tahun pada header pertama dan S2/S3 pada header berikutnya, seperti file contoh.</p>
                    <p class="text-muted fs-7">Angka Jumlah FM, S2, dan S3 diperbarui berdasarkan prodi dan tahun dalam file. NR dan catatan lama tetap tersimpan; prodi/tahun di luar file tetap tersimpan. Baris Total dihitung ulang. Pada prodi/tahun baru, NR dimulai dari 0.</p>
                    <button type="submit" class="btn btn-light-primary" id="roadmapUploadPreviewButton">Baca Pratinjau</button>
                </form>
                <div id="roadmapUploadStatus" class="fs-7 mt-4" role="status" aria-live="polite"></div>
                <div id="roadmapUploadError" class="alert alert-danger mt-4" role="alert" hidden></div>
                <div id="roadmapUploadPreview" class="mt-5"></div>
                <label id="roadmapUploadConfirmation" class="form-check mt-5" hidden>
                    <input type="checkbox" class="form-check-input" id="roadmapUploadConfirmed">
                    <span class="form-check-label">Saya sudah memeriksa pratinjau dan setuju memperbarui angka roadmap sesuai file, termasuk angka yang ditandai berbeda.</span>
                </label>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" id="roadmapUploadImportButton" disabled>Impor Roadmap</button>
            </div>
        </div>
    </div>
</div>

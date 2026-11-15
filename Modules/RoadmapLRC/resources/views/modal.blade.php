{{-- Edits one number of the roadmap and its note. --}}
<div class="modal fade" id="roadmapLrcModal" tabindex="-1" aria-labelledby="roadmapLrcModalTitle" aria-hidden="true" data-roadmap-modal>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form data-roadmap-form novalidate>
                <div class="modal-header">
                    <h3 class="modal-title fs-5" id="roadmapLrcModalTitle" data-roadmap-modal-title>Ubah roadmap</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-5">
                        <label class="form-label required" for="roadmapLrcValue">Nilai</label>
                        <input class="form-control" type="number" id="roadmapLrcValue" name="nilai" min="0" max="9999" step="1" required>
                    </div>
                    <div>
                        <label class="form-label" for="roadmapLrcNote">Catatan</label>
                        <textarea class="form-control" id="roadmapLrcNote" name="catatan" rows="3" maxlength="1000" placeholder="Opsional, misalnya alasan perubahan"></textarea>
                    </div>
                    <div class="text-danger fs-7 mt-4" data-roadmap-error role="alert" hidden></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" data-roadmap-submit>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

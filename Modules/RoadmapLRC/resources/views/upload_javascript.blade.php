<script>
(() => {
    const root = document.getElementById('roadmapLrc');
    if (!root) return;
    const form = root.querySelector('#roadmapUploadForm');
    const file = root.querySelector('#roadmapUploadFile');
    const previewButton = root.querySelector('#roadmapUploadPreviewButton');
    const importButton = root.querySelector('#roadmapUploadImportButton');
    const confirmed = root.querySelector('#roadmapUploadConfirmed');
    const confirmation = root.querySelector('#roadmapUploadConfirmation');
    const preview = root.querySelector('#roadmapUploadPreview');
    const status = root.querySelector('#roadmapUploadStatus');
    const error = root.querySelector('#roadmapUploadError');
    const modal = root.querySelector('#roadmapUploadModal');
    let previewHash = null;
    let previewFile = null;
    let busy = false;

    function controls() {
        file.disabled = busy;
        previewButton.disabled = busy;
        confirmed.disabled = busy;
        importButton.disabled = busy || !previewHash || !confirmed.checked;
    }

    function reset() {
        previewHash = null;
        previewFile = null;
        confirmed.checked = false;
        confirmation.hidden = true;
        preview.innerHTML = '';
        error.hidden = true;
        status.textContent = '';
        controls();
    }

    async function send(url, selectedFile, extra = {}) {
        const body = new FormData();
        body.append('roadmap', selectedFile);
        Object.entries(extra).forEach(([key, value]) => body.append(key, value));
        const response = await fetch(url, {
            method: 'POST', body, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': root.dataset.csrf },
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Upload gagal. Silakan coba lagi.');
        }
        return data;
    }

    function showError(exception) {
        status.textContent = '';
        error.textContent = exception.message;
        error.hidden = false;
    }

    file.addEventListener('change', reset);
    confirmed.addEventListener('change', controls);
    modal.addEventListener('hide.bs.modal', event => { if (busy) event.preventDefault(); });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy) return;
        reset();
        const selectedFile = file.files[0];
        if (!selectedFile) return;
        if (!selectedFile.name.toLowerCase().endsWith('.xlsx') || selectedFile.size > 20 * 1024 * 1024) {
            showError(new Error('Pilih file .xlsx berukuran maksimal 20 MB.'));
            return;
        }
        busy = true;
        controls();
        status.textContent = 'Membaca dan memvalidasi sheet JJP...';
        try {
            const data = await send(form.dataset.previewUrl, selectedFile);
            if (file.files[0] !== selectedFile) return;
            previewHash = data.preview_hash;
            previewFile = selectedFile;
            preview.innerHTML = data.preview_html;
            confirmation.hidden = false;
            status.textContent = 'Pratinjau siap. Periksa angka lalu centang konfirmasi untuk mengimpor.';
        } catch (exception) {
            showError(exception);
        } finally {
            busy = false;
            controls();
        }
    });

    importButton.addEventListener('click', async () => {
        if (busy || !previewHash || !confirmed.checked) return;
        if (file.files[0] !== previewFile) { reset(); return; }
        busy = true;
        controls();
        error.hidden = true;
        status.textContent = 'Mengimpor roadmap...';
        try {
            const data = await send(form.dataset.importUrl, previewFile, { confirmed: '1', preview_hash: previewHash });
            root.querySelector('[data-roadmap-table]').innerHTML = data.table_html;
            root.querySelector('[data-roadmap-period]').textContent = data.period_label;
            const pageStatus = root.querySelector('[data-roadmap-status]');
            pageStatus.className = 'fs-7 mb-4 text-success';
            pageStatus.textContent = data.message;
            reset();
            form.reset();
            busy = false;
            bootstrap.Modal.getOrCreateInstance(modal).hide();
        } catch (exception) {
            showError(exception);
        } finally {
            busy = false;
            controls();
        }
    });
})();
</script>

<script>
(function () {
    const root = document.getElementById('partnerImport');
    if (!root) return;
    window.partnerImportState?.request?.abort();
    const state = { file: null, preview: null, request: null, sequence: 0, loading: false, importing: false, confirmed: false };
    window.partnerImportState = state;
    const $ = id => root.querySelector('#' + id);
    const file = $('partnerWorkbook');
    const current = sequence => root.isConnected && window.partnerImportState === state && sequence === state.sequence;
    const message = (id, value) => { $(id).textContent = value; $(id).hidden = !value; };
    const errorText = error => error.errors ? Object.values(error.errors).flat().join('\n') : error.message || 'File gagal diproses. Silakan coba kembali.';
    const mapping = () => Object.fromEntries([...root.querySelectorAll('#partnerMappingRows select')].map(select => [select.dataset.field, select.value === '' ? null : Number(select.value)]));
    function valid() {
        if (!state.preview) return false;
        const values = mapping(), indexes = Object.values(values).filter(value => value !== null);
        return Object.keys(state.preview.mapping_config.required).every(field => Number.isInteger(values[field])) && new Set(indexes).size === indexes.length;
    }
    function controls() {
        const busy = state.loading || state.importing;
        root.querySelectorAll('input, select').forEach(input => { input.disabled = state.importing || (state.loading && ['partnerSheet', 'partnerHeaderRow'].includes(input.id)); });
        $('partnerImportSubmit').disabled = busy || !state.confirmed || !valid();
        $('partnerImportSubmit').textContent = state.importing ? 'Mengimpor…' : 'Import data mitra';
        $('partnerMappingConfirm').disabled = busy || state.confirmed || !valid();
        $('partnerPreviewRetry').disabled = busy;
        $('partnerMappingStatus').textContent = !state.preview ? '' : !valid() ? 'Petakan semua kolom wajib. Setiap kolom Excel hanya boleh dipakai sekali.' : state.confirmed ? 'Pemetaan dikonfirmasi. Data siap diimpor.' : 'Periksa kolom lalu konfirmasikan pemetaan.';
    }
    function render(preview) {
        $('partnerSheet').replaceChildren(...preview.sheets.map(name => new Option(name, name, false, name === preview.sheet)));
        $('partnerHeaderRow').value = preview.header_row;
        $('partnerSheetControls').hidden = false;
        $('partnerMappingRows').replaceChildren();
        Object.entries({ ...preview.mapping_config.required, ...preview.mapping_config.optional }).forEach(([field, label]) => {
            const row = document.createElement('tr'), title = document.createElement('td'), cell = document.createElement('td'), example = document.createElement('td');
            title.textContent = label + (Object.hasOwn(preview.mapping_config.required, field) ? ' (wajib)' : ' (opsional)');
            const select = document.createElement('select');
            select.className = 'form-select form-select-sm'; select.style.minWidth = '190px'; select.dataset.field = field;
            select.setAttribute('aria-label', 'Pemetaan ' + label);
            select.append(new Option('— Tidak dipetakan —', ''));
            preview.headers.forEach((header, index) => select.append(new Option((index + 1) + '. ' + (header || 'Kolom tanpa nama'), String(index))));
            if (Number.isInteger(preview.suggested_mapping[field])) select.value = String(preview.suggested_mapping[field]);
            const update = () => { example.textContent = select.value === '' ? '—' : String(preview.preview_data.map(row => row[Number(select.value)]).find(value => value !== '' && value !== null && value !== undefined) ?? '—'); };
            select.addEventListener('change', () => { state.confirmed = false; update(); controls(); }); update();
            example.className = 'text-muted text-break'; cell.append(select); row.append(title, cell, example); $('partnerMappingRows').append(row);
        });
        const headers = document.createElement('tr');
        preview.headers.forEach((label, index) => { const cell = document.createElement('th'); cell.className = 'text-nowrap'; cell.textContent = label || 'Kolom ' + (index + 1); headers.append(cell); });
        $('partnerPreviewHeaders').replaceChildren(headers); $('partnerPreviewRows').replaceChildren();
        preview.preview_data.forEach(values => { const row = document.createElement('tr'); preview.headers.forEach((_, index) => { const cell = document.createElement('td'); cell.className = 'text-nowrap'; cell.textContent = values[index] ?? ''; row.append(cell); }); $('partnerPreviewRows').append(row); });
        $('partnerMappingPanel').hidden = false;
    }
    async function request(url, data, signal) {
        const response = await fetch(url, { method: 'POST', body: data, signal, headers: { Accept: 'application/json', 'X-CSRF-TOKEN': root.dataset.csrf } });
        const payload = await response.json().catch(() => { throw { message: 'Respons server tidak dapat dibaca. Silakan coba kembali.' }; });
        if (!response.ok || payload.success !== true) throw payload;
        return payload;
    }
    async function preview(options = {}) {
        if (state.importing) return;
        const sequence = ++state.sequence;
        state.request?.abort(); state.file = file.files[0] || null;
        state.preview = null; state.confirmed = false; state.loading = false;
        $('partnerMappingPanel').hidden = true; $('partnerPreviewRetry').hidden = true;
        message('partnerImportErrors', ''); message('partnerImportSummary', ''); $('partnerPreviewStatus').textContent = '';
        controls();
        if (!state.file) return;
        if (!/\.(xlsx|xlsb)$/i.test(state.file.name)) { message('partnerImportErrors', 'Pilih file .xlsx atau .xlsb.'); return; }
        if (state.file.size > 20 * 1024 * 1024) { message('partnerImportErrors', 'Ukuran file maksimal 20 MB.'); return; }
        state.loading = true; state.request = new AbortController(); controls();
        $('partnerPreviewStatus').textContent = 'Membaca data mitra…';
        const data = new FormData(); data.append('partners', state.file);
        if (options.sheet) data.append('sheet', options.sheet);
        if (options.header) data.append('header_row', options.header);
        try {
            const result = await request(root.dataset.previewUrl, data, state.request.signal);
            if (!current(sequence) || file.files[0] !== state.file) return;
            state.preview = result; render(result); $('partnerPreviewStatus').textContent = 'Pratinjau tersedia. Pilih sheet dan baris header yang sesuai, lalu periksa pemetaan.';
        } catch (error) {
            if (!current(sequence) || error.name === 'AbortError') return;
            message('partnerImportErrors', errorText(error)); $('partnerPreviewStatus').textContent = ''; $('partnerPreviewRetry').hidden = false;
        } finally { if (current(sequence)) { state.loading = false; state.request = null; controls(); } }
    }
    file.addEventListener('change', () => { $('partnerSheetControls').hidden = true; preview(); });
    $('partnerSheet').addEventListener('change', () => preview({ sheet: $('partnerSheet').value }));
    $('partnerHeaderRow').addEventListener('input', () => { state.confirmed = false; state.preview = null; controls(); });
    $('partnerHeaderRow').addEventListener('change', () => preview({ sheet: $('partnerSheet').value, header: $('partnerHeaderRow').value }));
    $('partnerPreviewRetry').addEventListener('click', () => preview($('partnerSheetControls').hidden ? {} : { sheet: $('partnerSheet').value, header: $('partnerHeaderRow').value }));
    $('partnerMappingConfirm').addEventListener('click', () => { if (!state.loading && !state.importing && valid()) { state.confirmed = true; controls(); } });
    $('partnerImportForm').addEventListener('submit', async event => {
        event.preventDefault();
        if (state.loading || state.importing || !state.confirmed || !valid() || file.files[0] !== state.file) return;
        const sequence = state.sequence, data = new FormData();
        data.append('partners', state.file); data.append('data_year', $('partnerDataYear').value);
        data.append('sheet', state.preview.sheet); data.append('header_row', state.preview.header_row); data.append('column_mapping', JSON.stringify(mapping()));
        state.importing = true; state.request = new AbortController(); controls(); message('partnerImportErrors', '');
        try {
            const result = await request(root.dataset.uploadUrl, data, state.request.signal);
            if (!current(sequence)) return;
            message('partnerImportSummary', result.message + (result.warnings?.length ? '\n' + result.warnings.join('\n') : ''));
            if (result.history_html) $('partnerImportHistory').innerHTML = result.history_html;
            file.value = ''; state.file = null; state.preview = null; state.confirmed = false;
            $('partnerMappingPanel').hidden = true; $('partnerSheetControls').hidden = true; $('partnerPreviewStatus').textContent = '';
        } catch (error) {
            if (current(sequence) && error.name !== 'AbortError') message('partnerImportErrors', errorText(error));
        } finally { if (current(sequence)) { state.importing = false; state.request = null; controls(); } }
    });
    controls();
})();
</script>

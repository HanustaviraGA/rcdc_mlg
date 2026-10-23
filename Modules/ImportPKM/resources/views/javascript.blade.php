<script>
(function () {
    const root = document.getElementById('pkmImport');
    if (!root) return;
    window.pkmImportState?.request?.abort();
    const state = { file: null, preview: null, request: null, sequence: 0, loading: false, importing: false, confirmed: false };
    window.pkmImportState = state;
    const $ = id => root.querySelector('#' + id);
    const fileInput = $('pkmWorkbook');
    const sheetKeys = ['rasio', 'keterlibatan'];
    const isCurrent = sequence => root.isConnected && window.pkmImportState === state && sequence === state.sequence;
    const errorText = error => error.errors ? Object.values(error.errors).flat().join('\n') : (error.message || 'File gagal diproses. Silakan coba kembali.');
    const setMessage = (id, message) => { $(id).textContent = message; $(id).hidden = !message; };
    function mappings() {
        return Object.fromEntries(sheetKeys.map(key => [key, Object.fromEntries([...root.querySelectorAll('#pkm-' + key + '-mapping select')].map(select => [select.dataset.field, select.value === '' ? null : Number(select.value)]))]));
    }
    function validMapping() {
        if (!state.preview) return false;
        const mapping = mappings();
        return sheetKeys.every(key => {
            const required = Object.keys(state.preview.sheets[key].mapping_config.required);
            const indexes = Object.values(mapping[key]).filter(index => index !== null);
            return required.every(field => mapping[key][field] !== undefined && mapping[key][field] !== null) && new Set(indexes).size === indexes.length;
        });
    }
    function updateControls() {
        const valid = validMapping();
        const busy = state.loading || state.importing;
        $('pkmImportSubmit').disabled = busy || !valid || !state.confirmed;
        $('pkmImportSubmit').textContent = state.importing ? 'Mengimpor…' : 'Import data';
        $('pkmMappingConfirm').disabled = busy || !valid || state.confirmed;
        $('pkmMappingStatus').textContent = !state.preview ? '' : !valid ? 'Petakan seluruh kolom wajib. Satu kolom Excel hanya boleh digunakan sekali pada masing-masing sheet.' : state.confirmed ? 'Pemetaan dikonfirmasi. Data siap diimpor.' : 'Pemetaan lengkap. Konfirmasikan sebelum mengimpor.';
        root.querySelectorAll('select, input').forEach(input => { input.disabled = state.importing; });
        $('pkmPreviewRetry').disabled = busy;
    }
    function renderPreview(preview) {
        sheetKeys.forEach(key => {
            const sheet = preview.sheets[key];
            $('pkm-' + key + '-info').textContent = 'Sheet ' + sheet.sheet + ' · Baris header ' + sheet.header_row;
            const body = $('pkm-' + key + '-mapping');
            body.replaceChildren();
            Object.entries({ ...sheet.mapping_config.required, ...sheet.mapping_config.optional }).forEach(([field, label]) => {
                const row = document.createElement('tr');
                const title = document.createElement('td');
                title.textContent = label + (Object.hasOwn(sheet.mapping_config.required, field) ? ' (wajib)' : ' (opsional)');
                const cell = document.createElement('td');
                const select = document.createElement('select');
                select.className = 'form-select form-select-sm';
                select.style.minWidth = '190px';
                select.dataset.field = field;
                select.setAttribute('aria-label', sheet.sheet + ': ' + label);
                select.append(new Option('— Tidak dipetakan —', ''));
                sheet.headers.forEach((header, index) => select.append(new Option((index + 1) + '. ' + (header || 'Kolom tanpa nama'), String(index))));
                const suggested = sheet.suggested_mapping[field];
                if (Number.isInteger(suggested) && suggested >= 0 && suggested < sheet.headers.length) select.value = String(suggested);
                const example = document.createElement('td');
                example.className = 'text-muted text-break';
                const updateExample = () => { example.textContent = select.value === '' ? '—' : String(sheet.preview_data.map(sample => sample[Number(select.value)]).find(value => value !== '' && value !== null && value !== undefined) ?? '—'); };
                select.addEventListener('change', () => { state.confirmed = false; updateExample(); updateControls(); });
                updateExample();
                cell.append(select); row.append(title, cell, example); body.append(row);
            });
            const headers = document.createElement('tr');
            sheet.headers.forEach((header, index) => { const cell = document.createElement('th'); cell.className = 'text-nowrap'; cell.textContent = header || 'Kolom ' + (index + 1); headers.append(cell); });
            $('pkm-' + key + '-headers').replaceChildren(headers);
            $('pkm-' + key + '-preview').replaceChildren();
            sheet.preview_data.forEach(sample => { const row = document.createElement('tr'); sheet.headers.forEach((_, index) => { const cell = document.createElement('td'); cell.className = 'text-nowrap'; cell.textContent = sample[index] ?? ''; row.append(cell); }); $('pkm-' + key + '-preview').append(row); });
        });
        $('pkmMappingPanel').hidden = false;
    }
    async function request(url, data, signal) {
        const response = await fetch(url, { method: 'POST', body: data, signal, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': root.dataset.csrf } });
        const payload = await response.json().catch(() => { throw { message: 'Respons server tidak dapat dibaca. Silakan coba kembali.' }; });
        if (!response.ok || payload.success !== true) throw payload;
        return payload;
    }
    async function previewFile() {
        if (state.importing) return;
        const sequence = ++state.sequence;
        state.request?.abort();
        state.file = fileInput.files[0] || null;
        state.preview = null; state.confirmed = false; state.loading = false;
        $('pkmMappingPanel').hidden = true; $('pkmPreviewRetry').hidden = true;
        setMessage('pkmImportErrors', ''); setMessage('pkmImportSummary', '');
        $('pkmPreviewStatus').textContent = '';
        updateControls();
        if (!state.file) return;
        if (!/\.xlsx$/i.test(state.file.name)) { setMessage('pkmImportErrors', 'Pilih file Excel berformat .xlsx.'); return; }
        state.loading = true; state.request = new AbortController();
        $('pkmPreviewStatus').textContent = 'Membaca sheet Rasio dan Keterlibatan…';
        updateControls();
        const data = new FormData(); data.append('pkm', state.file);
        try {
            const result = await request(root.dataset.previewUrl, data, state.request.signal);
            if (!isCurrent(sequence) || fileInput.files[0] !== state.file) return;
            state.preview = result; renderPreview(result);
            $('pkmPreviewStatus').textContent = 'Pratinjau kedua sheet tersedia. Periksa kolom sebelum mengimpor.';
        } catch (error) {
            if (!isCurrent(sequence) || error.name === 'AbortError') return;
            setMessage('pkmImportErrors', errorText(error));
            $('pkmPreviewStatus').textContent = ''; $('pkmPreviewRetry').hidden = false;
        } finally { if (isCurrent(sequence)) { state.loading = false; state.request = null; updateControls(); } }
    }
    fileInput.addEventListener('change', previewFile);
    $('pkmPreviewRetry').addEventListener('click', previewFile);
    $('pkmMappingConfirm').addEventListener('click', () => { if (!state.loading && !state.importing && validMapping() && fileInput.files[0] === state.file) { state.confirmed = true; updateControls(); } });
    function periodLabel() { $('pkmPeriodLabel').textContent = $('pkmAcademicYear').value + ' ' + $('pkmSemester').value; }
    $('pkmAcademicYear').addEventListener('change', periodLabel); $('pkmSemester').addEventListener('change', periodLabel); periodLabel();
    $('pkmImportForm').addEventListener('submit', async event => {
        event.preventDefault();
        if (state.loading || state.importing || !state.confirmed || !validMapping() || fileInput.files[0] !== state.file) return;
        const sequence = state.sequence;
        const data = new FormData();
        data.append('pkm', state.file); data.append('academic_year', $('pkmAcademicYear').value); data.append('period', $('pkmSemester').value);
        data.append('column_mapping', JSON.stringify(mappings()));
        data.append('header_rows', JSON.stringify(Object.fromEntries(sheetKeys.map(key => [key, state.preview.sheets[key].header_row]))));
        state.importing = true; state.request = new AbortController(); updateControls();
        setMessage('pkmImportErrors', ''); setMessage('pkmImportSummary', '');
        try {
            const response = await request(root.dataset.uploadUrl, data, state.request.signal);
            if (!isCurrent(sequence)) return;
            let message = response.message + '\nData pada periode ini telah diganti sesuai file dan pemetaan terbaru.';
            if (response.summary.force_import) message += '\nForce import aktif. Baris dilewati: ' + response.summary.skipped_rows + '.';
            if (response.warnings?.length) message += '\n' + response.warnings.join('\n');
            setMessage('pkmImportSummary', message);
            if (response.history_html) $('pkmImportHistory').innerHTML = response.history_html;
            fileInput.value = ''; state.file = null; state.preview = null; state.confirmed = false;
            $('pkmMappingPanel').hidden = true; $('pkmPreviewStatus').textContent = '';
        } catch (error) {
            if (isCurrent(sequence) && error.name !== 'AbortError') setMessage('pkmImportErrors', errorText(error));
        } finally { if (isCurrent(sequence)) { state.importing = false; state.request = null; updateControls(); } }
    });
    updateControls();
})();
</script>

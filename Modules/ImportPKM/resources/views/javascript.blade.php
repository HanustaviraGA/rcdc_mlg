<script>
(function () {
    const root = document.getElementById('pkmImport');
    if (!root) return;
    window.pkmImportState?.request?.abort();
    const state = { file: null, preview: null, request: null, sequence: 0, sheets: null, loading: false, importing: false, deleting: false, confirmed: false };
    window.pkmImportState = state;
    const $ = id => root.querySelector('#' + id);
    const fileInput = $('pkmWorkbook');
    const importTypeSelect = $('pkmImportType');
    const getSheetKeys = () => importTypeSelect.value === 'periodic' ? ['keterlibatan'] : ['rasio', 'keterlibatan'];

    const isCurrent = sequence => root.isConnected && window.pkmImportState === state && sequence === state.sequence;
    const errorText = error => error.errors ? Object.values(error.errors).flat().join('\n') : (error.message || 'File gagal diproses. Silakan coba kembali.');
    const setMessage = (id, message) => { $(id).textContent = message; $(id).hidden = !message; };
    function updateHistory(response) {
        if (typeof response.history_html === 'string') $('pkmImportHistory').innerHTML = response.history_html;
        if (typeof response.periodic_history_html === 'string') $('periodicImportHistory').innerHTML = response.periodic_history_html;
    }
    function mappings() {
        const keys = getSheetKeys();
        return Object.fromEntries(keys.map(key => [key, Object.fromEntries([...root.querySelectorAll('#pkm-' + key + '-mapping select')].map(select => [select.dataset.field, select.value === '' ? null : Number(select.value)]))]));
    }
    function validMapping() {
        if (!state.preview) return false;
        const mapping = mappings();
        const keys = getSheetKeys();
        return keys.every(key => {
            const required = Object.keys(state.preview.sheets[key]?.mapping_config?.required || {});
            return required.every(field => mapping[key] && mapping[key][field] !== undefined && mapping[key][field] !== null);
        });
    }
    function updateControls() {
        const valid = validMapping();
        const busy = state.loading || state.importing || state.deleting;
        $('pkmImportSubmit').disabled = busy || !valid || !state.confirmed;
        $('pkmImportSubmit').textContent = state.importing ? 'Mengimpor…' : 'Import data';
        $('pkmMappingConfirm').disabled = busy || !valid || state.confirmed;
        const keys = getSheetKeys();
        const empty = state.preview ? keys.map(key => state.preview.sheets[key]).filter(sheet => !sheet || !sheet.headers.length) : [];
        $('pkmMappingStatus').textContent = !state.preview ? '' : empty.length ? 'Sheet data tidak berisi data. Pilih sheet lain.' : !valid ? 'Petakan seluruh kolom wajib pada sheet.' : state.confirmed ? 'Pemetaan dikonfirmasi. Data siap diimpor.' : 'Pemetaan lengkap. Konfirmasikan sebelum mengimpor.';
        root.querySelectorAll('select, input').forEach(input => { input.disabled = state.importing || state.deleting || (state.loading && input.dataset.sheetFor !== undefined); });
        root.querySelectorAll('[data-delete-import]').forEach(button => { button.disabled = busy; });
        $('pkmPreviewRetry').disabled = busy;
    }
    function renderPreview(preview) {
        const keys = getSheetKeys();
        keys.forEach(key => {
            const sheet = preview.sheets[key];
            if (!sheet) return;
            $('pkm-' + key + '-sheet').replaceChildren(...preview.available_sheets.map(name => new Option(name, name, false, name === sheet.sheet)));
            $('pkm-' + key + '-sheet').value = sheet.sheet;
            $('pkm-' + key + '-info').textContent = sheet.headers.length ? 'Sheet ' + sheet.sheet + ' · Baris header ' + sheet.header_row : 'Sheet ' + sheet.sheet + ' tidak berisi data.';
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
    async function request(url, data, signal, method = 'POST') {
        const response = await fetch(url, { method, body: data, signal, headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': root.dataset.csrf } });
        const payload = await response.json().catch(() => { throw { message: 'Respons server tidak dapat dibaca. Silakan coba kembali.' }; });
        if (!response.ok || payload.success !== true) throw payload;
        return payload;
    }
    async function previewFile(sheets = null) {
        if (state.importing || state.deleting) return;
        const sequence = ++state.sequence;
        state.request?.abort();
        state.file = fileInput.files[0] || null;
        state.sheets = sheets;
        state.preview = null; state.confirmed = false; state.loading = false;
        if (!sheets) $('pkmMappingPanel').hidden = true;
        $('pkmPreviewRetry').hidden = true;
        setMessage('pkmImportErrors', ''); setMessage('pkmImportSummary', '');
        $('pkmPreviewStatus').textContent = '';
        updateControls();
        if (!state.file) return;
        if (!/\.xlsx$/i.test(state.file.name)) { setMessage('pkmImportErrors', 'Pilih file Excel berformat .xlsx.'); return; }
        state.loading = true; state.request = new AbortController();
        $('pkmPreviewStatus').textContent = 'Membaca sheet data…';
        updateControls();
        const data = new FormData(); data.append('pkm', state.file);
        data.append('type', importTypeSelect.value);
        if (sheets) data.append('sheets', JSON.stringify(sheets));
        try {
            const result = await request(root.dataset.previewUrl, data, state.request.signal);
            if (!isCurrent(sequence) || fileInput.files[0] !== state.file) return;
            state.preview = result; renderPreview(result);
            $('pkmPreviewStatus').textContent = 'Pratinjau sheet tersedia. Periksa kolom sebelum mengimpor.';
        } catch (error) {
            if (!isCurrent(sequence) || error.name === 'AbortError') return;
            setMessage('pkmImportErrors', errorText(error));
            $('pkmPreviewStatus').textContent = ''; $('pkmPreviewRetry').hidden = false;
        } finally { if (isCurrent(sequence)) { state.loading = false; state.request = null; updateControls(); } }
    }
    const chosenSheets = () => {
        const keys = getSheetKeys();
        return Object.fromEntries(keys.map(key => [key, $('pkm-' + key + '-sheet').value]));
    };
    fileInput.addEventListener('change', () => previewFile());
    ['rasio', 'keterlibatan'].forEach(key => {
        const el = $('pkm-' + key + '-sheet');
        if (el) el.addEventListener('change', () => previewFile(chosenSheets()));
    });
    $('pkmPreviewRetry').addEventListener('click', () => previewFile(state.sheets));
    $('pkmMappingConfirm').addEventListener('click', () => { if (!state.loading && !state.importing && !state.deleting && validMapping() && fileInput.files[0] === state.file) { state.confirmed = true; updateControls(); } });

    const typeInput = $('pkmTypeInput');
    const formTitle = $('pkmFormTitle');
    const semesterFields = $('semesterFields');
    const periodicFields = $('periodicFields');

    function updateImportType() {
        const type = importTypeSelect.value;
        typeInput.value = type;
        const pkmForm = $('pkmImportForm');
        const partnerContainer = document.getElementById('partnerImportContainer');

        pkmForm.style.display = type === 'partners' ? 'none' : 'block';
        $('pkmHistoryCard').style.display = type === 'semester' ? 'block' : 'none';
        $('periodicHistoryCard').style.display = type === 'periodic' ? 'block' : 'none';
        if (partnerContainer) partnerContainer.style.display = type === 'partners' ? 'block' : 'none';
        if (type === 'partners') return;

        if (type === 'periodic') {
            formTitle.textContent = 'Import Community Development FM Periodik';
            semesterFields.style.display = 'none';
            periodicFields.style.display = 'flex';
            $('mappingSectionRasio').style.display = 'none';
            $('pkmInstructions').innerHTML = 'Unggah workbook berisi data <strong>Keterlibatan</strong>. Sheet Keterlibatan dipilih otomatis; sheet lain dapat dipilih pada pratinjau. Petakan kolom sebelum mengimpor.';
        } else {
            formTitle.textContent = 'Import Community Development FM';
            semesterFields.style.display = 'flex';
            periodicFields.style.display = 'none';
            $('mappingSectionRasio').style.display = 'block';
            $('pkmInstructions').innerHTML = 'Unggah workbook berisi data <strong>Rasio</strong> dan <strong>Keterlibatan</strong>. Sheet bernama Rasio dan Keterlibatan dipilih otomatis; sheet lain dapat dipilih pada pratinjau. Petakan kolom pada kedua sheet sebelum mengimpor.';
        }
        periodLabel();
        if (fileInput.files[0]) previewFile();
    }
    importTypeSelect.addEventListener('change', updateImportType);
    updateImportType();

    function periodLabel() {
        if (importTypeSelect.value === 'periodic') {
            $('pkmPeriodLabel').textContent = 'Rentang ' + ($('pkmStartDate').value || '—') + ' s.d. ' + ($('pkmEndDate').value || '—');
        } else {
            $('pkmPeriodLabel').textContent = $('pkmAcademicYear').value + ' ' + $('pkmSemester').value;
        }
    }
    $('pkmAcademicYear').addEventListener('change', periodLabel);
    $('pkmSemester').addEventListener('change', periodLabel);
    $('pkmStartDate').addEventListener('change', periodLabel);
    $('pkmEndDate').addEventListener('change', periodLabel);
    periodLabel();

    $('pkmImportForm').addEventListener('submit', async event => {
        event.preventDefault();
        if (state.loading || state.importing || state.deleting || !state.confirmed || !validMapping() || fileInput.files[0] !== state.file) return;
        const sequence = state.sequence;
        const data = new FormData();
        data.append('pkm', state.file);
        data.append('type', importTypeSelect.value);
        if (importTypeSelect.value === 'periodic') {
            data.append('start_date', $('pkmStartDate').value);
            data.append('end_date', $('pkmEndDate').value);
        } else {
            data.append('academic_year', $('pkmAcademicYear').value);
            data.append('period', $('pkmSemester').value);
        }
        data.append('column_mapping', JSON.stringify(mappings()));
        const keys = getSheetKeys();
        data.append('header_rows', JSON.stringify(Object.fromEntries(keys.map(key => [key, state.preview.sheets[key].header_row]))));
        data.append('sheets', JSON.stringify(Object.fromEntries(keys.map(key => [key, state.preview.sheets[key].sheet]))));
        state.importing = true; state.request = new AbortController(); updateControls();
        setMessage('pkmImportErrors', ''); setMessage('pkmImportSummary', '');
        try {
            const response = await request(root.dataset.uploadUrl, data, state.request.signal);
            if (!isCurrent(sequence)) return;
            let message = response.message + '\nData pada periode ini telah diganti sesuai file dan pemetaan terbaru.';
            if (response.summary.force_import) message += '\nForce import aktif. Baris dilewati: ' + response.summary.skipped_rows + '.';
            if (response.warnings?.length) message += '\n' + response.warnings.join('\n');
            setMessage('pkmImportSummary', message);
            updateHistory(response);
            fileInput.value = ''; state.file = null; state.preview = null; state.sheets = null; state.confirmed = false;
            $('pkmMappingPanel').hidden = true; $('pkmPreviewStatus').textContent = '';
        } catch (error) {
            if (isCurrent(sequence) && error.name !== 'AbortError') setMessage('pkmImportErrors', errorText(error));
        } finally { if (isCurrent(sequence)) { state.importing = false; state.request = null; updateControls(); } }
    });
    root.addEventListener('click', async event => {
        const button = event.target.closest('[data-delete-import]');
        if (!button || state.loading || state.importing || state.deleting) return;
        if (!window.confirm(`Hapus ${button.dataset.label} periode ${button.dataset.period} beserta seluruh data hasil importnya? Data yang dihapus tidak dapat dikembalikan.`)) return;

        const sequence = state.sequence;
        const originalLabel = button.textContent;
        state.deleting = true; state.request = new AbortController(); updateControls();
        button.textContent = 'Menghapus...';
        setMessage('pkmImportErrors', ''); setMessage('pkmImportSummary', '');
        try {
            const response = await request(button.dataset.url, undefined, state.request.signal, 'DELETE');
            if (!isCurrent(sequence)) return;
            updateHistory(response);
            setMessage('pkmImportSummary', response.message);
        } catch (error) {
            if (isCurrent(sequence) && error.name !== 'AbortError') setMessage('pkmImportErrors', errorText(error));
        } finally {
            button.textContent = originalLabel;
            if (isCurrent(sequence)) { state.deleting = false; state.request = null; updateControls(); }
        }
    });
    updateControls();
})();
</script>

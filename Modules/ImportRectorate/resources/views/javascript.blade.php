<script>
(() => {
    const root = document.getElementById('monthly-import');
    if (!root) return;

    const form = root.querySelector('form');
    const uploadButtons = [...form.querySelectorAll('[data-import-file]')];
    const periodInputs = [form.querySelector('[name="year"]'), form.querySelector('[name="month"]')];
    const errors = root.querySelector('#monthly-import-errors');
    const fileInputs = [...form.querySelectorAll('input[type="file"]')];
    const modal = document.getElementById('column-mapping-modal');
    const confirmButton = document.getElementById('cm-confirm-btn');
    const csrfToken = '{{ csrf_token() }}';
    // A confirmation belongs to the actual File object, never just its name/type.
    const files = new Map();
    let active = null;
    let previewRequest = null;
    let uploading = false;

    fileInputs.forEach(input => {
        input.addEventListener('change', () => {
            files.delete(input.name);
            if (active?.input === input) closePreview();
            if (!input.files.length) {
                updateFileInputBadge(input);
                updateUploadButton();
                return;
            }
            files.set(input.name, {
                file: input.files[0], preview: null, mapping: null,
                draft: null, status: 'pending',
            });
            updateUploadButton();
            openMappingModal(input);
        });
    });

    form.querySelector('[name="year"]').addEventListener('change', () => {
        const input = fileInputs.find(input => input.name === 'fm_file');
        if (!input) return;
        if (active?.input === input) {
            closePreview();
            bootstrap.Modal.getOrCreateInstance(modal).hide();
        }
        // FM status suggestions depend on the report year, not only the File object.
        const state = selectedState(input);
        if (state) {
            state.preview = null;
            state.mapping = null;
            state.draft = null;
            state.status = 'pending';
        }
        updateFileInputBadge(input);
        updateUploadButton();
    });

    modal.addEventListener('hide.bs.modal', closePreview);

    function selectedState(input) {
        const state = files.get(input.name);
        return state && state.file === input.files[0] ? state : null;
    }

    function updateUploadButton() {
        uploadButtons.forEach(button => {
            const input = fileInputs.find(input => input.name === button.dataset.importFile);
            button.disabled = uploading || !input?.files.length || !selectedState(input)?.mapping;
        });
    }

    function closePreview() {
        previewRequest?.abort();
        previewRequest = null;
        if (active?.state.status === 'loading') {
            active.state.status = 'pending';
            updateFileInputBadge(active.input);
        }
        active = null;
        confirmButton.disabled = true;
        confirmButton.onclick = null;
        updateUploadButton();
    }

    async function openMappingModal(input) {
        if (uploading || !input.files.length) return;
        closePreview();
        let state = selectedState(input);
        if (!state) {
            state = { file: input.files[0], preview: null, mapping: null, draft: null, status: 'pending' };
            files.set(input.name, state);
        }
        const session = { input, state, config: null, headers: [], previewData: [], phase: 'loading' };
        active = session;
        setMappingModalState('loading');
        bootstrap.Modal.getOrCreateInstance(modal).show();

        if (state.preview) {
            renderMappingModal(state.preview, session);
            return;
        }

        const controller = new AbortController();
        previewRequest = controller;
        state.status = 'loading';
        updateFileInputBadge(input);
        try {
            const formData = new FormData();
            formData.append('file', state.file);
            formData.append('type', input.name);
            formData.append('year', form.querySelector('[name="year"]').value);
            formData.append('_token', csrfToken);
            const response = await fetch('{{ route("importrectorate.preview-columns") }}', {
                method: 'POST', headers: { 'Accept': 'application/json' },
                body: formData, signal: controller.signal,
            });
            const data = await response.json();
            // Abort is best-effort; identity guards also reject already-resolved responses.
            if (active !== session || selectedState(input) !== state) return;
            if (!response.ok || !data.success) {
                throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Gagal membaca file.');
            }
            renderMappingModal(data, session);
            state.preview = data;
            state.status = 'pending';
            updateFileInputBadge(input);
        } catch (error) {
            if (active !== session || selectedState(input) !== state) return;
            state.status = 'error';
            state.mapping = null;
            setMappingModalState('error', error.message || 'Gagal membaca file. Silakan coba lagi.');
            updateFileInputBadge(input);
        } finally {
            if (previewRequest === controller) previewRequest = null;
            updateUploadButton();
        }
    }

    function renderMappingModal(data, session) {
        const config = data.mapping_config;
        if (!config || !config.required || !config.optional || !Array.isArray(data.headers) || !data.headers.length) {
            throw new Error('Konfigurasi pemetaan atau header file tidak tersedia. Silakan coba lagi.');
        }
        // Set config BEFORE rendering controls and running the initial validation.
        session.config = config;
        session.headers = data.headers;
        session.previewData = data.preview_data || [];
        document.getElementById('cm-file-name').textContent = data.file_info?.name || session.state.file.name;
        document.getElementById('cm-sheet-name').textContent = data.file_info?.sheet || data.sheet || '-';
        document.getElementById('cm-col-count').textContent = session.headers.length;
        renderMappingRows(session, session.state.draft ?? session.state.mapping ?? data.suggested_mapping ?? {});
        renderPreviewTable(session.headers, session.previewData);
        confirmButton.onclick = () => confirmMapping(session);
        setMappingModalState('content');
        revalidateMapping();
        updatePreviewHighlight();
    }

    function renderMappingRows(session, mapping) {
        const tbody = document.getElementById('cm-mapping-rows');
        tbody.replaceChildren();
        const fields = { ...session.config.required, ...session.config.optional };
        Object.entries(fields).forEach(([field, label]) => {
            const required = Object.hasOwn(session.config.required, field);
            const tr = document.createElement('tr');
            tr.dataset.field = field;
            const tdLabel = document.createElement('td');
            tdLabel.innerHTML = `<strong>${escHtml(label)}</strong><br><code class="text-muted small">${escHtml(field)}</code>`;
            const tdType = document.createElement('td');
            tdType.className = 'text-center';
            tdType.innerHTML = required
                ? '<span class="badge bg-danger-subtle text-danger">Wajib</span>'
                : '<span class="badge bg-secondary-subtle text-secondary">Opsional</span>';
            const tdSelect = document.createElement('td');
            const select = document.createElement('select');
            select.className = 'form-select form-select-sm';
            select.dataset.field = field;
            select.setAttribute('aria-label', label);
            const empty = document.createElement('option');
            empty.value = '';
            empty.textContent = '— Tidak dipetakan —';
            select.appendChild(empty);
            session.headers.forEach((header, index) => {
                const option = document.createElement('option');
                option.value = String(index);
                option.textContent = header || `(Kolom ${index + 1})`;
                select.appendChild(option);
            });
            // Do not coerce null/empty to zero, or drop a genuine index zero.
            const value = mapping[field];
            select.value = value !== null && value !== undefined && value !== '' ? String(value) : '';
            const tdExample = document.createElement('td');
            tdExample.className = 'text-muted small';
            tdExample.dataset.exampleFor = field;
            const tdStatus = document.createElement('td');
            tdStatus.className = 'text-center';
            tdStatus.dataset.statusFor = field;
            select.addEventListener('change', () => {
                if (active !== session) return;
                session.state.mapping = null;
                session.state.draft = getCurrentMappingFromSelects();
                session.state.status = 'pending';
                updateExampleValue(tr, select.value);
                updatePreviewHighlight();
                revalidateMapping();
                updateFileInputBadge(session.input);
                updateUploadButton();
            });
            tdSelect.appendChild(select);
            tr.append(tdLabel, tdType, tdSelect, tdExample, tdStatus);
            tbody.appendChild(tr);
            updateExampleValue(tr, select.value);
        });
    }

    function renderPreviewTable(headers, previewData) {
        const thead = document.getElementById('cm-preview-headers');
        const tbody = document.getElementById('cm-preview-body');
        thead.replaceChildren();
        tbody.replaceChildren();
        headers.forEach((header, index) => {
            const th = document.createElement('th');
            th.textContent = header || `Kolom ${index + 1}`;
            th.dataset.colIndex = index;
            th.className = 'text-nowrap';
            thead.appendChild(th);
        });
        previewData.forEach(row => {
            const tr = document.createElement('tr');
            headers.forEach((_, index) => {
                const td = document.createElement('td');
                td.textContent = row[index] ?? '';
                td.dataset.colIndex = index;
                td.className = 'text-nowrap';
                tr.appendChild(td);
            });
            tbody.appendChild(tr);
        });
    }

    function updatePreviewHighlight() {
        document.querySelectorAll('#cm-preview-table [data-col-index]').forEach(cell => {
            cell.classList.remove('table-primary');
        });
        document.querySelectorAll('#cm-mapping-rows select').forEach(select => {
            if (select.value === '') return;
            document.querySelectorAll(`#cm-preview-table [data-col-index="${Number(select.value)}"]`).forEach(cell => {
                cell.classList.add('table-primary');
            });
        });
    }

    function updateExampleValue(tr, value) {
        const example = tr.querySelector('[data-example-for]');
        example.textContent = value === '' ? '—' : (active.previewData[0]?.[Number(value)] ?? active.headers[Number(value)] ?? '—');
    }

    function getCurrentMappingFromSelects() {
        const mapping = {};
        document.querySelectorAll('#cm-mapping-rows select').forEach(select => {
            if (select.value !== '') mapping[select.dataset.field] = Number(select.value);
        });
        return mapping;
    }

    function revalidateMapping() {
        if (!active?.config || active.phase !== 'content') {
            confirmButton.disabled = true;
            return false;
        }
        const mapping = getCurrentMappingFromSelects();
        const problems = [];
        const optional = [];
        const used = new Map();
        const invalid = new Set();
        const fields = { ...active.config.required, ...active.config.optional };
        Object.entries(fields).forEach(([field, label]) => {
            const index = mapping[field];
            if (index === undefined) {
                if (Object.hasOwn(active.config.required, field)) {
                    problems.push(`${label} wajib dipetakan.`);
                    invalid.add(field);
                } else {
                    optional.push(label);
                }
            } else if (!Number.isInteger(index) || index < 0 || index >= active.headers.length) {
                problems.push(`Kolom sumber untuk ${label} tidak valid.`);
                invalid.add(field);
            } else if (used.has(index)) {
                const other = used.get(index);
                problems.push(`${label} dan ${fields[other]} menggunakan kolom sumber yang sama. Pilih kolom yang berbeda.`);
                invalid.add(field);
                invalid.add(other);
            } else {
                used.set(index, field);
            }
        });
        document.querySelectorAll('#cm-mapping-rows select').forEach(select => {
            const field = select.dataset.field;
            const bad = invalid.has(field);
            select.classList.toggle('is-invalid', bad);
            select.setAttribute('aria-invalid', String(bad));
            const status = select.closest('tr').querySelector('[data-status-for]');
            status.innerHTML = bad
                ? '<i class="bi bi-x-circle-fill text-danger" title="Periksa pemetaan"></i>'
                : mapping[field] === undefined
                    ? '<i class="bi bi-info-circle text-info" title="Opsional, boleh kosong"></i>'
                    : '<i class="bi bi-check-circle-fill text-success" title="Dipetakan"></i>';
        });
        document.getElementById('cm-validation-banner').hidden = false;
        document.getElementById('cm-validation-errors').hidden = !problems.length;
        document.getElementById('cm-validation-error-list').innerHTML = problems.map(message => `<li>${escHtml(message)}</li>`).join('');
        document.getElementById('cm-validation-warnings').hidden = !optional.length;
        document.getElementById('cm-validation-warning-list').innerHTML = optional.map(label => `<li>${escHtml(label)}</li>`).join('');
        document.getElementById('cm-validation-ok').hidden = !!problems.length;
        document.getElementById('cm-ready-badge').hidden = !problems.length;
        confirmButton.disabled = uploading || !!problems.length;
        return !problems.length;
    }

    function confirmMapping(session) {
        if (uploading || active !== session || selectedState(session.input) !== session.state || !revalidateMapping()) return;
        session.state.mapping = getCurrentMappingFromSelects();
        session.state.draft = { ...session.state.mapping };
        session.state.status = 'confirmed';
        updateFileInputBadge(session.input);
        updateUploadButton();
        bootstrap.Modal.getOrCreateInstance(modal).hide();
    }

    function setMappingModalState(phase, message = '') {
        if (active) active.phase = phase;
        document.getElementById('cm-loading').hidden = phase !== 'loading';
        document.getElementById('cm-error').hidden = phase !== 'error';
        document.getElementById('cm-content').hidden = phase !== 'content';
        document.getElementById('cm-error-message').textContent = message;
        document.getElementById('cm-validation-banner').hidden = true;
        document.getElementById('cm-ready-badge').hidden = true;
        // Only validation may enable confirmation, not a visibility transition.
        confirmButton.disabled = true;
        if (phase !== 'content') confirmButton.onclick = null;
    }

    function updateFileInputBadge(input) {
        const container = input.closest('.mb-6');
        let badge = container.querySelector('.cm-status-badge');
        if (!badge) {
            badge = document.createElement('div');
            badge.className = 'cm-status-badge mt-2';
            badge.setAttribute('aria-live', 'polite');
            input.after(badge);
        }
        badge.replaceChildren();
        if (!input.files.length) return;
        const status = selectedState(input)?.status || 'pending';
        const label = document.createElement('span');
        const labels = {
            pending: ['secondary', 'Pemetaan belum dikonfirmasi.'],
            loading: ['secondary', 'Memuat preview kolom...'],
            confirmed: ['success', 'Pemetaan kolom dikonfirmasi.'],
            error: ['danger', 'Gagal membaca file. Coba preview lagi atau pilih file lain.'],
        };
        const [color, text] = labels[status];
        label.className = `badge bg-${color}-subtle text-${color} px-3 py-2 me-2`;
        label.textContent = text;
        const edit = document.createElement('button');
        edit.type = 'button';
        edit.className = 'btn btn-sm btn-light-primary cm-reopen-link';
        edit.textContent = status === 'confirmed' ? 'Lihat / ubah pemetaan' : 'Buka preview / pemetaan';
        edit.disabled = uploading;
        edit.addEventListener('click', () => openMappingModal(input));
        badge.append(label, edit);
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (uploading) return;
        errors.hidden = true;
        const button = event.submitter;
        const input = fileInputs.find(input => input.name === button?.dataset.importFile);
        if (!input?.files.length) {
            showError('Pilih file dan gunakan tombol Upload dan import pada jenis data yang ingin diunggah.');
            return;
        }
        const state = selectedState(input);
        if (!state?.mapping) {
            showError('Konfirmasi pemetaan file ini terlebih dahulu melalui tombol Buka preview / pemetaan.');
            updateUploadButton();
            return;
        }
        const formData = new FormData();
        formData.append('_token', csrfToken);
        periodInputs.forEach(input => formData.append(input.name, input.value));
        // Each request contains only the source chosen with its own upload button.
        formData.append(input.name, state.file);
        formData.append(`column_mapping[${input.name}]`, JSON.stringify(state.mapping));
        const originalLabel = button.innerHTML;
        uploading = true;
        closePreview();
        bootstrap.Modal.getOrCreateInstance(modal).hide();
        root.querySelector('#monthly-import-summary').replaceChildren();
        button.textContent = 'Memproses import...';
        periodInputs.forEach(input => { input.disabled = true; });
        fileInputs.forEach(input => { input.disabled = true; updateFileInputBadge(input); });
        updateUploadButton();
        try {
            const response = await fetch(form.action, {
                method: 'POST', headers: { 'Accept': 'application/json' }, body: formData,
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Import gagal.');
            }
            root.querySelector('#monthly-import-summary').innerHTML = data.summary_html;
            root.querySelector('#monthly-import-history').innerHTML = data.history_html;
            input.value = '';
            files.delete(input.name);
        } catch (error) {
            showError(error.message);
        } finally {
            uploading = false;
            periodInputs.forEach(input => { input.disabled = false; });
            fileInputs.forEach(input => { input.disabled = false; updateFileInputBadge(input); });
            button.innerHTML = originalLabel;
            updateUploadButton();
        }
    });

    function showError(message) {
        errors.textContent = message;
        errors.hidden = false;
        errors.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function escHtml(value) {
        const div = document.createElement('div');
        div.textContent = String(value);
        return div.innerHTML;
    }

    updateUploadButton();
})();
</script>

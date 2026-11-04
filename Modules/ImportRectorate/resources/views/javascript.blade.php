<script>
(() => {
    const root = document.getElementById('monthly-import');
    if (!root) return;

    const form = root.querySelector('form');
    const uploadButtons = [...form.querySelectorAll('[data-import-file]')];
    const periodInputs = [form.querySelector('[name="year"]'), form.querySelector('[name="month"]')];
    const errors = root.querySelector('#monthly-import-errors');
    const history = root.querySelector('#monthly-import-history');
    const notice = root.querySelector('#monthly-import-notice');
    const fileInputs = [...form.querySelectorAll('input[type="file"]')];
    const modal = document.getElementById('column-mapping-modal');
    const confirmButton = document.getElementById('cm-confirm-btn');
    const sheetSelect = document.getElementById('cm-sheet-select');
    const csrfToken = '{{ csrf_token() }}';
    // A confirmation belongs to the actual File object, never just its name/type.
    const files = new Map();
    let active = null;
    let previewRequest = null;
    let processing = false;

    fileInputs.forEach(input => {
        input.addEventListener('change', () => {
            files.delete(input.name);
            if (active?.input === input) closePreview();
            if (!input.files.length) {
                updateFileInputBadge(input);
                updateActionButtons();
                return;
            }
            files.set(input.name, newState(input.files[0]));
            updateActionButtons();
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
        updateActionButtons();
    });

    modal.addEventListener('hide.bs.modal', closePreview);

    // The picker belongs to the part on screen. Another sheet has other columns, so its
    // preview and mapping start over; the other parts keep their sheets and mappings.
    sheetSelect.addEventListener('change', () => {
        if (!active || processing) return;
        const { input, state } = active;
        if (state.role === 'main') {
            if (!sheetSelect.value || (sheetSelect.value === state.sheet && state.preview)) return;
            state.sheet = sheetSelect.value;
            state.draft = null;
        } else {
            // An empty value: this optional sheet is not used.
            const sheet = sheetSelect.value || null;
            if (sheet === (state.extra[state.role]?.sheet ?? null) && state.preview) return;
            state.extra[state.role] = { sheet, draft: null, mapping: null };
        }
        state.preview = null;
        state.mapping = null;
        state.status = 'pending';
        updateFileInputBadge(input);
        updateActionButtons();
        openMappingModal(input);
    });

    function newState(file) {
        return { file, preview: null, mapping: null, draft: null, status: 'pending', sheet: null, sheets: [], extra: {}, role: 'main' };
    }

    function renderSheetPicker(state) {
        const role = state.role || 'main';
        const info = active?.state === state ? active.roles?.[role] : null;
        const names = state.sheets || [];
        document.getElementById('cm-sheet-picker').hidden = !names.length;
        const options = (role === 'main' ? [] : [['', '— Tidak digunakan —']]).concat(names.map(name => [name, name]));
        sheetSelect.replaceChildren(...options.map(([value, text]) => {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = text;
            return option;
        }));
        sheetSelect.value = (role === 'main' ? state.sheet : state.extra[role]?.sheet) ?? '';
        document.getElementById('cm-sheet-label').textContent = info
            ? `Sheet untuk ${info.label}${info.expected ? ` (default: ${info.expected})` : ''}`
            : 'Sheet yang digunakan';
        document.getElementById('cm-sheet-help').textContent = `${info?.description ? `${info.description} ` : ''}Sheet default dipilih otomatis. Pilih sheet lain bila data berada di sheet berbeda; preview dan pemetaan akan dimuat ulang.`;
    }

    function selectedState(input) {
        const state = files.get(input.name);
        return state && state.file === input.files[0] ? state : null;
    }

    function updateActionButtons() {
        uploadButtons.forEach(button => {
            const input = fileInputs.find(input => input.name === button.dataset.importFile);
            button.disabled = processing || !input?.files.length || !selectedState(input)?.mapping;
        });
        history.querySelectorAll('[data-delete-import]').forEach(button => { button.disabled = processing; });
    }

    function setProcessing(value) {
        processing = value;
        periodInputs.forEach(input => { input.disabled = value; });
        fileInputs.forEach(input => { input.disabled = value; updateFileInputBadge(input); });
        updateActionButtons();
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
        updateActionButtons();
    }

    async function openMappingModal(input) {
        if (processing || !input.files.length) return;
        closePreview();
        let state = selectedState(input);
        if (!state) {
            state = newState(input.files[0]);
            files.set(input.name, state);
        }
        const session = { input, state, config: null, headers: [], previewData: [], phase: 'loading' };
        active = session;
        renderSheetPicker(state);
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
            // Without a chosen sheet the server uses the importer's default sheet.
            if (state.sheet) formData.append('sheet', state.sheet);
            // Supporting sheets already shown keep their choice ('' = not used).
            const chosen = Object.entries(state.extra).filter(([, extra]) => extra.sheet !== undefined);
            if (chosen.length) formData.append('extra_sheets', JSON.stringify(Object.fromEntries(chosen.map(([role, extra]) => [role, extra.sheet ?? '']))));
            formData.append('_token', csrfToken);
            const response = await fetch('{{ route("importrectorate.preview-columns") }}', {
                method: 'POST', headers: { 'Accept': 'application/json' },
                body: formData, signal: controller.signal,
            });
            const data = await response.json();
            // Abort is best-effort; identity guards also reject already-resolved responses.
            if (active !== session || selectedState(input) !== state) return;
            // The upload always reads the sheet shown in this preview.
            if (Array.isArray(data.sheets)) state.sheets = data.sheets;
            if (response.ok && data.success && data.file_info?.sheet) state.sheet = data.file_info.sheet;
            if (response.ok && data.success) {
                Object.entries(data.extra_roles || {}).forEach(([role, entry]) => {
                    const extra = state.extra[role] ??= { draft: null, mapping: null };
                    if (extra.sheet !== undefined && extra.sheet !== (entry.sheet ?? null)) extra.draft = null;
                    extra.sheet = entry.sheet ?? null;
                });
            }
            renderSheetPicker(state);
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
            updateActionButtons();
        }
    }

    function renderMappingModal(data, session) {
        const config = data.mapping_config;
        if (Array.isArray(data.headers) && !data.headers.length && data.file_info?.sheet) {
            throw new Error(`Sheet ${data.file_info.sheet} tidak berisi data. Pilih sheet lain.`);
        }
        if (!config || !config.required || !config.optional || !Array.isArray(data.headers) || !data.headers.length) {
            throw new Error('Konfigurasi pemetaan atau header file tidak tersedia. Silakan coba lagi.');
        }
        // The main sheet, then each supporting sheet (KPI, LIST, ...) with its own columns.
        const main = data.main_role || {};
        session.roles = {
            main: {
                label: main.label || 'Data utama', expected: main.expected || '', description: main.description || '',
                sheet: data.file_info?.sheet || data.sheet || '-', config, headers: data.headers,
                previewData: data.preview_data || [], suggested: data.suggested_mapping || {},
            },
        };
        Object.entries(data.extra_roles || {}).forEach(([role, entry]) => {
            session.roles[role] = {
                label: entry.label || role, expected: entry.expected || '', description: entry.description || '',
                sheet: entry.sheet ?? null, config: entry.mapping_config || { required: {}, optional: {} },
                headers: entry.headers || [], previewData: entry.preview_data || [], suggested: entry.suggested_mapping || {},
            };
        });
        document.getElementById('cm-file-name').textContent = data.file_info?.name || session.state.file.name;
        confirmButton.onclick = () => confirmMapping(session);
        setMappingModalState('content');
        showRole(session, session.roles[session.state.role] ? session.state.role : 'main');
    }

    function showRole(session, role) {
        const data = session.roles[role];
        session.role = role;
        session.state.role = role;
        // Set config BEFORE rendering controls and running the validation.
        session.config = data.config;
        session.headers = data.headers;
        session.previewData = data.previewData;
        renderSheetPicker(session.state);
        document.getElementById('cm-sheet-name').textContent = data.sheet || 'Tidak digunakan';
        document.getElementById('cm-col-count').textContent = data.headers.length;
        const mappable = role === 'main' || (!!data.sheet && data.headers.length > 0);
        document.getElementById('cm-role-body').hidden = !mappable;
        document.getElementById('cm-role-notice').hidden = mappable;
        document.getElementById('cm-role-notice-text').textContent = data.sheet
            ? `Sheet ${data.sheet} tidak berisi data. Pilih sheet lain atau Tidak digunakan.`
            : `Sheet ${data.label} tidak digunakan. ${data.description}`;
        renderMappingRows(session, mappable ? storedMapping(session, role) : {});
        renderPreviewTable(mappable ? session.headers : [], mappable ? session.previewData : []);
        revalidateMapping();
        updatePreviewHighlight();
    }

    function renderRoleTabs(session, issues) {
        const roles = Object.entries(session.roles);
        document.getElementById('cm-role-tabs').hidden = roles.length < 2;
        document.getElementById('cm-role-list').replaceChildren(...roles.map(([role, data]) => {
            const button = document.createElement('button');
            const current = role === session.role;
            button.type = 'button';
            button.dataset.role = role;
            button.className = `btn btn-sm ${current ? 'btn-primary' : 'btn-light'}`;
            button.setAttribute('aria-pressed', String(current));
            button.innerHTML = `${escHtml(data.label)} <span class="badge ${current ? 'bg-white text-primary' : 'bg-secondary-subtle text-secondary'} ms-1">${escHtml(data.sheet || 'Tidak digunakan')}</span>`
                + (issues[role]?.length ? ' <i class="bi bi-exclamation-circle-fill text-danger" title="Perlu diperbaiki"></i>' : '');
            button.addEventListener('click', () => {
                if (active === session && !processing && session.role !== role) showRole(session, role);
            });
            return button;
        }));
    }

    // The mapping a part shows: its own changes, its confirmation, or the server suggestion.
    function storedMapping(session, role) {
        const state = session.state;
        const own = role === 'main' ? state : state.extra[role];
        return own?.draft ?? own?.mapping ?? session.roles[role].suggested ?? {};
    }

    // What a select would keep of a stored mapping: whole column indexes of this sheet.
    function usableMapping(mapping, headers) {
        return Object.fromEntries(Object.entries(mapping)
            .filter(([, index]) => index !== null && index !== undefined && /^\d+$/.test(String(index)) && Number(index) < headers.length)
            .map(([field, index]) => [field, Number(index)]));
    }

    function roleMapping(session, role) {
        return role === session.role ? getCurrentMappingFromSelects() : usableMapping(storedMapping(session, role), session.roles[role].headers);
    }

    function roleIssues(session, role, mapping) {
        const data = session.roles[role];
        const problems = [];
        const optional = [];
        const invalid = new Set();
        // An optional supporting sheet may be left out.
        if (role !== 'main' && !data.sheet) return { problems, optional, invalid };
        if (role !== 'main' && !data.headers.length) {
            problems.push(`Sheet ${data.sheet} untuk ${data.label} tidak berisi data. Pilih sheet lain atau Tidak digunakan.`);
            return { problems, optional, invalid };
        }
        const fields = { ...data.config.required, ...data.config.optional };
        Object.entries(fields).forEach(([field, label]) => {
            const index = mapping[field];
            if (index === undefined) {
                if (Object.hasOwn(data.config.required, field)) {
                    problems.push(`${label} wajib dipetakan.`);
                    invalid.add(field);
                } else {
                    optional.push(label);
                }
            } else if (!Number.isInteger(index) || index < 0 || index >= data.headers.length) {
                // One source column may feed several target fields.
                problems.push(`Kolom sumber untuk ${label} tidak valid.`);
                invalid.add(field);
            }
        });
        return { problems, optional, invalid };
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
                if (session.role === 'main') session.state.draft = getCurrentMappingFromSelects();
                else (session.state.extra[session.role] ??= {}).draft = getCurrentMappingFromSelects();
                session.state.status = 'pending';
                updateExampleValue(tr, select.value);
                updatePreviewHighlight();
                revalidateMapping();
                updateFileInputBadge(session.input);
                updateActionButtons();
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
        const session = active;
        const mapping = getCurrentMappingFromSelects();
        const { problems, optional, invalid } = roleIssues(session, session.role, mapping);
        // Every part is confirmed at once, so the other parts must be valid too.
        const issues = { [session.role]: [...problems] };
        Object.keys(session.roles).filter(role => role !== session.role).forEach(role => {
            issues[role] = roleIssues(session, role, roleMapping(session, role)).problems;
            if (issues[role].length) problems.push(`Sheet ${session.roles[role].label}: ${issues[role].join(' ')}`);
        });
        renderRoleTabs(session, issues);
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
        confirmButton.disabled = processing || !!problems.length;
        return !problems.length;
    }

    function confirmMapping(session) {
        if (processing || active !== session || selectedState(session.input) !== session.state || !revalidateMapping()) return;
        const state = session.state;
        state.mapping = roleMapping(session, 'main');
        state.draft = { ...state.mapping };
        Object.keys(session.roles).filter(role => role !== 'main').forEach(role => {
            const sheet = session.roles[role].sheet ?? null;
            const mapping = sheet ? roleMapping(session, role) : null;
            state.extra[role] = { sheet, mapping, draft: mapping && { ...mapping } };
        });
        state.status = 'confirmed';
        updateFileInputBadge(session.input);
        updateActionButtons();
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
        sheetSelect.disabled = phase === 'loading' || processing;
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
        edit.disabled = processing;
        edit.addEventListener('click', () => openMappingModal(input));
        badge.append(label, edit);
    }

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (processing) return;
        errors.hidden = true;
        notice.hidden = true;
        const button = event.submitter;
        const input = fileInputs.find(input => input.name === button?.dataset.importFile);
        if (!input?.files.length) {
            showError('Pilih file dan gunakan tombol Upload dan import pada jenis data yang ingin diunggah.');
            return;
        }
        const state = selectedState(input);
        if (!state?.mapping) {
            showError('Konfirmasi pemetaan file ini terlebih dahulu melalui tombol Buka preview / pemetaan.');
            updateActionButtons();
            return;
        }
        const formData = new FormData();
        formData.append('_token', csrfToken);
        periodInputs.forEach(input => formData.append(input.name, input.value));
        // Each request contains only the source chosen with its own upload button.
        formData.append(input.name, state.file);
        formData.append(`column_mapping[${input.name}]`, JSON.stringify(state.mapping));
        if (state.sheet) formData.append(`sheet[${input.name}]`, state.sheet);
        // Each supporting sheet confirmed in the preview, or null when it is not used.
        const roles = Object.keys(state.preview?.extra_roles || {});
        if (roles.length) {
            formData.append(`extra_sheets[${input.name}]`, JSON.stringify(Object.fromEntries(roles.map(role => [role, {
                sheet: state.extra[role]?.sheet ?? null, column_mapping: state.extra[role]?.mapping ?? null,
            }]))));
        }
        const originalLabel = button.innerHTML;
        setProcessing(true);
        closePreview();
        bootstrap.Modal.getOrCreateInstance(modal).hide();
        root.querySelector('#monthly-import-summary').replaceChildren();
        button.textContent = 'Memproses import...';
        try {
            const response = await fetch(form.action, {
                method: 'POST', headers: { 'Accept': 'application/json' }, body: formData,
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Import gagal.');
            }
            root.querySelector('#monthly-import-summary').innerHTML = data.summary_html;
            history.innerHTML = data.history_html;
            input.value = '';
            files.delete(input.name);
        } catch (error) {
            showError(error.message);
        } finally {
            setProcessing(false);
            button.innerHTML = originalLabel;
            updateActionButtons();
        }
    });

    history.addEventListener('click', async event => {
        const button = event.target.closest('[data-delete-import]');
        if (!button || processing) return;
        if (!window.confirm(`Hapus ${button.dataset.label} periode ${button.dataset.period} beserta seluruh data hasil importnya? Data yang dihapus tidak dapat dikembalikan.`)) return;

        errors.hidden = true;
        notice.hidden = true;
        const originalLabel = button.innerHTML;
        setProcessing(true);
        closePreview();
        bootstrap.Modal.getOrCreateInstance(modal).hide();
        button.textContent = 'Menghapus...';
        try {
            const response = await fetch(button.dataset.url, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Gagal menghapus data import.');
            }
            history.innerHTML = data.history_html;
            root.querySelector('#monthly-import-summary').replaceChildren();
            notice.textContent = data.message;
            notice.hidden = false;
        } catch (error) {
            showError(error.message);
        } finally {
            button.innerHTML = originalLabel;
            setProcessing(false);
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

    updateActionButtons();
})();
</script>

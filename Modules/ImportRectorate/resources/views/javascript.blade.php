<script>
(() => {
    const root = document.getElementById('monthly-import');
    const form = root.querySelector('form');
    const button = root.querySelector('#monthly-import-submit');
    const errors = root.querySelector('#monthly-import-errors');
    const csrfToken = '{{ csrf_token() }}';

    // ─── State: simpan mapping yang dikonfirmasi per input file ───────────────
    const confirmedMappings = {};   // { fm_file: {field: colIndex, ...}, ... }
    const pendingFile = {};         // { type: FileObject }
    let currentType = null;

    // ─── Intersep perubahan file input ──────────────────────────────────────
    form.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', async () => {
            const type = input.name;
            if (!input.files.length) {
                delete confirmedMappings[type];
                updateFileInputBadge(input, null);
                return;
            }
            const file = input.files[0];
            pendingFile[type] = file;
            currentType = type;

            // Reset badge
            updateFileInputBadge(input, 'loading');

            // Tampilkan modal dan load preview
            await openMappingModal(type, file, input);
        });
    });

    // ─── Submit form dengan mapping yang dikonfirmasi ─────────────────────────
    form.addEventListener('submit', async event => {
        event.preventDefault();
        errors.hidden = true;
        root.querySelector('#monthly-import-summary').replaceChildren();

        const fileInputs = [...form.querySelectorAll('input[type="file"]')];
        const selectedInputs = fileInputs.filter(i => i.files.length);

        if (!selectedInputs.length) {
            showError('Pilih minimal satu file untuk diupload.');
            return;
        }

        // Cek jika semua file yang dipilih sudah dikonfirmasi mapping-nya
        const unconfirmed = selectedInputs.filter(i => !confirmedMappings[i.name]);
        if (unconfirmed.length) {
            showError('Beberapa file belum dikonfirmasi pemetaan kolomnya. Klik file lagi untuk membuka preview.');
            return;
        }

        button.disabled = true;
        button.textContent = 'Memproses import...';

        try {
            const formData = new FormData(form);
            // Tambahkan mapping data
            Object.entries(confirmedMappings).forEach(([type, mapping]) => {
                formData.append(`column_mapping[${type}]`, JSON.stringify(mapping));
            });

            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
            });
            const data = await response.json();
            if (!response.ok) {
                throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Import gagal.');
            }
            root.querySelector('#monthly-import-summary').innerHTML = data.summary_html;
            root.querySelector('#monthly-import-history').innerHTML = data.history_html;
            fileInputs.forEach(i => {
                i.value = '';
                updateFileInputBadge(i, null);
            });
            Object.keys(confirmedMappings).forEach(k => delete confirmedMappings[k]);
        } catch (error) {
            showError(error.message);
        } finally {
            button.disabled = false;
            button.textContent = 'Upload dan import';
        }
    });

    // ─── Buka modal column mapping ────────────────────────────────────────────
    async function openMappingModal(type, file, fileInput) {
        const modal = document.getElementById('column-mapping-modal');
        if (!modal) return;

        const bsModal = bootstrap.Modal.getOrCreateInstance(modal);

        // Reset UI
        setMappingModalState('loading');
        bsModal.show();

        try {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', type);
            formData.append('_token', csrfToken);

            const response = await fetch('{{ route("importrectorate.preview-columns") }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                const errorMsg = Object.values(data.errors || {}).flat().join(' ') || data.message || 'Gagal membaca file.';
                setMappingModalState('error', errorMsg);
                updateFileInputBadge(fileInput, 'error');
                return;
            }

            renderMappingModal(data, type, fileInput);
            setMappingModalState('content');
        } catch (err) {
            setMappingModalState('error', 'Terjadi kesalahan jaringan: ' + err.message);
            updateFileInputBadge(fileInput, 'error');
        }
    }

    // ─── Render konten modal ──────────────────────────────────────────────────
    function renderMappingModal(data, type, fileInput) {
        const { file_info, headers, preview_data, suggested_mapping, mapping_config } = data;

        // Info bar
        document.getElementById('cm-file-name').textContent = file_info.name;
        document.getElementById('cm-sheet-name').textContent = file_info.sheet;
        document.getElementById('cm-col-count').textContent = headers.length;

        // Render mapping rows
        renderMappingRows(headers, suggested_mapping, mapping_config);

        // Render preview table
        renderPreviewTable(headers, preview_data);

        // Validasi awal
        revalidateMapping(mapping_config, type);

        // Listener confirm button
        const confirmBtn = document.getElementById('cm-confirm-btn');
        confirmBtn.onclick = () => confirmMapping(type, fileInput);
    }

    // ─── Render baris tabel mapping ───────────────────────────────────────────
    function renderMappingRows(headers, suggestedMapping, config) {
        const tbody = document.getElementById('cm-mapping-rows');
        tbody.innerHTML = '';

        const allFields = {
            ...Object.fromEntries(Object.entries(config.required).map(([k, v]) => [k, { label: v, required: true }])),
            ...Object.fromEntries(Object.entries(config.optional).map(([k, v]) => [k, { label: v, required: false }])),
        };

        Object.entries(allFields).forEach(([field, info]) => {
            const suggestedIndex = suggestedMapping[field] ?? null;
            const tr = document.createElement('tr');
            tr.dataset.field = field;

            const tdLabel = document.createElement('td');
            tdLabel.innerHTML = `<strong>${escHtml(info.label)}</strong><br><code class="text-muted small">${escHtml(field)}</code>`;

            const tdType = document.createElement('td');
            tdType.className = 'text-center';
            tdType.innerHTML = info.required
                ? '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Wajib</span>'
                : '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Opsional</span>';

            const tdSelect = document.createElement('td');
            const select = document.createElement('select');
            select.className = 'form-select form-select-sm';
            select.dataset.field = field;
            select.required = info.required;

            // Opsi: tidak dipetakan
            const defaultOpt = document.createElement('option');
            defaultOpt.value = '';
            defaultOpt.textContent = '— Tidak dipetakan —';
            select.appendChild(defaultOpt);

            // Tambahkan semua header sebagai opsi
            headers.forEach((header, index) => {
                const opt = document.createElement('option');
                opt.value = index;
                opt.textContent = header || `(Kolom ${index + 1})`;
                if (index === suggestedIndex) opt.selected = true;
                select.appendChild(opt);
            });

            // Listener untuk update preview highlight
            select.addEventListener('change', () => {
                updatePreviewHighlight(parseInt(select.value), field);
                updateExampleValue(tr, headers, select.value);
                revalidateMapping(window._cmCurrentConfig, window._cmCurrentType);
            });

            tdSelect.appendChild(select);

            // Tampilkan nilai contoh
            const tdExample = document.createElement('td');
            tdExample.className = 'text-muted small';
            tdExample.dataset.exampleFor = field;

            const tdStatus = document.createElement('td');
            tdStatus.className = 'text-center';
            tdStatus.dataset.statusFor = field;

            tr.append(tdLabel, tdType, tdSelect, tdExample, tdStatus);
            tbody.appendChild(tr);

            // Set nilai contoh awal
            updateExampleValue(tr, headers, suggestedIndex !== null ? suggestedIndex : '');
        });
    }

    // ─── Render tabel preview data ────────────────────────────────────────────
    function renderPreviewTable(headers, previewData) {
        const thead = document.getElementById('cm-preview-headers');
        const tbody = document.getElementById('cm-preview-body');
        thead.innerHTML = '';
        tbody.innerHTML = '';

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

    // ─── Highlight kolom di preview table ────────────────────────────────────
    function updatePreviewHighlight(colIndex, fieldName) {
        // Reset highlight yang sudah ada
        document.querySelectorAll('#cm-preview-table [data-mapped-field]').forEach(el => {
            el.removeAttribute('data-mapped-field');
            el.classList.remove('table-primary', 'table-success', 'table-warning');
        });

        // Highlight berdasarkan semua select yang aktif
        document.querySelectorAll('#cm-mapping-rows select').forEach(select => {
            const val = parseInt(select.value);
            if (isNaN(val)) return;
            const field = select.dataset.field;
            document.querySelectorAll(`#cm-preview-table [data-col-index="${val}"]`).forEach(el => {
                el.dataset.mappedField = field;
                el.classList.add('table-primary');
            });
        });
    }

    // ─── Update nilai contoh ──────────────────────────────────────────────────
    function updateExampleValue(tr, headers, selectedIndex) {
        const exampleTd = tr.querySelector('[data-example-for]');
        const statusTd = tr.querySelector('[data-status-for]');
        const field = tr.dataset.field;

        if (selectedIndex === '' || selectedIndex === null || selectedIndex === undefined) {
            exampleTd.textContent = '—';
            statusTd.innerHTML = '<i class="bi bi-dash-circle text-secondary"></i>';
            return;
        }

        const idx = parseInt(selectedIndex);
        const header = headers[idx] || '';
        exampleTd.textContent = header;
        statusTd.innerHTML = '<i class="bi bi-check-circle text-success"></i>';

        // Setelah render, highlight preview
        updatePreviewHighlight(idx, field);
    }

    // ─── Validasi mapping ─────────────────────────────────────────────────────
    function revalidateMapping(config, type) {
        if (!config) return;
        window._cmCurrentConfig = config;
        window._cmCurrentType = type;

        const currentMapping = getCurrentMappingFromSelects();
        const errors = [];
        const warnings = [];

        Object.entries(config.required).forEach(([field, label]) => {
            if (currentMapping[field] === undefined || currentMapping[field] === '') {
                errors.push(label);
                markFieldStatus(field, 'error');
            } else {
                markFieldStatus(field, 'ok');
            }
        });

        Object.entries(config.optional).forEach(([field, label]) => {
            if (currentMapping[field] === undefined || currentMapping[field] === '') {
                warnings.push(label);
                markFieldStatus(field, 'warning');
            } else {
                markFieldStatus(field, 'ok');
            }
        });

        const banner = document.getElementById('cm-validation-banner');
        const errorsDiv = document.getElementById('cm-validation-errors');
        const warningsDiv = document.getElementById('cm-validation-warnings');
        const okDiv = document.getElementById('cm-validation-ok');
        const confirmBtn = document.getElementById('cm-confirm-btn');
        const readyBadge = document.getElementById('cm-ready-badge');

        banner.hidden = false;

        if (errors.length) {
            errorsDiv.hidden = false;
            document.getElementById('cm-validation-error-list').innerHTML =
                errors.map(e => `<li>${escHtml(e)}</li>`).join('');
            confirmBtn.disabled = true;
            readyBadge.hidden = false;
            okDiv.hidden = true;
        } else {
            errorsDiv.hidden = true;
            confirmBtn.disabled = false;
            readyBadge.hidden = true;
            okDiv.hidden = false;
        }

        if (warnings.length) {
            warningsDiv.hidden = false;
            document.getElementById('cm-validation-warning-list').innerHTML =
                warnings.map(w => `<li>${escHtml(w)}</li>`).join('');
        } else {
            warningsDiv.hidden = true;
        }
    }

    // ─── Mark status baris ────────────────────────────────────────────────────
    function markFieldStatus(field, status) {
        const statusTd = document.querySelector(`[data-status-for="${field}"]`);
        if (!statusTd) return;
        const icons = {
            ok: '<i class="bi bi-check-circle-fill text-success"></i>',
            error: '<i class="bi bi-x-circle-fill text-danger"></i>',
            warning: '<i class="bi bi-dash-circle text-warning"></i>',
        };
        statusTd.innerHTML = icons[status] || '';
    }

    // ─── Ambil mapping saat ini dari selects ──────────────────────────────────
    function getCurrentMappingFromSelects() {
        const mapping = {};
        document.querySelectorAll('#cm-mapping-rows select').forEach(select => {
            const val = select.value;
            if (val !== '') {
                mapping[select.dataset.field] = parseInt(val);
            }
        });
        return mapping;
    }

    // ─── Konfirmasi mapping ───────────────────────────────────────────────────
    function confirmMapping(type, fileInput) {
        const mapping = getCurrentMappingFromSelects();
        confirmedMappings[type] = mapping;
        updateFileInputBadge(fileInput, 'confirmed');

        const modal = bootstrap.Modal.getInstance(document.getElementById('column-mapping-modal'));
        if (modal) modal.hide();
    }

    // ─── Set state modal ──────────────────────────────────────────────────────
    function setMappingModalState(state, message = '') {
        const loading = document.getElementById('cm-loading');
        const error = document.getElementById('cm-error');
        const content = document.getElementById('cm-content');
        const errorMsg = document.getElementById('cm-error-message');
        const errorDetails = document.getElementById('cm-error-details');
        const errorList = document.getElementById('cm-error-list');

        loading.hidden = state !== 'loading';
        error.hidden = state !== 'error';
        content.hidden = state !== 'content';

        if (state === 'error') {
            // Parse error message untuk cari missing columns
            const missingMatches = [];
            const regex = /Kolom ([^.]+) tidak ditemukan/gi;
            let match;
            while ((match = regex.exec(message)) !== null) {
                missingMatches.push(match[1]);
            }

            if (missingMatches.length > 0) {
                errorDetails.hidden = false;
                errorList.innerHTML = missingMatches.map(col => 
                    '<div class="mb-2"><span class="badge bg-danger-subtle text-danger me-2">✕</span><span>' + escHtml(col) + '</span></div>'
                ).join('');
            } else {
                errorDetails.hidden = true;
            }

            errorMsg.textContent = message;
        }

        document.getElementById('cm-confirm-btn').disabled = state !== 'content';
    }

    // ─── Badge di file input ──────────────────────────────────────────────────
    function updateFileInputBadge(input, status) {
        const container = input.closest('.mb-6');
        if (!container) return;

        let badge = container.querySelector('.cm-status-badge');
        if (!badge) {
            badge = document.createElement('div');
            badge.className = 'cm-status-badge mt-2';
            input.after(badge);
        }

        if (status === null) {
            badge.innerHTML = '';
        } else if (status === 'loading') {
            badge.innerHTML = '<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2">' +
                '<span class="spinner-border spinner-border-sm me-1"></span>Memuat preview kolom...</span>';
        } else if (status === 'confirmed') {
            badge.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">' +
                '<i class="bi bi-check-circle-fill me-1"></i>Pemetaan kolom dikonfirmasi</span> ' +
                '<a href="#" class="small text-muted link-underline-secondary cm-reopen-link">Ubah pemetaan</a>';
            // Link untuk membuka ulang modal
            badge.querySelector('.cm-reopen-link').addEventListener('click', e => {
                e.preventDefault();
                if (input.files.length) {
                    openMappingModal(input.name, input.files[0], input);
                }
            });
        } else if (status === 'error') {
            badge.innerHTML = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">' +
                '<i class="bi bi-x-circle me-1"></i>Gagal membaca file. Coba file lain.</span>';
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────
    function showError(msg) {
        errors.textContent = msg;
        errors.hidden = false;
        errors.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function escHtml(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
})();
</script>

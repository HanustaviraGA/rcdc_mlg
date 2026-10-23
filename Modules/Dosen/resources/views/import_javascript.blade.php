<script type="text/javascript">
    // Backoffice pages can be opened repeatedly without a full browser reload.
    if (typeof dosenImportState !== 'undefined' && dosenImportState.request) dosenImportState.request.abort();
    var dosenImportSequence = typeof dosenImportState !== 'undefined' ? dosenImportState.sequence + 1 : 0;
    var dosenImportState = {
        file: null,
        preview: null,
        request: null,
        sequence: dosenImportSequence,
        loading: false,
        importing: false,
        confirmed: false
    };

    function dosenImportError(xhr, fallback) {
        const errors = xhr.responseJSON?.errors;
        return errors ? Object.values(errors).flat().join('\n') : (xhr.responseJSON?.message || fallback);
    }

    function getDosenColumnMapping(prefix = 'dosen') {
        const mapping = {};
        document.querySelectorAll('#' + prefix + 'MappingRows select').forEach(function (select) {
            if (select.value !== '') mapping[select.dataset.field] = Number(select.value);
        });
        return mapping;
    }

    function validateDosenMapping() {
        if (!dosenImportState.preview) return false;
        const mapping = getDosenColumnMapping();
        const required = {
            ...dosenImportState.preview.mapping_config.required,
            kode_dosen: 'Kode Dosen',
            nama_dosen: 'Nama Dosen'
        };
        const missing = Object.keys(required).filter(field => !Object.prototype.hasOwnProperty.call(mapping, field));
        const indexes = Object.values(mapping);
        const duplicate = new Set(indexes).size !== indexes.length;
        const valid = missing.length === 0 && !duplicate;
        document.getElementById('dosenMappingStatus').textContent = missing.length
            ? 'Kolom wajib belum dipetakan: ' + missing.map(field => required[field]).join(', ')
            : duplicate ? 'Setiap kolom file hanya boleh dipetakan ke satu kolom tujuan.'
            : dosenImportState.confirmed ? 'Pemetaan dikonfirmasi. Data siap diunggah.'
            : 'Pemetaan lengkap. Konfirmasikan sebelum mengunggah.';
        return valid;
    }

    function updateDosenImportControls() {
        const valid = validateDosenMapping();
        const busy = dosenImportState.loading || dosenImportState.importing;
        document.getElementById('dosenMappingConfirm').disabled = busy || !valid || dosenImportState.confirmed;
        document.getElementById('dosenImportSubmit').disabled = busy || !valid || !dosenImportState.confirmed;
        document.getElementById('dosenImportSubmit').textContent = dosenImportState.importing ? 'Mengunggah…' : 'Unggah';
        // Keep the file editable while previewing; selecting another file cancels that preview.
        $('#dosen, #dosenImportYear, #dosenImportMonth, #dosenMappingRows select').prop('disabled', dosenImportState.importing);
        document.getElementById('dosenPreviewRetry').disabled = busy;
    }

    function confirmDosenMapping() {
        if (dosenImportState.loading || dosenImportState.importing || !validateDosenMapping()) return;
        if (document.getElementById('dosen').files[0] !== dosenImportState.file) return;
        dosenImportState.confirmed = true;
        updateDosenImportControls();
    }

    function dosenPreviewValue(row, index) {
        const value = row?.[index];
        return value === null || value === undefined ? '' : String(value);
    }

    function renderDosenWorkbookPreview(preview, options = {}) {
        const prefix = options.prefix || 'dosen';
        const state = options.state || dosenImportState;
        const updateControls = options.updateControls || updateDosenImportControls;
        const mappingRows = document.getElementById(prefix + 'MappingRows');
        const previewHeaders = document.getElementById(prefix + 'PreviewHeaders');
        const previewRows = document.getElementById(prefix + 'PreviewRows');
        mappingRows.replaceChildren();
        previewHeaders.replaceChildren();
        previewRows.replaceChildren();
        document.getElementById(prefix + 'SheetInfo').textContent = 'Sheet: ' + preview.sheet + ' · Baris header: ' + preview.header_row;

        const required = { ...preview.mapping_config.required, ...(options.required || { kode_dosen: 'Kode Dosen', nama_dosen: 'Nama Dosen' }) };
        const fields = { ...required, ...preview.mapping_config.optional };
        const samples = preview.preview_data.slice(0, 5);
        Object.entries(fields).forEach(function ([field, label]) {
            const row = document.createElement('tr');
            const labelCell = document.createElement('td');
            labelCell.textContent = label + (Object.prototype.hasOwnProperty.call(required, field) ? ' (wajib)' : ' (opsional)');
            const selectCell = document.createElement('td');
            const select = document.createElement('select');
            select.className = 'form-select form-select-sm';
            select.dataset.field = field;
            select.setAttribute('aria-label', 'Pemetaan ' + label);
            select.appendChild(new Option('— Tidak dipetakan —', ''));
            preview.headers.forEach(function (header, index) {
                select.appendChild(new Option((index + 1) + '. ' + (header || 'Kolom tanpa nama'), String(index)));
            });
            const suggested = preview.suggested_mapping?.[field];
            if (suggested !== null && suggested !== undefined && Number.isInteger(Number(suggested)) && Number(suggested) >= 0 && Number(suggested) < preview.headers.length) {
                select.value = String(Number(suggested));
            }
            const exampleCell = document.createElement('td');
            exampleCell.className = 'text-muted text-break';
            const updateExample = function () {
                const values = select.value === '' ? [] : samples.map(sample => dosenPreviewValue(sample, Number(select.value)));
                exampleCell.textContent = values.find(value => value !== '') || '—';
            };
            select.addEventListener('change', function () {
                state.confirmed = false;
                updateExample();
                updateControls();
            });
            updateExample();
            selectCell.appendChild(select);
            row.append(labelCell, selectCell, exampleCell);
            mappingRows.appendChild(row);
        });

        preview.headers.forEach(function (header, index) {
            const cell = document.createElement('th');
            cell.className = 'text-nowrap';
            cell.textContent = header || 'Kolom ' + (index + 1);
            previewHeaders.appendChild(cell);
        });
        samples.forEach(function (sample) {
            const row = document.createElement('tr');
            preview.headers.forEach(function (_, index) {
                const cell = document.createElement('td');
                cell.className = 'text-nowrap';
                cell.textContent = dosenPreviewValue(sample, index);
                row.appendChild(cell);
            });
            previewRows.appendChild(row);
        });
        $('#' + prefix + 'MappingPanel').removeClass('d-none');
    }

    function previewDosenWorkbook() {
        if (dosenImportState.importing) return;
        const file = document.getElementById('dosen').files[0];
        const sequence = ++dosenImportState.sequence;
        if (dosenImportState.request) dosenImportState.request.abort();
        dosenImportState.request = null;
        dosenImportState.file = file || null;
        dosenImportState.preview = null;
        dosenImportState.confirmed = false;
        dosenImportState.loading = false;
        $('#dosenMappingPanel, #dosenPreviewRetry').addClass('d-none');
        document.getElementById('dosenImportSummary').textContent = '';
        document.getElementById('dosenPreviewStatus').textContent = '';
        document.getElementById('dosenMappingStatus').textContent = '';
        updateDosenImportControls();
        if (!file) return;
        if (!/\.xlsx$/i.test(file.name)) {
            document.getElementById('dosenPreviewStatus').textContent = 'Pilih file Excel dengan format .xlsx.';
            return;
        }

        const formData = new FormData();
        formData.append('dosen', file);
        dosenImportState.loading = true;
        document.getElementById('dosenPreviewStatus').textContent = 'Membaca file dan menyiapkan pratinjau…';
        updateDosenImportControls();
        const isCurrent = () => sequence === dosenImportState.sequence && document.getElementById('dosen').files[0] === file;
        dosenImportState.request = $.ajax({
            url: '{{ route('dosen.preview') }}',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            success: function (response) {
                if (!isCurrent()) return;
                try {
                    if (response.success === false || !Array.isArray(response.headers) || !response.headers.length || !Array.isArray(response.preview_data) || !response.mapping_config || !Number.isInteger(Number(response.header_row)) || Number(response.header_row) < 1) {
                        throw new Error(response.message || 'Format pratinjau tidak valid. Silakan coba kembali.');
                    }
                    dosenImportState.preview = response;
                    renderDosenWorkbookPreview(response);
                    document.getElementById('dosenPreviewStatus').textContent = 'Pratinjau siap. Periksa pemetaan dan contoh nilai di bawah.';
                } catch (error) {
                    dosenImportState.preview = null;
                    $('#dosenMappingPanel').addClass('d-none');
                    document.getElementById('dosenPreviewStatus').textContent = error.message;
                    $('#dosenPreviewRetry').removeClass('d-none');
                }
            },
            error: function (xhr, status) {
                if (!isCurrent() || status === 'abort') return;
                document.getElementById('dosenPreviewStatus').textContent = dosenImportError(xhr, 'Pratinjau gagal. Silakan coba kembali.');
                $('#dosenPreviewRetry').removeClass('d-none');
            },
            complete: function () {
                if (!isCurrent()) return;
                dosenImportState.loading = false;
                dosenImportState.request = null;
                updateDosenImportControls();
            }
        });
    }

    function onAdd() {
        if (dosenImportState.importing || dosenImportState.loading) return;
        const form = document.getElementById('formHakakses');
        if (!form.reportValidity()) return;
        const file = document.getElementById('dosen').files[0];
        if (!file || file !== dosenImportState.file || !dosenImportState.confirmed || !validateDosenMapping()) {
            document.getElementById('dosenImportSummary').textContent = 'Pratinjau file dan konfirmasikan pemetaan sebelum mengunggah.';
            return;
        }
        const year = document.getElementById('dosenImportYear').value;
        const month = document.getElementById('dosenImportMonth').value;
        const formData = new FormData();
        formData.append('dosen', file);
        formData.append('year', year);
        formData.append('month', month);
        formData.append('column_mapping', JSON.stringify(getDosenColumnMapping()));
        formData.append('header_row', Number(dosenImportState.preview.header_row));
        dosenImportState.importing = true;
        document.getElementById('dosenImportSummary').textContent = 'Mengunggah data…';
        updateDosenImportControls();
        $.ajax({
            url: '{{ route('dosen.read') }}',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            success: function (response) {
                if (response.success === false) {
                    document.getElementById('dosenImportSummary').textContent = response.message || 'Upload gagal. Silakan coba kembali.';
                    return;
                }
                const summary = response.summary || {};
                let message = response.message || 'Data dosen berhasil diunggah.';
                if (summary.force_import) message += '\nForce import aktif. ' + (summary.skipped_rows || 0) + ' baris dilewati.';
                if (Array.isArray(summary.warnings) && summary.warnings.length) message += '\n' + summary.warnings.join('\n');
                if (Array.isArray(summary.unmapped_columns)) message += '\nKolom tidak dipetakan (dikosongkan pada periode ini): ' + (summary.unmapped_columns.join(', ') || 'Tidak ada');
                if (Array.isArray(summary.unknown_columns)) message += '\nKolom belum dikenali: ' + (summary.unknown_columns.join(', ') || 'Tidak ada');
                document.getElementById('dosenImportSummary').textContent = message;
                SUPER.showMessage({ success: true, title: 'Berhasil', message: response.message || 'Data dosen berhasil diunggah.' });
                // Clear the imported file, but retain the chosen period and visible result.
                document.getElementById('dosen').value = '';
                dosenImportState.file = null;
                dosenImportState.preview = null;
                dosenImportState.confirmed = false;
                $('#dosenMappingPanel, #dosenPreviewRetry').addClass('d-none');
                document.getElementById('dosenPreviewStatus').textContent = '';
                // Uploading history must not silently change the current-year listing.
                const years = document.getElementById('dosenAvailableYears');
                if (![...years.options].some(option => option.value === year)) years.appendChild(new Option(year, year));
                init_table();
            },
            error: function (xhr) {
                document.getElementById('dosenImportSummary').textContent = dosenImportError(xhr, 'Upload gagal. Silakan coba kembali.');
            },
            complete: function () {
                dosenImportState.importing = false;
                updateDosenImportControls();
            }
        });
    }
</script>

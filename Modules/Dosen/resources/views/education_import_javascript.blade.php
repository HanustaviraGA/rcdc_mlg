<script>
    if (typeof dosenEducationState !== 'undefined' && dosenEducationState.request) dosenEducationState.request.abort();
    var dosenEducationSequence = typeof dosenEducationState !== 'undefined' ? dosenEducationState.sequence + 1 : 0;
    var dosenEducationState = { file: null, preview: null, request: null, sequence: dosenEducationSequence, loading: false, importing: false, confirmed: false };

    function validateDosenEducationMapping() {
        if (!dosenEducationState.preview) return false;
        const mapping = getDosenColumnMapping('dosenEducation');
        const required = { kode_dosen: 'Kode Dosen', status_pendidikan: 'Status Pendidikan' };
        const missing = Object.keys(required).filter(field => !Object.hasOwn(mapping, field));
        const indices = Object.values(mapping);
        const valid = !missing.length && new Set(indices).size === indices.length
            && indices.every(index => Number.isInteger(index) && index >= 0 && index < dosenEducationState.preview.headers.length);
        document.getElementById('dosenEducationMappingStatus').textContent = missing.length
            ? 'Kolom wajib belum dipetakan: ' + missing.map(field => required[field]).join(', ')
            : !valid ? 'Gunakan kolom sumber yang valid dan berbeda untuk setiap tujuan.'
            : dosenEducationState.confirmed ? 'Pemetaan dikonfirmasi. Status pendidikan siap diperbarui.'
            : 'Pemetaan lengkap. Konfirmasikan sebelum mengunggah.';
        return valid;
    }

    function updateDosenEducationControls() {
        const valid = validateDosenEducationMapping();
        const busy = dosenEducationState.loading || dosenEducationState.importing;
        document.getElementById('dosenEducationMappingConfirm').disabled = busy || !valid || dosenEducationState.confirmed;
        document.getElementById('dosenEducationSubmit').disabled = busy || !valid || !dosenEducationState.confirmed;
        document.getElementById('dosenEducationSubmit').textContent = dosenEducationState.importing ? 'Mengunggah...' : 'Unggah';
        $('#dosenEducationFile, #dosenEducationYear, #dosenEducationMonth, #dosenEducationMappingRows select').prop('disabled', dosenEducationState.importing);
        document.getElementById('dosenEducationPreviewRetry').disabled = busy;
    }

    function confirmDosenEducationMapping() {
        if (dosenEducationState.loading || dosenEducationState.importing || !validateDosenEducationMapping()) return;
        if (document.getElementById('dosenEducationFile').files[0] !== dosenEducationState.file) return;
        dosenEducationState.confirmed = true;
        updateDosenEducationControls();
    }

    function previewDosenEducationWorkbook() {
        if (dosenEducationState.importing) return;
        const file = document.getElementById('dosenEducationFile').files[0];
        const sequence = ++dosenEducationState.sequence;
        dosenEducationState.request?.abort();
        Object.assign(dosenEducationState, { file: file || null, preview: null, request: null, confirmed: false, loading: false });
        $('#dosenEducationMappingPanel, #dosenEducationPreviewRetry').addClass('d-none');
        ['dosenEducationSummary', 'dosenEducationPreviewStatus', 'dosenEducationMappingStatus'].forEach(id => { document.getElementById(id).textContent = ''; });
        updateDosenEducationControls();
        if (!file) return;
        if (!/\.xlsx$/i.test(file.name)) {
            document.getElementById('dosenEducationPreviewStatus').textContent = 'Pilih file Excel dengan format .xlsx.';
            return;
        }
        const data = new FormData();
        data.append('dosen', file);
        dosenEducationState.loading = true;
        document.getElementById('dosenEducationPreviewStatus').textContent = 'Membaca sheet Detail Jenjang Pendidikan...';
        updateDosenEducationControls();
        const isCurrent = () => sequence === dosenEducationState.sequence && document.getElementById('dosenEducationFile').files[0] === file;
        dosenEducationState.request = $.ajax({
            url: '{{ route('dosen.education.preview') }}', type: 'POST', data, contentType: false, processData: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            success: function (response) {
                if (!isCurrent()) return;
                try {
                    if (response.success === false || !Array.isArray(response.headers) || !response.headers.length
                        || !Array.isArray(response.preview_data) || !response.mapping_config?.required
                        || !Number.isInteger(response.header_row) || response.header_row < 1) {
                        throw new Error(response.message || 'Format pratinjau tidak valid. Silakan coba kembali.');
                    }
                    dosenEducationState.preview = response;
                    renderDosenWorkbookPreview(response, {
                        prefix: 'dosenEducation', state: dosenEducationState, updateControls: updateDosenEducationControls,
                        required: { kode_dosen: 'Kode Dosen', status_pendidikan: 'Status Pendidikan' },
                    });
                    document.getElementById('dosenEducationPreviewStatus').textContent = 'Pratinjau siap. Periksa dan konfirmasikan pemetaan kolom.';
                } catch (error) {
                    dosenEducationState.preview = null;
                    $('#dosenEducationMappingPanel').addClass('d-none');
                    document.getElementById('dosenEducationPreviewStatus').textContent = error.message;
                    $('#dosenEducationPreviewRetry').removeClass('d-none');
                }
            },
            error: function (xhr, status) {
                if (!isCurrent() || status === 'abort') return;
                document.getElementById('dosenEducationPreviewStatus').textContent = dosenImportError(xhr, 'Pratinjau gagal. Silakan coba kembali.');
                $('#dosenEducationPreviewRetry').removeClass('d-none');
            },
            complete: function () {
                if (!isCurrent()) return;
                dosenEducationState.loading = false;
                dosenEducationState.request = null;
                updateDosenEducationControls();
            },
        });
    }

    function importDosenEducationWorkbook() {
        if (dosenEducationState.loading || dosenEducationState.importing || !document.getElementById('formDosenEducation').reportValidity()) return;
        const file = document.getElementById('dosenEducationFile').files[0];
        if (!file || file !== dosenEducationState.file || !dosenEducationState.confirmed || !validateDosenEducationMapping()) {
            document.getElementById('dosenEducationSummary').textContent = 'Pratinjau file dan konfirmasikan pemetaan sebelum mengunggah.';
            return;
        }
        const data = new FormData();
        data.append('dosen', file);
        data.append('year', document.getElementById('dosenEducationYear').value);
        data.append('month', document.getElementById('dosenEducationMonth').value);
        data.append('column_mapping', JSON.stringify(getDosenColumnMapping('dosenEducation')));
        data.append('header_row', dosenEducationState.preview.header_row);
        dosenEducationState.importing = true;
        document.getElementById('dosenEducationSummary').textContent = 'Memperbarui status pendidikan...';
        updateDosenEducationControls();
        $.ajax({
            url: '{{ route('dosen.education.import') }}', type: 'POST', data, contentType: false, processData: false,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            success: function (response) {
                if (response.success === false) {
                    document.getElementById('dosenEducationSummary').textContent = response.message || 'Unggahan gagal. Silakan coba kembali.';
                    return;
                }
                const unmatched = response.summary?.unmatched_codes || [];
                document.getElementById('dosenEducationSummary').textContent = response.message + (unmatched.length ? '\nKode tidak ditemukan pada periode ini: ' + unmatched.join(', ') : '');
                if (response.summary?.force_import) document.getElementById('dosenEducationSummary').textContent += '\nForce import aktif. ' + (response.summary.skipped_rows || 0) + ' baris dilewati.';
                if (response.summary?.warnings?.length) document.getElementById('dosenEducationSummary').textContent += '\n' + response.summary.warnings.join('\n');
                SUPER.showMessage({ success: true, title: 'Berhasil', message: response.message });
                document.getElementById('dosenEducationFile').value = '';
                Object.assign(dosenEducationState, { file: null, preview: null, confirmed: false });
                $('#dosenEducationMappingPanel, #dosenEducationPreviewRetry').addClass('d-none');
                document.getElementById('dosenEducationPreviewStatus').textContent = '';
                init_table();
            },
            error: function (xhr) {
                document.getElementById('dosenEducationSummary').textContent = dosenImportError(xhr, 'Unggahan gagal. Silakan coba kembali.');
            },
            complete: function () {
                dosenEducationState.importing = false;
                updateDosenEducationControls();
            },
        });
    }
</script>

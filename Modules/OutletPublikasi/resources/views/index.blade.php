<div class="row table_data mb-5" data-roleable="false" data-role="outletpublikasi.read">
    <div class="col-12">
        <div class="card card-bordered">
            <div class="card-body">
                <div class="row align-items-center g-4 mb-5">
                    <div class="col-12 col-xl">
                        <div class="d-flex align-items-center">
                            <span class="symbol symbol-40px me-4">
                                <span class="symbol-label bg-light-primary">
                                    <i class="las la-newspaper fs-2 text-primary"></i>
                                </span>
                            </span>
                            <div>
                                <h3 class="mb-1">Outlet Publikasi</h3>
                                <span class="text-muted">Kelola conference yang ditampilkan pada landing page.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-7 col-xl-4">
                        <div class="position-relative">
                            <i class="las la-search position-absolute top-50 translate-middle-y ms-4 fs-2 text-muted"></i>
                            <input
                                type="search"
                                class="form-control ps-12 bg-gray-100"
                                id="outletPublikasiSearch"
                                placeholder="Cari nama conference atau scope"
                                autocomplete="off"
                            >
                        </div>
                    </div>
                    <div class="col-12 col-md-5 col-xl-3">
                        <button type="button" class="btn btn-primary w-100" onclick="openOutletPublikasiForm()">
                            <i class="las la-plus fs-2"></i> Tambah Outlet
                        </button>
                    </div>
                </div>

                <div class="table-responsive border-top pt-4">
                    <table class="table table-striped table-row-bordered align-middle" id="outletPublikasiTable">
                        <thead>
                            <tr class="fw-bolder text-muted">
                                <th class="text-center">No.</th>
                                <th>Nama Conference</th>
                                <th>BJIC/Co-Host/-</th>
                                <th>Deadline Submission</th>
                                <th>Scope</th>
                                <th>Contact PIC</th>
                                <th>Update</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="outletPublikasiFormModal" tabindex="-1" aria-labelledby="outletPublikasiFormTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="outletPublikasiForm" autocomplete="off">
                <div class="modal-header">
                    <h2 class="modal-title" id="outletPublikasiFormTitle">Tambah Outlet Publikasi</h2>
                    <button type="button" class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal" aria-label="Tutup">
                        <i class="las la-times fs-1"></i>
                    </button>
                </div>
                <div class="modal-body py-7">
                    <input type="hidden" name="id" id="outletPublikasiId">

                    <div class="row g-5">
                        <div class="col-12">
                            <label for="outletPublikasiNama" class="required form-label">Nama Conference</label>
                            <input type="text" class="form-control" name="nama_conference" id="outletPublikasiNama" maxlength="255" required>
                        </div>
                        <div class="col-md-6">
                            <label for="outletPublikasiTipe" class="required form-label">BJIC/Co-Host/-</label>
                            <select class="form-select" name="tipe_kerjasama" id="outletPublikasiTipe" required>
                                <option value="-">-</option>
                                <option value="BJIC">BJIC</option>
                                <option value="Co-Host">Co-Host</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="outletPublikasiDeadline" class="required form-label">Deadline Submission</label>
                            <input type="date" class="form-control" name="deadline_submission" id="outletPublikasiDeadline" required>
                        </div>
                        <div class="col-12">
                            <label for="outletPublikasiScope" class="required form-label">Scope</label>
                            <textarea class="form-control" name="scope" id="outletPublikasiScope" rows="7" maxlength="20000" required></textarea>
                            <div class="form-text">Scope dapat diisi hingga 20.000 karakter.</div>
                        </div>
                        <div class="col-12">
                            <label for="outletPublikasiContact" class="required form-label">Contact PIC</label>
                            <input type="text" class="form-control" name="contact_pic" id="outletPublikasiContact" maxlength="255" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="outletPublikasiSubmit">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="outletPublikasiScopeModal" tabindex="-1" aria-labelledby="outletPublikasiScopeTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="outletPublikasiScopeTitle">Scope Conference</h2>
                <button type="button" class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal" aria-label="Tutup">
                    <i class="las la-times fs-1"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="outletPublikasiScopeContent" class="fs-6 text-gray-800" style="white-space: pre-wrap;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        initOutletPublikasiTable();

        var outletSearchDelay;
        $('#outletPublikasiSearch').on('input', function () {
            clearTimeout(outletSearchDelay);
            var searchValue = this.value;
            outletSearchDelay = setTimeout(function () {
                if (window.outletPublikasiTable) {
                    window.outletPublikasiTable.search(searchValue).draw();
                }
            }, 300);
        });

        $('#outletPublikasiForm').on('submit', function (event) {
            event.preventDefault();
            saveOutletPublikasi();
        });
    });

    function initOutletPublikasiTable() {
        window.outletPublikasiTable = SUPER.initDataTable('#outletPublikasiTable', {
            responsive: true,
            serverSide: true,
            processing: true,
            pageLength: 10,
            order: [[6, 'desc']],
            ajax: {
                url: '{{ route('outletpublikasi.init_table') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                error: handleOutletPublikasiAjaxError
            },
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    searchable: false,
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'nama_conference',
                    name: 'nama_conference',
                    render: function (data, type) {
                        return type === 'display' ? escapeOutletPublikasiHtml(data) : data;
                    }
                },
                {
                    data: 'tipe_kerjasama',
                    name: 'tipe_kerjasama',
                    render: function (data, type) {
                        if (type !== 'display') {
                            return data;
                        }

                        var badgeClass = data === 'BJIC' ? 'badge-light-primary' : (data === 'Co-Host' ? 'badge-light-success' : 'badge-light');
                        return '<span class="badge ' + badgeClass + '">' + escapeOutletPublikasiHtml(data || '-') + '</span>';
                    }
                },
                {
                    data: 'deadline_submission',
                    name: 'deadline_submission',
                    render: function (data, type) {
                        return type === 'display' ? formatOutletPublikasiDate(data) : data;
                    }
                },
                {
                    data: 'scope',
                    name: 'scope',
                    orderable: false,
                    render: function () {
                        return '<button type="button" class="btn btn-sm btn-light-primary" onclick="showOutletPublikasiScope(this)"><i class="las la-eye fs-4"></i> Lihat Scope</button>';
                    }
                },
                {
                    data: 'contact_pic',
                    name: 'contact_pic',
                    render: function (data, type) {
                        return type === 'display' ? escapeOutletPublikasiHtml(data) : data;
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    render: function (data, type) {
                        return type === 'display' ? formatOutletPublikasiDate(data) : data;
                    }
                },
                {
                    data: null,
                    searchable: false,
                    orderable: false,
                    render: function (data, type, row) {
                        return '<div class="d-flex flex-shrink-0">' +
                            '<button type="button" class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm me-1" data-id="' + row.id + '" onclick="editOutletPublikasi(this)" title="Edit"><i class="las la-edit fs-2"></i></button>' +
                            '<button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-id="' + row.id + '" onclick="deleteOutletPublikasi(this)" title="Hapus"><i class="las la-trash fs-2"></i></button>' +
                            '</div>';
                    }
                }
            ]
        }, {
            stateKey: 'outletpublikasi.table',
            stateSignature: {}
        });
    }

    function outletPublikasiRow(element) {
        var row = $(element).closest('tr');
        if (row.hasClass('child')) {
            row = row.prev();
        }

        return $('#outletPublikasiTable').DataTable().row(row).data();
    }

    function openOutletPublikasiForm() {
        document.getElementById('outletPublikasiForm').reset();
        $('#outletPublikasiId').val('');
        $('#outletPublikasiTipe').val('-');
        $('#outletPublikasiFormTitle').text('Tambah Outlet Publikasi');
        $('#outletPublikasiFormModal').modal('show');
    }

    function editOutletPublikasi(element) {
        var id = $(element).data('id');
        blockPage();

        $.ajax({
            url: '{{ route('outletpublikasi.read') }}',
            type: 'POST',
            data: { id: id },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            success: function (response) {
                var data = response.data;
                $('#outletPublikasiId').val(data.id);
                $('#outletPublikasiNama').val(data.nama_conference);
                $('#outletPublikasiTipe').val(data.tipe_kerjasama);
                $('#outletPublikasiDeadline').val((data.deadline_submission || '').substring(0, 10));
                $('#outletPublikasiScope').val(data.scope);
                $('#outletPublikasiContact').val(data.contact_pic);
                $('#outletPublikasiFormTitle').text('Edit Outlet Publikasi');
                $('#outletPublikasiFormModal').modal('show');
            },
            error: handleOutletPublikasiAjaxError,
            complete: function () {
                unblockPage(200);
            }
        });
    }

    function saveOutletPublikasi() {
        var id = $('#outletPublikasiId').val();
        var isEditing = id !== '';

        SUPER.confirm({
            message: isEditing ? 'Simpan perubahan outlet publikasi?' : 'Tambahkan outlet publikasi baru?',
            callback: function (confirmed) {
                if (!confirmed) {
                    return;
                }

                blockPage();
                $.ajax({
                    url: isEditing ? '{{ route('outletpublikasi.update') }}' : '{{ route('outletpublikasi.create') }}',
                    type: isEditing ? 'PUT' : 'POST',
                    data: $('#outletPublikasiForm').serialize(),
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        $('#outletPublikasiFormModal').modal('hide');
                        window.outletPublikasiTable.ajax.reload(null, false);
                        SUPER.showMessage({
                            success: true,
                            title: 'Berhasil',
                            message: response.message
                        });
                    },
                    error: handleOutletPublikasiAjaxError,
                    complete: function () {
                        unblockPage(200);
                    }
                });
            }
        });
    }

    function deleteOutletPublikasi(element) {
        var id = $(element).data('id');
        var row = outletPublikasiRow(element);
        var conferenceName = row ? row.nama_conference : 'data ini';

        SUPER.confirm({
            message: 'Hapus outlet publikasi "' + escapeOutletPublikasiHtml(conferenceName) + '"?',
            callback: function (confirmed) {
                if (!confirmed) {
                    return;
                }

                blockPage();
                $.ajax({
                    url: '{{ route('outletpublikasi.delete') }}',
                    type: 'DELETE',
                    data: { id: id },
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    success: function (response) {
                        window.outletPublikasiTable.ajax.reload(null, false);
                        SUPER.showMessage({
                            success: true,
                            title: 'Berhasil',
                            message: response.message
                        });
                    },
                    error: handleOutletPublikasiAjaxError,
                    complete: function () {
                        unblockPage(200);
                    }
                });
            }
        });
    }

    function showOutletPublikasiScope(element) {
        var row = outletPublikasiRow(element);
        if (!row) {
            return;
        }

        $('#outletPublikasiScopeTitle').text('Scope - ' + row.nama_conference);
        $('#outletPublikasiScopeContent').text(row.scope || '-');
        $('#outletPublikasiScopeModal').modal('show');
    }

    function formatOutletPublikasiDate(value) {
        if (!value) {
            return '-';
        }

        var dateValue = new Date(value.length === 10 ? value + 'T00:00:00' : value);
        if (Number.isNaN(dateValue.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        }).format(dateValue);
    }

    function escapeOutletPublikasiHtml(value) {
        return $('<div>').text(value === null || value === undefined ? '' : value).html();
    }

    function handleOutletPublikasiAjaxError(xhr) {
        var message = 'Terjadi kesalahan. Silakan coba kembali.';

        if (xhr.responseJSON && xhr.responseJSON.errors) {
            var errors = Object.values(xhr.responseJSON.errors);
            if (errors.length && errors[0].length) {
                message = errors[0][0];
            }
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }

        SUPER.showMessage({
            success: false,
            title: 'Gagal',
            message: message
        });
    }
</script>

<style>
    .outlet-publikasi-modal .modal-dialog {
        margin-bottom: .75rem;
        margin-top: .75rem;
        max-height: calc(100vh - 1.5rem);
    }

    .outlet-publikasi-modal .modal-content,
    .outlet-publikasi-modal .outlet-publikasi-modal-form {
        display: flex;
        flex-direction: column;
        max-height: calc(100vh - 1.5rem);
        min-height: 0;
        overflow: hidden;
    }

    .outlet-publikasi-modal .outlet-publikasi-modal-form {
        flex: 1 1 auto;
        width: 100%;
    }

    .outlet-publikasi-modal .modal-header,
    .outlet-publikasi-modal .modal-footer {
        flex: 0 0 auto;
    }

    .outlet-publikasi-modal .modal-body {
        min-height: 0;
        overflow-y: auto !important;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
        scrollbar-width: thin;
    }

    .outlet-publikasi-modal .modal-body::-webkit-scrollbar {
        width: 8px;
    }

    .outlet-publikasi-modal .modal-body::-webkit-scrollbar-thumb {
        background: #b5b5c3;
        border-radius: 8px;
    }

    @supports (height: 100dvh) {
        .outlet-publikasi-modal .modal-dialog,
        .outlet-publikasi-modal .modal-content,
        .outlet-publikasi-modal .outlet-publikasi-modal-form {
            max-height: calc(100dvh - 1.5rem);
        }
    }

    @media (max-height: 700px) {
        .outlet-publikasi-modal .modal-body {
            padding-bottom: 1rem !important;
            padding-top: 1rem !important;
        }

        .outlet-publikasi-modal textarea.form-control {
            max-height: 125px;
        }
    }
</style>

<div class="row table_data mb-5" data-roleable="false" data-role="outletpublikasi.read">
    <div class="col-12">
        <div class="card card-bordered">
            <div class="card-body">
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 mb-8 overflow-auto flex-nowrap" id="outletPublikasiCategoryTabs">
                    <li class="nav-item"><button type="button" class="nav-link active text-nowrap" data-category="publikasi" onclick="switchOutletPublikasiCategory(this)">Publikasi</button></li>
                    <li class="nav-item"><button type="button" class="nav-link text-nowrap" data-category="book_chapter" onclick="switchOutletPublikasiCategory(this)">Book Chapter</button></li>
                    <li class="nav-item"><button type="button" class="nav-link text-nowrap" data-category="scopus_journals" onclick="switchOutletPublikasiCategory(this)">Scopus Journals</button></li>
                    <li class="nav-item"><button type="button" class="nav-link text-nowrap" data-category="sinta" onclick="switchOutletPublikasiCategory(this)">SINTA</button></li>
                    <li class="nav-item"><button type="button" class="nav-link text-nowrap" data-category="penelitian_hibah" onclick="switchOutletPublikasiCategory(this)">Penelitian &amp; Hibah</button></li>
                </ul>

                <div class="row align-items-center g-4 mb-5">
                    <div class="col-12 col-xl">
                        <div class="d-flex align-items-center">
                            <span class="symbol symbol-40px me-4">
                                <span class="symbol-label bg-light-primary"><i class="las la-newspaper fs-2 text-primary"></i></span>
                            </span>
                            <div>
                                <h3 class="mb-1" id="outletPublikasiSectionTitle">Publikasi</h3>
                                <span class="text-muted" id="outletPublikasiSectionDescription">Kelola conference yang ditampilkan pada landing page.</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-7 col-xl-4">
                        <div class="position-relative">
                            <i class="las la-search position-absolute top-50 translate-middle-y ms-4 fs-2 text-muted"></i>
                            <input type="search" class="form-control ps-12 bg-gray-100" id="outletPublikasiSearch" placeholder="Cari nama conference atau scope" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-12 col-md-5 col-xl-3">
                        <button type="button" class="btn btn-primary w-100" onclick="openOutletPublikasiForm()">
                            <i class="las la-plus fs-2"></i> <span id="outletPublikasiAddLabel">Tambah Publikasi</span>
                        </button>
                    </div>
                </div>

                <div class="table-responsive border-top pt-4">
                    <table class="table table-striped table-row-bordered align-middle" id="outletPublikasiTable">
                        <thead><tr class="fw-bolder text-muted" id="outletPublikasiTableHead"></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade outlet-publikasi-modal" id="outletPublikasiFormModal" tabindex="-1" aria-labelledby="outletPublikasiFormTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="outletPublikasiForm" class="outlet-publikasi-modal-form" autocomplete="off">
                <div class="modal-header">
                    <h2 class="modal-title" id="outletPublikasiFormTitle">Tambah Publikasi</h2>
                    <button type="button" class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal" aria-label="Tutup"><i class="las la-times fs-1"></i></button>
                </div>
                <div class="modal-body py-7">
                    <input type="hidden" name="id" id="outletPublikasiId">
                    <input type="hidden" name="category" id="outletPublikasiCategory" value="publikasi">

                    <div class="row g-5">
                        <div class="col-12">
                            <label for="outletPublikasiNama" class="required form-label" id="outletPublikasiNamaLabel">Nama Conference</label>
                            <input type="text" class="form-control" name="nama_conference" id="outletPublikasiNama" maxlength="255" required>
                        </div>

                        <div class="col-12 outlet-category-fields" data-fields="publikasi">
                            <div class="row g-5">
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
                                    <textarea class="form-control" name="scope" id="outletPublikasiScope" rows="6" maxlength="20000" required></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="outletPublikasiContact" class="required form-label">Contact PIC</label>
                                    <input type="text" class="form-control" name="contact_pic" id="outletPublikasiContact" maxlength="255" required>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 outlet-category-fields d-none" data-fields="book_chapter">
                            <label for="outletBookFrequency" class="required form-label">Publication Frequency</label>
                            <input type="text" class="form-control" name="publication_frequency" id="outletBookFrequency" maxlength="255" required disabled>
                        </div>

                        <div class="col-12 outlet-category-fields d-none" data-fields="journal">
                            <div class="row g-5">
                                <div class="col-md-6">
                                    <label for="outletJournalIssn" class="required form-label">ISSN</label>
                                    <input type="text" class="form-control" name="issn" id="outletJournalIssn" maxlength="50" required disabled>
                                </div>
                                <div class="col-md-6">
                                    <label for="outletJournalMetric" class="required form-label" id="outletJournalMetricLabel">Quartile - SJR</label>
                                    <input type="text" class="form-control" name="quartile_sjr" id="outletJournalMetric" maxlength="100" required disabled>
                                </div>
                                <div class="col-12">
                                    <label for="outletJournalFrequency" class="required form-label">Publication Frequency</label>
                                    <input type="text" class="form-control" name="publication_frequency" id="outletJournalFrequency" maxlength="255" required disabled>
                                </div>
                                <div class="col-12">
                                    <label for="outletJournalScope" class="required form-label">Scope</label>
                                    <textarea class="form-control" name="scope" id="outletJournalScope" rows="6" maxlength="20000" required disabled></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 outlet-category-fields d-none" data-fields="penelitian_hibah">
                            <label for="outletHibahDeadline" class="required form-label">Deadline Submission</label>
                            <input type="date" class="form-control" name="deadline_submission" id="outletHibahDeadline" required disabled>
                        </div>

                        <div class="col-12">
                            <label for="outletPublikasiUrl" class="required form-label">URL Website</label>
                            <input type="text" inputmode="url" class="form-control" name="url_website" id="outletPublikasiUrl" maxlength="2048" placeholder="https://contoh.com" required>
                            <div class="form-text">Jika protokol tidak ditulis, sistem akan otomatis menggunakan HTTPS.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade outlet-publikasi-modal" id="outletPublikasiScopeModal" tabindex="-1" aria-labelledby="outletPublikasiScopeTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="outletPublikasiScopeTitle">Scope</h2>
                <button type="button" class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal" aria-label="Tutup"><i class="las la-times fs-1"></i></button>
            </div>
            <div class="modal-body"><div id="outletPublikasiScopeContent" class="fs-6 text-gray-800" style="white-space: pre-wrap;"></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    window.outletPublikasiCategories = {
        publikasi: {
            label: 'Publikasi',
            nameLabel: 'Nama Conference',
            nameField: 'nama_conference',
            description: 'Kelola conference yang ditampilkan pada landing page.',
            search: 'Cari nama conference atau scope'
        },
        book_chapter: {
            label: 'Book Chapter',
            nameLabel: 'Nama Book Chapter',
            nameField: 'nama',
            description: 'Kelola outlet book chapter dan publication frequency.',
            search: 'Cari nama book chapter atau publication frequency'
        },
        scopus_journals: {
            label: 'Scopus Journals',
            nameLabel: 'Nama Jurnal',
            nameField: 'nama',
            description: 'Kelola jurnal Scopus, quartile, SJR, dan scope.',
            search: 'Cari nama jurnal, ISSN, quartile, frequency, atau scope'
        },
        sinta: {
            label: 'SINTA',
            nameLabel: 'Nama Jurnal',
            nameField: 'nama',
            description: 'Kelola jurnal terakreditasi SINTA dan scope.',
            search: 'Cari nama jurnal, ISSN, SINTA, frequency, atau scope'
        },
        penelitian_hibah: {
            label: 'Penelitian & Hibah',
            nameLabel: 'Nama Hibah',
            nameField: 'nama',
            description: 'Kelola peluang penelitian dan hibah.',
            search: 'Cari nama penelitian atau hibah'
        }
    };
    window.outletPublikasiCurrentCategory = 'publikasi';

    $(document).ready(function () {
        renderOutletPublikasiCategory();

        $('#outletPublikasiFormModal, #outletPublikasiScopeModal').on('shown.bs.modal', function () {
            $(this).find('.modal-body').scrollTop(0);
        });

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

    function switchOutletPublikasiCategory(element) {
        var category = $(element).data('category');
        if (!window.outletPublikasiCategories[category] || category === window.outletPublikasiCurrentCategory) {
            return;
        }

        window.outletPublikasiCurrentCategory = category;
        $('#outletPublikasiCategoryTabs .nav-link').removeClass('active');
        $(element).addClass('active');
        $('#outletPublikasiSearch').val('');
        renderOutletPublikasiCategory();
    }

    function renderOutletPublikasiCategory() {
        var category = window.outletPublikasiCurrentCategory;
        var config = window.outletPublikasiCategories[category];

        $('#outletPublikasiSectionTitle').text(config.label);
        $('#outletPublikasiSectionDescription').text(config.description);
        $('#outletPublikasiSearch').attr('placeholder', config.search);
        $('#outletPublikasiAddLabel').text('Tambah ' + config.label);

        if ($.fn.DataTable.isDataTable('#outletPublikasiTable')) {
            $('#outletPublikasiTable').DataTable().destroy();
        }

        $('#outletPublikasiTable tbody').empty();
        $('#outletPublikasiTableHead').html(outletPublikasiHeaders(category));
        initOutletPublikasiTable();
    }

    function outletPublikasiHeaders(category) {
        var headers = {
            publikasi: ['No.', 'Nama Conference', 'BJIC/Co-Host/-', 'Deadline Submission', 'Scope', 'Contact PIC', 'Update', 'Aksi'],
            book_chapter: ['No.', 'Nama Book Chapter', 'Publication Frequency', 'Update', 'Aksi'],
            scopus_journals: ['No.', 'Nama Jurnal', 'ISSN', 'Quartile - SJR', 'Publication Frequency', 'Scope', 'Update', 'Aksi'],
            sinta: ['No.', 'Nama Jurnal', 'ISSN', 'SINTA', 'Publication Frequency', 'Scope', 'Update', 'Aksi'],
            penelitian_hibah: ['No.', 'Nama Hibah', 'Deadline Submission', 'Aksi']
        };

        return headers[category].map(function (header) {
            return '<th>' + header + '</th>';
        }).join('');
    }

    function initOutletPublikasiTable() {
        var category = window.outletPublikasiCurrentCategory;
        var defaultOrder = category === 'penelitian_hibah' ? [[2, 'asc']] : [[outletPublikasiUpdateColumn(category), 'desc']];

        window.outletPublikasiTable = SUPER.initDataTable('#outletPublikasiTable', {
            responsive: true,
            serverSide: true,
            processing: true,
            pageLength: 10,
            order: defaultOrder,
            ajax: {
                url: '{{ route('outletpublikasi.init_table') }}',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                data: function (data) {
                    data.category = window.outletPublikasiCurrentCategory;
                },
                error: handleOutletPublikasiAjaxError
            },
            columns: outletPublikasiColumns(category)
        }, {
            stateKey: 'outletpublikasi.table.' + category,
            stateSignature: { category: category }
        });
    }

    function outletPublikasiUpdateColumn(category) {
        return category === 'book_chapter' ? 3 : 6;
    }

    function outletPublikasiColumns(category) {
        var indexColumn = {
            data: 'DT_RowIndex',
            name: 'DT_RowIndex',
            searchable: false,
            orderable: false,
            className: 'text-center'
        };
        var actionColumn = {
            data: null,
            searchable: false,
            orderable: false,
            render: function (data, type, row) {
                return '<div class="d-flex flex-shrink-0">' +
                    '<button type="button" class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm me-1" data-id="' + row.id + '" onclick="editOutletPublikasi(this)" title="Edit"><i class="las la-edit fs-2"></i></button>' +
                    '<button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" data-id="' + row.id + '" onclick="deleteOutletPublikasi(this)" title="Hapus"><i class="las la-trash fs-2"></i></button>' +
                    '</div>';
            }
        };
        var nameField = category === 'publikasi' ? 'nama_conference' : 'nama';
        var nameColumn = {
            data: nameField,
            name: nameField,
            render: function (data, type, row) {
                if (type !== 'display') {
                    return data;
                }

                var safeUrl = safeOutletPublikasiUrl(row.url_website);
                var safeName = escapeOutletPublikasiHtml(data);
                return safeUrl ? '<a href="' + escapeOutletPublikasiHtml(safeUrl) + '" target="_blank" rel="noopener noreferrer" class="fw-bold text-dark text-hover-primary">' + safeName + ' <i class="las la-external-link-alt"></i></a>' : safeName;
            }
        };
        var dateColumn = function (field) {
            return {
                data: field,
                name: field,
                render: function (data, type) {
                    return type === 'display' ? formatOutletPublikasiDate(data) : data;
                }
            };
        };
        var textColumn = function (field) {
            return {
                data: field,
                name: field,
                render: function (data, type) {
                    return type === 'display' ? escapeOutletPublikasiHtml(data || '-') : data;
                }
            };
        };
        var scopeColumn = {
            data: 'scope',
            name: 'scope',
            orderable: false,
            render: function () {
                return '<button type="button" class="btn btn-sm btn-light-primary" onclick="showOutletPublikasiScope(this)"><i class="las la-eye fs-4"></i> Lihat Scope</button>';
            }
        };

        if (category === 'publikasi') {
            return [
                indexColumn,
                nameColumn,
                textColumn('tipe_kerjasama'),
                dateColumn('deadline_submission'),
                scopeColumn,
                textColumn('contact_pic'),
                dateColumn('created_at'),
                actionColumn
            ];
        }

        if (category === 'book_chapter') {
            return [indexColumn, nameColumn, textColumn('publication_frequency'), dateColumn('created_at'), actionColumn];
        }

        if (category === 'scopus_journals' || category === 'sinta') {
            return [
                indexColumn,
                nameColumn,
                textColumn('issn'),
                textColumn(category === 'scopus_journals' ? 'quartile_sjr' : 'sinta'),
                textColumn('publication_frequency'),
                scopeColumn,
                dateColumn('created_at'),
                actionColumn
            ];
        }

        return [indexColumn, nameColumn, dateColumn('deadline_submission'), actionColumn];
    }

    function outletPublikasiRow(element) {
        var row = $(element).closest('tr');
        if (row.hasClass('child')) {
            row = row.prev();
        }
        return $('#outletPublikasiTable').DataTable().row(row).data();
    }

    function configureOutletPublikasiForm(category) {
        var config = window.outletPublikasiCategories[category];
        var fieldGroup = category === 'scopus_journals' || category === 'sinta' ? 'journal' : category;

        $('.outlet-category-fields').addClass('d-none').find(':input').prop('disabled', true);
        $('.outlet-category-fields[data-fields="' + fieldGroup + '"]').removeClass('d-none').find(':input').prop('disabled', false);
        $('#outletPublikasiCategory').val(category);
        $('#outletPublikasiNamaLabel').text(config.nameLabel);
        $('#outletPublikasiNama').attr('name', config.nameField);

        if (fieldGroup === 'journal') {
            var isScopus = category === 'scopus_journals';
            $('#outletJournalMetricLabel').text(isScopus ? 'Quartile - SJR' : 'SINTA');
            $('#outletJournalMetric').attr('name', isScopus ? 'quartile_sjr' : 'sinta').attr('maxlength', isScopus ? 100 : 50);
        }
    }

    function openOutletPublikasiForm() {
        var category = window.outletPublikasiCurrentCategory;
        var config = window.outletPublikasiCategories[category];
        document.getElementById('outletPublikasiForm').reset();
        configureOutletPublikasiForm(category);
        $('#outletPublikasiId').val('');
        $('#outletPublikasiTipe').val('-');
        $('#outletPublikasiFormTitle').text('Tambah ' + config.label);
        $('#outletPublikasiFormModal').modal('show');
    }

    function editOutletPublikasi(element) {
        var id = $(element).data('id');
        var category = window.outletPublikasiCurrentCategory;
        blockPage();

        $.ajax({
            url: '{{ route('outletpublikasi.read') }}',
            type: 'POST',
            data: { id: id, category: category },
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            success: function (response) {
                var data = response.data;
                var config = window.outletPublikasiCategories[category];
                document.getElementById('outletPublikasiForm').reset();
                configureOutletPublikasiForm(category);
                $('#outletPublikasiId').val(data.id);
                $('#outletPublikasiNama').val(category === 'publikasi' ? data.nama_conference : data.nama);
                $('#outletPublikasiUrl').val(data.url_website);

                if (category === 'publikasi') {
                    $('#outletPublikasiTipe').val(data.tipe_kerjasama);
                    $('#outletPublikasiDeadline').val((data.deadline_submission || '').substring(0, 10));
                    $('#outletPublikasiScope').val(data.scope);
                    $('#outletPublikasiContact').val(data.contact_pic);
                } else if (category === 'book_chapter') {
                    $('#outletBookFrequency').val(data.publication_frequency);
                } else if (category === 'scopus_journals' || category === 'sinta') {
                    $('#outletJournalIssn').val(data.issn);
                    $('#outletJournalMetric').val(category === 'scopus_journals' ? data.quartile_sjr : data.sinta);
                    $('#outletJournalFrequency').val(data.publication_frequency);
                    $('#outletJournalScope').val(data.scope);
                } else {
                    $('#outletHibahDeadline').val((data.deadline_submission || '').substring(0, 10));
                }

                $('#outletPublikasiFormTitle').text('Edit ' + config.label);
                $('#outletPublikasiFormModal').modal('show');
            },
            error: handleOutletPublikasiAjaxError,
            complete: function () { unblockPage(200); }
        });
    }

    function saveOutletPublikasi() {
        var id = $('#outletPublikasiId').val();
        var isEditing = id !== '';
        var category = window.outletPublikasiCurrentCategory;
        var label = window.outletPublikasiCategories[category].label;

        SUPER.confirm({
            message: isEditing ? 'Simpan perubahan ' + label + '?' : 'Tambahkan ' + label + ' baru?',
            callback: function (confirmed) {
                if (!confirmed) {
                    return;
                }

                blockPage();
                $.ajax({
                    url: isEditing ? '{{ route('outletpublikasi.update') }}' : '{{ route('outletpublikasi.create') }}',
                    type: isEditing ? 'PUT' : 'POST',
                    data: $('#outletPublikasiForm').serialize(),
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    success: function (response) {
                        $('#outletPublikasiFormModal').modal('hide');
                        window.outletPublikasiTable.ajax.reload(null, false);
                        SUPER.showMessage({ success: true, title: 'Berhasil', message: response.message });
                    },
                    error: handleOutletPublikasiAjaxError,
                    complete: function () { unblockPage(200); }
                });
            }
        });
    }

    function deleteOutletPublikasi(element) {
        var id = $(element).data('id');
        var row = outletPublikasiRow(element);
        var category = window.outletPublikasiCurrentCategory;
        var nameField = category === 'publikasi' ? 'nama_conference' : 'nama';
        var recordName = row ? row[nameField] : 'data ini';

        SUPER.confirm({
            message: 'Hapus "' + escapeOutletPublikasiHtml(recordName) + '"?',
            callback: function (confirmed) {
                if (!confirmed) {
                    return;
                }

                blockPage();
                $.ajax({
                    url: '{{ route('outletpublikasi.delete') }}',
                    type: 'DELETE',
                    data: { id: id, category: category },
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    success: function (response) {
                        window.outletPublikasiTable.ajax.reload(null, false);
                        SUPER.showMessage({ success: true, title: 'Berhasil', message: response.message });
                    },
                    error: handleOutletPublikasiAjaxError,
                    complete: function () { unblockPage(200); }
                });
            }
        });
    }

    function showOutletPublikasiScope(element) {
        var row = outletPublikasiRow(element);
        if (!row) {
            return;
        }
        var name = row.nama_conference || row.nama;
        $('#outletPublikasiScopeTitle').text('Scope - ' + name);
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
        return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(dateValue);
    }

    function safeOutletPublikasiUrl(value) {
        if (!value) {
            return '';
        }
        try {
            var parsed = new URL(value);
            return parsed.protocol === 'http:' || parsed.protocol === 'https:' ? parsed.href : '';
        } catch (error) {
            return '';
        }
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
        SUPER.showMessage({ success: false, title: 'Gagal', message: message });
    }
</script>

<script type="text/javascript">
    $(document).ready(function() {
        let searchDelay;
        let $searchInput = $('#customSearchInput');
        // Bind keyup event to custom search input with a delay
        $searchInput.keyup(function() {
            clearTimeout(searchDelay);
            let searchText = $searchInput.val();
            searchDelay = setTimeout(function() {
                table.search(searchText).draw();
            }, 300); // Adjust the delay time (in milliseconds) as needed
        });
        init_table();
    });

    function init_table(){
        blockPage();
        var prodi = $('#prodi').val();
        if ($.fn.DataTable.isDataTable('#tableCourse')) {
            $('#tableCourse').DataTable().destroy();
        }
        let roles = (SUPER.get_role_access('editdosen'));
        var col =[
            {
                data: 'kode_dosen',
                name: 'kode_dosen',
                orderable: true,
                render: function (data, type, full, meta) {
                    if(full.kode_dosen){
                        return full.kode_dosen;
                    }else{
                        return '-';
                    }
                }
            },
            {
                data: 'nama_dosen',
                name: 'nama_dosen',
                orderable: true,
                render: function (data, type, full, meta) {
                    var link = '{{ asset('assets/backoffice/media/avatars/blank.png') }}';
                    // var span = '<span class="text-muted fw-bold text-muted d-block fs-7">`+full.tipe_faculty+` - `+full.jja+` `+full.pendidikan+`</span>';
                    var data = `<div class="d-flex align-items-center">
                        <div class="symbol symbol-45px me-5">
                            <img src="`+link+`" alt="" />
                        </div>
                        <div class="d-flex justify-content-start flex-column">
                            <a style="color: black;" href="javascript:void(0)" class="fw-bolder text-hover-primary fs-6">`+full.nama_dosen+`</a>
                        </div>
                    </div>`;
                    return data;
                }
            },
            {
                data: 'nama_gugus_binaan',
                name: 'nama_gugus_binaan',
                orderable: true,
                render: function (data, type, full, meta) {
                    if(full.nama_gugus_binaan){
                        return full.nama_gugus_binaan;
                    }else{
                        return '-';
                    }
                }
            },
            {
                data: 'tipe_faculty',
                name: 'tipe_faculty',
                orderable: true,
                render: function (data, type, full, meta) {
                    if(full.tipe_faculty){
                        return full.tipe_faculty;
                    }else{
                        return '-';
                    }
                }
            },
            {
                data: 'jja',
                name: 'jja',
                orderable: true,
                render: function (data, type, full, meta) {
                    if(full.jja){
                        return full.jja;
                    }else{
                        return '-';
                    }
                }
            },
            {
                data: null,
                orderable: false,
                visible: true,
                render: function (data, type, full, meta) {
                    var btn_aksi = '';
                    // btn_aksi += `<button data-id="`+full.id_kol+`" onclick="onEdit(this)" class="btn btn-light btn-sm btn-active-light-warning">Edit</button>`;
                    // btn_aksi += `<button data-id="`+full.id_kol+`" onclick="onDetail(this)" class="btn btn-light btn-sm btn-active-light-primary" style="margin-left: 5px !important">Detail</button>`;
                    btn_aksi += `<div class="d-flex justify-content-left flex-shrink-0">
                        <a href="javascript:void(0)" onclick="onEdit(this)" data-id="`+full.kode_dosen+`" class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm me-1">
                            <span class="svg-icon svg-icon-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path opacity="0.3" d="M21.4 8.35303L19.241 10.511L13.485 4.755L15.643 2.59595C16.0248 2.21423 16.5426 1.99988 17.0825 1.99988C17.6224 1.99988 18.1402 2.21423 18.522 2.59595L21.4 5.474C21.7817 5.85581 21.9962 6.37355 21.9962 6.91345C21.9962 7.45335 21.7817 7.97122 21.4 8.35303ZM3.68699 21.932L9.88699 19.865L4.13099 14.109L2.06399 20.309C1.98815 20.5354 1.97703 20.7787 2.03189 21.0111C2.08674 21.2436 2.2054 21.4561 2.37449 21.6248C2.54359 21.7934 2.75641 21.9115 2.989 21.9658C3.22158 22.0201 3.4647 22.0084 3.69099 21.932H3.68699Z" fill="black" />
                                    <path d="M5.574 21.3L3.692 21.928C3.46591 22.0032 3.22334 22.0141 2.99144 21.9594C2.75954 21.9046 2.54744 21.7864 2.3789 21.6179C2.21036 21.4495 2.09202 21.2375 2.03711 21.0056C1.9822 20.7737 1.99289 20.5312 2.06799 20.3051L2.696 18.422L5.574 21.3ZM4.13499 14.105L9.891 19.861L19.245 10.507L13.489 4.75098L4.13499 14.105Z" fill="black" />
                                </svg>
                            </span>
                        </a>
                        <a href="javascript:void(0)" onclick="onDestroy(this)" data-id="`+full.kode_dosen+`" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm">
                            <span class="svg-icon svg-icon-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M5 9C5 8.44772 5.44772 8 6 8H18C18.5523 8 19 8.44772 19 9V18C19 19.6569 17.6569 21 16 21H8C6.34315 21 5 19.6569 5 18V9Z" fill="black" />
                                    <path opacity="0.5" d="M5 5C5 4.44772 5.44772 4 6 4H18C18.5523 4 19 4.44772 19 5V5C19 5.55228 18.5523 6 18 6H6C5.44772 6 5 5.55228 5 5V5Z" fill="black" />
                                    <path opacity="0.5" d="M9 4C9 3.44772 9.44772 3 10 3H14C14.5523 3 15 3.44772 15 4V4H9V4Z" fill="black" />
                                </svg>
                            </span>
                        </a>
                    </div>`;
                    return btn_aksi;
                }
            }
        ];
        table = $('#tableCourse').DataTable({
            responsive: true,
            serverSide: true,
            processing: true,
            pageLength: 10,
            // select: 'single',
            ajax: {
                url: '{{ route('dosen.init_table') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: function (d) {
                    d.search.value = $('#customSearchInput').val();
                    d.prodi = prodi;
                },
                error: function(xhr, status, error) {
                    if (xhr.status === 419 || xhr.status === 401) {
                        SUPER.showMessage({
                            success: false,
                            message: 'Sesi telah berakhir',
                            title: 'Gagal'
                        });
                        setLogout();
                    } else {
                        // Handle other error cases
                        // For example, you can display an error message to the user
                        console.error(error);
                    }
                }
            },
            columns: col,
        });
        unblockPage();
    }

    function getRowDataFromElement(element) {
        let $row = $(element).closest('tr');
        if ($row.hasClass('child')) {
            $row = $row.prev();
        }
        return $('#tableCourse').DataTable().row($row).data();
    }

    function fillDosenDetail(data) {
        const setValue = (field, value) => {
            const text = value === null || value === undefined || value === '' ? '-' : value;
            $('#modalDosenDetail [data-field="' + field + '"]').text(text);
        };

        setValue('kode_dosen', data.kode_dosen);
        setValue('nama_dosen', data.nama_dosen);
        setValue('nama_gugus_binaan', data.nama_gugus_binaan);
        setValue('nama_program', data.nama_program);
        setValue('tipe_faculty', data.tipe_faculty);
        setValue('jja', data.jja);
        setValue('pendidikan', data.pendidikan);
        setValue('jurusan', data.jurusan);
        setValue('jenis_kelamin', data.jenis_kelamin);
        setValue('email_1', data.email_1);
        setValue('no_hp', data.no_hp);
        setValue('alamat', data.alamat);
        setValue('status', data.status);
        setValue('campus', data.campus);
        setValue('lokasi', data.lokasi);
        setValue('tgl_mulai_mengajar', data.tgl_mulai_mengajar);
        $('#dosenKodeInput').val(data.kode_dosen || '');
    }

    function resetDosenExtra() {
        $('#dosenPhoto').attr('src', '{{ asset('assets/backoffice/media/avatars/blank.png') }}');
        $('#dosenPhotoInput').val('');
        $('#dosenVideoInput').val('');
        $('#dosenDescriptionInput').val('');
        $('#dosenAttributesInput').val('');
        $('#dosenAttributeIconInput').val('');
        renderDosenAttributesTable([]);
    }

    function renderDosenAttributesTable(items) {
        const $table = $('#dosenAttributesTable');
        $table.empty();
        if (!items || items.length === 0) {
            $table.append('<tr><td colspan="3" class="text-center text-muted">Belum ada attribute</td></tr>');
            return;
        }

        items.forEach(function (item, index) {
            const attr = item.attribute_dosen || '-';
            const rawIcon = item.attribute_icon || '';
            const icon = rawIcon.replace(/^bi\s+/, '').replace(/^bi-/, 'bi-');
            const iconView = icon ? '<i class="bi ' + icon + ' me-2"></i>' + icon : '-';
            $table.append(
                '<tr data-index="' + index + '" data-attr="' + attr + '" data-icon="' + icon + '">' +
                    '<td>' + attr + '</td>' +
                    '<td>' + iconView + '</td>' +
                    '<td>' +
                        '<button type="button" class="btn btn-sm btn-light-danger dosen-attr-remove">Hapus</button>' +
                    '</td>' +
                '</tr>'
            );
        });
    }

    function fillDosenExtra(identitas, attributes) {
        if (identitas && identitas.foto_dosen) {
            var photoUrl = identitas.foto_dosen;
            if (!/^https?:\/\//i.test(photoUrl) && photoUrl.charAt(0) !== '/') {
                photoUrl = '{{ url('/') }}/' + photoUrl;
            }
            $('#dosenPhoto').attr('src', photoUrl);
        }
        if (identitas && identitas.video_dosen) {
            $('#dosenVideoInput').val(identitas.video_dosen);
        }
        if (identitas && identitas.deskripsi_dosen) {
            $('#dosenDescriptionInput').val(identitas.deskripsi_dosen);
        }

        if (Array.isArray(attributes)) {
            renderDosenAttributesTable(attributes);
        } else {
            renderDosenAttributesTable([]);
        }
    }

    function onEdit(element) {
        const data = getRowDataFromElement(element);
        if (!data) {
            return;
        }
        fillDosenDetail(data);
        resetDosenExtra();
        if (window.bootstrap && document.getElementById('dosen-data-tab')) {
            new bootstrap.Tab(document.getElementById('dosen-data-tab')).show();
        }
        $('#modalDosenDetail').modal('show');

        $.ajax({
            url: '{{ route('dosen.detail') }}',
            type: 'POST',
            data: {
                kode_dosen: data.kode_dosen
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function (response) {
                fillDosenExtra(response.identitas, response.attributes);
            }
        });
    }

    $(document).on('change', '#dosenPhotoInput', function () {
        const file = this.files && this.files[0];
        if (!file) {
            return;
        }
        const reader = new FileReader();
        reader.onload = function (event) {
            $('#dosenPhoto').attr('src', event.target.result);
        };
        reader.readAsDataURL(file);
    });

    $(document).on('click', '#dosenAttributeAdd', function () {
        const attribute = $('#dosenAttributesInput').val().trim();
        const icon = $('#dosenAttributeIconInput').val();
        if (!attribute) {
            return;
        }

        const items = [];
        $('#dosenAttributesTable tr').each(function () {
            const $row = $(this);
            if ($row.find('.dosen-attr-remove').length === 0) {
                return;
            }
            items.push({
                attribute_dosen: $row.data('attr'),
                attribute_icon: $row.data('icon') || ''
            });
        });

        items.push({
            attribute_dosen: attribute,
            attribute_icon: icon
        });
        renderDosenAttributesTable(items);
        $('#dosenAttributesInput').val('');
        $('#dosenAttributeIconInput').val('');
    });

    $(document).on('click', '.dosen-attr-remove', function () {
        const $row = $(this).closest('tr');
        $row.remove();
        if ($('#dosenAttributesTable tr').length === 0) {
            renderDosenAttributesTable([]);
        }
    });

    function onAdd(){
        $('#modalHakakses').modal('hide');
        blockPage();
        var formData = new FormData();
        formData.append('dosen', $('#dosen')[0].files[0]);
        $.ajax({
            url: "{{ route('dosen.read') }}",
            type: "POST",
            data: formData,
            contentType: false,     // Important
            processData: false,     // Important
            headers:{
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },  
            success: function (response) {
                $('#formHakakses').trigger('reset');
                unblockPage();
                SUPER.showMessage({
                    success: true,
                    message: 'Sukses mengunggah data',
                    title: 'Berhasil'
                });
                init_table();
                // $('#image_pic').empty().html(response.html);
                // window.open(response.link);
                // $("#image_pic object").attr("data", response.data);
            }
        });
    }

</script>

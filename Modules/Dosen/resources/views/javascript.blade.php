<script type="text/javascript">
    $(document).ready(function() {
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
                    var link = '{{ asset('assets/media/avatars/blank.png') }}';
                    var data = `<div class="d-flex align-items-center">
                        <div class="symbol symbol-45px me-5">
                            <img src="`+link+`" alt="" />
                        </div>
                        <div class="d-flex justify-content-start flex-column">
                            <a style="color: black;" href="javascript:void(0)" class="fw-bolder text-hover-primary fs-6">`+full.nama_dosen+`</a>
                            <span class="text-muted fw-bold text-muted d-block fs-7">`+full.ft_dosen+` - `+full.jja_dosen+` `+full.pendidikan_dosen+`</span>
                        </div>
                    </div>`;
                    return data;
                }
            },
            {
                data: 'jurusan_dosen',
                name: 'jurusan_dosen',
                orderable: true,
                render: function (data, type, full, meta) {
                    if(full.jurusan_dosen){
                        return full.jurusan_dosen;
                    }else{
                        return '-';
                    }
                }
            },
            {
                data: 'telp_dosen',
                name: 'telp_dosen',
                orderable: true,
                render: function (data, type, full, meta) {
                    if(full.telp_dosen){
                        return full.telp_dosen;
                    }else{
                        return '-';
                    }
                }
            },
            {
                data: 'email_dosen',
                name: 'email_dosen',
                orderable: true,
                render: function (data, type, full, meta) {
                    if(full.email_dosen){
                        return full.email_dosen;
                    }else{
                        return '-';
                    }
                }
            },
            {
                data: null,
                orderable: false,
                visible: roles,
                render: function (data, type, full, meta) {
                    var btn_aksi = '';
                    // btn_aksi += `<button data-id="`+full.id_kol+`" onclick="onEdit(this)" class="btn btn-light btn-sm btn-active-light-warning">Edit</button>`;
                    // btn_aksi += `<button data-id="`+full.id_kol+`" onclick="onDetail(this)" class="btn btn-light btn-sm btn-active-light-primary" style="margin-left: 5px !important">Detail</button>`;
                    btn_aksi += `<div class="d-flex justify-content-left flex-shrink-0">
                        <a href="https://agency.youtzmedia.id/order-influencer/`+full.kode_dosen+`" target="_blank" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                            <span class="svg-icon svg-icon-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M17.5 11H6.5C4 11 2 9 2 6.5C2 4 4 2 6.5 2H17.5C20 2 22 4 22 6.5C22 9 20 11 17.5 11ZM15 6.5C15 7.9 16.1 9 17.5 9C18.9 9 20 7.9 20 6.5C20 5.1 18.9 4 17.5 4C16.1 4 15 5.1 15 6.5Z" fill="black" />
                                    <path opacity="0.3" d="M17.5 22H6.5C4 22 2 20 2 17.5C2 15 4 13 6.5 13H17.5C20 13 22 15 22 17.5C22 20 20 22 17.5 22ZM4 17.5C4 18.9 5.1 20 6.5 20C7.9 20 9 18.9 9 17.5C9 16.1 7.9 15 6.5 15C5.1 15 4 16.1 4 17.5Z" fill="black" />
                                </svg>
                            </span>
                        </a>
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

</script>
<script type="text/javascript">
    $(document).ready(function() {
        // init_table();
    });

    function init_table(){
        blockPage();
        var year = $('#year').val();
        var period = $('#period').val();
        var month = $('#month').val();
        if(!year){
            SUPER.showMessage({
                success: false,
                message: 'Tahun tidak boleh kosong',
                title: 'Gagal'
            });
            unblockPage();
            return;
        }
        if(!period){
            SUPER.showMessage({
                success: false,
                message: 'Periode tidak boleh kosong',
                title: 'Gagal'
            });
            unblockPage();
            return;
        }
        if(!month){
            SUPER.showMessage({
                success: false,
                message: 'Bulan tidak boleh kosong',
                title: 'Gagal'
            });
            unblockPage();
            return;
        }
        var prodi = $('#prodi').val();
        if ($.fn.DataTable.isDataTable('#tableCourse')) {
            $('#tableCourse').DataTable().destroy();
        }
        var col =[
            {
                data: 'nama_dosen',
                name: 'nama_dosen',
                orderable: true,
                render: function (data, type, full, meta) {
                    if(!full.mandiri_seminar_scopus){
                        var ms_sc = 0;
                    }else{
                        var ms_sc = full.mandiri_seminar_scopus;
                    }

                    if(full.nama_dosen){
                        // var nama = SUPER.trim_string(full.nama_dosen, 30);
                        var nama = full.nama_dosen;
                    }else{
                        var nama = '-';
                    }

                    if(parseInt(ms_sc) > parseInt(full.max_mandiri_scopus)){
                        var color = 'orange';
                    }else if(parseInt(ms_sc) < parseInt(full.max_mandiri_scopus)){
                        var color = 'red';
                    }else if(parseInt(ms_sc) == parseInt(full.max_mandiri_scopus)){
                        var color = 'green';
                    }else{
                        var color = 'black';
                    }

                    var link = '{{ asset('assets/media/avatars/blank.png') }}';
                    var data = `<div class="d-flex align-items-center">
                        <div class="symbol symbol-45px me-5">
                            <img src="`+link+`" alt="" />
                        </div>
                        <div class="d-flex justify-content-start flex-column">
                            <a style="color: `+color+`" href="javascript:void(0)" class="fw-bolder text-hover-primary fs-6">`+nama+`</a>
                            <span class="text-muted fw-bold text-muted d-block fs-7">`+full.kode_dosen+` - `+full.pendidikan_dosen+` - `+full.jurusan_dosen+`</span>
                        </div>
                    </div>`;
                    return data;
                }
            },
            {
                data: 'jja_dosen',
                name: 'jja_dosen',
                orderable: true,
                render: function (data, type, full, meta) {
                    if(full.jja_dosen){
                        return full.jja_dosen + ' - ' + full.ft_dosen;
                    }else{
                        return '-';
                    }
                }
            },
            {
                data: 'max_mandiri_scopus',
                name: 'max_mandiri_scopus',
                orderable: true,
                render: function (data, type, full, meta) {
                    if(full.max_mandiri_scopus){
                        return full.max_mandiri_scopus;
                    }else{
                        return '-';
                    }
                }
            },
            {
                data: 'mandiri_seminar_scopus',
                name: 'mandiri_seminar_scopus',
                orderable: true,
                render: function (data, type, full, meta) {
                    // if(full.ms_scopus){
                    //     return full.ms_scopus;
                    // }else{
                    //     return '-';
                    // }
                    return full.mandiri_seminar_scopus;
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
                url: '{{ route('dosenmaxscopus.init_table') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                data: function (d) {
                    d.year = year;
                    d.period = period;
                    d.month = month;
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
<script type="text/javascript">
    $(document).ready(function() {
        init_chart();
    });

    function init_chart(){
        var xValues = ["Italy", "France", "Spain", "USA", "Argentina"];
        var yValues = [55, 49, 44, 24, 15];
        var barColors = [
            "#b91d47",
            "#00aba9",
            "#2b5797",
            "#e8c3b9",
            "#1e7145"
        ];

        new Chart("myChart", {
            type: "pie",
            data: {
                labels: xValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: yValues
                }]
            },
            options: {
                title: {
                    display: true,
                    text: "World Wide Wine Production 2018"
                }
            }
        });
    }

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
                    if(full.nama_dosen){
                        // var nama = SUPER.trim_string(full.nama_dosen, 30);
                        var nama = full.nama_dosen;
                    }else{
                        var nama = '-';
                    }

                    // Punya Scopus tidak punya nscopus
                    if(parseFloat(full.jml_nscopus) == 0 && parseFloat(full.jml_scopus) > 0){
                        var color = 'orange';
                    // Punya Nscopus tidak punya Scopus
                    }else if(parseFloat(full.jml_scopus) == 0 && parseFloat(full.jml_nscopus) > 0){
                        var color = 'red';
                    // Punya Scopus punya nscopus
                    }else if(parseFloat(full.jml_nscopus) > 0 && parseFloat(full.jml_scopus) > 0){
                        var color = 'green';
                    // Tidak punya sama sekali
                    }else if(parseFloat(full.jml_nscopus) == 0 && parseFloat(full.jml_scopus) == 0){
                        var color = 'black';
                    }

                    var link = '{{ asset('assets/media/avatars/blank.png') }}';
                    var data = `<div class="d-flex align-items-center">
                        <div class="symbol symbol-45px me-5">
                            <img src="`+link+`" alt="" />
                        </div>
                        <div class="d-flex justify-content-start flex-column">
                            <a style="color: `+color+`" href="javascript:void(0)" class="fw-bolder text-hover-primary fs-6">`+nama+`</a>
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
                data: 'jml_nscopus',
                name: 'jml_nscopus',
                orderable: true,
                render: function (data, type, full, meta) {
                    if(full.jml_nscopus){
                        return full.jml_nscopus;
                    }else{
                        return '0';
                    }
                }
            },
            {
                data: 'jml_scopus',
                name: 'jml_scopus',
                orderable: true,
                render: function (data, type, full, meta) {
                    if(full.jml_scopus){
                        return full.jml_scopus;
                    }else{
                        return '0';
                    }
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
                url: '{{ route('dosennonpublikasi.init_table') }}',
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
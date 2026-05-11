<script type="text/javascript">
    $(document).ready(function() {
        // init_chart();
    });

    function init_chart(year, month, period, prodi = null, kondisi = null) {
        $('#chartContainer').empty().html('<div class="row mb-5"><div class="col-12 col-xl d-flex align-items-center"><div class=" d-flex align-items-center" style="margin-left: 80px !important;"><canvas id="myChart" style="height: 350px !important;"></canvas></div></div></div>');
        $.ajax({
            url: '{{ route('dosennonpublikasi.init_chart') }}', 
            method: 'POST',
            data: {
                year: year,
                month: month,
                period: period,
                prodi: prodi,
                kondisi: kondisi
            },
            headers:{
                'X-CSRF-TOKEN' : '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;

                    const xValues = data.map(item => item.category);
                    const yValues = data.map(item => item.jumlah_dosen);

                    const barColors = [
                        "#4e73df",  // Blue
                        "#1cc88a",  // Green
                        "#36b9cc",  // Cyan
                        "#f6c23e"   // Yellow
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
                                text: "Perbandingan Dosen Scopus dan Non Scopus"
                            }
                        }
                    });
                    var canvas1 = document.getElementById("myChart");
                    if (canvas1.getContext) {
                        // var ctx = canvas1.getContext("2d");                
                        // var myImage = canvas1.toDataURL("image/png");
                        // var myImage = canvas1.toDataURL("image/png").replace("image/png", "image/octet-stream");
                        // window.open(myImage);    
                    }
                } else {
                    console.error('Data fetch unsuccessful:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            }
        });
    }

    function init_table(){
        blockPage();
        var year = $('#year').val();
        var period = $('#period').val();
        var month = $('#month').val();
        var kondisi = $('#kondisi').val();
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
        init_chart(year, month, period, prodi, kondisi);
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
        table = SUPER.initDataTable('#tableCourse', {
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
                    d.kondisi = kondisi;
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
        }, {
            stateKey: 'dosennonpublikasi.tableCourse',
            stateSignature: {
                year: year,
                period: period,
                month: month,
                prodi: prodi,
                kondisi: kondisi
            }
        });
        unblockPage();
    }

</script>

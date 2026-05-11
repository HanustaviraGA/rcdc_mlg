<script type="text/javascript">
    $(document).ready(function() {
        init_chart();
        init_table();
    });

    init_chart = () => {
        $('#canvas_chart').empty().html('<canvas id="chartFM"></canvas>');
        const ctx = document.getElementById('chartFM');

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [{!! '"' . implode('", "', $array_nama) . '"' !!}],
                datasets: [{
                    label: 'Jumlah FM',
                    data: [{{ implode(', ', $array_fm) }}],
                }]
            },
        });
    }

    init_table = () => {
        table = SUPER.initDataTable('#tableDashboard', {
            responsive: true,
            serverSide: true,
            processing: true,
            // pageLength: 5,
            paging: false,
            info : false,
            // select: 'single',
            ajax: {
                url: '{{ route('home.init_table') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
            columns: [
                {
                    data: null,
                    name: 'kode_dosen',
                    orderable: true,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        return SUPER.trim_string(full.kode_dosen, 30);
                    }
                },
                {
                    data: null,
                    name: 'nama_dosen',
                    orderable: true,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        return SUPER.trim_string(full.nama_dosen, 30);
                    }
                },
                {
                    data: null,
                    name: 'nama_gugus_binaan',
                    orderable: true,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        return SUPER.trim_string(full.nama_gugus_binaan, 30);
                    }
                },
                {
                    data: null,
                    name: 'jja',
                    orderable: true,
                    searchable: false,
                    render: function (data, type, full, meta) {
                        return full.jja;
                    }
                },
            ],
        }, {
            stateKey: 'home.tableDashboard',
            stateSignature: 'home',
            preservePage: false
        });
    }

</script>

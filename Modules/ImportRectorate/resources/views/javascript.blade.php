<script>
    const quarterMonths = {
        1: [1, 2, 3],
        2: [4, 5, 6],
        3: [7, 8, 9],
        4: [10, 11, 12],
    };
    const monthLabels = {
        1: 'January',
        2: 'February',
        3: 'March',
        4: 'April',
        5: 'May',
        6: 'June',
        7: 'July',
        8: 'August',
        9: 'September',
        10: 'October',
        11: 'November',
        12: 'December',
    };

    function updateMonthOptions() {
        const period = parseInt($('#period').val(), 10);
        const allowed = quarterMonths[period] || [];
        const current = parseInt($('#month').val(), 10);

        $('#month').empty();
        allowed.forEach((value) => {
            const label = monthLabels[value] || value;
            $('#month').append(new Option(label, value));
        });

        if (allowed.indexOf(current) !== -1) {
            $('#month').val(current);
        } else if (allowed.length) {
            $('#month').val(allowed[0]);
        }
    }

    $(document).ready(function () {
        $('#year').val('{{ now()->subMonth()->year }}');
        $('#period').val('{{ (int) ceil(now()->subMonth()->month / 3) }}');
        updateMonthOptions();
        $('#month').val('{{ now()->subMonth()->month }}');
        $('#period').on('change', updateMonthOptions);
    });

    function onAdd(){
        blockPage();
        var formData = new FormData();
        formData.append('rectorate', $('#rectorate')[0].files[0]);
        formData.append('year', $('#year').val());
        formData.append('month', $('#month').val());
        formData.append('period', $('#period').val());
        formData.append('fmmhs', $('#fmmhs').val());
        $.ajax({
            url: "{{ route('importrectorate.create') }}",
            type: "POST",
            data: formData,
            contentType: false,     // Important
            processData: false,     // Important
            headers:{
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },  
            success: function (response) {
                unblockPage();
                SUPER.showMessage({
                    success: true,
                    message: response.message || 'Sukses mengunggah data',
                    title: 'Berhasil'
                });
                if (response.summary) {
                    const s = response.summary;
                    $('#importSummary').text([
                        response.message,
                        'Sumber: ' + s.sheet + '; dosen KPI: ' + (s.kpi?.lecturers || 0),
                        'Rekap Scopus: ' + s.workbook_totals.titles + ' kontribusi judul; ' + s.workbook_totals.first_author + ' first author; bobot asli ' + s.workbook_totals.rectorate_scopus.toLocaleString('id-ID', {maximumFractionDigits: 4}),
                        'Dilewati: ' + s.excluded_campus + ' baris kampus lain; ' + s.excluded_submitted + ' kategori Submitted lain; ' + s.duplicates + ' duplikat.',
                        'Kolom belum lengkap: ' + (Object.entries(s.missing).map(([key, count]) => key + ' (' + count + ' baris)').join(', ') || 'Tidak ada'),
                        'Kode dosen belum ada di master: ' + (s.unmatched_codes.join(', ') || 'Tidak ada'),
                        ...(s.warnings || [])
                    ].join('\n'));
                }
                // $('#image_pic').empty().html(response.html);
                // window.open(response.link);
                // $("#image_pic object").attr("data", response.data);
            },
            error: function (response) {
                unblockPage();
                const message = response.responseJSON?.message || 'Import gagal. Periksa format file dan periode.';
                $('#importSummary').text(message);
                SUPER.showMessage({success: false, message: message, title: 'Import gagal'});
            }
        });
    }
</script>

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
        updateMonthOptions();
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
                    message: 'Sukses mengunggah data',
                    title: 'Berhasil'
                });
                // $('#image_pic').empty().html(response.html);
                // window.open(response.link);
                // $("#image_pic object").attr("data", response.data);
            }
        });
    }
</script>

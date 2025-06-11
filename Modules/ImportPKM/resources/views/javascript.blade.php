<script>
    function onAdd(){
        blockPage();
        var formData = new FormData();
        formData.append('pkm', $('#pkm')[0].files[0]);
        formData.append('year', $('#year').val());
        formData.append('period', $('#period').val());
        $.ajax({
            url: "{{ route('importpkm.create') }}",
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
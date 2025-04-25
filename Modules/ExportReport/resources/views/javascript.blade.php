<script>
    function onAdd(){
        blockPage();
        $.ajax({
            url: "{{ route('exportreport.generate_pdf') }}",
            type: "POST",
            headers:{
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },  
            success: function (response) {
                unblockPage();
                SUPER.showMessage({
                    success: true,
                    message: 'Sukses mengunduh data',
                    title: 'Berhasil'
                });
                // $('#image_pic').empty().html(response.html);
                // window.open(response.link);
                // $("#image_pic object").attr("data", response.data);
            }
        });
    }
</script>
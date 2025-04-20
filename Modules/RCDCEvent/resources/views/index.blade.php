<div class="row table_data mt-5 mb-5" data-roleable="false" data-role="Company-Read">
    <div class=" col-12" id="tableCourseContainer">
        <div class="card card-bordered mt-5">
            <div class="card-body">
                {{-- <div class="fv-row mb-5 col-md-12 ">
                    <label for="" class="required form-label mb-3 fw-bold">Nama Lengkap</label>
                    <input required type="text" name="name" id="name" class="form-control bg-white border border-2 py-4 px-6 rounded-3 fw-light fs-6" placeholder="Input Data" />
                </div> --}}
                <div class="fv-row mb-5 col-md-12 ">
                    <label for="" class="required form-label mb-3 fw-bold">Nama Event</label>
                    <input required type="text" name="eventname" id="eventname" class="form-control bg-white border border-2 py-4 px-6 rounded-3 fw-light fs-6" placeholder="Input Data" />
                </div>
                <div class="fv-row mb-5 col-md-12 ">
                    <label for="" class="required form-label mb-3 fw-bold">Nomor Sertifikat</label>
                    <input required type="number" name="number" id="number" class="form-control bg-white border border-2 py-4 px-6 rounded-3 fw-light fs-6" placeholder="Input Data" />
                </div>
                <button class="btn btn-primary w-20" onclick="onAdd()" id="toggleFormButton"><i class="las la-plus fs-2"></i> Generate</button>
            </div>
        </div>
        <div class="card card-bordered mt-5">
            <div class="card-body" id="image_pic">
                <object data="" type="application/pdf" width="100%" height="500px"></object>
            </div>
        </div>
    </div>
</div>
<script>
    function onAdd(){
        blockPage();
        $.ajax({
            url: "{{ route('rcdcevent.init_table') }}",
            type: "POST",
            data: {
                name: $('#name').val(),
                eventname: $('#eventname').val(),
                number: $('#number').val(),
            },
            headers:{
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },  
            success: function (response) {
                unblockPage();
                // $('#image_pic').empty().html(response.html);
                // window.open(response.link);
                // $("#image_pic object").attr("data", response.data);
            }
        });
    }
</script>
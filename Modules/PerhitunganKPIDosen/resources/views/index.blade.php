<div class="row table_data mt-5 mb-5" data-roleable="false" data-role="Company-Read">
    <div class=" col-12" id="tableCourseContainer">
        <div class="card card-bordered">
            <div class="card-body">
                <form action="javascript:init_table()">
                    <div class="row mb-5">
                        <div class="col-12 col-xl d-flex align-items-center justify-content-between">
                            <input required type="text" name="kode_dosen" id="kode_dosen" class="form-control form-control bg-gray-100 me-5" placeholder="Kode Dosen">
                            <select required name="year" id="year" class="form-control form-control bg-gray-100" placeholder="Tahun">
                                <option disabled>Pilih Tahun</option>
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option value="2025">2025</option>
                                <option selected value="2026">2026</option>
                            </select>
                        </div>
                        <div class="col-12 col-xl-3 col-md-6 col-lg-6 mt-5 my-md-0 mx-auto">
                            <button type="submit" class="btn btn-primary w-100" id="toggleFormButton">
                                <i class="las la-search fs-2"></i> Search
                            </button>
                        </div>
                    </div>
                    <div class="row mb-5">
                        <div class="col-12 col-xl-6 col-md-6 col-lg-6 mt-5 my-md-0">
                            <input readonly type="text" id="nama_dosen" class="form-control form-control bg-gray-100 me-5" placeholder="Nama Dosen">
                        </div>
                        <div class="col-12 col-xl-3 col-md-4 col-lg-3 mt-5 my-md-0">
                            <input readonly type="text" id="skor_dosen" class="form-control form-control bg-gray-100 me-5" placeholder="Score : -">
                        </div>
                    </div>
                </form>
                <div class="table-responsive border-2 border-top">
                    <table class="table table-striped table-row-bordered align-middle rounded tdFirstCenter" id="tableCourse">
                        <thead>
                            <tr class="fw-bolder text-dark">
                                <th>Title</th>
                                <th>Publication Type</th>
                                <th>Scopus / Non Scopus</th>
                                <th>Quartile</th>
                                <th>Weight</th>
                            </tr>
                        </thead>
                        <tbody id="table_kpi">
                            <tr id="package_empty">
                                <td colspan="5" id="empty-message-package" class="text-center">No Records Found</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card card-bordered mt-5">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-xl d-flex align-items-center justify-content-between">
                        <div class=" d-flex align-items-center">
                            <label for="" class="required form-label fw-bold" style="text-align: justify;">Penilaian kinerja publikasi Faculty Member ditentukan berdasarkan Jenjang Jabatan Akademik/ Pendidikan.</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function init_table(){
        blockPage();
        $.ajax({
            url: "{{ route('perhitungankpidosen.init_table') }}",
            type: "POST",
            data: {
                kode_dosen: $('#kode_dosen').val(),
                year: $('#year').val(),
            },
            // dataType: "html",
            headers:{
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function (response) {
                unblockPage();
                $('#nama_dosen').empty().val(response.main_data.nama_dosen).trigger('change');
                $('#skor_dosen').empty().val('Score : ' + response.kpi.kpi).trigger('change');
                $('#table_kpi').empty().html(response.html);
                // alert(response.data);
            },
            error: function(response){
                unblockPage();
                SUPER.showMessage({
                    message: response.responseJSON.message,
                    type: 'error'
                });
            }
        });
    }
</script>

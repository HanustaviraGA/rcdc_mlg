<div class="row table_data mt-5 mb-5" data-roleable="false" data-role="Company-Read">
    <div class=" col-12" id="tableCourseContainer">
        <div class="card card-bordered">
            <div class="card-body">
                <form action="javascript:init_table()">
                    <div class="row mb-5">
                        <div class="col-12 col-xl d-flex align-items-center justify-content-between">
                            <input required type="text" name="kode_dosen" id="kode_dosen" class="form-control form-control bg-gray-100 me-5" placeholder="Kode Dosen">
                            <select required name="period" id="period" class="form-control form-control bg-gray-100 me-5" placeholder="Periode">
                                <option disabled>Pilih Periode</option>
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                            <select required name="year" id="year" class="form-control form-control bg-gray-100" placeholder="Tahun">
                                <option disabled>Pilih Tahun</option>
                                <option value="2020">2020</option>
                                <option value="2021">2021</option>
                                <option value="2022">2022</option>
                                <option value="2023">2023</option>
                                <option value="2024">2024</option>
                                <option selected value="2025">2025</option>
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
                                <th>Periode</th>
                                <th>Judul PKM</th>
                                <th>Jenis PKM</th>
                                <th>Peserta</th>
                                <th>Skema Pendanaan</th>
                                <th>Nama Mahasiswa</th>
                            </tr>
                        </thead>
                        <tbody id="table_pkm">
                            <tr id="package_empty">
                                <td colspan="6" id="empty-message-package" class="text-center">Tidak ada Data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- <div class="card card-bordered mt-5">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-xl d-flex align-items-center justify-content-between">
                        <div class=" d-flex align-items-center">
                            <label for="" class="required form-label fw-bold" style="text-align: justify;">Penilaian kinerja publikasi Faculty Member ditentukan berdasarkan Jenjang Jabatan Akademik/ Pendidikan.</label>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6 col-md-6 col-lg-6 mt-5 my-md-0">
                        <input readonly type="text" id="nama_dosen" class="form-control form-control bg-gray-100 me-5" placeholder="Nama Dosen">
                    </div>
                    <div class="col-12 col-xl-2 col-md-4 col-lg-3 mt-5 my-md-0">
                        <input readonly type="text" id="skor_dosen" class="form-control form-control bg-gray-100 me-5" placeholder="Score : -">
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
</div>
<script>
    function init_table(){
        blockPage();
        $.ajax({
            url: "{{ route('perhitungankpipkm.init_table') }}",
            type: "POST",
            data: {
                kode_dosen: $('#kode_dosen').val(),
                year: $('#year').val(),
                period: $('#period').val(),
            },
            // dataType: "html",
            headers:{
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function (response) {
                unblockPage();
                $('#nama_dosen').empty().val(response.dosen).trigger('change');
                $('#skor_dosen').empty().val('Score : ' + response.skor).trigger('change');
                $('#table_pkm').empty().html(response.html);
                // alert(response.data);
                // console.log(response);
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

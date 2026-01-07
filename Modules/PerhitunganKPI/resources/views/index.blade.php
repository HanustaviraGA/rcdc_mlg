<div class="row table_data mb-5" data-roleable="false" data-role="Company-Read">
    <div class=" col-12" id="tableCourseContainer">
        <form>
            <div class="card card-bordered">
                <div class="card-body">
                    <div class="row mb-5">
                        <div class="col-12 col-xl d-flex align-items-center justify-content-between ">
                            <div class=" d-flex align-items-center">
                                <p> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M15 2.28572V3.71428C15 4.97322 11.8646 6 8 6C4.13541 6 1 4.97322 1 3.71428V2.28572C1 1.02678 4.13541 0 8 0C11.8646 0 15 1.02678 15 2.28572ZM15 5.5V8.71428C15 9.97322 11.8646 11 8 11C4.13541 11 1 9.97322 1 8.71428V5.5C2.50391 6.53572 5.2565 7.01788 8 7.01788C10.7435 7.01788 13.4961 6.53572 15 5.5ZM15 10.5V13.7143C15 14.9732 11.8646 16 8 16C4.13541 16 1 14.9732 1 13.7143V10.5C2.50391 11.5357 5.2565 12.0179 8 12.0179C10.7435 12.0179 13.4961 11.5357 15 10.5Z" fill="black" />
                                    </svg></p>
                                <h3 class="mt-1 mb-5 ms-5">Admin KPI</h3>
                            </div>
                        </div>
                        <div class="col-12 col-xl-3 col-md-6 col-lg-6 mt-5 my-md-0">
                            <button type="button" class="btn btn-primary w-100" onclick="$('#modalHakakses').modal('show')" id="toggleFormButton"><i class="las la-plus fs-2"></i> Filter</button>
                        </div>
                    </div>
                    <div class="table-responsive border-2 border-top">
                        <table class="table table-striped table-row-bordered align-middle rounded tdFirstCenter" id="tableCourse">
                            <thead>
                                <tr class="fw-bolder text-dark">
                                    <th>No.</th>
                                    <th style="width: 300px !important;">Dosen</th>
                                    <th>Prodi</th>
                                    {{-- <th style="width: 150px !important;">JJA - FT</th> --}}
                                    <th>Non Scopus</th>
                                    <th>Scopus</th>
                                    <th>Skor KPI</th>
                                </tr>
                            </thead>
                            <tbody id="table_kpi">
                                <tr id="package_empty">
                                    <td colspan="7" id="empty-message-package" class="text-center">Tidak ada Data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card card-bordered mt-5">
                <div class="card-body">
                    <button type="button" class="btn btn-primary w-20" onclick="javascript:void(0)" id="toggleFormButton"><i class="las la-plus fs-2"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="modalHakakses" tabindex="-1" aria-labelledby="modalHakakses" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="javascript:onAdd()" method="post" id="formHakakses" name="formHakakses" autocomplete="off" enctype="multipart/form-data">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalHakakses">Filter</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="role_id" name="role_id">
                    <div class="fv-row mb-7 fv-plugins-icon-container">
                        <label for="" class="required form-label">Year</label>
                        <select required id="filter_year" name="filter_year" class="form-control form-control-outline">
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                            <option selected value="2026">2026</option>
                        </select>
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>
                    <div class="fv-row mb-7 fv-plugins-icon-container">
                        <label for="" class="required form-label">Period</label>
                        <select required id="filter_period" name="filter_period" class="form-control form-control-outline">
                            <option value="1">Quarter 1</option>
                            <option value="2">Quarter 2</option>
                            <option value="3">Quarter 3</option>
                            <option value="4">Quarter 4</option>
                        </select>
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>
                    <div class="fv-row mb-7 fv-plugins-icon-container">
                        <label for="" class="required form-label">Until Month</label>
                        <select required id="filter_month" name="filter_month" class="form-control form-control-outline">
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>
                    <div class="fv-row mb-7 fv-plugins-icon-container">
                        <label for="" class="form-label">Program Studi</label>
                        <select id="filter_prodi" name="filter_prodi" class="form-control form-control-outline">
                            <option value="Semua Prodi">Semua Prodi</option>
                            <option value="DI">DI</option>
                            <option value="CS">CS</option>
                            <option value="DKV">DKV</option>
                            <option value="Ilkom">ILKOM</option>
                            <option value="CBDC">CBDC</option>
                            <option value="LC">LC</option>
                            <option value="PR">PR</option>
                            <option value="BC">BC</option>
                        </select>
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Show</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    function onAdd(){
        $('#modalHakakses').modal('hide')
        blockPage();
        $.ajax({
            url: "{{ route('perhitungankpi.init_table') }}",
            type: "POST",
            // dataType: "html",
            headers:{
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            data:{
                year: $('#filter_year').val(),
                period: $('#filter_period').val(),
                month: $('#filter_month').val(),
                prodi: $('#filter_prodi').val(),
            },
            success: function (response) {
                unblockPage();
                $('#table_kpi').empty().html(response.html);
                // alert(response.data);
            }
        });
    }
</script>
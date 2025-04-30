<div class="row table_data mt-5 mb-5" data-roleable="false" data-role="Company-Read">
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
                                <h3 class="mt-1 mb-5 ms-5">KPI</h3>
                            </div>
                        </div>
                        <div class="col-12 col-xl-3 col-md-6 col-lg-6 mt-5 my-md-0">
                            <button type="button" class="btn btn-primary w-100" onclick="onAdd()" id="toggleFormButton"><i class="las la-plus fs-2"></i> Tampilkan</button>
                        </div>
                    </div>
                    <div class="table-responsive border-2 border-top">
                        <table class="table table-striped table-row-bordered align-middle rounded tdFirstCenter" id="tableCourse">
                            <thead>
                                <tr class="fw-bolder text-dark">
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
                                    <td colspan="6" id="empty-message-package" class="text-center">Tidak ada Data</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="card card-bordered mt-5">
                <div class="card-body">
                    <button class="btn btn-primary w-20" onclick="onAdd()" id="toggleFormButton"><i class="las la-plus fs-2"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    function onAdd(){
        blockPage();
        $.ajax({
            url: "{{ route('perhitungankpi.init_table') }}",
            type: "POST",
            // dataType: "html",
            headers:{
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },  
            success: function (response) {
                unblockPage();
                $('#table_kpi').empty().html(response.html);
            }
        });
    }
</script>
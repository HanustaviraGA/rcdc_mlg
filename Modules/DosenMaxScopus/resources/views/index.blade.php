<div class="row table_data mt-5 mb-5" data-roleable="false" data-role="Company-Read">
    <div class=" col-12" id="tableCourseContainer">
        <div class="card card-bordered">
            <div class="card-body">
                <div class="row mb-5">
                    <div class="col-12 col-xl d-flex align-items-center justify-content-between">
                        <div class=" d-flex align-items-center">
                            <label for="" class="required form-label mb-3 fw-bold" style="text-align: justify;">Konferensi / seminar yang dihitung merupakan konferensi / seminar yang dilakukan dengan sumber paper Penelitian Mandiri</label>
                        </div>
                    </div>
                </div>
                <div class="row mb-5">
                    <div class="col-12 col-xl d-flex align-items-center justify-content-between ">
                        <select required name="year" id="year" class="form-control form-control bg-gray-100 me-5" placeholder="Input Data">
                            <option selected disabled>Pilih Tahun</option>
                            <option value="2022">2022</option>
                            <option value="2023">2023</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                        </select>
                        <select required name="period" id="period" class="form-control form-control bg-gray-100 me-5" placeholder="Input Data">
                            <option selected disabled>Pilih Periode</option>
                            <option value="1">Quarter 1</option>
                            <option value="2">Quarter 2</option>
                            <option value="3">Quarter 3</option>
                            <option value="4">Quarter 4</option>
                        </select>
                        <select required name="month" id="month" class="form-control form-control bg-gray-100 me-5" placeholder="Input Data">
                            <option selected disabled>Pilih Bulan</option>
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                        <select required id="prodi" class="form-control form-control bg-gray-100">
                            <option selected disabled>Pilih Prodi</option>
                            <option>Semua Prodi</option>
                            <option value="DI">DI</option>
                            <option value="CS">CS</option>
                            <option value="DKV">DKV</option>
                            <option value="Ilkom">ILKOM</option>
                            <option value="CBDC">CBDC</option>
                            <option value="LC">LC</option>
                            <option value="PR">PR</option>
                            <option value="BC">BC</option>
                        </select>
                    </div>
                    {{-- <div class="col-12 col-xl-4 col-md-6 col-lg-6 d-flex align-items-center mt-5 mt-md-0">
                        <select id="prodi" onchange="init_table()" class="form-control form-control bg-gray-100">
                            <option value="DI" selected>DI</option>
                            <option value="CS">CS</option>
                            <option value="DKV">DKV</option>
                            <option value="Ilkom">ILKOM</option>
                            <option value="CBDC">CBDC</option>
                            <option value="LC">LC</option>
                            <option value="PR">PR</option>
                            <option value="BC">BC</option>
                        </select>
                    </div> --}}
                    <div class="col-12 col-xl-3 col-md-6 col-lg-6 mt-5 my-md-0">
                        <button onclick="init_table()" type="button" class="btn btn-primary w-100" id="toggleFormButton"><i class="las la-plus fs-2"></i> Tampilkan</button>
                    </div>
                </div>
                <div id="chartContainer">
                </div>
                <div class="table-responsive border-2 border-top">
                    <table class="table table-striped table-row-bordered align-middle rounded tdFirstCenter" id="tableCourse">
                        <thead>
                            <tr class="fw-bolder text-dark">
                                <th style="width: 300px !important;">Dosen</th>
                                {{-- <th>Prodi</th> --}}
                                <th style="width: 150px !important;">JJA - FT</th>
                                <th>Batas Bobot</th>
                                <th>Bobot Dimiliki</th>
                                {{-- <th>Aksi</th> --}}
                            </tr>
                        </thead>
                        <tbody id="table_dosen">
                            <tr id="package_empty">
                                <td colspan="6" id="empty-message-package" class="text-center">Tidak ada Data</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('dosenmaxscopus::javascript')
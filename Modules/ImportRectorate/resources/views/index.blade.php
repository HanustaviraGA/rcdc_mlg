<div class="mb-5"><a class="btn btn-light-primary" href="{{ route('research-import.index') }}">Import Riset / Hibah Rectorate (sheet Detail)</a></div>
<div class="row table_data mb-5" data-roleable="false" data-role="Company-Read">
    <div class=" col-12" id="tableCourseContainer">
        <div class="card card-bordered">
            <div class="card-body">
                <div class="fv-row mb-5 col-md-12 ">
                    <label for="" class="required form-label mb-3 fw-bold">Year</label>
                    <select required name="year" id="year" class="form-control bg-white border border-2 py-4 px-6 rounded-3 fw-light fs-6" placeholder="Input Data">
                        @for ($year = 2022; $year <= (int) date('Y'); $year++)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>
                <div class="fv-row mb-5 col-md-12 ">
                    <label for="" class="required form-label mb-3 fw-bold">Period</label>
                    <select required name="period" id="period" class="form-control bg-white border border-2 py-4 px-6 rounded-3 fw-light fs-6" placeholder="Input Data">
                        <option value="1">Quarter 1</option>
                        <option value="2">Quarter 2</option>
                        <option value="3">Quarter 3</option>
                        <option value="4">Quarter 4</option>
                    </select>
                </div>
                <div class="fv-row mb-5 col-md-12 ">
                    <label for="" class="required form-label mb-3 fw-bold">Until Month</label>
                    <select required name="month" id="month" class="form-control bg-white border border-2 py-4 px-6 rounded-3 fw-light fs-6" placeholder="Input Data">
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
                </div>
                <div class="fv-row mb-5 col-md-12 ">
                    <label for="" class="required form-label mb-3 fw-bold">FM / Mahasiswa</label>
                    <select required name="fmmhs" id="fmmhs" class="form-control bg-white border border-2 py-4 px-6 rounded-3 fw-light fs-6" placeholder="Input Data">
                        <option value="FM">FM</option>
                        <option value="MHS">Mahasiswa</option>
                    </select>
                </div>
                <div class="fv-row mb-5 col-md-12 ">
                    <label for="" class="required form-label mb-3 fw-bold">File (.xlsx)</label>
                    <input type="file" accept=".xlsx" required name="rectorate" id="rectorate" class="form-control bg-white border border-2 py-4 px-6 rounded-3 fw-light fs-6" placeholder="Input Data">
                    <p class="text-muted mt-3">Import FM mengutamakan sheet <strong>MALANG</strong>; jika tidak ada, memakai <strong>Raw</strong>. Filter: Kampus MALANG dan Submitted Non Scopus FM / Scopus FM. Sheet <strong>KPI</strong> dibaca untuk data RTTO dan daftar dosen, termasuk nilai nol. Rekap FIRST AUTHOR, TITLE &amp; BOBOT, dan PIVOT dihitung dari data publikasi dengan bobot asli. Bobot akhir memakai nilai tertinggi Rectorate–RTTO per kategori; Score KPI mengikuti Score KPI RTTO. File Raw lama tetap didukung.</p>
                    <p class="text-muted">Tahun dan Until Month adalah periode snapshot laporan; pilih quarter yang sesuai. Import ulang mengganti publikasi dan data KPI periode yang sama setelah seluruh file lolos validasi. Jika file pengganti tidak memiliki sheet KPI, data RTTO periode tersebut ikut dihapus.</p>
                    <a href="{{ route('publication-dashboard') }}">Buka dashboard KPI publikasi</a>
                    <pre id="importSummary" class="mt-3" style="white-space:pre-wrap" aria-live="polite"></pre>
                </div>
                <button class="btn btn-primary w-20" onclick="onAdd()" id="toggleFormButton"><i class="las la-plus fs-2"></i> Simpan</button>
            </div>
        </div>
        {{-- <div class="card card-bordered mt-5">
            <div class="card-body" id="image_pic">
                <object data="" type="application/pdf" width="100%" height="500px"></object>
            </div>
        </div> --}}
    </div>
</div>
@include('importrectorate::javascript')

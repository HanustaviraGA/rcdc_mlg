<div class="row table_data mt-5 mb-5" data-roleable="false" data-role="Company-Read">
    <div class=" col-12" id="tableCourseContainer">
        <div class="card card-bordered mt-5">
            <div class="card-body">
                <div class="fv-row mb-5 col-md-12 ">
                    <label for="" class="required form-label mb-3 fw-bold">Tahun</label>
                    <select required name="year" id="year" class="form-control bg-white border border-2 py-4 px-6 rounded-3 fw-light fs-6" placeholder="Input Data">
                        <option value="2022">2022</option>
                        <option value="2023">2023</option>
                        <option value="2024">2024</option>
                        <option value="2025">2025</option>
                    </select>
                </div>
                <div class="fv-row mb-5 col-md-12 ">
                    <label for="" class="required form-label mb-3 fw-bold">Periode</label>
                    <select required name="period" id="period" class="form-control bg-white border border-2 py-4 px-6 rounded-3 fw-light fs-6" placeholder="Input Data">
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                </div>
                <div class="fv-row mb-5 col-md-12 ">
                    <label for="" class="required form-label mb-3 fw-bold">File (.csv)</label>
                    <input type="file" accept=".csv" required name="pkm" id="pkm" class="form-control bg-white border border-2 py-4 px-6 rounded-3 fw-light fs-6" placeholder="Input Data">
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
@include('importpkm::javascript')
<div id="monthly-import">
    <div class="card card-bordered mb-6">
        <div class="card-body">
            <h2 class="mb-3">Upload laporan bulanan</h2>
            <p class="text-muted">Tiga sumber per bulan: publikasi FM, publikasi mahasiswa, dan hibah penelitian.</p>
            <div class="alert alert-primary">
                Pilih tahun dan bulan laporan, lalu unggah Publikasi FM, Publikasi MHS, atau Hibah melalui tombol masing-masing. Setiap jenis data dapat diunggah terpisah tanpa menunggu file lainnya. Upload ulang mengganti sumber sejenis pada bulan yang sama.
                Setelah file dipilih, sistem akan menampilkan <strong>preview pemetaan kolom</strong> untuk memastikan kolom file sesuai dengan yang dibutuhkan sistem.
            </div>
            <div id="monthly-import-errors" class="alert alert-danger" role="alert" hidden></div>
            <div id="monthly-import-summary" aria-live="polite">@include('importrectorate::summary', ['summaries' => session('monthly_summaries', [])])</div>
            <form id="monthly-import-form" action="{{ route('importrectorate.upload') }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="row g-5 mb-6">
                    <div class="col-md-6"><label class="form-label required" for="upload-year">Tahun laporan</label><input class="form-control" id="upload-year" name="year" type="number" min="2000" max="2100" required value="{{ old('year', now()->year) }}"></div>
                    <div class="col-md-6"><label class="form-label required" for="upload-month">Bulan laporan</label><select class="form-select" id="upload-month" name="month" required>@foreach(range(1, 12) as $month)<option value="{{ $month }}" @selected((int) old('month', now()->month) === $month)>{{ \Carbon\Carbon::create(2026, $month, 1)->locale('id')->translatedFormat('F') }}</option>@endforeach</select></div>
                </div>
                <div class="border rounded p-5 mb-6">
                    <label class="form-label fw-bold" for="fm_file">
                        Publikasi FM (.xlsx)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2 fs-8">
                            <i class="bi bi-arrows-angle-expand me-1"></i>Preview mapping otomatis
                        </span>
                    </label>
                    <input class="form-control" id="fm_file" name="fm_file" type="file" accept=".xlsx">
                    <div class="form-text">Sheet MALANG (atau Raw) dan KPI. Termasuk daftar dosen, bobot Rectorate / RTTO, first author, dan skor KPI.</div>
                    <button class="btn btn-primary mt-4" id="fm_file-submit" data-import-file="fm_file" type="submit" disabled>
                        <i class="bi bi-cloud-upload me-1"></i>Upload dan import Publikasi FM
                    </button>
                </div>
                <div class="border rounded p-5 mb-6">
                    <label class="form-label fw-bold" for="mhs_file">
                        Publikasi MHS (.xlsx)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2 fs-8">
                            <i class="bi bi-arrows-angle-expand me-1"></i>Preview mapping otomatis
                        </span>
                    </label>
                    <input class="form-control" id="mhs_file" name="mhs_file" type="file" accept=".xlsx">
                    <div class="form-text">Membaca sheet MALANG, TITLE, dan LIST untuk publikasi mahasiswa.</div>
                    <button class="btn btn-primary mt-4" id="mhs_file-submit" data-import-file="mhs_file" type="submit" disabled>
                        <i class="bi bi-cloud-upload me-1"></i>Upload dan import Publikasi MHS
                    </button>
                </div>
                <div class="border rounded p-5 mb-6">
                    <label class="form-label fw-bold" for="research_file">
                        Hibah penelitian (.xlsx)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-2 fs-8">
                            <i class="bi bi-arrows-angle-expand me-1"></i>Preview mapping otomatis
                        </span>
                    </label>
                    <input class="form-control" id="research_file" name="research_file" type="file" accept=".xlsx">
                    <div class="form-text">Mengutamakan sheet MALANG, lalu Detail. Lokasi Kampus mengikuti kolom yang dipilih pada preview pemetaan. Tahun Anggaran mengikuti isi file; tahun dan bulan laporan mengikuti pilihan di atas.</div>
                    <button class="btn btn-primary mt-4" id="research_file-submit" data-import-file="research_file" type="submit" disabled>
                        <i class="bi bi-cloud-upload me-1"></i>Upload dan import Hibah
                    </button>
                </div>
                <p class="text-muted">Maksimal 20 MB per file. Petakan semua kolom wajib dan konfirmasi pemetaan file yang ingin diunggah, lalu klik tombol Upload dan import pada jenis data tersebut. Kolom opsional boleh kosong dan tidak menghalangi import. File lain yang sudah dipilih tetap tersedia untuk diunggah berikutnya. Gunakan tombol Buka preview / pemetaan untuk membuka kembali preview; mengganti file membatalkan konfirmasi sebelumnya.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a class="btn btn-light-primary" href="{{ url('/dashboard/perhitungankpi') }}">Data KPI dosen</a>
                    <a class="btn btn-light-primary" href="{{ url('/dashboard/exportreport') }}">Generate laporan</a>
                </div>
            </form>
        </div>
    </div>
    <div class="card card-bordered">
        <div class="card-body">
            <h3 class="mb-3">Kelengkapan sumber bulanan</h3>
            <p class="text-muted">Hapus hasil import per jenis data pada bulan yang dipilih. Jenis data dan periode lainnya tetap tersimpan.</p>
            <div id="monthly-import-notice" class="alert alert-success" role="status" @if(!session('monthly_import_message')) hidden @endif>{{ session('monthly_import_message') }}</div>
            <div id="monthly-import-history">@include('importrectorate::history')</div>
        </div>
    </div>
</div>

{{-- Modal Column Mapping --}}
@include('importrectorate::column-mapping')

@include('importrectorate::javascript')

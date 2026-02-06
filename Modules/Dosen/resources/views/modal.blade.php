<div class="modal fade" id="modalDosenDetail" tabindex="-1" aria-labelledby="modalDosenDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form>
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalDosenDetailLabel">Detail Dosen</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="dosenKodeInput">
                    <ul class="nav nav-tabs" id="dosenDetailTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="dosen-data-tab" data-bs-toggle="tab" data-bs-target="#dosen-data" type="button" role="tab" aria-controls="dosen-data" aria-selected="true">Data Dosen</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="dosen-desc-tab" data-bs-toggle="tab" data-bs-target="#dosen-desc" type="button" role="tab" aria-controls="dosen-desc" aria-selected="false">Deskripsi Dosen</button>
                        </li>
                    </ul>
                    <div class="tab-content pt-4">
                        <div class="tab-pane fade show active" id="dosen-data" role="tabpanel" aria-labelledby="dosen-data-tab">
                            <div class="table-responsive">
                                <table class="table table-sm table-striped align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <th style="width: 35%;">Kode Dosen</th>
                                            <td data-field="kode_dosen">-</td>
                                        </tr>
                                        <tr>
                                            <th>Nama Dosen</th>
                                            <td data-field="nama_dosen">-</td>
                                        </tr>
                                        <tr>
                                            <th>Program Studi</th>
                                            <td data-field="nama_gugus_binaan">-</td>
                                        </tr>
                                        <tr>
                                            <th>Nama Program</th>
                                            <td data-field="nama_program">-</td>
                                        </tr>
                                        <tr>
                                            <th>Faculty Type</th>
                                            <td data-field="tipe_faculty">-</td>
                                        </tr>
                                        <tr>
                                            <th>JJA</th>
                                            <td data-field="jja">-</td>
                                        </tr>
                                        <tr>
                                            <th>Pendidikan</th>
                                            <td data-field="pendidikan">-</td>
                                        </tr>
                                        <tr>
                                            <th>Jurusan</th>
                                            <td data-field="jurusan">-</td>
                                        </tr>
                                        <tr>
                                            <th>Jenis Kelamin</th>
                                            <td data-field="jenis_kelamin">-</td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td data-field="email_1">-</td>
                                        </tr>
                                        <tr>
                                            <th>No HP</th>
                                            <td data-field="no_hp">-</td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td data-field="alamat">-</td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td data-field="status">-</td>
                                        </tr>
                                        <tr>
                                            <th>Campus</th>
                                            <td data-field="campus">-</td>
                                        </tr>
                                        <tr>
                                            <th>Lokasi</th>
                                            <td data-field="lokasi">-</td>
                                        </tr>
                                        <tr>
                                            <th>Tgl Mulai Mengajar</th>
                                            <td data-field="tgl_mulai_mengajar">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="dosen-desc" role="tabpanel" aria-labelledby="dosen-desc-tab">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="border rounded p-3 text-center">
                                        <img id="dosenPhoto" src="{{ asset('assets/backoffice/media/avatars/blank.png') }}" class="img-fluid rounded" alt="Foto Dosen">
                                    </div>
                                    <div class="mt-3">
                                        <label for="dosenPhotoInput" class="form-label">Foto Dosen</label>
                                        <input type="file" id="dosenPhotoInput" class="form-control" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-4">
                                        <label for="dosenVideoInput" class="form-label">Video Perkenalan</label>
                                        <input type="url" id="dosenVideoInput" class="form-control" placeholder="https://">
                                    </div>
                                    <div class="mb-4">
                                        <label for="dosenDescriptionInput" class="form-label">Deskripsi Singkat</label>
                                        <textarea id="dosenDescriptionInput" class="form-control" rows="4" placeholder="Deskripsi singkat dosen"></textarea>
                                    </div>
                                    <div>
                                        <label for="dosenAttributesInput" class="form-label">Digital Lecturer Attribute</label>
                                        <div class="input-group">
                                            <input id="dosenAttributesInput" class="form-control" placeholder="Nama attribute">
                                            <select id="dosenAttributeIconInput" class="form-select" style="max-width: 220px;">
                                                <option value="">Pilih icon</option>
                                                <option value="bi-award">bi-award</option>
                                                <option value="bi-people">bi-people</option>
                                                <option value="bi-briefcase">bi-briefcase</option>
                                                <option value="bi-lightbulb">bi-lightbulb</option>
                                                <option value="bi-gear">bi-gear</option>
                                                <option value="bi-laptop">bi-laptop</option>
                                                <option value="bi-journal-bookmark">bi-journal-bookmark</option>
                                                <option value="bi-bar-chart">bi-bar-chart</option>
                                                <option value="bi-globe">bi-globe</option>
                                                <option value="bi-mortarboard">bi-mortarboard</option>
                                            </select>
                                            <button type="button" id="dosenAttributeAdd" class="btn btn-primary">Tambah</button>
                                        </div>
                                        <div class="table-responsive mt-3">
                                            <table class="table table-sm table-striped align-middle mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Attribute</th>
                                                        <th style="width: 35%;">Icon</th>
                                                        <th style="width: 100px;">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="dosenAttributesTable">
                                                    <tr>
                                                        <td colspan="3" class="text-center text-muted">Belum ada attribute</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    {{-- <button type="submit" class="btn btn-primary">Simpan</button> --}}
                </div>
            </form>
        </div>
    </div>
</div>

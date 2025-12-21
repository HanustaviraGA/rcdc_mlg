<div class="col-xl-12" data-roleable="false" data-role="HakAccess.read">
    <div class="card card-xl-stretch mb-xl-8">
        <div class="card-body pt-10">
            <form action="javascript:save('config_form')" name="config_form" class="form-horizontal">
                <div class="form-body">
                    <div class="row">
                        <div class="col-md-4">

                            <div class="alert alert-warning d-flex align-items-center p-5">
                                <span class="svg-icon svg-icon-2hx svg-icon-warning me-3">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="currentColor"></path>
                                        <path d="M10.5606 11.3042L9.57283 10.3018C9.28174 10.0065 8.80522 10.0065 8.51412 10.3018C8.22897 10.5912 8.22897 11.0559 8.51412 11.3452L10.4182 13.2773C10.8099 13.6747 11.451 13.6747 11.8427 13.2773L15.4859 9.58051C15.771 9.29117 15.771 8.82648 15.4859 8.53714C15.1948 8.24176 14.7183 8.24176 14.4272 8.53714L11.7002 11.3042C11.3869 11.6221 10.874 11.6221 10.5606 11.3042Z" fill="currentColor"></path>
                                    </svg>
                                </span>

                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 text-dark">1. Pilih role yang diinginkan</h6>
                                </div>
                            </div>

                            <!-- <button type="button" onclick="addRow()" data-roleable="false" data-role="HakAccess.create" class="btn btn-primary font-weight-bolder btn-sm mr-2 mb-2"><i class="fas fa-plus"></i> Add Hak Akses</button> -->
                            {{-- <button type="button" onclick="openModal()" data-roleable="false" data-role="HakAccess.create" class="btn btn-primary font-weight-bolder btn-sm mr-2 mb-2"><i class="fas fa-plus"></i> Add Hak Akses</button> --}}

                            <input type="hidden" name="markerAdd" value="0">
                            <div id="">
                                <table class="table table-condensed table-bordered" id="role_container">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-role"></tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-4">

                            <div class="alert alert-primary d-flex align-items-center p-5">
                                <span class="svg-icon svg-icon-2hx svg-icon-primary me-3">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="currentColor"></path>
                                        <path d="M10.5606 11.3042L9.57283 10.3018C9.28174 10.0065 8.80522 10.0065 8.51412 10.3018C8.22897 10.5912 8.22897 11.0559 8.51412 11.3452L10.4182 13.2773C10.8099 13.6747 11.451 13.6747 11.8427 13.2773L15.4859 9.58051C15.771 9.29117 15.771 8.82648 15.4859 8.53714C15.1948 8.24176 14.7183 8.24176 14.4272 8.53714L11.7002 11.3042C11.3869 11.6221 10.874 11.6221 10.5606 11.3042Z" fill="currentColor"></path>
                                    </svg>
                                </span>

                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 text-dark">2. Pilih menu yang diinginkan</h6>
                                </div>
                            </div>

                            {{-- <input type="text" placeholder="Search..." name="search" value="" id="search" class="form-control form-control-sm" autocomplete="off" /> --}}
                            <div id="dataTree" class="tree-demo mt-5"></div>
                        </div>


                        <div class="col-md-4">

                            <div class="alert alert-primary d-flex align-items-center p-5">
                                <span class="svg-icon svg-icon-2hx svg-icon-primary me-3">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path opacity="0.3" d="M20.5543 4.37824L12.1798 2.02473C12.0626 1.99176 11.9376 1.99176 11.8203 2.02473L3.44572 4.37824C3.18118 4.45258 3 4.6807 3 4.93945V13.569C3 14.6914 3.48509 15.8404 4.4417 16.984C5.17231 17.8575 6.18314 18.7345 7.446 19.5909C9.56752 21.0295 11.6566 21.912 11.7445 21.9488C11.8258 21.9829 11.9129 22 12.0001 22C12.0872 22 12.1744 21.983 12.2557 21.9488C12.3435 21.912 14.4326 21.0295 16.5541 19.5909C17.8169 18.7345 18.8277 17.8575 19.5584 16.984C20.515 15.8404 21 14.6914 21 13.569V4.93945C21 4.6807 20.8189 4.45258 20.5543 4.37824Z" fill="currentColor"></path>
                                        <path d="M10.5606 11.3042L9.57283 10.3018C9.28174 10.0065 8.80522 10.0065 8.51412 10.3018C8.22897 10.5912 8.22897 11.0559 8.51412 11.3452L10.4182 13.2773C10.8099 13.6747 11.451 13.6747 11.8427 13.2773L15.4859 9.58051C15.771 9.29117 15.771 8.82648 15.4859 8.53714C15.1948 8.24176 14.7183 8.24176 14.4272 8.53714L11.7002 11.3042C11.3869 11.6221 10.874 11.6221 10.5606 11.3042Z" fill="currentColor"></path>
                                    </svg>
                                </span>

                                <div class="d-flex flex-column">
                                    <h6 class="mb-1 text-dark">3. Klik tombol di bawah untuk menyimpan</h6>
                                </div>
                            </div>

                            <button onclick="saveHakAkses()" type="button" id="btnSaveHA" class="m-btn btn btn-info btn-sm d-none"> <i class="fa fa-cogs"></i> Simpan Konfigurasi</button>
                            <input type="hidden" name="role_id_val" id="role_id_val" value="">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="modalHakakses" tabindex="-1" aria-labelledby="modalHakakses" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="javascript:saveRow()" method="post" id="formHakakses" name="formHakakses" autocomplete="off" enctype="multipart/form-data">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalHakakses">Hak Akses</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="role_id" name="role_id">
                    <!-- <div class="fv-row mb-7 fv-plugins-icon-container">
                    <label for="" class="required form-label">Code</label>
                    <input type="text" name="code" class="form-control form-control-outline" placeholder="Input Code" required="" fdprocessedid="xek3g">
                    <div class="fv-plugins-message-container invalid-feedback"></div>
                </div> -->
                    <div class="fv-row mb-7 fv-plugins-icon-container">
                        <label for="" class="required form-label">Nama</label>
                        <input type="text" id="role_name" name="role_name" class="form-control form-control-outline" placeholder="Input Nama" required="" fdprocessedid="xek3g">
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>
                    <div class="fv-row mb-7 fv-plugins-icon-container">
                        <label for="" class="required form-label">Akses Halaman</label>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="portal" name="role_type_module" value="2">
                            <label class="form-check-label" for="portal">Portal</label>
                        </div>
                        <div class="form-check mt-2">
                            <input type="radio" class="form-check-input" id="admin" name="role_type_module" value="0">
                            <label class="form-check-label" for="admin">Admin</label>
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback"></div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@include('hakakses::javascript')

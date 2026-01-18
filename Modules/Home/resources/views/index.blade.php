<div class="row">
    <div class="col-lg-7 d-flex flex-column gap-4">
        <div class="card card-bordered">
            <div class="card-body">
                <div>
                    <p class="text-muted mb-0" data-roleable="false" data-role="dashboardadmin">Jumlah FM Saat Ini</p>
                    <span class="h1" style="font-size: 3em">{{ $total_fm }}</span>
                </div>
            </div>
        </div>

        <div class="card card-bordered mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="mb-4">Ringkasan Data FM</h1>
                    <a href="{{ url('dashboard/dosen') }}" data-navigo class="d-flex gap-2 align-items-center">
                        <span class="d-none d-sm-inline">Lihat lebih banyak </span>
                        <i class="las la-arrow-right"></i>
                    </a>
                </div>
                @include('home::table')
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div data-roleable="false" data-role="dashboardadmin">
            <h2 class="text-center mt-2 mb-6">Statistik</h2>
            <div id="canvas_chart"></div>
        </div>
    </div>
</div>
@include('home::javascript')

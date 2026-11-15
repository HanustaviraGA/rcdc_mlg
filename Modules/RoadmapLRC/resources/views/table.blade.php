{{-- The roadmap table: a row per program, then the total of each year. Re-rendered after each edit. --}}
@if(empty($roadmap['rows']))
    <p class="text-muted mb-0" data-roadmap-empty>Belum ada data roadmap jumlah FM.</p>
@else
    @if($roadmap['mismatches'] > 0)
        <div class="alert alert-warning d-flex align-items-center p-5 mb-6" data-roadmap-mismatches="{{ $roadmap['mismatches'] }}">
            <span class="fs-7">Ada {{ $roadmap['mismatches'] }} isian prodi yang jumlah S2 + S3-nya tidak sama dengan jumlah FM. Isian tersebut ditandai kuning; arahkan kursor ke selnya untuk melihat selisihnya.</span>
        </div>
    @endif
    <div class="table-responsive">
        <table class="table table-bordered align-middle fs-7 gy-2 gx-2 mb-0 roadmap-table">
            <thead class="bg-light fw-bold">
                <tr>
                    <th scope="col" class="roadmap-prodi bg-light">Prodi</th>
                    @foreach($roadmap['years'] as $year)
                        <th scope="col" class="roadmap-year-start">Jumlah FM<br>{{ $year }}</th>
                        <th scope="col">S2</th>
                        <th scope="col">S3</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($roadmap['rows'] as $row)
                    <tr data-roadmap-prodi="{{ $row['prodi'] }}">
                        <th scope="row" class="roadmap-prodi bg-body fw-bold">
                            {{ $row['prodi'] }}
                            @if($row['program_studi'])<span class="d-block text-muted fs-8 fw-normal">{{ $row['program_studi'] }}</span>@endif
                        </th>
                        @foreach($roadmap['years'] as $year)
                            @include('roadmaplrc::roadmap_cells', ['cell' => $row['years'][$year] ?? null, 'key' => $row['prodi'].'-'.$year, 'name' => $row['prodi'].' '.$year])
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-light fw-bold">
                <tr data-roadmap-prodi="total">
                    <th scope="row" class="roadmap-prodi bg-light">Total</th>
                    @foreach($roadmap['years'] as $year)
                        @include('roadmaplrc::roadmap_cells', ['cell' => $roadmap['totals'][$year], 'key' => 'total-'.$year, 'name' => 'Total '.$year])
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
    <p class="text-muted fs-8 mt-3 mb-0">Segitiga biru di pojok kanan atas sel menandakan angka tersebut memiliki catatan.</p>
@endif

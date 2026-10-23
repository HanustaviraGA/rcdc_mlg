@php
    $communityNumber = fn ($value, $digits = 0) => $value === null ? '—' : number_format($value, $digits, ',', '.');
@endphp
<div class="cd-dashboard" id="communityDevelopment">
    <section class="pd-section" aria-labelledby="communityHeading">
        <div class="pd-section-heading">
            <div class="pd-heading-copy">
                <span class="pd-section-number">03</span>
                <div><span class="pd-eyebrow">PENGABDIAN KEPADA MASYARAKAT</span><h2 id="communityHeading">Community Development FM</h2></div>
            </div>
            <span class="cd-demo-badge"><i class="bi {{ $community['isDemo'] ? 'bi-info-circle' : 'bi-file-earmark-spreadsheet' }}" aria-hidden="true"></i> {{ $community['isDemo'] ? 'Data contoh' : $community['periodLabel'] }}</span>
        </div>
        <p class="cd-intro">Ringkasan kegiatan pengabdian, capaian dosen per prodi, dan komunitas binaan BINUS Malang.</p>
        <p class="cd-demo-note">{{ $community['isDemo'] ? 'Data contoh untuk pratinjau.' : 'Rekap menggunakan sheet Rasio; rincian dosen menggunakan Keterlibatan dan profil Modules/Dosen.' }} Filter tahun, kuartal, dan prodi pada bagian publikasi tidak mengubah data ini.</p>
        @if(count($community['periods']))
            <form method="get" action="{{ route('publication-dashboard') }}#communityDevelopment" class="cd-period-form" aria-label="Filter periode Community Development">
                @foreach(['year', 'indicator', 'quarter', 'program'] as $key)
                    @if(is_scalar(request()->query($key)))<input type="hidden" name="{{ $key }}" value="{{ request()->query($key) }}">@endif
                @endforeach
                @foreach((array) request()->query('years', []) as $year)
                    @if(is_scalar($year))<input type="hidden" name="years[]" value="{{ $year }}">@endif
                @endforeach
                <div class="pd-field"><label for="communityPeriod">Periode Community Development</label><select name="community_period" id="communityPeriod">
                    @foreach($community['periods'] as $period)
                        <option value="{{ $period['id'] }}" @selected($community['selectedPeriod'] == $period['id'])>{{ $period['label'] }}</option>
                    @endforeach
                </select></div>
                <button type="submit" class="pd-button">Tampilkan</button>
            </form>
        @endif

        <div class="cd-kpi-guide" aria-label="Target KPI Community Development FM">
            <span><i class="bi bi-bullseye" aria-hidden="true"></i> Target KPI FM</span>
            <span><b>1 kegiatan</b> <span class="pd-score">Skor <strong>5</strong></span></span>
            <span><b>2 kegiatan atau lebih</b> <span class="pd-score is-good">Skor <strong>6</strong></span></span>
        </div>

        <div class="cd-overview-grid">
            <article class="cd-panel">
                <div class="cd-panel-heading"><h3><i class="bi bi-table" aria-hidden="true"></i> Rekapitulasi capaian prodi</h3></div>
                <p class="cd-hint" id="communityRecapHint">Pilih prodi untuk melihat kartu capaiannya. Rasio = kegiatan per dosen.</p>
                <div class="pd-table-scroll cd-recap-scroll" tabindex="0" role="region" aria-label="Rekap Community Development per prodi">
                    <table class="pd-table cd-recap-table" aria-describedby="communityRecapHint">
                        <thead><tr><th scope="col">Prodi / unit</th><th scope="col" class="pd-numeric">Jumlah FM</th><th scope="col" class="pd-numeric">Kegiatan</th><th scope="col" class="pd-numeric">Rasio</th></tr></thead>
                        <tbody>
                            <tr class="cd-total-row"><th scope="row">BINUS @MALANG <small>Total</small></th><td class="pd-numeric">{{ $communityNumber($community['totals']['faculty']) }}</td><td class="pd-numeric">{{ $communityNumber($community['totals']['activities']) }}</td><td class="pd-numeric">{{ $communityNumber($community['totals']['ratio'], 1) }}</td></tr>
                            @foreach($community['programs'] as $program)
                                <tr data-community-jump="{{ $program['key'] }}">
                                    <th scope="row"><a href="#community-card-{{ $program['key'] }}">{{ $program['code'] }} <i class="bi bi-arrow-down-right" aria-hidden="true"></i></a></th>
                                    <td class="pd-numeric">{{ $communityNumber($program['faculty']) }}</td><td class="pd-numeric">{{ $communityNumber($program['activities']) }}</td><td class="pd-numeric cd-ratio">{{ $communityNumber($program['ratio'], 1) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="cd-panel">
                <div class="cd-panel-heading"><h3><i class="bi bi-people" aria-hidden="true"></i> Komunitas binaan</h3><span class="cd-partner-count">{{ count($community['partners']) }} mitra</span></div>
                <p class="cd-hint">Telusuri mitra binaan berdasarkan kluster atau skala usahanya. <strong>Data mitra dan area masih berupa contoh.</strong></p>
                <div class="cd-category-grid">
                    <button type="button" class="cd-category" data-community-category="cluster" aria-haspopup="dialog" aria-controls="communityDetail">
                        <span><i class="bi bi-grid" aria-hidden="true"></i> Kategori kluster <i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                        <strong>{{ count($community['clusters']) }} <small>kluster</small></strong>
                        <span class="cd-category-description">F&amp;B, Kriya, Batik, Holti, dan Non UMKM</span>
                        <span class="cd-category-link">Lihat rincian <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                    </button>
                    <button type="button" class="cd-category" data-community-category="scale" aria-haspopup="dialog" aria-controls="communityDetail">
                        <span><i class="bi bi-shop" aria-hidden="true"></i> Skala usaha <i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                        <strong>{{ count($community['scales']) - 1 }} <small>skala UMKM</small></strong>
                        <span class="cd-category-description">Mikro, Kecil, Menengah; serta mitra lainnya</span>
                        <span class="cd-category-link">Lihat rincian <i class="bi bi-arrow-right" aria-hidden="true"></i></span>
                    </button>
                </div>
                <div class="cd-area">
                    <h4><i class="bi bi-geo-alt" aria-hidden="true"></i> Sebaran area binaan</h4>
                    <div class="cd-area-content">
                        @php
                            $areaTotal = array_sum(array_column($community['areas'], 'count'));
                            $areaFirst = 100 * $community['areas'][0]['count'] / $areaTotal;
                            $areaSecond = $areaFirst + 100 * $community['areas'][1]['count'] / $areaTotal;
                        @endphp
                        <div class="cd-donut" style="--cd-area-first: {{ $areaFirst }}%; --cd-area-second: {{ $areaSecond }}%" role="img" aria-label="Sebaran mitra: {{ collect($community['areas'])->map(fn ($area) => $area['label'].' '.$area['count'])->implode(', ') }}.">
                            <span><strong>{{ count($community['partners']) }}</strong><small>Mitra binaan</small></span>
                        </div>
                        <ul class="cd-area-legend">
                            @foreach($community['areas'] as $area)
                                <li><i class="cd-area-key cd-area-key-{{ $loop->index }}" aria-hidden="true"></i><span>{{ $area['label'] }}</span><strong>{{ $area['count'] }} <small>({{ number_format(100 * $area['count'] / $areaTotal, 0) }}%)</small></strong></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section class="pd-section" aria-labelledby="communityProgramsHeading">
        <div class="pd-section-heading">
            <div class="pd-heading-copy"><span class="pd-section-number">04</span><div><span class="pd-eyebrow">CAPAIAN PER PRODI</span><h2 id="communityProgramsHeading">Kegiatan dan kontribusi dosen</h2></div></div>
            <span class="pd-period-label">{{ count($community['programs']) }} prodi / unit &middot; {{ $communityNumber($community['totals']['activities']) }} kegiatan</span>
        </div>
        <div class="pd-department-grid cd-program-grid">
            @foreach($community['programs'] as $program)
                <article class="pd-department cd-program" id="community-card-{{ $program['key'] }}" tabindex="-1" aria-labelledby="community-title-{{ $program['key'] }}">
                    <div class="pd-department-head"><div class="pd-program-name"><h3 id="community-title-{{ $program['key'] }}">{{ $program['code'] }}</h3><p title="{{ $program['label'] }}">{{ $program['label'] }}</p></div><span class="cd-program-icon"><i class="bi bi-people" aria-hidden="true"></i></span></div>
                    <dl class="cd-program-stats"><div><dt>Dosen</dt><dd>{{ $communityNumber($program['faculty']) }}</dd></div><div><dt>Kegiatan</dt><dd>{{ $communityNumber($program['activities']) }}</dd></div><div><dt>Rasio</dt><dd>{{ $communityNumber($program['ratio'], 1) }}</dd></div></dl>
                    <div class="pd-department-footer"><span>{{ $community['isDemo'] ? 'Data contoh PkM' : $community['periodLabel'] }}</span><button type="button" class="pd-button pd-detail-button" data-community-lecturers="{{ $program['key'] }}" aria-label="Lihat daftar dosen {{ $program['code'] }}" aria-haspopup="dialog" aria-controls="communityDetail">List dosen <i class="bi bi-arrow-up-right" aria-hidden="true"></i></button></div>
                </article>
            @endforeach
        </div>
        @if($community['isDemo'])
            <p class="pd-context">Sumber contoh: dashboard Community Development FM. Rincian dosen merupakan sebagian contoh dan belum mewakili seluruh jumlah dosen atau kegiatan pada rekap.</p>
        @else
            <p class="pd-context">Sumber: {{ $community['filename'] ?? 'Periode tidak ditemukan' }}. Rekap mengikuti sheet Rasio. Jumlah kegiatan per dosen mengikuti kegiatan yang dilaporkan ke Binusmaya pada sheet Keterlibatan. Nama dan pengelompokan prodi mengikuti data dosen yang diunggah melalui Modules/Dosen.</p>
            @if(count($community['unassignedLecturers']))
                <p class="pd-context"><button type="button" class="pd-text-button" data-community-lecturers="_unassigned" aria-haspopup="dialog" aria-controls="communityDetail">Lihat {{ count($community['unassignedLecturers']) }} dosen yang belum terhubung ke prodi pada rekap <i class="bi bi-arrow-up-right" aria-hidden="true"></i></button></p>
            @endif
            @if(!count($community['programs']))<p class="pd-empty">Belum ada data Community Development untuk periode ini.</p>@endif
        @endif
    </section>
</div>

<dialog class="pd-dialog cd-dialog" id="communityDetail" aria-labelledby="communityDetailTitle" aria-describedby="communityDetailCaption">
    <div class="pd-dialog-heading"><div><span class="pd-eyebrow" id="communityDetailSource">COMMUNITY DEVELOPMENT</span><h2 id="communityDetailTitle">Rincian Community Development</h2></div><button class="pd-icon-button" type="button" id="communityDetailClose" aria-label="Tutup rincian Community Development"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
    <div class="pd-dialog-body">
        <div class="pd-table-tools"><button type="button" class="pd-text-button" id="communityDetailBack" hidden><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke kategori</button><label class="pd-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" id="communityDetailSearch" placeholder="Cari pada rincian…" aria-label="Cari pada rincian Community Development"></label></div>
        <p class="pd-table-caption" id="communityDetailCaption"></p>
        <div id="communityCategoryList" class="cd-category-list" hidden></div>
        <div class="pd-table-scroll" id="communityDetailTableRegion" tabindex="0" role="region" aria-label="Tabel rincian Community Development"><table class="pd-table cd-detail-table" id="communityDetailTable"></table></div>
        <p class="pd-table-meta" id="communityDetailMeta" role="status" aria-live="polite"></p>
    </div>
</dialog>

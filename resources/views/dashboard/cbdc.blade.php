@extends('landing.layout')
@section('title', 'Dashboard CBDC')
@section('description', 'Capaian Character Building Development Center (CBDC) BINUS Malang: realisasi, target, dan skor setiap kuartal.')
@section('page_class', 'publication-page performance-page cbdc-page')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/publication-kpi.css') }}?v={{ filemtime(public_path('css/publication-kpi.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/publication-performance.css') }}?v={{ filemtime(public_path('css/publication-performance.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/community-development.css') }}?v={{ filemtime(public_path('css/community-development.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/cbdc-dashboard.css') }}?v={{ filemtime(public_path('css/cbdc-dashboard.css')) }}">
@endpush
@php
    $number = fn ($value) => $value === null ? '—' : \App\Services\CharacterBuilding\CharacterBuildingTargets::number((float) $value);
    $month = fn ($batch) => \Carbon\Carbon::create($batch->year, $batch->month, 1)->locale('id')->translatedFormat('F Y');
    $publication = $cbdc['publication'];
    $comdev = $cbdc['comdev'];
    $areas = [
        'publikasi' => ['label' => 'Publikasi', 'icon' => 'bi-journal-text', 'unit' => 'judul Scopus', 'summary' => $publication['summary'],
            'source' => $publication['batch'] ? 'Upload Publikasi FM '.$month($publication['batch']).'.' : 'Belum ada upload Publikasi FM pada '.$cbdc['label'].'.'],
        'pengajaran' => ['label' => 'Pengajaran', 'icon' => 'bi-easel2', 'soon' => 'Data realisasi pengajaran sedang disiapkan.'],
        'comdev' => ['label' => 'Community Development', 'icon' => 'bi-people', 'unit' => 'kegiatan', 'summary' => $comdev['summary'],
            'source' => $comdev['batch'] ? 'Import Data PKM semester '.$comdev['semester'].'.' : 'Belum ada upload Community Development untuk semester '.$comdev['semester'].'.'],
        'pengembangan_diri' => ['label' => 'Pengembangan Diri', 'icon' => 'bi-person-up', 'soon' => 'Data realisasi pengembangan diri sedang disiapkan.'],
    ];
    // Lecturer details (section 03). They are set here because Blade would pair a later PHP block
    // with the first inline php directive below and break the view.
    $publicationLecturers = $publication['lecturers'];
    $comdevProgram = $comdev['program'];
    $soonCards = [
        'pengajaran' => ['Pengajaran', 'bi-easel2', 'Rincian pengajaran dosen CBDC sedang disiapkan.'],
        'pengembangan_diri' => ['Pengembangan Diri', 'bi-person-up', 'Rincian pengembangan diri dosen CBDC sedang disiapkan.'],
    ];
@endphp
@section('content')
<section class="portal-hero">
    <div class="container">
        <nav class="portal-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Dashboard CBDC</span></nav>
        <div class="portal-hero-grid">
            <div>
                <span class="portal-eyebrow">CHARACTER BUILDING DEVELOPMENT CENTER</span>
                <h1>Dashboard <span>CBDC.</span></h1>
                <p class="portal-intro">Pantau capaian Character Building Development Center BINUS Malang: realisasi, target, dan skor setiap kuartal.</p>
                <p class="portal-caption">Publikasi dari upload Publikasi FM · Community Development dari Import Data PKM</p>
                @auth<a class="portal-link" href="{{ url('/dashboard/characterbuilding') }}">Kelola target CBDC <i class="bi bi-arrow-right" aria-hidden="true"></i></a>@endauth
            </div>
            <div class="stamps" aria-label="Ringkasan dashboard">
                <div class="stamp"><b>{{ $cbdc['label'] }}</b><span>Periode</span></div>
                <div class="stamp"><b data-cbdc-stamp="publikasi">{{ $number($publication['summary']['earned']) }}</b><span>Skor Publikasi</span></div>
                <div class="stamp"><b data-cbdc-stamp="comdev">{{ $number($comdev['summary']['earned']) }}</b><span>Skor Community Development</span></div>
            </div>
        </div>
    </div>
</section>

<div class="container pd-content">
    <section class="pd-section" aria-labelledby="cbdcFilterHeading">
        <div class="pd-section-heading">
            <div class="pd-heading-copy"><span class="pd-section-number">00</span><div><span class="pd-eyebrow">PERIODE</span><h2 id="cbdcFilterHeading">Pilih kuartal</h2></div></div>
            <span class="pd-period-label">Menampilkan {{ $cbdc['label'] }}</span>
        </div>
        <form class="cbdc-filters" method="get" action="{{ route('cbdc-dashboard') }}" aria-label="Filter Dashboard CBDC">
            <div class="pd-field"><label for="cbdc-year">Tahun</label><select name="year" id="cbdc-year">@foreach($cbdc['years'] as $year)<option value="{{ $year }}" @selected($year === $cbdc['year'])>{{ $year }}</option>@endforeach</select></div>
            <div class="pd-field"><label for="cbdc-quarter">Kuartal</label><select name="quarter" id="cbdc-quarter">@foreach(range(1, 4) as $quarter)<option value="{{ $quarter }}" @selected($quarter === $cbdc['quarter'])>Q{{ $quarter }}</option>@endforeach</select></div>
            <button type="submit" class="pd-button pd-reset"><i class="bi bi-funnel" aria-hidden="true"></i> Terapkan</button>
            <a class="pd-button cbdc-clear-button" href="{{ route('cbdc-dashboard') }}">Kuartal terbaru</a>
        </form>
    </section>

    <section class="pd-section" aria-labelledby="cbdcScoreHeading">
        <div class="pd-section-heading">
            <div class="pd-heading-copy"><span class="pd-section-number">01</span><div><span class="pd-eyebrow">CAPAIAN CBDC</span><h2 id="cbdcScoreHeading">Skor {{ $cbdc['label'] }}</h2></div></div>
        </div>
        <div class="cbdc-area-grid">
            @foreach($areas as $key => $area)
                <article @class(['cbdc-area-card', 'is-soon' => isset($area['soon'])]) data-cbdc-area="{{ $key }}">
                    {{-- Not a <header>: the publication pages give every header the navy page-header background. --}}
                    <div class="cbdc-area-head"><span class="cbdc-area-icon"><i class="bi {{ $area['icon'] }}" aria-hidden="true"></i></span><h3>{{ $area['label'] }}</h3></div>
                    @isset($area['soon'])
                        <span class="cbdc-badge">Coming Soon</span>
                        <p class="cbdc-note">{{ $area['soon'] }}</p>
                    @else
                        @php(['realization' => $realization, 'target' => $target, 'reached' => $reached, 'next' => $next] = $area['summary'])
                        <dl class="cbdc-metrics">
                            <div><dt>Realisasi</dt><dd data-cbdc-stat="realization">{{ $number($realization) }}</dd></div>
                            <div><dt>Target</dt><dd data-cbdc-stat="target">{{ $number($target?->target) }}</dd></div>
                            <div><dt>Skor</dt><dd data-cbdc-stat="score">{{ $number($area['summary']['earned']) }}</dd></div>
                        </dl>
                        <p class="cbdc-unit">Satuan: {{ $area['unit'] }}{{ $target ? ' · skor target '.$number($target->score) : '' }}</p>
                        @if($realization === null)
                            <span class="cbdc-badge">Belum ada data</span>
                        @elseif(! $target)
                            <span class="cbdc-badge">Target belum ditetapkan</span>
                        @elseif($realization >= $target->target)
                            <span class="cbdc-badge is-good">Target tercapai</span>
                        @else
                            <span class="cbdc-badge is-low">Belum tercapai</span>
                        @endif
                        @if($realization !== null && $area['summary']['ladder']->isNotEmpty())
                            <p class="cbdc-note" data-cbdc-next>
                                @if($next)
                                    Kurang {{ $number($area['summary']['gap']) }} untuk skor {{ $number($next->score) }} (target {{ $number($next->target) }}).
                                @else
                                    Skor maksimal.
                                @endif
                            </p>
                        @endif
                        <p class="cbdc-source">{{ $area['source'] }}</p>
                    @endisset
                </article>
            @endforeach
        </div>
        <p class="pd-context"><i class="bi bi-info-circle" aria-hidden="true"></i> Skor mengikuti target tertinggi yang tercapai pada tahun tersebut. Misalnya target Q1–Q4 3, 6, 9, dan 11 judul dengan skor 2, 3, 4, dan 6: realisasi 7 judul mendapat skor 3, dan 11 judul ke atas mendapat skor maksimal 6.</p>
    </section>

    <section class="pd-section" aria-labelledby="cbdcTrendHeading">
        <div class="pd-section-heading">
            <div class="pd-heading-copy"><span class="pd-section-number">02</span><div><span class="pd-eyebrow">PERKEMBANGAN TAHUNAN</span><h2 id="cbdcTrendHeading">Kuartal {{ $cbdc['year'] }}</h2></div></div>
            <span class="pd-period-label">Pilih kuartal untuk melihat rinciannya</span>
        </div>
        <div class="cbdc-trend-grid">
            @foreach(['publikasi' => 'Publikasi · judul Scopus', 'comdev' => 'Community Development · kegiatan'] as $key => $title)
                <article class="cbdc-trend-card" data-cbdc-trend="{{ $key }}">
                    <h3>{{ $title }}</h3>
                    <div class="pd-table-scroll cbdc-table-scroll">
                        <table class="pd-table">
                            <thead><tr><th scope="col">Kuartal</th><th scope="col">Sumber</th><th scope="col">Realisasi</th><th scope="col">Target</th><th scope="col">Skor</th></tr></thead>
                            <tbody>
                                @foreach($cbdc['quarters'][$key] as $term => $data)
                                    @php($summary = $data['summary'])
                                    @php($ratio = $summary['realization'] !== null && $summary['target']?->target ? min(100, round($summary['realization'] / $summary['target']->target * 100)) : null)
                                    <tr @class(['is-selected' => $term === $cbdc['quarter']])>
                                        <td><a href="{{ route('cbdc-dashboard', ['year' => $cbdc['year'], 'quarter' => $term]) }}" @if($term === $cbdc['quarter']) aria-current="true" @endif>Q{{ $term }}</a></td>
                                        <td class="cbdc-muted">{{ $key === 'publikasi' ? ($data['batch'] ? $month($data['batch']) : '—') : ($data['batch'] ? $data['semester'] : '—') }}</td>
                                        <td>
                                            <strong>{{ $number($summary['realization']) }}</strong>
                                            @if($ratio !== null)<span class="cbdc-bar" role="img" aria-label="{{ $ratio }}% dari target"><span style="width: {{ $ratio }}%"></span></span>@endif
                                        </td>
                                        <td>{{ $number($summary['target']?->target) }}</td>
                                        <td><strong>{{ $number($summary['earned']) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </article>
            @endforeach
        </div>
        <p class="pd-context">Data Community Development diunggah per semester, sehingga setiap kuartal memakai semester yang mencakup sebagian besar bulannya (Ganjil September–Februari, Genap Maret–Agustus).</p>
    </section>

    <section class="pd-section" aria-labelledby="cbdcLecturerHeading">
        <div class="pd-section-heading">
            <div class="pd-heading-copy"><span class="pd-section-number">03</span><div><span class="pd-eyebrow">KONTRIBUSI DOSEN</span><h2 id="cbdcLecturerHeading">Rincian dosen CBDC {{ $cbdc['label'] }}</h2></div></div>
        </div>
        <div class="cbdc-area-grid">
            @foreach(['publikasi', 'pengajaran', 'comdev', 'pengembangan_diri'] as $key)
                @if(isset($soonCards[$key]))
                    @php([$title, $icon, $text] = $soonCards[$key])
                    <article class="pd-department cbdc-detail-card is-soon" data-cbdc-detail="{{ $key }}">
                        <div class="pd-department-head"><div class="pd-program-name"><h3>{{ $title }}</h3><p>Dosen CBDC</p></div><span class="cd-program-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span></div>
                        <div class="cbdc-soon"><span class="cbdc-soon-icon"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span><strong>Coming Soon</strong><p>{{ $text }}</p></div>
                    </article>
                @elseif($key === 'publikasi')
                    <article class="pd-department cbdc-detail-card" data-cbdc-detail="publikasi">
                        <div class="pd-department-head"><div class="pd-program-name"><h3>Publikasi</h3><p>Publikasi FM dosen CBDC</p></div><span class="cd-program-icon"><i class="bi bi-journal-text" aria-hidden="true"></i></span></div>
                        <dl class="cd-program-stats">
                            <div><dt>Dosen</dt><dd>{{ $publication['batch'] ? $publicationLecturers->count() : '—' }}</dd></div>
                            <div><dt>Judul Scopus</dt><dd>{{ $number($publication['summary']['realization']) }}</dd></div>
                            <div><dt>Dosen Scopus</dt><dd>{{ $publication['batch'] ? $publicationLecturers->where('scopus', '>', 0)->count() : '—' }}</dd></div>
                        </dl>
                        <div class="pd-department-footer">
                            <span>{{ $publication['batch'] ? 'Upload '.$month($publication['batch']) : 'Belum ada upload' }}</span>
                            <button type="button" class="pd-button pd-detail-button" data-cbdc-open="publikasi" aria-haspopup="dialog" aria-controls="cbdcPublicationDetail" @disabled(! $publication['batch'])>Detail dosen <i class="bi bi-arrow-up-right" aria-hidden="true"></i></button>
                        </div>
                    </article>
                @else
                    <article class="pd-department cbdc-detail-card" data-cbdc-detail="comdev">
                        <div class="pd-department-head"><div class="pd-program-name"><h3>Community Development</h3><p>Pengabdian dosen CBDC</p></div><span class="cd-program-icon"><i class="bi bi-people" aria-hidden="true"></i></span></div>
                        <dl class="cd-program-stats">
                            <div><dt>Dosen</dt><dd>{{ $number($comdevProgram['faculty'] ?? ($comdev['batch'] ? $comdev['lecturers']->count() : null)) }}</dd></div>
                            <div><dt>Kegiatan</dt><dd>{{ $number($comdev['summary']['realization']) }}</dd></div>
                            <div><dt>Rasio</dt><dd>{{ $number($comdevProgram['ratio'] ?? null) }}</dd></div>
                        </dl>
                        <div class="pd-department-footer">
                            <span>{{ $comdev['batch'] ? 'Semester '.$comdev['semester'] : 'Belum ada upload' }}</span>
                            <button type="button" class="pd-button pd-detail-button" data-cbdc-open="comdev" aria-haspopup="dialog" aria-controls="cbdcComdevDetail" @disabled(! $comdev['batch'])>List dosen <i class="bi bi-arrow-up-right" aria-hidden="true"></i></button>
                        </div>
                    </article>
                @endif
            @endforeach
        </div>
        <p class="pd-context">Dosen CBDC Publikasi adalah dosen dengan Jurusan Binaan CBDC pada sheet KPI (atau Gugus Binaan Character Building pada Data Center); dosen CBDC Community Development adalah dosen dengan Gugus Binaan Character Building. Rincian publikasi mengikuti Dashboard Riset, dan list dosen Community Development mengikuti Dashboard Community Development.</p>
    </section>
</div>

<dialog class="pd-dialog" id="cbdcPublicationDetail" aria-labelledby="cbdcPublicationTitle">
    <div class="pd-dialog-heading"><div><span class="pd-eyebrow">PUBLIKASI · {{ $publication['batch'] ? 'UPLOAD '.mb_strtoupper($month($publication['batch'])) : 'BELUM ADA UPLOAD' }}</span><h2 id="cbdcPublicationTitle">Detail dosen CBDC - {{ $cbdc['label'] }}</h2></div><button class="pd-icon-button" type="button" id="cbdcPublicationClose" aria-label="Tutup detail"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
    <div class="pd-dialog-body">
        <div class="pd-detail-stats" id="cbdcPublicationStats">
            <div class="pd-detail-stat"><span>Target {{ $cbdc['label'] }}</span><strong>{{ $number($publication['summary']['target']?->target) }}</strong></div>
            <div class="pd-detail-stat"><span>Realisasi (judul Scopus)</span><strong>{{ $number($publication['summary']['realization']) }}</strong></div>
            <div class="pd-detail-stat"><span>Skor</span><strong>{{ $number($publication['summary']['earned']) }}</strong></div>
        </div>
        <section class="pd-faculty-summary" id="cbdcFacultySummary" aria-labelledby="cbdcFacultyName" hidden>
            <h3 id="cbdcFacultyName"></h3>
            <div class="pd-count-scroll" tabindex="0" role="region" aria-label="Ringkasan jumlah publikasi FM">
                <table class="pd-count-table">
                    <thead>
                        <tr><th colspan="2" scope="colgroup">First Author</th><th colspan="2" scope="colgroup">Non First Author</th><th rowspan="2" scope="col">SINTA</th><th rowspan="2" scope="col">KPI</th></tr>
                        <tr><th scope="col">Conference</th><th scope="col">Journal</th><th scope="col">Conference</th><th scope="col">Journal</th></tr>
                    </thead>
                    <tbody id="cbdcFacultyCounts"></tbody>
                </table>
            </div>
            <p class="pd-table-caption">Ringkasan menunjukkan jumlah publikasi pada upload terpilih. SINTA dihitung dari Quartile Jurnal yang memuat “Jurnal SINTA”. KPI menunjukkan skor KPI dosen.</p>
        </section>
        <div class="pd-table-tools"><button type="button" class="pd-text-button" id="cbdcPublicationBack" hidden><i class="bi bi-arrow-left" aria-hidden="true"></i> Daftar dosen</button><label class="pd-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" id="cbdcPublicationSearch" placeholder="Cari nama, kode, atau judul…" aria-label="Cari pada tabel detail"></label><button class="pd-button" id="cbdcPublicationExport" type="button"><i class="bi bi-download" aria-hidden="true"></i> Export CSV</button></div>
        <section class="pd-publication-filters" id="cbdcPublicationFilters" aria-labelledby="cbdcPublicationFiltersTitle" hidden>
            <div class="pd-publication-filter-heading">
                <div><h3 id="cbdcPublicationFiltersTitle">Filter Publikasi</h3><p>Pilih lebih dari satu opsi pada setiap filter; hasil tabel diperbarui otomatis</p></div>
                <button type="button" class="pd-button" id="cbdcPublicationReset">Reset Semua Filter</button>
            </div>
            <div class="pd-publication-filter-grid" id="cbdcPublicationFilterGrid"></div>
        </section>
        <p class="pd-table-caption" id="cbdcPublicationCaption"></p>
        <div class="pd-table-scroll" tabindex="0" role="region" aria-label="Tabel rincian publikasi dosen CBDC"><table class="pd-table" id="cbdcPublicationTable"></table></div>
        <p class="pd-table-meta" id="cbdcPublicationMeta" role="status"></p>
    </div>
</dialog>

<dialog class="pd-dialog cd-dialog" id="cbdcComdevDetail" aria-labelledby="cbdcComdevTitle" aria-describedby="cbdcComdevCaption">
    <div class="pd-dialog-heading"><div><span class="pd-eyebrow">COMMUNITY DEVELOPMENT · {{ mb_strtoupper($comdev['semester']) }}</span><h2 id="cbdcComdevTitle">List dosen · CBDC</h2></div><button class="pd-icon-button" type="button" id="cbdcComdevClose" aria-label="Tutup list dosen"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
    <div class="pd-dialog-body">
        <div class="pd-table-tools"><label class="pd-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" id="cbdcComdevSearch" placeholder="Cari pada rincian…" aria-label="Cari pada list dosen Community Development"></label></div>
        <p class="pd-table-caption" id="cbdcComdevCaption">Character Building Development Center · {{ $comdev['semester'] }}. Jumlah kegiatan mengikuti kegiatan yang dilaporkan ke Binusmaya pada sheet Keterlibatan; profil dosen dicocokkan berdasarkan kode dosen.</p>
        <div class="pd-table-scroll" tabindex="0" role="region" aria-label="Tabel list dosen Community Development"><table class="pd-table cd-detail-table" id="cbdcComdevTable"></table></div>
        <p class="pd-table-meta" id="cbdcComdevMeta" role="status" aria-live="polite"></p>
    </div>
</dialog>
<noscript><p class="container">Aktifkan JavaScript untuk membuka rincian dosen.</p></noscript>
@endsection
@push('scripts')
    <script>window.cbdcDashboard = {{ Illuminate\Support\Js::from($cbdc['details']) }};</script>
    <script type="module">
        import * as shared from '{{ asset('js/publication-performance.js') }}?v={{ filemtime(public_path('js/publication-performance.js')) }}';
        import { initializeCbdc } from '{{ asset('js/cbdc-dashboard.js') }}?v={{ filemtime(public_path('js/cbdc-dashboard.js')) }}';
        initializeCbdc(window.cbdcDashboard, shared);
    </script>
@endpush

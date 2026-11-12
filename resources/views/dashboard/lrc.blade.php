@extends('landing.layout')
@section('title', 'Dashboard LRC')
@section('description', 'Ringkasan data dosen BINUS Malang untuk kebutuhan LRC.')
@section('page_class', 'publication-page performance-page lrc-page')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/publication-kpi.css') }}?v={{ filemtime(public_path('css/publication-kpi.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/publication-performance.css') }}?v={{ filemtime(public_path('css/publication-performance.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/lrc-dashboard.css') }}?v={{ filemtime(public_path('css/lrc-dashboard.css')) }}">
@endpush
@section('content')
<section class="portal-hero">
    <div class="container">
        <nav class="portal-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Dashboard LRC</span></nav>
        <div class="portal-hero-grid">
            <div>
                <span class="portal-eyebrow">LECTURER RESOURCE CENTER</span>
                <h1>Dashboard <span>LRC.</span></h1>
                <p class="portal-intro">Ringkasan profil dosen BINUS Malang untuk melihat komposisi akademik dan karakteristik faculty secara lebih cepat.</p>
                <p class="portal-caption">Data roster dosen terkini · Filter dapat diterapkan pada seluruh indikator</p>
            </div>
            <div class="stamps" aria-label="Cakupan dashboard">
                <div class="stamp"><b>{{ number_format($lrc['total'], 0, ',', '.') }}</b><span>Total dosen</span></div>
                <div class="stamp"><b>{{ count($lrc['filters']['fakultas']) }}</b><span>Fakultas</span></div>
                <div class="stamp"><b>{{ count($lrc['filters']['gugusBinaan']) }}</b><span>Gugus binaan</span></div>
            </div>
        </div>
    </div>
</section>

<div class="container pd-content">
    <section class="pd-section lrc-filter-section" aria-labelledby="lrcFilterHeading">
        <div class="pd-section-heading">
            <div class="pd-heading-copy"><span class="pd-section-number">00</span><div><span class="pd-eyebrow">PENYARINGAN DATA</span><h2 id="lrcFilterHeading">Filter dashboard</h2></div></div>
            <span class="pd-period-label">{{ number_format($lrc['total'], 0, ',', '.') }} dosen ditampilkan</span>
        </div>
        <form class="lrc-filters" method="get" action="{{ route('lrc-dashboard') }}" aria-label="Filter Dashboard LRC">
            @foreach([
                'fakultas' => ['label' => 'Fakultas', 'options' => $lrc['filters']['fakultas']],
                'gugusBinaan' => ['label' => 'Gugus Binaan', 'options' => $lrc['filters']['gugusBinaan']],
                'program' => ['label' => 'Program', 'options' => $lrc['filters']['program']],
                'statusDosen' => ['label' => 'Status Dosen', 'options' => $lrc['filters']['statusDosen']],
            ] as $name => $filter)
                <div class="pd-field"><label for="lrc-{{ $name }}">{{ $filter['label'] }}</label><select name="{{ $name }}" id="lrc-{{ $name }}"><option value="">Semua {{ strtolower($filter['label']) }}</option>@foreach($filter['options'] as $option)<option value="{{ $option }}" @selected($lrc['filters']['selected'][$name] === $option)>{{ $option }}</option>@endforeach</select></div>
            @endforeach
            <button type="submit" class="pd-button pd-reset"><i class="bi bi-funnel" aria-hidden="true"></i> Terapkan filter</button>
            <a class="pd-button lrc-clear-button" href="{{ route('lrc-dashboard') }}">Reset</a>
        </form>
        <p class="pd-context"><i class="bi bi-info-circle" aria-hidden="true"></i> Filter memengaruhi seluruh chart dan daftar dosen pada rinciannya. Persentase dihitung dari total data yang ditampilkan pada masing-masing chart.</p>
    </section>

    <section class="pd-section" aria-labelledby="lrcKpiHeading">
        <div class="pd-section-heading"><div class="pd-heading-copy"><span class="pd-section-number">01</span><div><span class="pd-eyebrow">OVERVIEW</span><h2 id="lrcKpiHeading">Total Dosen</h2></div></div></div>
        <article class="lrc-kpi-card"><div class="lrc-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></div><div><span>Dosen pada filter terpilih</span><strong>{{ number_format($lrc['total'], 0, ',', '.') }}</strong><small>Roster dosen terkini yang tersedia pada Data Center</small></div></article>
    </section>

    <section class="pd-section" aria-labelledby="lrcEducationHeading">
        <div class="pd-section-heading"><div class="pd-heading-copy"><span class="pd-section-number">02</span><div><span class="pd-eyebrow">KUALIFIKASI AKADEMIK</span><h2 id="lrcEducationHeading">Jenjang Pendidikan</h2></div></div><span class="pd-period-label">Klik chart atau kategori untuk melihat daftar dosen</span></div>
        <div class="lrc-chart-grid lrc-chart-grid-wide">@include('dashboard.lrc_chart_card', ['key' => 'pendidikan', 'title' => 'Jenjang pendidikan dosen', 'canvas' => 'lrc-chart-pendidikan'])</div>
    </section>

    <section class="pd-section" aria-labelledby="lrcProfileHeading">
        <div class="pd-section-heading"><div class="pd-heading-copy"><span class="pd-section-number">03</span><div><span class="pd-eyebrow">PROFIL DOSEN</span><h2 id="lrcProfileHeading">Komposisi dosen</h2></div></div><span class="pd-period-label">Klik chart atau kategori untuk melihat daftar dosen</span></div>
        <div class="lrc-chart-grid">
            @foreach([
                'jja' => ['title' => 'JJA', 'canvas' => 'lrc-chart-jja'],
                'usia' => ['title' => 'Rentang Usia', 'canvas' => 'lrc-chart-usia'],
                'serdos' => ['title' => 'Jumlah Serdos', 'canvas' => 'lrc-chart-serdos'],
                'nuptk' => ['title' => 'NUPTK', 'canvas' => 'lrc-chart-nuptk'],
                'tipeDosen' => ['title' => 'Tipe Dosen', 'canvas' => 'lrc-chart-tipe-dosen'],
                'agama' => ['title' => 'Agama', 'canvas' => 'lrc-chart-agama'],
            ] as $key => $chart)
                @include('dashboard.lrc_chart_card', ['key' => $key, 'title' => $chart['title'], 'canvas' => $chart['canvas']])
            @endforeach
        </div>
    </section>

    <section class="pd-section" aria-labelledby="lrcFacultyHeading">
        <div class="pd-section-heading"><div class="pd-heading-copy"><span class="pd-section-number">04</span><div><span class="pd-eyebrow">STRUKTUR FACULTY</span><h2 id="lrcFacultyHeading">Komposisi Fakultas</h2></div></div><span class="pd-period-label">Klik chart atau tombol untuk membuka detail</span></div>
        <article class="lrc-chart-card lrc-faculty-card"><div class="lrc-chart-wrap lrc-faculty-chart-wrap"><canvas id="lrc-chart-fakultas" aria-label="Chart komposisi fakultas"></canvas></div><div class="lrc-faculty-actions">@foreach($lrc['charts']['fakultas']['labels'] as $index => $label)<button type="button" class="pd-button" data-lrc-faculty="{{ $label }}">{{ $label }} <strong>{{ $lrc['charts']['fakultas']['data'][$index] }}</strong> <i class="bi bi-arrow-up-right" aria-hidden="true"></i></button>@endforeach</div></article>
        <p class="pd-context">Rincian dibuka bertahap: Fakultas → tipe faculty detail (misalnya Functional Faculty(A)) → daftar nama dosen.</p>
    </section>
</div>

<dialog class="pd-dialog lrc-dialog" id="lrcDetail" aria-labelledby="lrcDetailTitle" aria-describedby="lrcDetailCaption">
    <div class="pd-dialog-heading"><div><span class="pd-eyebrow" id="lrcDetailEyebrow">KOMPOSISI FAKULTAS</span><h2 id="lrcDetailTitle">Rincian Fakultas</h2></div><button class="pd-icon-button" type="button" id="lrcDetailClose" aria-label="Tutup rincian"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
    <div class="pd-dialog-body">
        <div id="lrcEducationStats" class="lrc-education-stats" aria-label="Statistik perencanaan dosen per tahun" hidden></div>
        <div id="lrcEducationFilters" class="lrc-filters lrc-education-filters" role="group" aria-label="Filter rincian dosen" hidden>
            <div class="pd-field"><label for="lrcGugusFilter">Gugus Binaan</label><select id="lrcGugusFilter"><option value="">Semua gugus binaan</option></select></div>
            <div class="pd-field"><label for="lrcProgramFilter">Program</label><select id="lrcProgramFilter"><option value="">Semua program</option></select></div>
            <div class="pd-field" id="lrcRoadmapFilterField"><label for="lrcRoadmapFilter">Roadmap S3</label><select id="lrcRoadmapFilter"><option value="">Semua tahun</option></select></div>
            <div class="pd-field" id="lrcProjectionFilterField"><label for="lrcProjectionFilter">Proyeksi Lulus S3</label><select id="lrcProjectionFilter"><option value="">Semua tahun</option></select></div>
            <div class="pd-field" id="lrcJjaRoadmapFilterField" hidden><label for="lrcJjaRoadmapFilter">Roadmap kenaikan JJA</label><select id="lrcJjaRoadmapFilter"><option value="">Semua roadmap</option></select></div>
            <div class="pd-field" id="lrcJjaEligibleFilterField" hidden><label for="lrcJjaEligibleFilter">Tahun eligible kenaikan JJA</label><select id="lrcJjaEligibleFilter"><option value="">Semua tahun</option></select></div>
            <button type="button" class="pd-button lrc-clear-button" id="lrcEducationReset">Reset filter rincian</button>
        </div>
        <div class="pd-table-tools"><button type="button" class="pd-text-button" id="lrcDetailBack" hidden><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali</button><label class="pd-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" id="lrcDetailSearch" placeholder="Cari dosen atau kategori…" aria-label="Cari pada rincian"></label></div>
        <p class="pd-table-caption" id="lrcDetailCaption"></p>
        <div id="lrcFacultyList" class="lrc-detail-list"></div>
        <div class="pd-table-scroll" id="lrcLecturerRegion" hidden><table class="pd-table lrc-detail-table" id="lrcLecturerTable"></table></div>
        <p class="pd-table-meta" id="lrcDetailMeta" role="status" aria-live="polite"></p>
    </div>
</dialog>
<noscript><p class="container pd-context">Aktifkan JavaScript untuk membuka daftar dosen pada setiap chart.</p></noscript>
@endsection
@push('scripts')
    <script src="{{ asset('assets/backoffice/js/custom/campur/chart.js') }}"></script>
    <script>window.lrcDashboard = {{ Illuminate\Support\Js::from($lrc) }};</script>
    <script src="{{ asset('js/lrc-dashboard.js') }}?v={{ filemtime(public_path('js/lrc-dashboard.js')) }}"></script>
@endpush

@extends('landing.layout')
@section('title', 'Dashboard KPI Publikasi')
@section('description', 'Pantau target dan realisasi publikasi FM dan mahasiswa BINUS Malang, menurut tahun, kuartal, dan program studi.')
@section('page_class', 'publication-page performance-page')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/publication-kpi.css') }}?v={{ filemtime(public_path('css/publication-kpi.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/publication-performance.css') }}?v={{ filemtime(public_path('css/publication-performance.css')) }}">
@endpush
@section('content')
<section class="portal-hero">
    <div class="container">
        <nav class="portal-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Dashboard KPI</span></nav>
        <div class="portal-hero-grid">
            <div>
                <span class="portal-eyebrow">PUBLIKASI &amp; KINERJA RISET</span>
                <h1>Dashboard KPI <span>Publikasi.</span></h1>
                <p class="portal-intro">Pantau target dan realisasi publikasi, telusuri capaian setiap program studi, dan lihat kontribusi dosen serta mahasiswa BINUS Malang.</p>
                <p class="portal-caption">Publication Performance Intelligence Dashboard &middot; Target, capaian, dan arah pengembangan</p>
                <a class="portal-link" href="{{ route('research-gallery.index') }}">Jelajahi Research Gallery <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="stamps" aria-label="Cakupan dashboard">
                <div class="stamp"><b id="performanceLatestYear">{{ $performance['defaultYear'] }}</b><span>Tahun publikasi terbaru</span></div>
                <div class="stamp"><b id="performanceProgramCount">&mdash;</b><span>Program studi pada periode terpilih</span></div>
                <div class="stamp"><b class="pd-stamp-period" id="performanceCutoff">&mdash;</b><span>Data kumulatif sampai periode ini</span></div>
            </div>
        </div>
    </div>
</section>

<div class="pd-filter-band">
    <form class="container pd-filters" id="performanceFilters" aria-label="Filter dashboard">
        <div class="pd-field pd-year-field">
            <label id="performanceYearsLabel">Tahun</label>
            <details class="pd-year-select" id="performanceYears">
                <summary aria-labelledby="performanceYearsLabel performanceYearLabel"><span id="performanceYearLabel">{{ $performance['defaultYear'] }}</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                <div class="pd-year-options">
                    <span class="pd-option-caption">Pilih satu atau beberapa tahun</span>
                    @forelse($performance['years'] as $year)
                        <label><input type="checkbox" name="years[]" value="{{ $year }}" @checked($year == $performance['defaultYear'])> {{ $year }}</label>
                    @empty
                        <span class="pd-option-caption">Belum ada periode publikasi.</span>
                    @endforelse
                    <button type="button" class="pd-text-button" id="performanceAllYears">Pilih semua tahun</button>
                </div>
            </details>
        </div>
        <div class="pd-field pd-indicator-field">
            <label for="performanceIndicator">Indikator</label>
            <select id="performanceIndicator" name="indicator">
                <option value="fm">Publication FM</option>
                <option value="mhs">Publikasi Mahasiswa</option>
                <option value="citation">New Citation</option>
                <option value="research">Research Program</option>
                <option value="ip"># of New IP</option>
                <option value="ntr">Other Revenue Stream (NTR)</option>
                <option value="vrtt">Alokasi Budget VRTT</option>
            </select>
        </div>
        <div class="pd-field">
            <label for="performanceQuarter">Kuartal</label>
            <select id="performanceQuarter" name="quarter">
                <option value="all">Periode terbaru</option>
                <option value="Q1">Q1 &middot; s.d. Maret</option>
                <option value="Q2">Q2 &middot; s.d. Juni</option>
                <option value="Q3">Q3 &middot; s.d. September</option>
                <option value="Q4">Q4 &middot; s.d. Desember</option>
            </select>
        </div>
        <div class="pd-field">
            <label for="performanceProgram">Program studi</label>
            <select id="performanceProgram" name="program"><option value="">Semua program studi</option></select>
        </div>
        <button class="pd-button pd-reset" type="reset"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Reset filter</button>
        <div class="pd-filter-meta"><span class="pd-live-dot" aria-hidden="true"></span><span id="performanceUpdated">Data publikasi terintegrasi</span></div>
    </form>
</div>

<div class="container pd-content">
    <section class="pd-section" aria-labelledby="performanceSummaryHeading">
        <div class="pd-section-heading">
            <div class="pd-heading-copy"><span class="pd-section-number">01</span><div><span class="pd-eyebrow">OVERVIEW</span><h2 id="performanceSummaryHeading">Ringkasan capaian</h2></div></div>
            <span class="pd-period-label" id="performanceSummaryPeriod"></span>
        </div>
        <div class="pd-summary-grid" id="performanceSummary" aria-live="polite"></div>
        <p class="pd-context" id="performanceContext"></p>
    </section>

    <section class="pd-section" aria-labelledby="performanceDepartmentsHeading">
        <div class="pd-section-heading">
            <div class="pd-heading-copy"><span class="pd-section-number">02</span><div><span class="pd-eyebrow">PERFORMANCE</span><h2 id="performanceDepartmentsHeading">Kinerja per jurusan <span id="performanceIndicatorLabel">Publication FM</span></h2></div></div>
            <div class="pd-legend" aria-label="Legenda grafik"><span><i class="pd-target-key"></i>Target tahunan</span><span><i class="pd-actual-key"></i>Realisasi kumulatif</span></div>
        </div>
        <p class="pd-scope" id="performanceScope" role="status"></p>
        <div class="pd-department-grid" id="performanceDepartments"></div>
        <div class="pd-empty" id="performanceEmpty" hidden><span class="pd-empty-icon"><i class="bi bi-bar-chart" aria-hidden="true"></i></span><h3 id="performanceEmptyTitle">Data belum tersedia</h3><p id="performanceEmptyText"></p><button class="pd-button" type="button" id="performanceEmptyReset">Kembali ke publikasi terbaru</button></div>
    </section>

    <details class="pd-source-note" id="performanceSourceNotes"><summary><i class="bi bi-info-circle" aria-hidden="true"></i> Sumber dan catatan perhitungan <i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div id="performanceNotes"></div></details>
    <div class="pd-bottom-actions">
        <a href="{{ route('publication-dashboard.legacy') }}"><i class="bi bi-layout-text-window-reverse" aria-hidden="true"></i> Lihat dashboard sebelumnya</a>
        <div class="pd-action-group">
            @auth
                <a href="{{ url('/dashboard/importrectorate') }}">Import Excel</a>
                <a href="{{ url('/dashboard/importrectorate') }}">Import hibah</a>
                <a href="{{ url('/dashboard/matrixkpidosen') }}">Pedoman matriks</a>
            @endauth
            <button class="pd-button" id="performancePrint" type="button"><i class="bi bi-printer" aria-hidden="true"></i> Cetak dashboard</button>
        </div>
    </div>
</div>

<dialog class="pd-dialog" id="performanceDetail" aria-labelledby="performanceDetailTitle">
    <div class="pd-dialog-heading"><div><span class="pd-eyebrow" id="performanceDetailSubtitle">RINCIAN CAPAIAN</span><h2 id="performanceDetailTitle">Detail program studi</h2></div><button class="pd-icon-button" type="button" id="performanceDetailClose" aria-label="Tutup detail"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
    <div class="pd-dialog-body">
        <div class="pd-detail-stats" id="performanceDetailStats"></div>
        <div class="pd-table-tools"><button type="button" class="pd-text-button" id="performanceDetailBack" hidden><i class="bi bi-arrow-left" aria-hidden="true"></i> Daftar dosen</button><label class="pd-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" id="performanceDetailSearch" placeholder="Cari nama, kode, atau judul…" aria-label="Cari pada tabel detail"></label><button class="pd-button" id="performanceExport" type="button"><i class="bi bi-download" aria-hidden="true"></i> Export CSV</button></div>
        <p class="pd-table-caption" id="performanceDetailCaption"></p>
        <div class="pd-table-scroll" tabindex="0" role="region" aria-label="Tabel rincian capaian"><table class="pd-table" id="performanceDetailTable"></table></div>
        <p class="pd-table-meta" id="performanceDetailMeta" role="status"></p>
    </div>
</dialog>
<noscript><p class="container">Aktifkan JavaScript untuk menggunakan filter, grafik, dan rincian publikasi.</p></noscript>
@endsection
@push('scripts')
    <script>window.publicationPerformance = {{ Illuminate\Support\Js::from($performance) }};</script>
    <script type="module" src="{{ asset('js/publication-performance.js') }}?v={{ filemtime(public_path('js/publication-performance.js')) }}"></script>
@endpush

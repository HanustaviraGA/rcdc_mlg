@extends('landing.research_gallery.layout')
@section('content')
<section class="portal-hero research-hero">
    <div class="container">
        <nav class="portal-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Research Gallery</span></nav>
        <div class="hero-grid">
            <div>
                <span class="portal-eyebrow">RISET, KOLABORASI &amp; PENGETAHUAN</span>
                <h1>Research <span>Gallery.</span></h1>
                <p class="portal-intro">Temukan ide dan kolaborasi di balik penelitian BINUS Malang. Kenali tim peneliti, bidang keilmuan, dan kontribusinya bagi pembangunan berkelanjutan.</p>
                <div class="hero-actions"><a class="btn" href="#showcase">Jelajahi koleksi ↓</a><a class="btn btn-ghost" href="{{ route('publication-dashboard') }}">Lihat Dashboard KPI</a></div>
            </div>
            <div class="rg-wall" aria-hidden="true" inert>@foreach($featured as $project) @include('landing.research_gallery.card') @endforeach</div>
        </div>
    </div>
</section>
<div class="ribbon"><div class="wrap"><div class="ribbon-grid">
    <div class="stat"><span class="stat-num">{{ $stats['projects'] }}</span><span class="stat-label">Proyek dalam sumber terpilih</span></div>
    <div class="stat"><span class="stat-num">{{ $stats['people'] }}</span><span class="stat-label">Peneliti tercatat</span></div>
    <div class="stat"><span class="stat-num">{{ $stats['years'] }}</span><span class="stat-label">Tahun anggaran</span></div>
    <div class="stat"><span class="stat-num">{{ $stats['rectorate'] }} / {{ $stats['system'] }}</span><span class="stat-label">Proyek Rectorate / Sistem</span></div>
</div></div></div>
<section class="band-tight" id="showcase">
    <div class="wrap sec-head"><div><h2 class="display-md">Koleksi penelitian</h2><p class="lede">Pilih sumber data, lalu telusuri riset berdasarkan tahun, bidang, atau penelitinya.</p></div></div>
    <form class="filters" method="get" action="{{ route('research-gallery.index') }}#showcase">
        <div class="wrap filters-in">
            <div class="search"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg><input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Judul, peneliti, kode proposal…" aria-label="Cari riset"></div>
            <div class="select"><select name="source" aria-label="Sumber data"><option value="rectorate" @selected($filters['source'] === 'rectorate')>Upload Rectorate</option><option value="system" @selected($filters['source'] === 'system')>Sistem Riset</option><option value="all" @selected($filters['source'] === 'all')>Semua sumber</option></select></div>
            <div class="select"><select name="year" aria-label="Tahun anggaran"><option value="">Semua tahun</option>@foreach($options['years'] as $year)<option value="{{ $year }}" @selected(($filters['year'] ?? '') == $year)>{{ $year }}</option>@endforeach</select></div>
            <div class="select"><select name="field" aria-label="Bidang ilmu"><option value="">Semua bidang</option>@foreach($options['fields'] as $field)<option value="{{ $field }}" @selected(($filters['field'] ?? '') === $field)>{{ $field }}</option>@endforeach</select></div>
            <div class="select"><select name="faculty" aria-label="Fakultas"><option value="">Semua fakultas</option>@foreach($options['faculties'] as $faculty)<option value="{{ $faculty }}" @selected(($filters['faculty'] ?? '') === $faculty)>{{ $faculty }}</option>@endforeach</select></div>
            <div class="select"><select name="person" aria-label="Peneliti"><option value="">Semua peneliti</option>@foreach($options['people'] as $person)<option value="{{ $person['code'] }}" @selected(($filters['person'] ?? '') === $person['code'])>{{ $person['name'] }}</option>@endforeach</select></div>
            <div class="select"><select name="sort" aria-label="Urutan"><option value="new" @selected($filters['sort'] === 'new')>Terbaru</option><option value="old" @selected($filters['sort'] === 'old')>Terlama</option><option value="az" @selected($filters['sort'] === 'az')>Judul A–Z</option></select></div>
            <button class="btn btn-sm" type="submit">Terapkan</button><a class="link-quiet" href="{{ route('research-gallery.index', ['source' => $filters['source']]) }}#showcase">Reset</a>
        </div>
    </form>
    <div class="wrap">
        <p class="rg-source-note">Sumber <b>Rectorate</b> menggunakan laporan kampus Binus @Malang, dengan pembaruan terakhir untuk setiap tahun anggaran. Sumber <b>Sistem Riset</b> menampilkan katalog sistem secara terpisah. Proyek yang muncul di kedua sumber tetap memiliki dua entri dengan label sumber masing-masing.</p>
        <div class="result-line" id="research-result">{{ $projects->total() }} proyek ditemukan @if($projects->total()) · menampilkan {{ $projects->firstItem() }}–{{ $projects->lastItem() }} @endif</div>
        <div class="gallery" id="research-gallery">@foreach($projects as $project) @include('landing.research_gallery.card') @endforeach</div>
        @if($projects->isEmpty())<div class="empty"><h3 class="h3">Belum ada riset yang sesuai</h3><p>Ubah pencarian atau reset filter untuk menjelajahi koleksi.</p></div>@endif
        @if($projects->hasPages())<nav class="rg-pagination" aria-label="Halaman koleksi">@if(!$projects->onFirstPage())<a class="btn btn-ghost btn-sm" href="{{ $projects->previousPageUrl() }}#showcase">← Sebelumnya</a>@endif<span class="meta">{{ $projects->currentPage() }} / {{ $projects->lastPage() }}</span>@if($projects->hasMorePages())<a class="btn btn-ghost btn-sm" href="{{ $projects->nextPageUrl() }}#showcase">Berikutnya →</a>@endif</nav>@endif
    </div>
</section>
@endsection

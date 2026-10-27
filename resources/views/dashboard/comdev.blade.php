@extends('landing.layout')
@section('title', 'Dashboard Community Development')
@section('description', 'Pantau kegiatan pengabdian dosen dan komunitas binaan BINUS Malang berdasarkan data impor PKM dan UMKM.')
@section('page_class', 'publication-page performance-page comdev-page')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/publication-kpi.css') }}?v={{ filemtime(public_path('css/publication-kpi.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/publication-performance.css') }}?v={{ filemtime(public_path('css/publication-performance.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/community-development.css') }}?v={{ filemtime(public_path('css/community-development.css')) }}">
@endpush
@section('content')
<section class="portal-hero">
    <div class="container">
        <nav class="portal-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Dashboard Comdev</span></nav>
        <div class="portal-hero-grid">
            <div>
                <span class="portal-eyebrow">PENGABDIAN &amp; KOMUNITAS BINAAN</span>
                <h1>Dashboard <span>Comdev.</span></h1>
                <p class="portal-intro">Telusuri kegiatan pengabdian dosen dan perkembangan komunitas binaan BINUS Malang.</p>
                <p class="portal-caption">Community Development · Kontribusi dosen dan kemitraan masyarakat</p>
                @auth<a class="portal-link" href="{{ url('/dashboard/importpkm') }}">Import data PKM dan mitra <i class="bi bi-arrow-right" aria-hidden="true"></i></a>@endauth
            </div>
            <div class="stamps" aria-label="Cakupan dashboard">
                <div class="stamp"><b>{{ count($communityDevelopment['partners']) }}</b><span>Mitra binaan{{ $communityDevelopment['selectedPartnerYear'] ? ' · '.$communityDevelopment['selectedPartnerYear'] : '' }}</span></div>
                <div class="stamp"><b>{{ count($communityDevelopment['clusters']) }}</b><span>Kluster komunitas</span></div>
                <div class="stamp"><b>{{ count($communityDevelopment['programs']) }}</b><span>Program studi / unit</span></div>
            </div>
        </div>
    </div>
</section>
<div class="container pd-content">
    @include('dashboard.community_development', ['community' => $communityDevelopment])
</div>
<noscript><p class="container">Aktifkan JavaScript untuk membuka rincian dosen dan mitra.</p></noscript>
@endsection
@push('scripts')
    <script>window.communityDevelopment = {{ Illuminate\Support\Js::from($communityDevelopment) }};</script>
    <script type="module" src="{{ asset('js/community-development.js') }}?v={{ filemtime(public_path('js/community-development.js')) }}"></script>
@endpush

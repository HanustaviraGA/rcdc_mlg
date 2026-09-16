@extends('landing.layout')
@section('title', 'Dashboard KPI Publikasi FM')
@section('page_class', 'publication-page')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/publication-kpi.css') }}">
@endpush
@section('content')
<section class="portal-hero">
    <div class="container">
        <nav class="portal-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Dashboard KPI</span></nav>
        <div class="portal-hero-grid">
            <div>
                <span class="portal-eyebrow">PUBLIKASI &amp; KINERJA RISET</span>
                <h1>Dashboard KPI <span>Publikasi.</span></h1>
                <p class="portal-intro">Telusuri capaian publikasi Faculty Member BINUS Malang, perkembangan program studi, dan potensi kolaborasi riset.</p>
                <p class="portal-caption">Publication Performance Intelligence Dashboard · Persiapan riset 2027</p>
                <a class="portal-link" href="{{ route('research-gallery.index') }}">Jelajahi Research Gallery <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="stamps" id="stamps" aria-label="Cakupan data publikasi"></div>
        </div>
    </div>
</section>
<div class="filters">
    <div class="fin">
        <div class="fg"><label for="year">Tahun snapshot</label><select id="year"></select></div>
        <div class="fg"><label for="program">Program studi</label><select id="program"></select></div>
        <div class="fg"><label for="faculty">Faculty type</label><select id="faculty"></select></div>
        <div class="fg"><label for="education">Pendidikan</label><select id="education"></select></div>
        <div class="fg"><label for="rank">Jabatan akademik</label><select id="rank"></select></div>
        <div class="fg"><label for="cluster">Cluster riset</label><select id="cluster"></select></div>
        <div class="fg"><label for="mentor">Label mentor</label><select id="mentor"></select></div>
        <div class="fg"><label for="lecturer">Nama dosen</label><select id="lecturer"></select></div>
        <button type="button" class="btn" id="reset">Reset filter</button>
        @auth <a class="btn" href="{{ url('/dashboard/importrectorate') }}">Import Excel</a> @endauth
        <button type="button" class="btn" id="print">Cetak</button>
        <div class="scope" id="scope" aria-live="polite"></div>
    </div>
</div>
<div class="wrap">
    <div class="notice" id="sourceNote"></div>
    <section class="sec">
        <div class="sech"><span class="n">01</span><h2>Ringkasan capaian</h2><p>Skor operasional 0–6 berdasarkan aturan sistem.</p></div>
        <div class="grid kpis" id="kpis"></div>
    </section>
    <section class="sec grid twocol">
        <div class="card"><div class="sech"><span class="n">02</span><h2>Perkembangan KPI</h2></div><p class="hint">Snapshot terakhir setiap tahun. Cakupan laporan dapat berbeda antarperiode.</p><div id="trend"></div></div>
        <div class="card"><div class="sech"><span class="n">03</span><h2>Status produktivitas dosen</h2></div><p class="hint">Tanpa skor ditampilkan terpisah dari skor nol.</p><div id="productivity"></div></div>
    </section>
    <section class="sec">
        <div class="sech"><span class="n">04</span><h2>Performa program studi</h2><p>Rata-rata hanya mencakup dosen dengan skor tersedia.</p></div>
        <div class="grid twocol"><div class="card"><h3>Rata-rata KPI per prodi</h3><div id="programScores"></div></div><div class="card"><h3>Kontribusi publikasi FM</h3><p class="hint">Jumlah baris dosen–publikasi pada snapshot terpilih.</p><div id="programPapers"></div></div></div>
    </section>
    <section class="sec grid twocol">
        <div class="card"><div class="sech"><span class="n">05</span><h2>Publication risk matrix</h2></div><p class="hint">Skor KPI versus ambang bobot matriks. Profil belum lengkap dan kriteria kualitatif tidak diplot.</p><div id="risk"></div></div>
        <div class="card"><div class="sech"><span class="n">06</span><h2>Dosen yang perlu ditinjau</h2></div><p class="hint">Skor di bawah 3. Tinjau kelengkapan laporan dan status publikasinya sebelum intervensi.</p><div class="scroll" id="interventions"></div></div>
    </section>
    <section class="sec">
        <div class="sech"><span class="n">07</span><h2>Performa dosen dan rincian publikasi</h2></div>
        <div class="card">
            <p class="hint">Urut berdasarkan skor, lalu bobot publikasi. Klik nama untuk melihat rinciannya. Bobot asli disimpan terpisah dari bobot penyesuaian KPI.</p>
            <div class="scroll" id="ranking"></div>
            <h3 style="margin-top:20px">Publikasi pada cakupan aktif</h3>
            <label class="muted" for="search">Cari judul, RequestCode, sumber, atau status</label>
            <input id="search" class="srch" type="search" placeholder="Cari publikasi…">
            <p class="hint" id="paperCount"></p><div class="scroll" id="papers"></div>
        </div>
    </section>
    <section class="sec">
        <div class="sech"><span class="n">08</span><h2>Kapasitas riset dan pendampingan</h2><p>Data riset yang sudah tercatat dalam sistem.</p></div>
        <div class="grid twocol"><div class="card"><h3>Penetapan mentor dan cluster</h3><div id="mentors"></div></div><div class="card"><h3>Peran dalam laporan hibah rectorate</h3><p class="hint">Seluruh tahun anggaran yang tersedia, berdasarkan filter dosen aktif. Pengakuan KPI Research mengikuti nilai Y/N dari sumber.</p><p id="grantSummary" class="hint"></p><div class="scroll" id="grants"></div><p>@auth <a href="{{ route('research-import.index') }}">Import hibah</a> · @endauth <a href="{{ route('research-gallery.index') }}">Galeri riset</a></p></div></div>
        <div class="card" style="margin-top:16px"><h3>Katalog riset dosen</h3><p class="hint">Riwayat seluruh tahun yang tersedia; tidak mengikuti tahun publikasi. Nama peneliti tidak menentukan peran ketua/anggota.</p><div class="scroll" id="research"></div></div>
    </section>
    <section class="sec">
        <div class="sech"><span class="n">09</span><h2>Matriks dan kelengkapan data</h2></div>
        <div class="grid twocol"><div class="card"><h3>Profil matriks dalam cakupan</h3><p class="hint">Ambang dari perhitungan sistem. Penilaian TP S1/S2 menggunakan kriteria kualitatif.</p><div id="matrix"></div>@auth <a href="{{ url('/dashboard/matrixkpidosen') }}">Lihat pedoman matriks sistem →</a> @endauth</div><div class="card"><h3>Data yang perlu dilengkapi</h3><div id="completeness"></div></div></div>
        <div class="card" style="margin-top:16px"><details><summary>Sumber data dan batas perhitungan</summary><div id="issues"></div></details></div>
    </section>
    <section class="sec">
        <div class="sech"><span class="n">10</span><h2>Topik penelitian prioritas 2027</h2><p>Hanya rencana dan SDG yang sudah dicatat secara eksplisit.</p></div>
        <div class="grid twocol"><div class="card"><h3>SDG yang dituju</h3><div id="sdgs"></div></div><div class="card"><h3>Rumusan topik per dosen</h3><div id="priorities"></div></div></div>
    </section>
    <p class="foothr">Sumber: database RCDC Malang dan laporan publikasi Rectorate. Cakupan: Kampus MALANG; Non Scopus FM / Scopus FM. Gunakan filter untuk menelusuri data sesuai kebutuhan.</p>
</div>
<noscript><p>Aktifkan JavaScript untuk menampilkan dashboard interaktif.</p></noscript>
@endsection
@push('scripts')
<script>window.publicationDashboard = {{ Illuminate\Support\Js::from($dashboard) }};</script>
<script src="{{ asset('js/publication-kpi.js') }}" defer></script>
@endpush

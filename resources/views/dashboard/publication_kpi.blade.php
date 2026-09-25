@extends('landing.layout')
@section('title', 'Dashboard KPI Publikasi FM')
@section('page_class', 'publication-page')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/publication-kpi.css') }}?v={{ filemtime(public_path('css/publication-kpi.css')) }}">
@endpush
@section('content')
<div id="tip" role="tooltip"></div>
<section class="portal-hero">
    <div class="container">
        <nav class="portal-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true">/</span><span aria-current="page">Dashboard KPI</span></nav>
        <div class="portal-hero-grid">
            <div>
                <span class="portal-eyebrow">PUBLIKASI &amp; KINERJA RISET</span>
                <h1>Dashboard KPI <span>Publikasi.</span></h1>
                <p class="portal-intro">Telusuri capaian publikasi Faculty Member BINUS Malang, perkembangan program studi, dan potensi kolaborasi riset.</p>
                <p class="portal-caption">Publication Performance Intelligence Dashboard · Peta publikasi dan riset</p>
                <a class="portal-link" href="{{ route('research-gallery.index') }}">Jelajahi Research Gallery <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="stamps" id="stamps" aria-label="Cakupan data publikasi"></div>
        </div>
    </div>
</section>
<div class="filters">
  <div class="fin">
    <div class="fg"><label for="fYear">Tahun</label><select id="fYear"></select></div>
    <div class="fg"><label for="fProdi">Program studi</label><select id="fProdi"></select></div>
    <div class="fg"><label for="fType">Faculty type</label><select id="fType"></select></div>
    <div class="fg"><label for="fPend">Pendidikan</label><select id="fPend"></select></div>
    <div class="fg"><label for="fJJA">Jabatan akademik</label><select id="fJJA"></select></div>
    <div class="fg"><label for="fClu">Cluster FM</label><select id="fClu"></select></div>
    <div class="fg"><label for="fLab">Label mentor</label><select id="fLab"></select></div>
    <div class="fg"><label for="fDosen">Nama dosen</label><select id="fDosen"></select></div>
    <button type="button" class="btn" id="fReset">Kosongkan filter</button>
    <div class="fspacer"></div>
    <div class="scope" id="scope" aria-live="polite"></div>
  </div>
</div>

<div class="wrap">

  <section class="sec">
    <div class="sech"><span class="n">01</span><h2>Ringkasan capaian</h2><p id="kpiSub"></p></div>
    <div class="grid kpis" id="kpiRow"></div>
  </section>

  <section class="sec">
    <div class="grid" style="grid-template-columns:1.35fr 1fr">
      <div class="card">
        <div class="sech" style="margin-bottom:2px"><span class="n">02</span><h2 id="trendTitle">Perkembangan KPI</h2></div>
        <p class="hint" id="trendHint"></p>
        <div id="trend"></div>
        <div class="legend" id="trendLeg"></div>
        <p class="note">Skor KPI memakai skala 0&ndash;6. Grafik mengikuti snapshot terakhir setiap tahun. Tahun tanpa skor dibiarkan kosong.</p>
      </div>
      <div class="card">
        <div class="sech" style="margin-bottom:2px"><span class="n">03</span><h2>Status produktivitas dosen</h2></div>
        <p class="hint" id="donutHint"></p>
        <div id="donut"></div>
        <div class="legend" id="donutLeg"></div>
      </div>
    </div>
  </section>

  <section class="sec">
    <div class="sech"><span class="n">04</span><h2>Performa program studi</h2><p>Peringkat prodi menurut rata-rata skor KPI dan intensitas keterlibatan hibah penelitian.</p></div>
    <div class="grid" style="grid-template-columns:1fr 1fr">
      <div class="card"><h3>Rata-rata skor KPI</h3><p class="hint" id="pr1Hint"></p><div id="prodiKPI"></div></div>
      <div class="card"><h3>Keterlibatan hibah per dosen</h3><p class="hint">Total peran ketua dan anggota pada tahun yang tersedia dibagi jumlah dosen prodi. Garis putus-putus adalah rata-rata institusi.</p><div id="prodiHibah"></div></div>
    </div>
  </section>

  <section class="sec">
    <div class="grid" style="grid-template-columns:1.15fr 1fr">
      <div class="card">
        <div class="sech" style="margin-bottom:2px"><span class="n">05</span><h2>Publication risk matrix</h2></div>
        <p class="hint" id="scatHint"></p>
        <div id="scatter"></div>
        <div class="legend" id="scatLeg"></div>
      </div>
      <div class="card">
        <div class="sech" style="margin-bottom:2px"><span class="n">06</span><h2>Dosen yang perlu intervensi</h2></div>
        <p class="hint" id="needHint"></p>
        <div class="scroll"><div id="needTbl"></div></div>
      </div>
    </div>
  </section>

  <section class="sec">
    <div class="sech"><span class="n">07</span><h2>Peringkat dosen</h2><p>Diurutkan menurut skor KPI, lalu total keterlibatan hibah.</p></div>
    <div class="card">
      <div class="mini" id="topToggle">
        <button type="button" data-n="10" aria-pressed="true">Top 10</button>
        <button type="button" data-n="25" aria-pressed="false">Top 25</button>
        <button type="button" data-n="0" aria-pressed="false">Semua dosen</button>
      </div>
      <div class="scroll" style="max-height:520px"><div id="topTbl"></div></div>
      <p class="note" id="topNote"></p>
    </div>
  </section>

  <section class="sec">
    <div class="sech"><span class="n">08</span><h2>Kapasitas riset dan pendampingan</h2><p>Ketersediaan mentor, riwayat keterlibatan hibah, dan tren peran per tahun.</p></div>
    <div class="grid" style="grid-template-columns:1fr 1fr">
      <div class="card"><h3>Sebaran label mentor per prodi</h3><p class="hint" id="mentorHint"></p><div id="mentorBar"></div><div class="legend" id="mentorLeg"></div></div>
      <div class="card"><h3>Status keterlibatan hibah per prodi</h3><p class="hint" id="hibahHint"></p><div id="hibahBar"></div><div class="legend" id="hibahLeg"></div></div>
    </div>
    <div class="card" style="margin-top:16px">
      <h3 id="grantTitle">Peran dalam hibah penelitian</h3>
      <p class="hint">Jumlah peran ketua dan anggota yang tercatat tiap tahun pada cakupan aktif.</p>
      <div id="peranBar"></div><div class="legend" id="peranLeg"></div>
    </div>
  </section>

  <section class="sec">
    <div class="sech"><span class="n">09</span><h2>Ambang matriks dan cluster faculty member</h2><p>Pengelompokan berdasarkan profil dosen, publikasi Scopus, dan riwayat hibah.</p></div>
    <div class="grid" style="grid-template-columns:1.45fr 1fr">
      <div class="card">
        <h3>Ambang skor menurut kolom matriks</h3>
        <p class="hint">Ringkasan pedoman matriks. Skor 3 dan 4 menunjukkan ambang bobot; angka skor 6 merupakan syarat tambahan jurnal Scopus setelah skor 4 terpenuhi.</p>
        <div class="scroll" style="max-height:none"><div id="matriksTbl"></div></div>
        <div id="kolomBar" style="margin-top:16px"></div>
        <p class="hint" style="margin:8px 0 0" id="kolomHint"></p>
      </div>
      <div class="card">
        <h3>Cluster faculty member</h3>
        <p class="hint" id="clusterHint"></p>
        <div id="cluBox"></div>
      </div>
    </div>
    <div class="card" style="margin-top:16px">
      <h3>Dasar cluster per dosen</h3>
      <p class="hint">Profil memakai master dosen terbaru. Bukti publikasi dan hibah dibatasi sampai tahun terpilih. Indikasi otomatis membantu pemetaan; penetapan cluster yang tersimpan tetap diutamakan.</p>
      <div class="scroll" style="max-height:420px"><div id="clusterTbl"></div></div>
    </div>
  </section>

  <section class="sec" id="research-topics">
    <div class="sech"><span class="n">10</span><h2>Peta SDG dan topik penelitian</h2><p>SDG dominan dan rumusan topik dari hibah yang telah diupload, mengikuti tahun anggaran dan cakupan dosen terpilih.</p></div>
    <div class="grid" style="grid-template-columns:1fr 1.9fr">
      <div class="card"><h3>SDG paling banyak pada hibah</h3><p class="hint" id="sdgHint"></p><div id="sdgBar"></div><div id="sdgFocus" class="note"></div></div>
      <div class="card">
        <h3>Rumusan topik per dosen</h3>
        <p class="hint" id="topikHint"></p>
        <label for="fTopicSdg">Tampilkan topik pada SDG</label>
        <select class="srch" id="fTopicSdg"><option value="">Semua SDG</option></select>
        <input class="srch" id="fTopik" type="search" placeholder="Cari kata kunci topik, nama dosen, atau SDG&hellip;" aria-label="Cari topik penelitian">
        <div class="scroll" style="max-height:460px"><div id="topikTbl"></div></div>
      </div>
    </div>
  </section>

  <div class="foothr" id="footnote"></div>
    <div class="dashboard-actions">
        <button class="btn" type="button" id="printDashboard">Cetak dashboard</button>
        @auth
            <a class="btn" href="{{ url('/dashboard/importrectorate') }}">Import Excel</a>
            <a class="btn" href="{{ url('/dashboard/importrectorate') }}">Import hibah</a>
            <a class="btn" href="{{ url('/dashboard/matrixkpidosen') }}">Pedoman matriks</a>
        @endauth
    </div>
</div>

<noscript><p>Aktifkan JavaScript untuk menampilkan dashboard interaktif.</p></noscript>
@endsection
@push('scripts')
<script>window.publicationDashboard = {{ Illuminate\Support\Js::from($dashboard) }};</script>
<script src="{{ asset('js/publication-research.js') }}?v={{ filemtime(public_path('js/publication-research.js')) }}" defer></script>
<script src="{{ asset('js/publication-kpi.js') }}?v={{ filemtime(public_path('js/publication-kpi.js')) }}" defer></script>
@endpush

@extends('landing.research_gallery.layout')
@section('title', $project['title'])
@section('content')
<div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb"><a href="{{ route('research-gallery.index', ['source' => $project['source']]) }}">Galeri riset</a><span>/</span><span>{{ $project['year'] }}</span></nav>
    <div class="detail-grid">
        <aside class="viewer">
            <div class="rg-poster" id="research-poster">
                <span class="rg-label">RCDC · BINUS Malang / {{ $project['source_label'] }}</span>
                <h2>{{ $project['title'] }}</h2>
                <p>{{ $project['code'] ?: 'Kode belum tersedia' }} · {{ $project['year'] }}</p>
                <div class="tags">@foreach($project['sdgs'] as $sdg)<span class="tag">SDG {{ $sdg }}</span>@endforeach</div>
                <dl><dt>Bidang ilmu</dt><dd>{{ implode(' · ', $project['fields']) ?: 'Belum tercatat' }}</dd><dt>Tim peneliti yang tercatat</dt><dd>{{ implode(', ', array_column($project['people'], 'name')) }}</dd><dt>Sumber pendanaan</dt><dd>{{ implode(' · ', $project['funds']) ?: 'Belum tercatat' }}</dd></dl>
                <p class="micro">Ringkasan metadata riset dari sumber yang tertera.</p>
            </div>
            <div class="viewer-tools"><button class="btn btn-ghost btn-sm" id="print-research" type="button">Cetak / simpan PDF</button>@if($project['permalink'])<a class="btn btn-ghost btn-sm" href="{{ $project['permalink'] }}" target="_blank" rel="noopener noreferrer">Buka sumber ↗</a>@endif</div>
        </aside>
        <div>
            <span class="rg-source">{{ $project['source_label'] }}</span>
            <h1 class="display-md detail-title">{{ $project['title'] }}</h1>
            <dl class="factsheet">
                @foreach(['Tahun anggaran' => $project['year'], 'Kode proposal / kontrak' => $project['code'], 'Fakultas' => implode(' · ', $project['faculties']), 'Program studi' => implode(' · ', $project['programs']), 'Program hibah' => implode(' · ', $project['grant_programs']), 'Skema' => implode(' · ', $project['schemes']), 'Jenis penelitian' => implode(' · ', $project['types']), 'Status usulan' => implode(' · ', $project['statuses']), 'Sumber pendanaan' => implode(' · ', $project['funds']), 'Pemberi hibah' => implode(' · ', $project['funders'])] as $label => $value)
                    <div class="fact"><dt>{{ $label }}</dt><dd>{{ $value ?: 'Belum tercatat' }}</dd></div>
                @endforeach
                <div class="fact"><dt>Dana proyek tercatat</dt><dd>{{ $project['amount'] !== null ? 'Rp '.number_format((float) $project['amount'], 0, ',', '.') : 'Belum tersedia / perlu konfirmasi' }}<div class="micro">Nilai tingkat proyek; tidak dijumlahkan berulang per peneliti.</div></dd></div>
                <div class="fact"><dt>Periode penelitian</dt><dd>{{ $project['start'] ?? 'Belum tercatat' }} — {{ $project['end'] ?? 'Belum tercatat' }}</dd></div>
                <div class="fact"><dt>Peneliti dan peran</dt><dd>@foreach($project['people'] as $person)<span class="rg-person"><a href="{{ route('research-gallery.index', ['source' => $project['source'], 'person' => $person['code']]) }}#showcase">{{ $person['name'] }}</a><small>{{ $person['role'] }} · {{ $person['program'] ?? 'Prodi belum tercatat' }}</small></span>@endforeach</dd></div>
            </dl>
            @if($project['source'] === 'rectorate')<p class="rg-source-note">Tim di halaman ini hanya mencakup baris berlokasi Binus @Malang. Ketua atau anggota dari kampus lain dapat berada di luar cakupan laporan ini. Status “Baru” adalah status usulan dari sumber.</p>@endif
        </div>
    </div>
    <section class="summary rg-notes">
        @foreach(['abstracts' => 'Abstrak dari sistem', 'outputs' => 'Produk yang dicatat', 'sdg_notes' => 'Keterangan SDG dari laporan', 'roadmap' => 'Topik roadmap dari laporan'] as $field => $label)
            @if($project[$field])<article class="sum-card"><div class="sum-head"><h2 class="h3">{{ $label }}</h2></div>@foreach($project[$field] as $text)<p style="margin-top:12px">{{ $text }}</p>@endforeach</article>@endif
        @endforeach
        @if(!$project['abstracts'] && !$project['outputs'])<div class="sum-card"><h2 class="h3">Luaran dan ringkasan hasil</h2><p style="margin-top:12px">Abstrak, metode, dan hasil terukur belum tersedia dalam sumber proyek ini.</p></div>@endif
        @if($project['variants'])<details class="form-note"><summary>Beberapa metadata perlu konfirmasi</summary><p>Nilai berikut berbeda antarbaris dalam proyek yang sama dan tetap ditampilkan sesuai sumber.</p>@foreach($project['variants'] as $field => $values)<b>{{ ['judul'=>'Judul', 'program_hibah'=>'Program hibah', 'skema'=>'Skema', 'approved_amount'=>'Dana disetujui', 'starts_on'=>'Tanggal mulai', 'ends_on'=>'Tanggal selesai'][$field] ?? $field }}</b><ul>@foreach($values as $value)<li>{{ $value }}</li>@endforeach</ul>@endforeach</details>@endif
        @if($project['row_issues'])<p class="form-note">Beberapa nilai sumber tidak dapat dinormalisasi: {{ implode(', ', $project['row_issues']) }}. Nilai asli tersimpan untuk pemeriksaan pengelola.</p>@endif
        @if($project['source'] === 'rectorate')<p class="micro">SDG, bidang ilmu, dan produk merupakan gabungan nilai yang tercatat pada anggota proyek. Data tersebut tidak otomatis menjadi topik prioritas 2027. Pembaruan: {{ $project['updated_at'] }}.</p>@endif
    </section>
</div>
@endsection
@push('scripts')<script>document.getElementById('print-research').addEventListener('click', () => window.print());</script>@endpush

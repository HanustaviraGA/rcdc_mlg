@extends('landing.research_gallery.layout')
@section('title', 'Import Riset dan Hibah Rectorate')
@section('content')
<div class="wrap band-tight">
    <p><a href="{{ url('/dashboard') }}">← Kembali ke dashboard</a></p>
    <h1 class="display-md">Import riset dan hibah Rectorate</h1>
    <p class="lede" style="margin-top:16px">Unggah file XLSX laporan rectorate. Sistem membaca sheet <b>Detail</b> dengan <b>Lokasi Kampus = Binus @Malang</b>.</p>
    <div class="form-note" style="margin-top:24px">Tahun mengikuti kolom Tahun Anggaran. Upload baru menjadi sumber terbaru untuk tahun-tahun yang tercantum dalam file; riwayat upload sebelumnya tetap tersimpan. File yang sama tidak diimpor dua kali. Data ini disimpan terpisah dari katalog sistem riset.</div>
    @if($errors->any())<div class="form-note" role="alert"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if(session('research_summary'))
        @php($summary = session('research_summary'))
        <div class="sum-card sum-impact" role="status"><h2 class="h3">{{ $summary['already_imported'] ? 'File ini sudah pernah diimpor' : 'Import berhasil' }}</h2><p>{{ $summary['selected'] }} baris · {{ $summary['projects'] }} proyek · {{ $summary['researchers'] }} peneliti.</p><p>{{ $summary['excluded_campus'] }} baris kampus lain dilewati; {{ $summary['duplicates'] }} duplikat dilewati.</p><p>Tahun: {{ implode(', ', array_keys($summary['years'])) }}.</p>
        @if($summary['missing'])<p>Data belum lengkap:</p><ul>@foreach($summary['missing'] as $field => $count)<li>{{ \App\Services\Research\RectorateResearchReader::HEADERS[$field] ?? $field }}: {{ $count }} baris</li>@endforeach</ul>@endif
        @if($summary['invalid_values'])<p>Nilai perlu diperiksa: {{ implode(', ', array_keys($summary['invalid_values'])) }}.</p>@endif
        @if($summary['unmatched_fm_codes'])<p>Kode FM belum ada di master: {{ implode(', ', $summary['unmatched_fm_codes']) }}.</p>@endif</div>
    @endif
    <form action="{{ route('research-import.store') }}" method="post" enctype="multipart/form-data" class="panel" style="margin-top:24px">
        @csrf
        <div class="field"><label for="research_file">File laporan (.xlsx, maksimal 20 MB)</label><input id="research_file" name="research_file" type="file" accept=".xlsx" required></div>
        <div class="form-actions"><button class="btn" type="submit">Upload dan import</button><a class="btn btn-ghost" href="{{ route('research-gallery.index') }}">Lihat galeri</a><a class="btn btn-ghost" href="{{ route('publication-dashboard') }}">Dashboard KPI</a></div>
    </form>
    <h2 class="h3" style="margin-top:32px">Riwayat upload</h2>
    <div style="overflow:auto"><table class="rg-table"><thead><tr><th>File</th><th>Tanggal upload</th><th>Tahun data</th><th>Baris</th><th>Proyek</th></tr></thead><tbody>@forelse($imports as $import)@php($report = json_decode($import->summary, true))<tr><td>{{ $import->filename }}</td><td>{{ $import->created_at }}</td><td>{{ implode(', ', array_keys($report['years'])) }}</td><td>{{ $report['selected'] }}</td><td>{{ $report['projects'] }}</td></tr>@empty<tr><td colspan="5">Belum ada file yang diimpor.</td></tr>@endforelse</tbody></table></div>
</div>
@endsection

@foreach($summaries as $type => $summary)
    <div class="alert alert-success mb-5" role="status">
        <b>{{ ['fm_file' => 'Publikasi FM', 'mhs_file' => 'Publikasi MHS', 'research_file' => 'Hibah'][$type] }} berhasil diproses</b>
        <p class="mb-2">{{ $summary['selected'] }} baris dari {{ $summary['sheet'] }}; bulan {{ $summary['month'] }}/{{ $summary['year'] }}.</p>
        @if(isset($summary['projects']))<p class="mb-2">{{ $summary['projects'] }} proyek; {{ $summary['researchers'] }} peneliti.</p>@endif
        @if(isset($summary['kpi']['lecturers']))<p class="mb-2">{{ $summary['kpi']['lecturers'] }} dosen dari sheet KPI.</p>@endif
        @if(isset($summary['list_count']))<p class="mb-2">{{ $summary['list_count'] }} entri LIST; {{ $summary['scopus'] }} baris Scopus.</p>@endif
        @foreach($summary['warnings'] ?? [] as $warning)<p class="mb-2">{{ $warning }}</p>@endforeach
        @foreach($summary['missing'] ?? [] as $field => $count)<p class="mb-2">Belum lengkap: {{ \App\Services\Research\RectorateResearchReader::HEADERS[$field] ?? $field }} ({{ $count }} baris).</p>@endforeach
        @if(!empty($summary['invalid_values']))<p class="mb-2">Nilai perlu diperiksa: {{ implode(', ', array_keys($summary['invalid_values'])) }}.</p>@endif
        @if(!empty($summary['unmatched_fm_codes']) || !empty($summary['unmatched_codes']))<p class="mb-0">Kode FM belum ada di master: {{ implode(', ', $summary['unmatched_fm_codes'] ?? $summary['unmatched_codes']) }}.</p>@endif
    </div>
@endforeach

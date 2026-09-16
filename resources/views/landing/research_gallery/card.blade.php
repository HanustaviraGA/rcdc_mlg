@php($tone = ['','green','gold'][hexdec(substr($project['id'], 0, 2)) % 3])
<article class="card" data-project-source="{{ $project['source'] }}">
    <div class="rg-cover {{ $tone }}" aria-hidden="true">
        <span class="rg-label">{{ $project['source_label'] }}</span>
        <svg viewBox="0 0 240 190" fill="none"><circle cx="120" cy="95" r="70" stroke="white"/><circle cx="120" cy="95" r="48" stroke="white"/><path d="M25 145L65 40L165 25L210 135L120 175Z M65 40L120 175L165 25L25 145L210 135L65 40" stroke="white"/><g fill="white"><circle cx="65" cy="40" r="6"/><circle cx="165" cy="25" r="6"/><circle cx="25" cy="145" r="6"/><circle cx="120" cy="175" r="6"/><circle cx="210" cy="135" r="6"/></g></svg>
        <strong>{{ $project['year'] ?: '—' }}</strong>
        <span class="micro">{{ Illuminate\Support\Str::limit($project['fields'][0] ?? $project['funds'][0] ?? 'Riset BINUS Malang', 65) }}</span>
        <div class="tags">@foreach(array_slice($project['sdgs'], 0, 4) as $sdg)<span class="tag">SDG {{ $sdg }}</span>@endforeach</div>
    </div>
    <div class="card-body">
        <div class="card-cat"><span class="dot" style="background:var(--verdant)"></span>{{ $project['source_label'] }}</div>
        <h2 class="card-title"><a href="{{ route('research-gallery.show', [$project['source'], $project['id']]) }}">{{ $project['title'] }}</a></h2>
        <div class="card-meta"><b>{{ $project['people'][0]['name'] ?? 'Peneliti belum tercatat' }}</b>@if(count($project['people']) > 1) dan {{ count($project['people']) - 1 }} peneliti lainnya @endif</div>
        <div class="card-foot"><span>{{ $project['year'] ?: 'Tahun belum tercatat' }}</span><span>{{ count($project['people']) }} peneliti</span><a style="margin-left:auto" href="{{ route('research-gallery.show', [$project['source'], $project['id']]) }}">Lihat riset →</a></div>
    </div>
</article>

<section id="research" class="section rg-home" aria-labelledby="researchGalleryHeading">
    <style>
        .rg-home { background: var(--background-color); padding: 72px 0; }
        .rg-home .rg-heading { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 24px; margin-bottom: 28px; }
        .rg-home .section-title { min-width: 0; padding-bottom: 0; }
        .rg-home .section-title h2 { line-height: 1.3; }
        .rg-home .rg-cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 22px; }
        .rg-home .rg-item { border: 1px solid var(--brand-100); background: var(--surface-color); border-radius: 10px; overflow: hidden; display: flex; flex-direction: column; }
        .rg-home .rg-art { padding: 22px; background: var(--brand-800); color: var(--contrast-color); min-height: 155px; display: flex; flex-direction: column; justify-content: space-between; background-image: radial-gradient(circle at 80% 60%, transparent 35%, #ffffff20 36%, transparent 37%); }
        .rg-home .rg-art small { font-size: 11px; letter-spacing: .06em; color: var(--brand-100); }
        .rg-home .rg-art strong { font-family: var(--heading-font); font-size: 44px; font-weight: 500; }
        .rg-home .rg-body { padding: 22px; }
        .rg-home h3 { font-family: var(--heading-font); font-size: 21px; font-weight: 500; line-height: 1.4; color: var(--heading-color); }
        .rg-home .rg-meta { font-size: 12px; color: color-mix(in srgb, var(--default-color), transparent 35%); margin: 14px 0; }
        .rg-home .rg-link { color: var(--brand-700); font-size: 13px; font-weight: 600; }
        .rg-home .rg-browse { border: 1px solid var(--brand-600); color: var(--brand-700); padding: 11px 18px; border-radius: 6px; white-space: nowrap; text-decoration: none; }
        .rg-home .rg-browse:hover { background: var(--brand-600); color: var(--contrast-color); }
        .rg-home h3 a { color: inherit; }
        .rg-home h3 a:hover, .rg-home .rg-link:hover { color: var(--brand-500); }
        @media (max-width: 991px) { .rg-home .rg-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 575px) { .rg-home .rg-cards { grid-template-columns: 1fr; } }
    </style>
    <div class="container">
        <div class="rg-heading"><div class="section-title"><h2 id="researchGalleryHeading">Research Gallery</h2></div><a class="rg-browse" href="{{ route('research-gallery.index') }}">Jelajahi galeri riset →</a></div>
        <div class="rg-cards">
            @forelse($researchGalleryProjects as $project)
                <article class="rg-item"><div class="rg-art"><small>{{ $project['source_label'] }}</small><strong>{{ $project['year'] }}</strong></div><div class="rg-body"><h3><a href="{{ route('research-gallery.show', [$project['source'], $project['id']]) }}">{{ $project['title'] }}</a></h3><p class="rg-meta">{{ count($project['people']) }} peneliti @if($project['sdgs']) · SDG {{ implode(', ', $project['sdgs']) }} @endif</p><a class="rg-link" href="{{ route('research-gallery.show', [$project['source'], $project['id']]) }}">Baca profil riset →</a></div></article>
            @empty
                <p>Data riset belum tersedia.</p>
            @endforelse
        </div>
        <p class="rg-meta">{{ $researchGalleryCount }} proyek tersedia pada sumber {{ $researchGalleryProjects->first()['source_label'] ?? 'riset' }}. Galeri menyediakan pilihan sumber Rectorate dan Sistem Riset.</p>
    </div>
</section>

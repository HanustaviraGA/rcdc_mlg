<section id="research" class="section rg-home">
    <style>
        .rg-home{background:#f4f6f9;padding:72px 0}.rg-home .rg-heading{display:flex;justify-content:space-between;align-items:end;gap:24px;margin-bottom:28px}.rg-home .rg-eyebrow{font-size:12px;letter-spacing:.14em;text-transform:uppercase;color:#1b8a6b;font-weight:600}.rg-home h2{font:400 46px/1.12 Georgia,serif;color:#0c2340;margin:12px 0}.rg-home .rg-intro{color:#4a5c72;max-width:640px}.rg-home .rg-cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:22px}.rg-home .rg-item{border:1px solid #dfe4ec;background:white;border-radius:10px;overflow:hidden;display:flex;flex-direction:column}.rg-home .rg-art{padding:22px;background:#0c2340;color:white;min-height:155px;display:flex;flex-direction:column;justify-content:space-between;background-image:radial-gradient(circle at 80% 60%,transparent 35%,#ffffff20 36%,transparent 37%)}.rg-home .rg-art small{font-size:11px;letter-spacing:.06em;color:#c5d5e3}.rg-home .rg-art strong{font:400 44px Georgia,serif}.rg-home .rg-body{padding:22px}.rg-home h3{font:400 21px/1.25 Georgia,serif;color:#0c2340}.rg-home .rg-meta{font-size:12px;color:#71829a;margin:14px 0}.rg-home .rg-link{color:#1b8a6b;font-size:13px;font-weight:600}.rg-home .rg-browse{border:1px solid #0c2340;color:#0c2340;padding:11px 18px;border-radius:24px;white-space:nowrap;text-decoration:none}.rg-home .rg-browse:hover{background:#0c2340;color:white}
        .rg-home h3 a{color:inherit}.rg-home h3 a:hover{color:#1b8a6b}
        @media(max-width:991px){.rg-home .rg-cards{grid-template-columns:repeat(2,minmax(0,1fr))}.rg-home .rg-heading{display:block}.rg-home .rg-browse{display:inline-block;margin-top:14px}}@media(max-width:575px){.rg-home .rg-cards{grid-template-columns:1fr}.rg-home h2{font-size:36px}}
    </style>
    <div class="container">
        <div class="rg-heading"><div><span class="rg-eyebrow">Research Gallery · BINUS Malang</span><h2>Riset yang mempertemukan<br>ide dan kolaborasi.</h2><p class="rg-intro">Telusuri proyek, tim peneliti, dan tujuan pembangunan berkelanjutan yang tercatat dalam laporan riset.</p></div><a class="rg-browse" href="{{ route('research-gallery.index') }}">Jelajahi galeri riset →</a></div>
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

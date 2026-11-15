{{-- The planned share of S3 FM per year (Roadmap LRC): a box per year with S3 FM / total FM = percentage. --}}
@php($projection = $lrc['projection'])
@php($number = fn ($value) => number_format($value, 0, ',', '.'))
<article class="lrc-projection" aria-labelledby="lrcProjectionHeading">
    <h3 id="lrcProjectionHeading">Proyeksi FM S3</h3>
    <p class="lrc-projection-caption">
        Persentase FM bergelar S3 terhadap total FM per tahun berdasarkan roadmap jumlah FM ·
        @if(empty($projection['prodi']))
            Belum ada roadmap untuk prodi pada filter ini.
        @elseif($projection['narrowed'])
            Prodi {{ implode(', ', $projection['prodi']) }}.
        @else
            Seluruh prodi ({{ count($projection['prodi']) }}).
        @endif
        @if($projection['statusIgnored'])
            Roadmap tidak dibedakan per status dosen.
        @endif
    </p>
    @if($projection['years'])
        <ol class="lrc-projection-list" tabindex="0" aria-label="Proyeksi FM S3 per tahun. Geser untuk melihat tahun lainnya.">
            @foreach($projection['years'] as $item)
                @php($percent = $item['percent'] === null ? '–' : number_format($item['percent'], 1, ',', '.').'%')
                <li class="lrc-projection-card" data-lrc-projection="{{ $item['year'] }}">
                    <h4>{{ $item['year'] }}</h4>
                    <span class="visually-hidden">{{ $number($item['s3']) }} dari {{ $number($item['fm']) }} FM bergelar S3, {{ $percent }}</span>
                    <div class="lrc-projection-formula" aria-hidden="true">
                        <span class="lrc-ratio">
                            <strong class="lrc-ratio-top" data-lrc-projection-s3>{{ $number($item['s3']) }}</strong>
                            <span class="lrc-ratio-slash">/</span>
                            <strong class="lrc-ratio-bottom" data-lrc-projection-fm>{{ $number($item['fm']) }}</strong>
                        </span>
                        <span class="lrc-projection-result">= <strong data-lrc-projection-percent>{{ $percent }}</strong></span>
                    </div>
                    <small class="lrc-projection-legend" aria-hidden="true">Jml FM S3 / Total FM</small>
                </li>
            @endforeach
        </ol>
    @endif
</article>

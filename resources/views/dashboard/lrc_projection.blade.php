{{-- The planned share of S3 FM per year (Roadmap LRC), the body of the Proyeksi FM S3 section: a box per year with
     S3 FM / total FM = percentage. A box opens the FM S3 of that year by name, grouped per program: the existing S3
     and the lecturers projected to graduate S3 that year. --}}
@php($projection = $lrc['projection'])
@php($number = fn ($value) => number_format($value, 0, ',', '.'))
<article class="lrc-projection">
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
                <li>
                    <button type="button" class="lrc-projection-card" data-lrc-projection="{{ $item['year'] }}" aria-haspopup="dialog">
                        <span class="lrc-projection-year">{{ $item['year'] }}</span>
                        <span class="visually-hidden">: {{ $number($item['s3']) }} dari {{ $number($item['fm']) }} FM bergelar S3, {{ $percent }}. Lihat FM S3 eksisting dan proyeksi lulus S3 per prodi.</span>
                        <span class="lrc-projection-formula" aria-hidden="true">
                            <span class="lrc-ratio">
                                <strong class="lrc-ratio-top" data-lrc-projection-s3>{{ $number($item['s3']) }}</strong>
                                <span class="lrc-ratio-slash">/</span>
                                <strong class="lrc-ratio-bottom" data-lrc-projection-fm>{{ $number($item['fm']) }}</strong>
                            </span>
                            <span class="lrc-projection-result">= <strong data-lrc-projection-percent>{{ $percent }}</strong></span>
                        </span>
                        <small class="lrc-projection-legend" aria-hidden="true">Jml FM S3 / Total FM</small>
                    </button>
                </li>
            @endforeach
        </ol>
    @endif
</article>
@if($projection['years'])
    <dialog class="pd-dialog lrc-dialog" id="lrcProjectionDetail" aria-labelledby="lrcProjectionTitle" aria-describedby="lrcProjectionCaption">
        <div class="pd-dialog-heading">
            <div><span class="pd-eyebrow" id="lrcProjectionEyebrow">PROYEKSI FM S3</span><h2 id="lrcProjectionTitle">Proyeksi FM S3</h2></div>
            <button class="pd-icon-button" type="button" id="lrcProjectionClose" aria-label="Tutup rincian proyeksi"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </div>
        <div class="pd-dialog-body">
            <p class="pd-table-caption" id="lrcProjectionCaption"></p>
            <div id="lrcProjectionSummary" class="lrc-formula"></div>
            <div class="pd-table-scroll"><table class="pd-table lrc-projection-table" id="lrcProjectionTable"></table></div>
            <p class="pd-table-meta" id="lrcProjectionMeta" role="status" aria-live="polite"></p>
        </div>
    </dialog>
@endif

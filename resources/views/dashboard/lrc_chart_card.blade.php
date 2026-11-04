{{-- A chart whose bars/slices, and the category buttons below it, open the lecturers in that category. --}}
@php($chart = $lrc['charts'][$key])
<article class="lrc-chart-card" data-lrc-chart-card="{{ $key }}" data-lrc-title="{{ $title }}">
    <h3>{{ $title }}</h3>
    <div class="lrc-chart-wrap"><canvas id="{{ $canvas }}" aria-label="Chart {{ $title }}. Pilih kategori di bawah chart untuk melihat daftar dosen."></canvas></div>
    <div class="lrc-chart-actions" role="group" aria-label="Daftar dosen per kategori {{ $title }}">
        @foreach($chart['labels'] as $index => $label)
            <button type="button" class="lrc-chip" data-lrc-chart="{{ $key }}" data-lrc-index="{{ $index }}" @disabled(! $chart['data'][$index])>{{ $label }} <strong>{{ number_format($chart['data'][$index], 0, ',', '.') }}</strong></button>
        @endforeach
    </div>
</article>

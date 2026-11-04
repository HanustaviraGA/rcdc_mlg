{{-- CBDC as a whole: the realization, the quarter's target, and the score earned from the targets of the year. --}}
@php($number = fn ($value) => $value === null ? '—' : \App\Services\CharacterBuilding\CharacterBuildingTargets::number((float) $value))
@php(['ladder' => $ladder, 'target' => $target, 'reached' => $reached, 'next' => $next, 'realization' => $realization] = $summary)
<div class="row g-4 mb-6">
    <div class="col-md-4">
        <div class="border rounded p-5 h-100">
            <div class="text-muted fs-7">Realisasi CBDC</div>
            <div class="fs-2hx fw-bold" data-cb-stat="realization">{{ $number($realization) }}</div>
            <div class="text-muted fs-8">{{ $realization === null ? 'Belum ada data upload.' : $unit.' '.$realizationNote }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="border rounded p-5 h-100">
            <div class="text-muted fs-7">Target {{ $periodLabel }}</div>
            <div class="fs-2hx fw-bold" data-cb-stat="target">{{ $number($target?->target) }}</div>
            @if(! $target)
                <div class="text-muted fs-8">Belum ditetapkan; isi pada tab Target Skor.</div>
            @else
                <div class="text-muted fs-8 mb-1">{{ $unit }}, skor {{ $number($target->score) }}.</div>
                @if($realization !== null)
                    @if($realization >= $target->target)
                        <span class="badge bg-success-subtle text-success">Tercapai</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger">Belum tercapai</span>
                    @endif
                @endif
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <div class="border rounded p-5 h-100">
            <div class="text-muted fs-7">Skor</div>
            <div class="fs-2hx fw-bold" data-cb-stat="score">{{ $number($summary['earned']) }}</div>
            <div class="text-muted fs-8">
                @if($realization !== null && $ladder->isNotEmpty())
                    @if(! $reached)
                        Belum mencapai target terendah ({{ $number($ladder->first()->target) }}).
                    @elseif(! $next)
                        Skor maksimal.
                    @endif
                @endif
                @if($next)
                    <div data-cb-next>Kurang {{ $number($summary['gap']) }} untuk skor {{ $number($next->score) }} (target {{ $number($next->target) }}).</div>
                @endif
            </div>
        </div>
    </div>
</div>

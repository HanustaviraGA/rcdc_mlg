{{-- One year of a roadmap row: the FM count, S2 and S3, then the New FM Recruits (NR S2, NR S3) joining that year.
     A program's numbers are read-only inputs that open the edit modal; the total row only shows its sums.
     The FM count, S2 and S3 are marked when S2 + S3 differs from the FM count. --}}
@php($warning = $cell && $cell['mismatch'] ? 'S2 + S3 = '.($cell['s2'] + $cell['s3']).', tidak sama dengan jumlah FM '.$cell['fm'] : null)
@foreach(\App\Services\Lecturers\ProdiFmRoadmap::FIELDS as $field => $definition)
    @php($note = $cell['notes'][$field] ?? null)
    @php($editable = isset($cell['id']))
    @php($marked = in_array($field, \App\Services\Lecturers\ProdiFmRoadmap::LEVELS, true) ? $warning : null)
    <td @class(['roadmap-year-start' => $field === 'fm', 'roadmap-nr' => ! $marked && str_starts_with($field, 'nr_'), 'bg-light-warning' => $marked, 'roadmap-has-note' => $note !== null]) data-roadmap-cell="{{ $key }}-{{ $field }}"
        @if(! $editable && $marked) title="{{ $marked }}" @endif>
        @if($editable)
            @php($title = implode("\n", array_filter([$marked, $note !== null ? 'Catatan: '.$note : null])))
            <input type="text" readonly @class(['form-control form-control-sm form-control-solid roadmap-input', 'fw-bold' => $field === 'fm', 'bg-light-warning border-warning' => $marked])
                value="{{ $cell[$field] }}" aria-haspopup="dialog" aria-label="{{ $definition['label'] }} {{ $name }}{{ $note !== null ? ' (ada catatan)' : '' }}"
                data-roadmap-edit data-id="{{ $cell['id'] }}" data-field="{{ $field }}" data-label="{{ $definition['label'] }} {{ $name }}"
                data-value="{{ $cell[$field] }}" data-note="{{ $note }}" @if($title !== '') title="{{ $title }}" @endif>
        @else
            {{ $cell[$field] ?? '–' }}
        @endif
    </td>
@endforeach

{{-- One year of a roadmap row: the FM count, then S2 and S3. A program's numbers are read-only inputs that open the
     edit modal; the total row only shows its sums. Marked when S2 + S3 differs from the FM count. --}}
@php($warning = $cell && $cell['mismatch'] ? 'S2 + S3 = '.($cell['s2'] + $cell['s3']).', tidak sama dengan jumlah FM '.$cell['fm'] : null)
@foreach(\App\Services\Lecturers\ProdiFmRoadmap::FIELDS as $field => $definition)
    @php($note = $cell['notes'][$field] ?? null)
    @php($editable = isset($cell['id']))
    <td @class(['roadmap-year-start' => $field === 'fm', 'bg-light-warning' => $warning, 'roadmap-has-note' => $note !== null]) data-roadmap-cell="{{ $key }}-{{ $field }}"
        @if(! $editable && $warning) title="{{ $warning }}" @endif>
        @if($editable)
            @php($title = implode("\n", array_filter([$warning, $note !== null ? 'Catatan: '.$note : null])))
            <input type="text" readonly @class(['form-control form-control-sm form-control-solid roadmap-input', 'fw-bold' => $field === 'fm', 'bg-light-warning border-warning' => $warning])
                value="{{ $cell[$field] }}" aria-haspopup="dialog" aria-label="{{ $definition['label'] }} {{ $name }}{{ $note !== null ? ' (ada catatan)' : '' }}"
                data-roadmap-edit data-id="{{ $cell['id'] }}" data-field="{{ $field }}" data-label="{{ $definition['label'] }} {{ $name }}"
                data-value="{{ $cell[$field] }}" data-note="{{ $note }}" @if($title !== '') title="{{ $title }}" @endif>
        @else
            {{ $cell[$field] ?? '–' }}
        @endif
    </td>
@endforeach

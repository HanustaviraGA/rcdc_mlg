{{-- The targets of each period, newest year first and in period order. --}}
@php($definition = \App\Services\CharacterBuilding\CharacterBuildingTargets::CATEGORIES[$category])
@php($number = fn ($value) => \App\Services\CharacterBuilding\CharacterBuildingTargets::number((float) $value))
@if($rows->isEmpty())
    <p class="text-muted fs-7 mb-0">Belum ada target {{ $definition['label'] }}.</p>
@else
    <div class="table-responsive">
        <table class="table table-row-bordered table-row-gray-200 align-middle fs-7 mb-0">
            <thead><tr class="fw-bold text-muted">
                <th>Periode</th><th class="text-end">Target{{ $definition['unit'] ? ' ('.$definition['unit'].')' : '' }}</th><th class="text-end">Skor</th>
                <th>Diperbarui</th><th class="text-end">Aksi</th>
            </tr></thead>
            <tbody>
                @foreach($rows as $target)
                    @php($name = $target->label.' ('.$number($target->target).' → skor '.$number($target->score).')')
                    <tr data-cb-period="{{ $target->label }}">
                        <td class="fw-bold">{{ $target->label }}</td>
                        <td class="text-end">{{ $number($target->target) }}</td>
                        <td class="text-end fw-bold">{{ $number($target->score) }}</td>
                        <td class="text-muted">{{ \Carbon\Carbon::parse($target->updated_at)->format('d/m/Y H:i') }}</td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="btn btn-sm btn-light-primary" data-cb-edit data-category="{{ $category }}" data-id="{{ $target->id }}"
                                data-year="{{ $target->year }}" data-term="{{ $target->term }}" data-target="{{ $target->target }}" data-score="{{ $target->score }}" data-label="{{ $name }}">Ubah</button>
                            <button type="button" class="btn btn-sm btn-light-danger" data-cb-delete data-category="{{ $category }}" data-id="{{ $target->id }}" data-label="{{ $name }}">Hapus</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

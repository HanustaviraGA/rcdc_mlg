
@php
    $semesterImports = isset($imports)
        ? $imports->filter(fn($i) => ($i->type ?? 'semester') === 'semester')
        : DB::table('community_development_imports')
            ->where(fn($q) => $q->where('type', 'semester')->orWhereNull('type'))
            ->orderByDesc('year_start')
            ->orderBy('semester')
            ->get();
@endphp

@if($semesterImports->isEmpty())
    <p class="text-muted mb-0">
        Belum ada import Community Development semester.
    </p>
@else
    <div class="table-responsive">
        <table class="table table-row-bordered align-middle">
            <thead>
                <tr>
                    <th>Periode</th>
                    <th>File</th>
                    <th>Diperbarui</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($semesterImports as $import)
                    @php
                        $period = $import->year_start
                            . '/'
                            . ($import->year_start + 1)
                            . ' '
                            . $import->semester;
                    @endphp

                    <tr>
                        <td class="text-nowrap">
                            {{ $period }}
                        </td>
                        <td>{{ $import->filename }}</td>
                        <td class="text-nowrap">
                            {{ $import->updated_at }}
                        </td>
                        <td class="text-end">
                            <button
                                type="button"
                                class="btn btn-sm btn-light-danger"
                                data-delete-import
                                data-url="{{ route('importpkm.destroy', $import->id) }}"
                                data-label="Community Development Semester"
                                data-period="{{ $period }}"
                            >
                                Hapus
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
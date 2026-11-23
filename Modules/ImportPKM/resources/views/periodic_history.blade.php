@php
    $periodicImports = isset($periodicImports) ? $periodicImports : DB::table('community_development_imports')->where('type', 'periodic')->orderByDesc('start_date')->get();
@endphp

@if($periodicImports->isEmpty())
    <p class="text-muted mb-0">Belum ada import Community Development Periodik.</p>
@else
    <div class="table-responsive"><table class="table table-row-bordered align-middle"><thead><tr><th>Rentang Tanggal</th><th>File</th><th>Diperbarui</th><th class="text-end">Aksi</th></tr></thead><tbody>
        @foreach($periodicImports as $import)
            <tr>
                <td class="text-nowrap">{{ $import->start_date }} s.d. {{ $import->end_date }}</td>
                <td>{{ $import->filename }}</td>
                <td class="text-nowrap">{{ $import->updated_at }}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-light-danger" data-delete-import data-url="{{ route('importpkm.destroy', $import->id) }}" data-label="Community Development Periodik" data-period="{{ $import->start_date.' s.d. '.$import->end_date }}">Hapus</button>
                </td>
            </tr>
        @endforeach
    </tbody></table></div>
@endif

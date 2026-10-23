@if($imports->isEmpty())
    <p class="text-muted mb-0">Belum ada import Community Development.</p>
@else
    <div class="table-responsive"><table class="table table-row-bordered"><thead><tr><th>Periode</th><th>File</th><th>Diperbarui</th></tr></thead><tbody>
        @foreach($imports as $import)
            <tr><td class="text-nowrap">{{ $import->year_start }}/{{ $import->year_start + 1 }} {{ $import->semester }}</td><td>{{ $import->filename }}</td><td class="text-nowrap">{{ $import->updated_at }}</td></tr>
        @endforeach
    </tbody></table></div>
@endif

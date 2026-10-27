@if($partnerImports->isEmpty())
    <p class="text-muted mb-0">Belum ada import mitra binaan.</p>
@else
    <div class="table-responsive"><table class="table table-row-bordered"><thead><tr><th>Tahun data</th><th>Mitra</th><th>File / sheet</th><th>Diperbarui</th></tr></thead><tbody>
        @foreach($partnerImports as $import)
            <tr><td>{{ $import->data_year }}</td><td>{{ $import->partner_count }}</td><td>{{ $import->filename }} <small class="d-block text-muted">{{ $import->sheet }} · Header baris {{ $import->header_row }}</small></td><td class="text-nowrap">{{ $import->updated_at }}</td></tr>
        @endforeach
    </tbody></table></div>
@endif

@if($partnerImports->isEmpty())
    <p class="text-muted mb-0">Belum ada import mitra binaan.</p>
@else
    <div class="table-responsive"><table class="table table-row-bordered align-middle"><thead><tr><th>Tahun data</th><th>Mitra</th><th>File / sheet</th><th>Diperbarui</th><th class="text-end">Aksi</th></tr></thead><tbody>
        @foreach($partnerImports as $import)
            <tr>
                <td>{{ $import->data_year }}</td>
                <td>{{ $import->partner_count }}</td>
                <td>{{ $import->filename }} <small class="d-block text-muted">{{ $import->sheet }} · Header baris {{ $import->header_row }}</small></td>
                <td class="text-nowrap">{{ $import->updated_at }}</td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-light-danger" data-delete-import data-url="{{ route('importpkm.partners.destroy', $import->id) }}" data-label="Mitra Binaan" data-period="{{ $import->data_year }}">Hapus</button>
                </td>
            </tr>
        @endforeach
    </tbody></table></div>
@endif

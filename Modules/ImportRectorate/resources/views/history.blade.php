<div class="table-responsive">
    <table class="table table-striped table-row-bordered align-middle">
        <thead>
            <tr class="fw-bold">
                <th>Bulan laporan</th>
                <th>Publikasi FM</th>
                <th>Publikasi MHS</th>
                <th>Hibah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($monthly as $period => $files)
                <tr>
                    <td class="text-nowrap">{{ $period }}</td>
                    @foreach(['fm' => 'Publikasi FM', 'mhs' => 'Publikasi Mahasiswa', 'hibah' => 'Hibah'] as $type => $label)
                        <td>
                            @if(isset($files[$type]))
                                <span class="badge badge-light-success mb-1">Sudah diupload</span>
                                <div class="text-break">{{ $files[$type] }}</div>
                                <button type="button" class="btn btn-sm btn-light-danger mt-2" data-delete-import
                                    data-url="{{ route('importrectorate.destroy', ['type' => $type, 'year' => (int) substr($period, 0, 4), 'month' => (int) substr($period, 5, 2)]) }}"
                                    data-label="{{ $label }}" data-period="{{ $period }}"
                                    aria-label="Hapus {{ $label }} periode {{ $period }}">
                                    <i class="bi bi-trash me-1" aria-hidden="true"></i>Hapus
                                </button>
                            @else
                                <span class="text-muted">Belum diupload</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr><td colspan="4" class="text-center">Belum ada laporan bulanan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($legacyResearch)
    <p class="text-muted mb-0">Import hibah lama tanpa bulan laporan tetap tersimpan. Upload ulang dengan tahun dan bulan untuk mencatat kelengkapan bulanan.</p>
@endif

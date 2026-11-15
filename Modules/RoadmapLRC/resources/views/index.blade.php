{{-- The FM count roadmap per study program, laid out like sheet JJP: per year the FM count, then its S2 and S3. --}}
<style>
    #roadmapLrc .roadmap-table th,
    #roadmapLrc .roadmap-table td {
        text-align: center;
        white-space: nowrap;
    }

    #roadmapLrc .roadmap-table .roadmap-prodi {
        box-shadow: inset -1px 0 0 var(--bs-gray-300);
        left: 0;
        min-width: 12rem;
        position: sticky;
        text-align: left;
        z-index: 2;
    }

    #roadmapLrc .roadmap-table .roadmap-year-start {
        border-left: 2px solid var(--bs-gray-400);
    }

    #roadmapLrc .roadmap-input {
        cursor: pointer;
        min-width: 3.75rem;
        padding: .35rem .5rem;
        text-align: center;
    }

    #roadmapLrc .roadmap-has-note {
        position: relative;
    }

    /* A note marker in the top right corner of the cell, like a spreadsheet comment. */
    #roadmapLrc .roadmap-has-note::after {
        border-color: transparent var(--bs-primary) transparent transparent;
        border-style: solid;
        border-width: 0 .5rem .5rem 0;
        content: '';
        position: absolute;
        right: 0;
        top: 0;
    }
</style>
<div id="roadmapLrc" data-update-url="{{ route('roadmaplrc.update') }}" data-csrf="{{ csrf_token() }}">
    <div class="card card-bordered">
        <div class="card-header">
            <div class="card-title flex-column align-items-start py-4">
                <h3 class="mb-1">Roadmap Jumlah FM</h3>
                <span class="text-muted fs-7 fw-normal">
                    Rencana jumlah faculty member (FM) per prodi beserta jenjang pendidikan S2 dan S3{{ $roadmap['years'] ? ', '.head($roadmap['years']).'–'.last($roadmap['years']) : '' }}.
                    Klik angka untuk mengubah nilai dan catatannya.
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="fs-7" data-roadmap-status role="status" aria-live="polite"></div>
            <div data-roadmap-table>
                @include('roadmaplrc::table')
            </div>
        </div>
    </div>
    @include('roadmaplrc::modal')
</div>
@include('roadmaplrc::javascript')

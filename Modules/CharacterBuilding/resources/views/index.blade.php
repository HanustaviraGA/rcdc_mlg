<div id="characterBuilding" data-create-url="{{ route('characterbuilding.create') }}" data-update-url="{{ route('characterbuilding.update') }}"
    data-delete-url="{{ route('characterbuilding.delete') }}" data-read-url="{{ route('characterbuilding.read') }}" data-csrf="{{ csrf_token() }}">
    <div class="card card-bordered">
        <div class="card-header">
            <div class="card-title flex-column align-items-start py-4">
                <h3 class="mb-1">Character Building (CBDC)</h3>
                <span class="text-muted fs-7 fw-normal">Target skor dan capaian dosen Character Building Development Center.</span>
            </div>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x fs-6 mb-8 overflow-auto flex-nowrap" role="tablist">
                @foreach(['target' => 'Target Skor', 'publikasi' => 'Publikasi', 'pengajaran' => 'Pengajaran', 'comdev' => 'Community Development', 'pengembangan' => 'Pengembangan Diri'] as $tab => $title)
                    <li class="nav-item" role="presentation">
                        <button type="button" class="nav-link text-nowrap @if($loop->first) active @endif" id="cbTab-{{ $tab }}-button" data-bs-toggle="tab"
                            data-bs-target="#cbTab-{{ $tab }}" role="tab" aria-controls="cbTab-{{ $tab }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $loop->iteration }}. {{ $title }}</button>
                    </li>
                @endforeach
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="cbTab-target" role="tabpanel" aria-labelledby="cbTab-target-button">
                    @include('characterbuilding::targets')
                </div>
                <div class="tab-pane fade" id="cbTab-publikasi" role="tabpanel" aria-labelledby="cbTab-publikasi-button">
                    @include('characterbuilding::progress_tab', ['tab' => 'publikasi', 'view' => 'publication', 'data' => $publication])
                </div>
                <div class="tab-pane fade" id="cbTab-pengajaran" role="tabpanel" aria-labelledby="cbTab-pengajaran-button">
                    @include('characterbuilding::coming_soon', ['title' => 'Pengajaran'])
                </div>
                <div class="tab-pane fade" id="cbTab-comdev" role="tabpanel" aria-labelledby="cbTab-comdev-button">
                    @include('characterbuilding::progress_tab', ['tab' => 'comdev', 'view' => 'comdev', 'data' => $comdev])
                </div>
                <div class="tab-pane fade" id="cbTab-pengembangan" role="tabpanel" aria-labelledby="cbTab-pengembangan-button">
                    @include('characterbuilding::coming_soon', ['title' => 'Pengembangan Diri'])
                </div>
            </div>
        </div>
    </div>
</div>
@include('characterbuilding::javascript')

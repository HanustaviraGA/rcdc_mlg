{{-- Tab 1: one form and list of score ranges per area. --}}
<p class="text-muted mb-6">Isi target CBDC setiap periode beserta skornya. Target dalam satu tahun membentuk rentang skor tahun itu. Misalnya target Publikasi Q1 3 judul → skor 2, Q2 6 → skor 3, Q3 9 → skor 4, dan Q4 11 → skor 6: realisasi 7 atau 8 judul mendapat skor 3, 9–10 judul mendapat skor 4, dan 11 judul ke atas mendapat skor maksimal 6. Target yang lebih tinggi harus memiliki skor yang lebih tinggi. Untuk Pengajaran, rentangnya dibentuk oleh semester Ganjil dan Genap pada tahun akademik yang sama. Menyimpan periode yang sudah memiliki target akan menggantinya.</p>
<div class="row g-6">
    @foreach($categories as $category => $definition)
        <div class="col-xl-6">
            <section class="border rounded p-6 h-100" aria-labelledby="cb-target-{{ $category }}-title">
                <h4 class="mb-1" id="cb-target-{{ $category }}-title">{{ $loop->iteration }}. Target {{ $definition['label'] }}</h4>
                <p class="text-muted fs-7 mb-5">{{ $definition['hint'] }}</p>
                <form class="row g-3 align-items-end" data-cb-target-form="{{ $category }}" novalidate>
                    @if($definition['period'] === 'semester')
                        <div class="col-sm-6">
                            <label class="form-label required" for="cb-{{ $category }}-academic-year">Tahun akademik</label>
                            <select class="form-select" name="academic_year" id="cb-{{ $category }}-academic-year">
                                @foreach($academicYears as $year)<option value="{{ $year }}/{{ $year + 1 }}" @selected($year === $defaultAcademicYear)>{{ $year }}/{{ $year + 1 }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label required" for="cb-{{ $category }}-semester">Semester</label>
                            <select class="form-select" name="semester" id="cb-{{ $category }}-semester"><option value="Ganjil">Ganjil</option><option value="Genap">Genap</option></select>
                        </div>
                    @else
                        <div class="col-sm-6">
                            <label class="form-label required" for="cb-{{ $category }}-year">Tahun</label>
                            <select class="form-select" name="year" id="cb-{{ $category }}-year">
                                @foreach($years as $year)<option value="{{ $year }}" @selected($year === (int) now()->year)>{{ $year }}</option>@endforeach
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label required" for="cb-{{ $category }}-quarter">Kuartal</label>
                            <select class="form-select" name="quarter" id="cb-{{ $category }}-quarter">
                                @foreach(range(1, 4) as $quarter)<option value="{{ $quarter }}" @selected($quarter === $currentQuarter)>Q{{ $quarter }}</option>@endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-sm-6">
                        <label class="form-label required" for="cb-{{ $category }}-target">Target{{ $definition['unit'] ? ' ('.$definition['unit'].')' : '' }}</label>
                        <input class="form-control" type="number" name="target" id="cb-{{ $category }}-target" min="0" max="9999" step="0.01" required placeholder="Pencapaian yang ditargetkan">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label required" for="cb-{{ $category }}-score">Skor</label>
                        <input class="form-control" type="number" name="score" id="cb-{{ $category }}-score" min="0" max="{{ $definition['score_max'] }}" step="0.01" required placeholder="Skor bila tercapai (0–{{ $definition['score_max'] }})">
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-sm btn-primary" data-cb-submit>Simpan target</button>
                        <button type="button" class="btn btn-sm btn-light" data-cb-cancel hidden>Batal ubah</button>
                    </div>
                </form>
                <div class="fs-7 mt-3" data-cb-target-status="{{ $category }}" role="status" aria-live="polite"></div>
                <div class="mt-5" data-cb-target-list="{{ $category }}">
                    @include('characterbuilding::target_list', ['category' => $category, 'rows' => $targets[$category]])
                </div>
            </section>
        </div>
    @endforeach
</div>

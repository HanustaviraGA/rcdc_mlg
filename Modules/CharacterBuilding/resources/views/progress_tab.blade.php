{{-- A quarter filter and the progress it shows; changing the filter reloads the panel. --}}
<form class="row g-3 align-items-end mb-6" data-cb-filter="{{ $tab }}">
    <div class="col-sm-4 col-lg-2">
        <label class="form-label fw-bold" for="cb-{{ $tab }}-filter-year">Tahun</label>
        <select class="form-select" name="year" id="cb-{{ $tab }}-filter-year">
            @foreach($years as $year)<option value="{{ $year }}" @selected($year === $data['year'])>{{ $year }}</option>@endforeach
        </select>
    </div>
    <div class="col-sm-4 col-lg-3">
        <label class="form-label fw-bold" for="cb-{{ $tab }}-filter-quarter">Kuartal</label>
        <select class="form-select" name="quarter" id="cb-{{ $tab }}-filter-quarter">
            @foreach([1 => 'Jan–Mar', 2 => 'Apr–Jun', 3 => 'Jul–Sep', 4 => 'Okt–Des'] as $quarter => $months)
                <option value="{{ $quarter }}" @selected($quarter === $data['quarter'])>Q{{ $quarter }} ({{ $months }})</option>
            @endforeach
        </select>
    </div>
</form>
<div class="alert alert-danger" data-cb-panel-error="{{ $tab }}" role="alert" hidden></div>
<div data-cb-panel="{{ $tab }}" aria-live="polite">@include('characterbuilding::'.$view, ['data' => $data])</div>

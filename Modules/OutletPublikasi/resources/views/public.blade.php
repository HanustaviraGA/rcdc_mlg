<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>BINUS@Malang - Portal Catur Dharma - Outlet Publikasi</title>
  <meta name="description" content="Daftar conference, jurnal, book chapter, dan hibah BINUS@Malang.">

  <link href="{{ asset('assets/backoffice/media/logos/favicon.ico') }}" rel="icon">
  <link href="{{ asset('assets/backoffice/media/logos/favicon.ico') }}" rel="apple-touch-icon">
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Lato:wght@300;400;700;900&family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">

  <link href="{{ asset('landing/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('landing/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('landing/assets/vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('landing/assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
  <link href="{{ asset('landing/assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('landing/assets/css/main.css') }}" rel="stylesheet">

  <style>
    .publication-tabs-wrapper {
      overflow-x: auto;
      padding-bottom: 2px;
    }

    .publication-tabs {
      border-bottom: 1px solid color-mix(in srgb, var(--default-color), transparent 85%);
      flex-wrap: nowrap;
      min-width: max-content;
    }

    .publication-tabs .nav-link {
      border: 0;
      border-bottom: 3px solid transparent;
      border-radius: 0;
      color: color-mix(in srgb, var(--default-color), transparent 25%);
      font-family: var(--heading-font);
      font-weight: 700;
      padding: 14px 20px;
    }

    .publication-tabs .nav-link.active,
    .publication-tabs .nav-link:hover {
      background: transparent;
      border-bottom-color: var(--accent-color);
      color: var(--accent-color);
    }

    .publication-filter,
    .publication-table-card {
      background: #fff;
      border: 1px solid color-mix(in srgb, var(--default-color), transparent 88%);
      border-radius: 14px;
      box-shadow: 0 8px 30px color-mix(in srgb, var(--default-color), transparent 94%);
    }

    .publication-filter {
      padding: 24px;
    }

    .publication-table-card {
      overflow: hidden;
    }

    .publication-table {
      margin-bottom: 0;
      min-width: 900px;
    }

    .publication-table thead th {
      background: color-mix(in srgb, var(--accent-color), transparent 92%);
      border-bottom: 0;
      color: var(--heading-color);
      font-family: var(--heading-font);
      font-size: 14px;
      padding: 18px 16px;
      vertical-align: middle;
      white-space: nowrap;
    }

    .publication-table tbody td {
      border-color: color-mix(in srgb, var(--default-color), transparent 90%);
      padding: 16px;
      vertical-align: middle;
    }

    .publication-name {
      font-weight: 700;
      min-width: 210px;
    }

    .publication-name a {
      color: var(--heading-color);
      display: inline-flex;
      gap: 7px;
    }

    .publication-name a:hover {
      color: var(--accent-color);
    }

    .publication-badge {
      background: color-mix(in srgb, var(--accent-color), transparent 88%);
      border-radius: 999px;
      color: var(--accent-color);
      display: inline-block;
      font-size: 13px;
      font-weight: 700;
      padding: 6px 12px;
      white-space: nowrap;
    }

    .publication-scope-button,
    .publication-search-button {
      align-items: center;
      background: var(--accent-color);
      border: 1px solid var(--accent-color);
      border-radius: 8px;
      color: var(--contrast-color);
      display: inline-flex;
      font-weight: 600;
      gap: 7px;
      justify-content: center;
      padding: 10px 18px;
      transition: .2s ease;
      white-space: nowrap;
    }

    .publication-scope-button {
      font-size: 13px;
      padding: 7px 12px;
    }

    .publication-scope-button:hover,
    .publication-search-button:hover {
      background: color-mix(in srgb, var(--accent-color), #000 12%);
      color: var(--contrast-color);
    }

    .publication-empty {
      color: color-mix(in srgb, var(--default-color), transparent 35%);
      padding: 56px 20px !important;
      text-align: center;
    }

    .publication-empty i {
      display: block;
      font-size: 42px;
      margin-bottom: 10px;
    }

    .publication-pagination .pagination {
      justify-content: center;
      margin-bottom: 0;
    }

    .publication-scope-content {
      line-height: 1.75;
      white-space: pre-wrap;
    }

    .publication-modal .modal-dialog,
    .publication-modal .modal-content {
      max-height: calc(100vh - 1.5rem);
    }

    .publication-modal .modal-dialog {
      margin-bottom: .75rem;
      margin-top: .75rem;
    }

    .publication-modal .modal-content {
      display: flex;
      flex-direction: column;
      min-height: 0;
      overflow: hidden;
    }

    .publication-modal .modal-header,
    .publication-modal .modal-footer {
      flex: 0 0 auto;
    }

    .publication-modal .modal-body {
      min-height: 0;
      overflow-y: auto !important;
      overscroll-behavior: contain;
      scrollbar-gutter: stable;
      scrollbar-width: thin;
    }

    @supports (height: 100dvh) {
      .publication-modal .modal-dialog,
      .publication-modal .modal-content {
        max-height: calc(100dvh - 1.5rem);
      }
    }

    @media (max-width: 767px) {
      .publication-filter {
        padding: 18px;
      }
    }
  </style>
</head>

<body class="team-page">
  @include('landing.components.header')

  @php
    $tabDescriptions = [
      'publikasi' => 'Temukan conference berdasarkan nama atau cakupan bidang publikasinya.',
      'book_chapter' => 'Daftar outlet book chapter beserta frekuensi penerbitannya.',
      'scopus_journals' => 'Daftar jurnal terindeks Scopus beserta informasi quartile dan SJR.',
      'sinta' => 'Daftar jurnal terakreditasi SINTA dan cakupan publikasinya.',
      'penelitian_hibah' => 'Daftar peluang penelitian dan hibah beserta deadline submission.',
    ];
    $searchPlaceholders = [
      'publikasi' => 'Cari nama conference atau scope',
      'book_chapter' => 'Cari nama book chapter atau publication frequency',
      'scopus_journals' => 'Cari nama jurnal, ISSN, quartile, frequency, atau scope',
      'sinta' => 'Cari nama jurnal, ISSN, SINTA, frequency, atau scope',
      'penelitian_hibah' => 'Cari nama penelitian atau hibah',
    ];
    $emptyColspans = [
      'publikasi' => 7,
      'book_chapter' => 4,
      'scopus_journals' => 7,
      'sinta' => 7,
      'penelitian_hibah' => 3,
    ];
    $sortGroups = [
      'publikasi' => [
        'Deadline Submission' => ['deadline_desc' => 'Deadline Submission - Terbaru', 'deadline_asc' => 'Deadline Submission - Terlama'],
        'Update' => ['update_desc' => 'Update - Terbaru', 'update_asc' => 'Update - Terlama'],
        'Nama Conference' => ['name_asc' => 'Nama Conference - A-Z', 'name_desc' => 'Nama Conference - Z-A'],
      ],
      'book_chapter' => [
        'Update' => ['update_desc' => 'Update - Terbaru', 'update_asc' => 'Update - Terlama'],
        'Nama Book Chapter' => ['name_asc' => 'Nama Book Chapter - A-Z', 'name_desc' => 'Nama Book Chapter - Z-A'],
      ],
      'scopus_journals' => [
        'Update' => ['update_desc' => 'Update - Terbaru', 'update_asc' => 'Update - Terlama'],
        'Nama Jurnal' => ['name_asc' => 'Nama Jurnal - A-Z', 'name_desc' => 'Nama Jurnal - Z-A'],
      ],
      'sinta' => [
        'Update' => ['update_desc' => 'Update - Terbaru', 'update_asc' => 'Update - Terlama'],
        'Nama Jurnal' => ['name_asc' => 'Nama Jurnal - A-Z', 'name_desc' => 'Nama Jurnal - Z-A'],
      ],
      'penelitian_hibah' => [
        'Deadline Submission' => ['deadline_desc' => 'Deadline Submission - Terbaru', 'deadline_asc' => 'Deadline Submission - Terlama'],
        'Nama Hibah' => ['name_asc' => 'Nama Hibah - A-Z', 'name_desc' => 'Nama Hibah - Z-A'],
      ],
    ];
  @endphp

  <main class="main">
    <div class="page-title light-background">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">Outlet Publikasi</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="{{ route('home') }}">Home</a></li>
            <li class="current">Outlet Publikasi</li>
          </ol>
        </nav>
      </div>
    </div>

    <section class="section">
      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="section-title">
          <h2>{{ $tabs[$activeTab] }}</h2>
          <p>{{ $tabDescriptions[$activeTab] }}</p>
        </div>

        <div class="publication-tabs-wrapper mb-4">
          <nav class="nav nav-tabs publication-tabs" aria-label="Kategori Outlet Publikasi">
            @foreach ($tabs as $tabKey => $tabLabel)
              <a
                href="{{ route('outletpublikasi.public', ['tab' => $tabKey]) }}"
                class="nav-link @if ($activeTab === $tabKey) active @endif"
                @if ($activeTab === $tabKey) aria-current="page" @endif
              >
                {{ $tabLabel }}
              </a>
            @endforeach
          </nav>
        </div>

        <form action="{{ route('outletpublikasi.public') }}" method="get" class="publication-filter mb-4">
          <input type="hidden" name="tab" value="{{ $activeTab }}">
          <div class="row g-3 align-items-end">
            <div class="col-lg-7">
              <label for="publicationSearch" class="form-label fw-semibold">Search {{ $tabs[$activeTab] }}</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input
                  type="search"
                  name="search"
                  id="publicationSearch"
                  class="form-control"
                  value="{{ $search }}"
                  placeholder="{{ $searchPlaceholders[$activeTab] }}"
                  autocomplete="off"
                >
              </div>
            </div>
            <div class="col-md-7 col-lg-3">
              <label for="publicationSort" class="form-label fw-semibold">Urutkan</label>
              <select name="sort" id="publicationSort" class="form-select">
                @foreach ($sortGroups[$activeTab] as $groupLabel => $options)
                  <optgroup label="{{ $groupLabel }}">
                    @foreach ($options as $optionValue => $optionLabel)
                      <option value="{{ $optionValue }}" @selected($sort === $optionValue)>{{ $optionLabel }}</option>
                    @endforeach
                  </optgroup>
                @endforeach
              </select>
            </div>
            <div class="col-md-5 col-lg-2 d-grid">
              <button type="submit" class="publication-search-button">
                <i class="bi bi-search"></i> Search
              </button>
            </div>
          </div>
        </form>

        <div class="publication-table-card">
          <div class="table-responsive">
            <table class="table publication-table">
              <thead>
                <tr>
                  <th>No.</th>
                  @if ($activeTab === 'publikasi')
                    <th>Nama Conference</th>
                    <th>BJIC/Co-Host/-</th>
                    <th>Deadline Submission</th>
                    <th>Scope</th>
                    <th>Contact PIC</th>
                    <th>Update</th>
                  @elseif ($activeTab === 'book_chapter')
                    <th>Nama Book Chapter</th>
                    <th>Publication Frequency</th>
                    <th>Update</th>
                  @elseif ($activeTab === 'scopus_journals')
                    <th>Nama Jurnal</th>
                    <th>ISSN</th>
                    <th>Quartile - SJR</th>
                    <th>Publication Frequency</th>
                    <th>Scope</th>
                    <th>Update</th>
                  @elseif ($activeTab === 'sinta')
                    <th>Nama Jurnal</th>
                    <th>ISSN</th>
                    <th>SINTA</th>
                    <th>Publication Frequency</th>
                    <th>Scope</th>
                    <th>Update</th>
                  @else
                    <th>Nama Hibah</th>
                    <th>Deadline Submission</th>
                  @endif
                </tr>
              </thead>
              <tbody>
                @forelse ($records as $record)
                  <tr>
                    <td>{{ $records->firstItem() + $loop->index }}</td>
                    <td class="publication-name">
                      @if ($record->url_website)
                        <a href="{{ $record->url_website }}" target="_blank" rel="noopener noreferrer">
                          {{ $activeTab === 'publikasi' ? $record->nama_conference : $record->nama }}
                          <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                      @else
                        {{ $activeTab === 'publikasi' ? $record->nama_conference : $record->nama }}
                      @endif
                    </td>

                    @if ($activeTab === 'publikasi')
                      <td><span class="publication-badge">{{ $record->tipe_kerjasama }}</span></td>
                      <td>{{ $record->deadline_submission->translatedFormat('d M Y') }}</td>
                      <td>
                        <button type="button" class="publication-scope-button" onclick="openPublicationScope(this)" data-scope-target="publicationScope{{ $activeTab }}{{ $record->id }}" data-title="{{ $record->nama_conference }}">
                          <i class="bi bi-eye"></i> Lihat Scope
                        </button>
                        <div id="publicationScope{{ $activeTab }}{{ $record->id }}" class="d-none">{!! nl2br(e($record->scope)) !!}</div>
                      </td>
                      <td>{{ $record->contact_pic }}</td>
                      <td>{{ $record->created_at->translatedFormat('d M Y') }}</td>
                    @elseif ($activeTab === 'book_chapter')
                      <td>{{ $record->publication_frequency }}</td>
                      <td>{{ $record->created_at->translatedFormat('d M Y') }}</td>
                    @elseif (in_array($activeTab, ['scopus_journals', 'sinta'], true))
                      <td>{{ $record->issn }}</td>
                      <td><span class="publication-badge">{{ $activeTab === 'scopus_journals' ? $record->quartile_sjr : $record->sinta }}</span></td>
                      <td>{{ $record->publication_frequency }}</td>
                      <td>
                        <button type="button" class="publication-scope-button" onclick="openPublicationScope(this)" data-scope-target="publicationScope{{ $activeTab }}{{ $record->id }}" data-title="{{ $record->nama }}">
                          <i class="bi bi-eye"></i> Lihat Scope
                        </button>
                        <div id="publicationScope{{ $activeTab }}{{ $record->id }}" class="d-none">{!! nl2br(e($record->scope)) !!}</div>
                      </td>
                      <td>{{ $record->created_at->translatedFormat('d M Y') }}</td>
                    @else
                      <td>{{ $record->deadline_submission->translatedFormat('d M Y') }}</td>
                    @endif
                  </tr>
                @empty
                  <tr>
                    <td colspan="{{ $emptyColspans[$activeTab] }}" class="publication-empty">
                      <i class="bi bi-inbox"></i>
                      @if ($search !== '')
                        Tidak ada data {{ $tabs[$activeTab] }} yang cocok dengan pencarian “{{ $search }}”.
                      @else
                        Belum ada data {{ $tabs[$activeTab] }}.
                      @endif
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        @if ($records->hasPages())
          <div class="publication-pagination mt-4">
            {{ $records->links('pagination::bootstrap-5') }}
          </div>
        @endif
      </div>
    </section>
  </main>

  <div class="modal fade publication-modal" id="publicationScopeModal" tabindex="-1" aria-labelledby="publicationScopeModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="publicationScopeModalTitle">Scope</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div id="publicationScopeModalContent" class="publication-scope-content"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
      </div>
    </div>
  </div>

  @include('landing.components.footer')

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <div id="preloader"></div>

  <script src="{{ asset('landing/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('landing/assets/vendor/php-email-form/validate.js') }}"></script>
  <script src="{{ asset('landing/assets/vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('landing/assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('landing/assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
  <script src="{{ asset('landing/assets/js/main.js') }}"></script>

  <script>
    function openPublicationScope(button) {
      var scopeElement = document.getElementById(button.dataset.scopeTarget);
      var modalElement = document.getElementById('publicationScopeModal');

      document.getElementById('publicationScopeModalTitle').textContent = 'Scope - ' + button.dataset.title;
      document.getElementById('publicationScopeModalContent').innerHTML = scopeElement ? scopeElement.innerHTML : '-';
      bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }
  </script>
</body>

</html>

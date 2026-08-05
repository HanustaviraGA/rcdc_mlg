<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>BINUS@Malang - Portal Catur Dharma - Outlet Publikasi</title>
  <meta name="description" content="Daftar conference dan outlet publikasi BINUS@Malang.">

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
    .publication-filter {
      background: #fff;
      border: 1px solid color-mix(in srgb, var(--default-color), transparent 88%);
      border-radius: 14px;
      box-shadow: 0 8px 30px color-mix(in srgb, var(--default-color), transparent 94%);
      padding: 24px;
    }

    .publication-table-card {
      background: #fff;
      border: 1px solid color-mix(in srgb, var(--default-color), transparent 88%);
      border-radius: 14px;
      box-shadow: 0 8px 30px color-mix(in srgb, var(--default-color), transparent 94%);
      overflow: hidden;
    }

    .publication-table {
      margin-bottom: 0;
      min-width: 960px;
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
      color: var(--heading-color);
      font-weight: 700;
      min-width: 220px;
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

    @media (max-width: 767px) {
      .publication-filter {
        padding: 18px;
      }
    }
  </style>
</head>

<body class="team-page">
  @include('landing.components.header')

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
          <h2>Conference dan Outlet Publikasi</h2>
          <p>Temukan conference berdasarkan nama atau cakupan bidang publikasinya.</p>
        </div>

        <form action="{{ route('outletpublikasi.public') }}" method="get" class="publication-filter mb-4">
          <div class="row g-3 align-items-end">
            <div class="col-lg-7">
              <label for="publicationSearch" class="form-label fw-semibold">Cari Conference atau Scope</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input
                  type="search"
                  name="search"
                  id="publicationSearch"
                  class="form-control"
                  value="{{ $search }}"
                  placeholder="Masukkan nama conference atau scope"
                  autocomplete="off"
                >
              </div>
            </div>
            <div class="col-md-7 col-lg-3">
              <label for="publicationSort" class="form-label fw-semibold">Urutkan Update</label>
              <select name="sort" id="publicationSort" class="form-select">
                <option value="desc" @selected($sort === 'desc')>Terbaru</option>
                <option value="asc" @selected($sort === 'asc')>Terlama</option>
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
                  <th>Nama Conference</th>
                  <th>BJIC/Co-Host/-</th>
                  <th>Deadline Submission</th>
                  <th>Scope</th>
                  <th>Contact PIC</th>
                  <th>Update</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($outletPublikasi as $outlet)
                  <tr>
                    <td>{{ $outletPublikasi->firstItem() + $loop->index }}</td>
                    <td class="publication-name">{{ $outlet->nama_conference }}</td>
                    <td><span class="publication-badge">{{ $outlet->tipe_kerjasama }}</span></td>
                    <td>{{ $outlet->deadline_submission->translatedFormat('d M Y') }}</td>
                    <td>
                      <button
                        type="button"
                        class="publication-scope-button"
                        data-scope-target="publicationScope{{ $outlet->id }}"
                        data-conference="{{ $outlet->nama_conference }}"
                        onclick="openPublicationScope(this)"
                      >
                        <i class="bi bi-eye"></i> Lihat Scope
                      </button>
                      <div id="publicationScope{{ $outlet->id }}" class="d-none">{!! nl2br(e($outlet->scope)) !!}</div>
                    </td>
                    <td>{{ $outlet->contact_pic }}</td>
                    <td>{{ $outlet->created_at->translatedFormat('d M Y') }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="publication-empty">
                      <i class="bi bi-inbox"></i>
                      @if ($search !== '')
                        Tidak ada outlet publikasi yang cocok dengan pencarian “{{ $search }}”.
                      @else
                        Belum ada data outlet publikasi.
                      @endif
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        @if ($outletPublikasi->hasPages())
          <div class="publication-pagination mt-4">
            {{ $outletPublikasi->links('pagination::bootstrap-5') }}
          </div>
        @endif
      </div>
    </section>
  </main>

  <div class="modal fade" id="publicationScopeModal" tabindex="-1" aria-labelledby="publicationScopeModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="publicationScopeModalTitle">Scope Conference</h5>
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

      document.getElementById('publicationScopeModalTitle').textContent = 'Scope - ' + button.dataset.conference;
      document.getElementById('publicationScopeModalContent').innerHTML = scopeElement ? scopeElement.innerHTML : '-';
      bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }
  </script>
</body>

</html>

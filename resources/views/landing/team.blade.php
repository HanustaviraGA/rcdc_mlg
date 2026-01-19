@php
    use App\Models\AttributeDosen;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>BINUS@Malang - Portal Catur Dharma - Lecturers</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="{{ asset('assets/backoffice/media/logos/favicon.ico') }}" rel="icon">
  <link href="{{ asset('assets/backoffice/media/logos/favicon.ico') }}" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&family=Ubuntu:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('landing/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('landing/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('landing/assets/vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('landing/assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
  <link href="{{ asset('landing/assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{ asset('landing/assets/css/main.css') }}" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Constructo
  * Template URL: https://bootstrapmade.com/constructo-bootstrap-construction-template/
  * Updated: Aug 30 2025 with Bootstrap v5.3.8
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="team-page">

  @include('landing.components.header')

  <main class="main">

    <!-- Page Title -->
    <div class="page-title light-background">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">Lecturers</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="index.html">Home</a></li>
            <li class="current">Lecturers</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Team Section -->
    <section id="team" class="team section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row mb-4">
          <div class="col-lg-12">
            <form action="{{ url()->current() }}" method="get" class="team-search d-flex gap-2">
              <input type="text" name="search" class="form-control" placeholder="Search lecturers" value="{{ request('search') }}">
              <select name="gugus" class="form-select">
                <option value="">Semua Gugus Binaan</option>
                @foreach ($gugusBinaan as $gugus)
                  <option value="{{ $gugus }}" @selected(request('gugus') === $gugus)>{{ $gugus }}</option>
                @endforeach
              </select>
              <button type="submit" class="btn-landing-primary">Search</button>
            </form>
          </div>
        </div>

        <div class="row gy-4">

          @foreach($dosen as $listdosen)
            {{-- Random AOS Delay --}}
            @php
                $rand = rand(1, 4) * 100
            @endphp
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ $rand }}">
                <div class="team-card compact">
                    <div class="member-photo">
                        <img src="{{ asset('landing/assets/img/construction/team-3.webp') }}" class="img-fluid" alt="">
                        <div class="hover-overlay">
                        <div class="overlay-content">
                            <a href="{{ url('lecturers/detail', $listdosen->kode_dosen) }}"><h5>{{ $listdosen->nama_dosen }}</h5></a>
                            <span>{{ $listdosen->nama_gugus_binaan }}</span>
                            <div class="quick-contact">
                                <a href="mailto:{{ $listdosen->email_1 }}"><i class="bi bi-envelope"></i></a>
                                {{-- <a href="#"><i class="bi bi-telephone"></i></a> --}}
                                {{-- <a href="#"><i class="bi bi-linkedin"></i></a> --}}
                            </div>
                        </div>
                        </div>
                    </div>
                    <div class="member-summary">
                        <a href="{{ url('lecturers/detail', $listdosen->kode_dosen) }}"><h5>{{ $listdosen->nama_dosen }}</h5></a>
                        <span>{{ $listdosen->nama_gugus_binaan }}</span>
                        @php
                            $attribute = AttributeDosen::where('kode_dosen', $listdosen->kode_dosen)->get();
                        @endphp
                        @if($attribute->isNotEmpty())
                            <div class="skills">
                                @foreach($attribute as $attr)
                                    <span class="skill-tag">{{ $attr->attribute_dosen }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
          @endforeach

        </div>

        @if (method_exists($dosen, 'links'))
          <div class="team-pagination mt-5">
            {{ $dosen->appends(request()->query())->links('pagination::bootstrap-5') }}
          </div>
        @endif

      </div>

    </section><!-- /Team Section -->

  </main>

  @include('landing.components.footer')

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Preloader -->
  <div id="preloader"></div>

  <!-- Vendor JS Files -->
  <script src="{{ asset('landing/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('landing/assets/vendor/php-email-form/validate.js') }}"></script>
  <script src="{{ asset('landing/assets/vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('landing/assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('landing/assets/vendor/glightbox/js/glightbox.min.js') }}"></script>

  <!-- Main JS File -->
  <script src="{{ asset('landing/assets/js/main.js') }}"></script>

</body>

</html>

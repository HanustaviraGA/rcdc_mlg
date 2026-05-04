<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>BINUS@Malang - Portal Catur Dharma - {{ $dosen->nama_dosen }}</title>
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

<body class="service-details-page">

  @include('landing.components.header')

  <main class="main">

    <!-- Page Title -->
    <div class="page-title light-background">
      <div class="container d-lg-flex justify-content-between align-items-center">
        <h1 class="mb-2 mb-lg-0">Lecturer Details</h1>
        <nav class="breadcrumbs">
          <ol>
            <li><a href="{{ url('/') }}">Home</a></li>
            <li class="current">Lecturer Details</li>
          </ol>
        </nav>
      </div>
    </div><!-- End Page Title -->

    <!-- Service Details Section -->
    <section id="service-details" class="service-details section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row">
          <div class="col-lg-4 order-lg-2">
            <div class="service-sidebar" data-aos="fade-left" data-aos-delay="200">

              <div class="service-overview-card">
                <div class="service-icon">
                  @if(isset($dosen->foto_dosen))
                  <img
                    src="{{ asset('uploads/dosen/foto/'.$dosen->foto_dosen.'') }}"
                    alt="Foto dosen"
                    class="service-icon-img"
                  >
                  @else
                  <img
                    src="{{ asset('assets/backoffice/media/avatars/blank.png') }}"
                    alt="Foto dosen"
                    class="service-icon-img"
                  >
                  @endif
                </div>
                <h3>{{ $dosen->nama_dosen }}</h3>
                {{-- <p>Nulla facilisi morbi tempus iaculis urna id volutpat lacus laoreet non curabitur gravida.</p> --}}
                {{-- <div class="service-stats">
                  <div class="stat-item">
                  <span class="stat-number">150+</span>
                  <span class="stat-label">Projects Completed</span>
                  </div>
                  <div class="stat-item">
                  <span class="stat-number">25</span>
                  <span class="stat-label">Years Experience</span>
                  </div>
                </div> --}}
                @if((isset($dosen->link_google_scholar) && !empty($dosen->link_google_scholar)) || (isset($dosen->link_scopus) && !empty($dosen->link_scopus)) || (isset($dosen->link_sinta) && !empty($dosen->link_sinta)) || (isset($dosen->link_garuda) && !empty($dosen->link_garuda)) || (isset($dosen->link_orcid) && !empty($dosen->link_orcid)))
                  <div class="research-links mt-4">
                    <h5 class="mb-3">Research Profiles</h5>
                    <div class="d-grid gap-2">
                    @if(isset($dosen->link_google_scholar) && !empty($dosen->link_google_scholar))
                      <a href="{{ $dosen->link_google_scholar }}" target="_blank" class="btn btn-outline-primary btn-sm">
                      <i class="bi bi-mortarboard"></i> Google Scholar
                      </a>
                    @endif
                    @if(isset($dosen->link_scopus) && !empty($dosen->link_scopus))
                      <a href="{{ $dosen->link_scopus }}" target="_blank" class="btn btn-outline-primary btn-sm">
                      <i class="bi bi-graph-up"></i> Scopus
                      </a>
                    @endif
                    @if(isset($dosen->link_sinta) && !empty($dosen->link_sinta))
                      <a href="{{ $dosen->link_sinta }}" target="_blank" class="btn btn-outline-primary btn-sm">
                      <i class="bi bi-award"></i> SINTA
                      </a>
                    @endif
                    @if(isset($dosen->link_garuda) && !empty($dosen->link_garuda))
                      <a href="{{ $dosen->link_garuda }}" target="_blank" class="btn btn-outline-primary btn-sm">
                      <i class="bi bi-journal-text"></i> GARUDA
                      </a>
                    @endif
                    @if(isset($dosen->link_orcid) && !empty($dosen->link_orcid))
                      <a href="{{ $dosen->link_orcid }}" target="_blank" class="btn btn-outline-primary btn-sm">
                      <i class="bi bi-person-badge"></i> ORCID
                      </a>
                    @endif
                    </div>
                  </div>
                @endif
              </div>

              <div class="quick-info-card">
                <h4>Informasi Dosen</h4>
                <div class="info-grid">
                  <div class="info-row">
                    <span class="label">Kode Dosen :</span>
                    <span class="value">{{ $kode_dosen }}</span>
                  </div>
                  <div class="info-row">
                    <span class="label">Program Studi :</span>
                    <span class="value">{{ $dosen->nama_gugus_binaan }}</span>
                  </div>
                  <div class="info-row">
                    <span class="label">JJA :</span>
                    <span class="value">{{ $dosen->jja }}</span>
                  </div>
                  <div class="info-row">
                    <span class="label">Faculty Type :</span>
                    <span class="value">{{ $dosen->tipe_faculty }}</span>
                  </div>
                </div>
              </div>

              <div class="contact-action-card">
                <h4>Kontak</h4>
                {{-- <p class="contact-text">Mauris blandit aliquet elit eget tincidunt nibh pulvinar a proin gravida hendrerit.</p> --}}
                <div class="contact-methods single">
                  {{-- <a href="tel:+15551234567" class="contact-btn">
                    <i class="bi bi-telephone-fill"></i>
                    <span>Call Now</span>
                  </a> --}}
                  <a href="mailto:{{ $dosen->email_1 }}" class="contact-btn">
                    <i class="bi bi-envelope-fill"></i>
                    <span>Email</span>
                  </a>
                </div>
                {{-- <a href="quote.html" class="btn btn-primary w-100 mt-3">Get Free Estimate</a> --}}
              </div>

            </div><!-- End Service Sidebar -->
          </div>

          <div class="col-lg-8 order-lg-1">
            <div class="service-main-content">

                {{-- <div class="hero-section" data-aos="zoom-in" data-aos-delay="150">
                    <img src="{{ asset('landing/assets/img/construction/team-3.webp') }}" alt="Commercial Construction Services" class="img-fluid">
                    <!-- <div class="hero-overlay">
                    <div class="hero-badge">
                        <i class="bi bi-award"></i>
                        <span>Licensed &amp; Insured</span>
                    </div>
                    </div> -->
                </div> --}}

                <div class="content-section" data-aos="fade-up" data-aos-delay="200">
                    <h1>Portfolio</h1>
                    @if(isset($dosen->video_dosen) && !empty($dosen->video_dosen))
                      <div class="service-video">
                          <iframe
                              src="{{ $dosen->video_dosen }}"
                              title="Service Overview Video"
                              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                              allowfullscreen>
                          </iframe>
                          {{-- <iframe src="https://binusianorg.sharepoint.com/sites/DigitalContentBINUSMalang/_layouts/15/embed.aspx?UniqueId=bce8a2b4-839c-43ce-a0cb-9ad57075cbe6&embed=%7B%22af%22%3Atrue%2C%22ust%22%3Atrue%7D&referrer=StreamWebApp&referrerScenario=EmbedDialog.Create" width="1280" height="720" frameborder="0" scrolling="no" allowfullscreen title="MANALAGI_CARE.mp4"></iframe> --}}
                      </div>
                    @endif
                    @if(isset($dosen->deskripsi_dosen) && !empty($dosen->deskripsi_dosen))
                      <div class="content-intro" style="text-align: justify !important;">
                          {{-- <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit.</p> --}}
                          {!! $dosen->deskripsi_dosen !!}
                      </div>
                    @endif
                </div>

              @if($attribute->isNotEmpty())
                <div class="capabilities-grid" data-aos="fade-up" data-aos-delay="250">
                    <h2>Digital Lecturer Attribute</h2>
                    <div class="row g-4">
                        @foreach($attribute as $attr)
                            <div class="col-md-6">
                                <div class="capability-card">
                                    <div class="capability-icon">
                                        <i class="{{ $attr->attribute_icon }}"></i>
                                    </div>
                                    <h4>{{ $attr->attribute_dosen }}</h4>
                                    {{-- <p>Lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt.</p> --}}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
              @endif

              @if(isset($researchs) && !empty($researchs))
                <div class="methodology-section" data-aos="fade-up" data-aos-delay="300">
                  <h2>Research Projects</h2>
                  <div class="methodology-timeline">
                    @php
                      $i = 1;
                    @endphp
                    @foreach($researchs as $list)
                      <div class="timeline-item">
                        <div class="timeline-marker">
                          <span class="phase-number">{{ $i }}</span>
                        </div>
                        <div class="timeline-content">
                          <h4 style="text-align: justify !important;">{{ $list['title'] }}</h4>
                          <p style="text-align: justify !important;">{{ truncateDescription($list['abstract'], 200) }}</p>
                          @php
                            $keywords = explode(',', $list['keywords']);
                          @endphp
                          <ul class="phase-features">
                            @foreach($keywords as $katakunci)
                              <li>{{ $katakunci }}</li>
                            @endforeach
                          </ul>
                        </div>
                      </div>
                      @php
                        $i++;
                      @endphp
                    @endforeach

                  </div>
                </div>
              @endif

            </div><!-- End Service Main Content -->
          </div>
        </div>

        @if(isset($comdevs) && !empty($comdevs))
            <div class="portfolio-showcase certifications mt-5" data-aos="fade-up" data-aos-delay="350">
            <div class="showcase-header text-center">
                <h2>Community Development</h2>
                {{-- <p>Explore the portfolio of successfully completed community development involvements</p> --}}
            </div>
            <div class="certification-grid mt-4" data-aos="fade-up" data-aos-delay="400">
                @foreach($comdevs as $comdev_list)
                @php
                    $rand = rand(1, 4) * 100
                @endphp
                <div class="cert-card" data-aos="flip-left" data-aos-delay="{{ $rand }}">
                    <div class="cert-icon">
                    <img src="{{ asset('assets/backoffice/media/avatars/blank.png') }}" alt="{{ $comdev_list['community_name'] }}" class="img-fluid">
                    </div>
                    <div class="cert-details">
                    <h5 style="text-align: justify !important;">{{ $comdev_list['community_name'] }}</h5>
                    <span class="cert-category" style="text-align: justify !important;">{{ $comdev_list['location'] }}</span>
                    <p style="text-align: justify !important;">{{ $comdev_list['topic_name'] }}</p>
                    </div>
                </div>
                @endforeach

            </div>

            </div><!-- End Portfolio Showcase -->
        @endif
      </div>

    </section><!-- /Service Details Section -->

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

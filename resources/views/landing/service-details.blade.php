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
                  <i class="bi bi-building"></i>
                </div>
                <h3>Commercial Construction</h3>
                <p>Nulla facilisi morbi tempus iaculis urna id volutpat lacus laoreet non curabitur gravida.</p>
                <div class="service-stats">
                  <div class="stat-item">
                    <span class="stat-number">150+</span>
                    <span class="stat-label">Projects Completed</span>
                  </div>
                  <div class="stat-item">
                    <span class="stat-number">25</span>
                    <span class="stat-label">Years Experience</span>
                  </div>
                </div>
              </div>

              <div class="quick-info-card">
                <h4>Project Information</h4>
                <div class="info-grid">
                  <div class="info-row">
                    <span class="label">Duration:</span>
                    <span class="value">6-18 months</span>
                  </div>
                  <div class="info-row">
                    <span class="label">Investment:</span>
                    <span class="value">$50k - $2M+</span>
                  </div>
                  <div class="info-row">
                    <span class="label">Permit Support:</span>
                    <span class="value">Included</span>
                  </div>
                  <div class="info-row">
                    <span class="label">Warranty:</span>
                    <span class="value">10 years</span>
                  </div>
                </div>
              </div>

              <div class="contact-action-card">
                <h4>Ready to Start?</h4>
                <p class="contact-text">Mauris blandit aliquet elit eget tincidunt nibh pulvinar a proin gravida hendrerit.</p>
                <div class="contact-methods">
                  <a href="tel:+15551234567" class="contact-btn">
                    <i class="bi bi-telephone-fill"></i>
                    <span>Call Now</span>
                  </a>
                  <a href="mailto:projects@example.com" class="contact-btn">
                    <i class="bi bi-envelope-fill"></i>
                    <span>Email Us</span>
                  </a>
                </div>
                <a href="quote.html" class="btn btn-primary w-100 mt-3">Get Free Estimate</a>
              </div>

            </div><!-- End Service Sidebar -->
          </div>

          <div class="col-lg-8 order-lg-1">
            <div class="service-main-content">

                <div class="hero-section" data-aos="zoom-in" data-aos-delay="150">
                    <img src="{{ asset('landing/assets/img/construction/team-3.webp') }}" alt="Commercial Construction Services" class="img-fluid">
                    {{-- <div class="hero-overlay">
                    <div class="hero-badge">
                        <i class="bi bi-award"></i>
                        <span>Licensed &amp; Insured</span>
                    </div>
                    </div> --}}
                </div>

                <div class="content-section" data-aos="fade-up" data-aos-delay="200">
                    <h1>{{ $dosen->nama_dosen }}</h1>
                    <div class="service-video">
                        <iframe
                            src="https://www.youtube.com/embed/T8WMhX4GT7o"
                            title="Service Overview Video"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <div class="content-intro">
                        <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit.</p>
                        <p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores.</p>
                    </div>
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

              <div class="methodology-section" data-aos="fade-up" data-aos-delay="300">
                <h2>Research Projects</h2>
                <div class="methodology-timeline">
                  <div class="timeline-item">
                    <div class="timeline-marker">
                      <span class="phase-number">1</span>
                    </div>
                    <div class="timeline-content">
                      <h4>Planning &amp; Design</h4>
                      <p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</p>
                      <ul class="phase-features">
                        <li>Site analysis and assessment</li>
                        <li>Architectural drawings</li>
                        <li>Permit acquisition</li>
                      </ul>
                    </div>
                  </div>

                  <div class="timeline-item">
                    <div class="timeline-marker">
                      <span class="phase-number">2</span>
                    </div>
                    <div class="timeline-content">
                      <h4>Foundation &amp; Structure</h4>
                      <p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias.</p>
                      <ul class="phase-features">
                        <li>Site preparation and excavation</li>
                        <li>Foundation construction</li>
                        <li>Structural framework</li>
                      </ul>
                    </div>
                  </div>

                  <div class="timeline-item">
                    <div class="timeline-marker">
                      <span class="phase-number">3</span>
                    </div>
                    <div class="timeline-content">
                      <h4>Construction &amp; Installation</h4>
                      <p>Et harum quidem rerum facilis est et expedita distinctio nam libero tempore cum soluta nobis est eligendi optio cumque nihil impedit quo minus.</p>
                      <ul class="phase-features">
                        <li>Mechanical and electrical systems</li>
                        <li>Interior and exterior finishing</li>
                        <li>Quality control inspections</li>
                      </ul>
                    </div>
                  </div>

                  <div class="timeline-item">
                    <div class="timeline-marker">
                      <span class="phase-number">4</span>
                    </div>
                    <div class="timeline-content">
                      <h4>Completion &amp; Handover</h4>
                      <p>Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae.</p>
                      <ul class="phase-features">
                        <li>Final inspections and testing</li>
                        <li>Documentation and warranties</li>
                        <li>Project handover and training</li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

            </div><!-- End Service Main Content -->
          </div>
        </div>

        <div class="portfolio-showcase mt-5" data-aos="fade-up" data-aos-delay="350">
          <div class="showcase-header text-center">
            <h2>Community Development</h2>
            <p>Explore the portfolio of successfully completed community development involvements</p>
          </div>
          <div class="row g-4 mt-3">
            <div class="col-lg-6">
              <div class="project-showcase-item">
                <div class="project-image">
                  <img src="{{ asset('landing/assets/img/construction/project-6.webp') }}" alt="Office Building Construction" class="img-fluid">
                  <div class="project-overlay">
                    <div class="project-info">
                      <h4 style="color: white;">Downtown Office Complex</h4>
                      <p>12-story commercial building with modern amenities</p>
                      <a href="{{ asset('landing/assets/img/construction/project-6.webp') }}" class="view-btn glightbox">
                        <i class="bi bi-eye"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="row g-4">
                <div class="col-12">
                  <div class="project-showcase-item">
                    <div class="project-image">
                      <img src="{{ asset('landing/assets/img/construction/project-7.webp') }}" alt="Retail Space Construction" class="img-fluid">
                      <div class="project-overlay">
                        <div class="project-info">
                          <h4 style="color: white;">Shopping Center Renovation</h4>
                          <p>Complete modernization of existing retail space</p>
                          <a href="{{ asset('landing/assets/img/construction/project-7.webp') }}" class="view-btn glightbox">
                            <i class="bi bi-eye"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-12">
                  <div class="project-showcase-item">
                    <div class="project-image">
                      <img src="{{ asset('landing/assets/img/construction/project-8.webp') }}" alt="Warehouse Construction" class="img-fluid">
                      <div class="project-overlay">
                        <div class="project-info">
                          <h4 style="color: white;">Industrial Warehouse</h4>
                          <p>50,000 sq ft distribution facility</p>
                          <a href="{{ asset('landing/assets/img/construction/project-8.webp') }}" class="view-btn glightbox">
                            <i class="bi bi-eye"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div><!-- End Portfolio Showcase -->

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

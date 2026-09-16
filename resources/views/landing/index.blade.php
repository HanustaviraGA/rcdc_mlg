@php
    use App\Models\AttributeDosen;
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>BINUS@Malang - Portal Catur Dharma</title>
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

<body class="index-page">

  @include('landing.components.header')

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="hero-content" data-aos="fade-right" data-aos-delay="200">
              <span class="subtitle">BINUS@Malang</span>
              <h1>Portal Catur Dharma</h1>
              {{-- <p>Showcase of lecturer achievements in research, publications, professional development, and community development.</p> --}}

              <div class="hero-buttons">
                <a href="#lecturers" class="btn-primary">Our Lecturers</a>
                <a href="#research" class="btn-secondary">Our Research</a>
              </div>

              <div class="trust-badges">
                <div class="badge-item">
                  <i class="bi bi-people"></i>
                  <div class="badge-text">
                    <span class="count">118</span>
                    <span class="label">Lecturers</span>
                  </div>
                </div>
                <div class="badge-item">
                  <i class="bi bi-clipboard-data"></i>
                  <div class="badge-text">
                    <span class="count">500+</span>
                    <span class="label">Research Completed</span>
                  </div>
                </div>
                <div class="badge-item">
                  <i class="bi bi-building"></i>
                  <div class="badge-text">
                    <span class="count">100+</span>
                    <span class="label">MSMEs Acquired</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
            <div class="hero-image">
              <img src="{{ asset('landing/assets/img/BINUS-Malang.png') }}" alt="Construction Project" class="img-fluid">
              {{-- <div class="image-badge">
                <span>ISO 9001:2015</span>
                <p>Certified Construction</p>
              </div> --}}
            </div>
          </div>
        </div>

      </div>

    </section><!-- /Hero Section -->

    <!-- Team Section -->
    <section id="lecturers" class="team section">

      <!-- Section Title -->
      <div class="container section-title">
        <h2>Lecturers</h2>
        {{-- <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p> --}}
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        {{-- <div class="col-lg-12 content" data-aos="fade-right" data-aos-delay="200">
            <h2>Digital Lecturer Attribute</h2>
            <p>BINUS@Malang memiliki pengajar dengan keterampilan serta keahlian di bidang digital yang mendukung kegiatan serta peran dalam kegiatan pembelajaran.</p>
        </div> --}}

        <div class="row gy-4 mt-5">

          {{-- <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="team-card featured">
              <div class="team-header">
                <div class="team-image">
                  <img src="{{ asset('landing/assets/img/construction/team-1.webp') }}" class="img-fluid" alt="">
                  <div class="experience-badge">15+ Years</div>
                </div>
                <div class="team-info">
                  <h4>Marcus Thompson</h4>
                  <span class="position">Project Manager</span>
                  <div class="contact-info">
                    <a href="mailto:marcus@example.com"><i class="bi bi-envelope"></i> marcus@example.com</a>
                    <a href="tel:+1555123456"><i class="bi bi-telephone"></i> +1 (555) 123-456</a>
                  </div>
                </div>
              </div>
              <div class="team-details">
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
                <div class="credentials">
                  <div class="cred-item">
                    <i class="bi bi-award"></i>
                    <span>PMP Certified</span>
                  </div>
                  <div class="cred-item">
                    <i class="bi bi-shield-check"></i>
                    <span>OSHA 30</span>
                  </div>
                </div>
                <div class="social-links">
                  <a href="#"><i class="bi bi-linkedin"></i></a>
                  <a href="#"><i class="bi bi-twitter-x"></i></a>
                  <a href="#"><i class="bi bi-facebook"></i></a>
                </div>
              </div>
            </div>
          </div><!-- End Featured Team Member -->

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <div class="team-card featured">
              <div class="team-header">
                <div class="team-image">
                  <img src="{{ asset('landing/assets/img/construction/team-2.webp') }}" class="img-fluid" alt="">
                  <div class="experience-badge">12+ Years</div>
                </div>
                <div class="team-info">
                  <h4>Sarah Rodriguez</h4>
                  <span class="position">Site Supervisor</span>
                  <div class="contact-info">
                    <a href="mailto:sarah@example.com"><i class="bi bi-envelope"></i> sarah@example.com</a>
                    <a href="tel:+1555123457"><i class="bi bi-telephone"></i> +1 (555) 123-457</a>
                  </div>
                </div>
              </div>
              <div class="team-details">
                <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
                <div class="credentials">
                  <div class="cred-item">
                    <i class="bi bi-person-badge"></i>
                    <span>Licensed Contractor</span>
                  </div>
                  <div class="cred-item">
                    <i class="bi bi-tools"></i>
                    <span>Site Management</span>
                  </div>
                </div>
                <div class="social-links">
                  <a href="#"><i class="bi bi-linkedin"></i></a>
                  <a href="#"><i class="bi bi-twitter-x"></i></a>
                  <a href="#"><i class="bi bi-instagram"></i></a>
                </div>
              </div>
            </div>
          </div><!-- End Featured Team Member --> --}}

          <div class="col-lg-4 col-md-12" data-aos="fade-up" data-aos-delay="100">
            <div class="team-card compact">
              <div class="member-summary">
                <h5>Program Studi</h5>
                <div class="mt-3">
                  <canvas id="chart-prodi" height="260"></canvas>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-12" data-aos="fade-up" data-aos-delay="200">
            <div class="team-card compact">
              <div class="member-summary">
                <h5>JJA</h5>
                <div class="mt-3">
                  <canvas id="chart-jja" height="260"></canvas>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-md-12" data-aos="fade-up" data-aos-delay="300">
            <div class="team-card compact">
              <div class="member-summary">
                <h5>Pendidikan</h5>
                <div class="mt-3">
                  <canvas id="chart-pendidikan" height="260"></canvas>
                </div>
              </div>
            </div>
          </div>

          @if(false)
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
          @endif

        </div>

      </div>

    </section><!-- /Team Section -->

    <!-- About Section -->
    {{-- <section id="about" class="about section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row align-items-center g-5">
          <div class="col-lg-6">
            <div class="about-content" data-aos="fade-right" data-aos-delay="200">
              <h2>Building Excellence Since 1995</h2>
              <p class="lead">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin gravida tortor in magna feugiat, quis faucibus libero commodo. Maecenas semper lacus vel leo ultrices, vel tempus lectus varius.</p>
              <p>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Nulla facilisi. Duis cursus nisi eu orci laoreet, vel molestie enim ullamcorper. Phasellus at convallis neque, id vehicula magna.</p>

              <div class="achievement-boxes row g-4 mt-4">
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="300">
                  <div class="achievement-box">
                    <h3>25+</h3>
                    <p>Years Experience</p>
                  </div>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="400">
                  <div class="achievement-box">
                    <h3>500+</h3>
                    <p>Projects Completed</p>
                  </div>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="500">
                  <div class="achievement-box">
                    <h3>100%</h3>
                    <p>Client Satisfaction</p>
                  </div>
                </div>
                <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="600">
                  <div class="achievement-box">
                    <h3>48</h3>
                    <p>Team Members</p>
                  </div>
                </div>
              </div>

              <div class="certifications mt-5" data-aos="fade-up" data-aos-delay="700">
                <h5>Certifications &amp; Partnerships</h5>
                <div class="row g-3 align-items-center">
                  <div class="col-4 col-md-3">
                    <img src="{{ asset('landing/assets/img/construction/badge-4.webp') }}" alt="Certification" class="img-fluid">
                  </div>
                  <div class="col-4 col-md-3">
                    <img src="{{ asset('landing/assets/img/construction/badge-3.webp') }}" alt="Certification" class="img-fluid">
                  </div>
                  <div class="col-4 col-md-3">
                    <img src="{{ asset('landing/assets/img/construction/badge-5.webp') }}" alt="Certification" class="img-fluid">
                  </div>
                </div>
              </div>

              <div class="cta-container mt-5" data-aos="fade-up" data-aos-delay="800">
                <a href="about.html" class="btn btn-primary">Learn More About Us</a>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="about-image position-relative" data-aos="fade-left" data-aos-delay="200">
              <img src="{{ asset('landing/assets/img/construction/project-3.webp') }}" alt="Construction Team" class="img-fluid main-image rounded">
              <div class="image-overlay">
                <img src="{{ asset('landing/assets/img/construction/project-7.webp') }}" alt="Construction Project" class="img-fluid rounded">
              </div>
              <div class="experience-badge" data-aos="zoom-in" data-aos-delay="500">
                <span>25+</span>
                <p>Years of Experience</p>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /About Section --> --}}

    <!-- Services Section -->
    {{-- <section id="services" class="services section">

      <!-- Section Title -->
      <div class="container section-title">
        <h2>Services</h2>
        <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4">
          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
            <div class="service-card">
              <div class="service-icon">
                <i class="bi bi-building"></i>
              </div>
              <h3>Commercial Construction</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.</p>
              <div class="service-features">
                <span><i class="bi bi-check-circle"></i> Office Buildings</span>
                <span><i class="bi bi-check-circle"></i> Retail Spaces</span>
                <span><i class="bi bi-check-circle"></i> Warehouses</span>
              </div>
              <a href="service-details.html" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
            <div class="service-card featured">
              <div class="service-badge">Most Requested</div>
              <div class="service-icon">
                <i class="bi bi-house"></i>
              </div>
              <h3>Residential Construction</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla quam velit, vulputate eu pharetra nec, mattis ac neque.</p>
              <div class="service-features">
                <span><i class="bi bi-check-circle"></i> Custom Homes</span>
                <span><i class="bi bi-check-circle"></i> Renovations</span>
                <span><i class="bi bi-check-circle"></i> Additions</span>
              </div>
              <a href="service-details.html" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
            </div>
          </div><!-- End Service Item -->

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="400">
            <div class="service-card">
              <div class="service-icon">
                <i class="bi bi-gear"></i>
              </div>
              <h3>Industrial Construction</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque id tellus quis risus vehicula vehicula ut turpis.</p>
              <div class="service-features">
                <span><i class="bi bi-check-circle"></i> Manufacturing</span>
                <span><i class="bi bi-check-circle"></i> Processing Plants</span>
                <span><i class="bi bi-check-circle"></i> Storage Facilities</span>
              </div>
              <a href="service-details.html" class="service-link">Learn More <i class="bi bi-arrow-right"></i></a>
            </div>
          </div><!-- End Service Item -->
        </div>

        <div class="row mt-5">
          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
            <div class="service-image-block">
              <img src="{{ asset('landing/assets/img/construction/project-1.webp') }}" alt="Construction Services" class="img-fluid">
            </div>
          </div>

          <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
            <div class="service-list-block">
              <h3>Additional Services</h3>
              <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat.</p>

              <div class="service-list">
                <div class="service-list-item" data-aos="fade-up" data-aos-delay="100">
                  <div class="service-list-icon">
                    <i class="bi bi-rulers"></i>
                  </div>
                  <div class="service-list-content">
                    <h4>Architectural Design</h4>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi vitae imperdiet neque.</p>
                  </div>
                </div><!-- End Service List Item -->

                <div class="service-list-item" data-aos="fade-up" data-aos-delay="200">
                  <div class="service-list-icon">
                    <i class="bi bi-calendar-check"></i>
                  </div>
                  <div class="service-list-content">
                    <h4>Project Management</h4>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer lacinia dui lectus.</p>
                  </div>
                </div><!-- End Service List Item -->

                <div class="service-list-item" data-aos="fade-up" data-aos-delay="300">
                  <div class="service-list-icon">
                    <i class="bi bi-tools"></i>
                  </div>
                  <div class="service-list-content">
                    <h4>Renovation &amp; Remodeling</h4>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur in nulla ut magna.</p>
                  </div>
                </div><!-- End Service List Item -->
              </div>
            </div>
          </div>
        </div>

        <div class="cta-container text-center mt-5" data-aos="fade-up" data-aos-delay="300">
          <h3>Ready to Start Your Construction Project?</h3>
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla facilisi.</p>
          <a href="#" class="btn btn-cta">Request a Free Quote</a>
        </div>

      </div>

    </section><!-- /Services Section --> --}}

    @include('landing.research_gallery.home_section')

    <!-- Certifications Section -->
    <section id="certifications" class="certifications section">

      <!-- Section Title -->
      <div class="container section-title">
        <h2>Community Development</h2>
        {{-- <p>Necessitatibus eius consequatur ex aliquid fuga eum quidem sint consectetur velit</p> --}}
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row align-items-center mb-5 content">
          <div class="col-lg-12" data-aos="fade-right" data-aos-delay="200">
            <h2>UMKM Partnership</h2>
            <p>BINUS@Malang memiliki hubungan yang kuat dengan UMKM sebagai bentuk pengabdian kepada masyarakat.</p>
          </div>
          {{-- <div class="col-lg-6" data-aos="fade-left" data-aos-delay="300">
            <div class="badge-highlight">
              <img src="{{ asset('landing/assets/img/construction/badge-5.webp') }}" alt="Quality Excellence Badge" class="img-fluid">
              <div class="badge-content">
                <h4>Premier Contractor Status</h4>
                <p>Recognized by the state board for outstanding quality and safety standards</p>
              </div>
            </div>
          </div> --}}
        </div>

        <div class="certification-grid" data-aos="fade-up" data-aos-delay="400">
          <div class="row gy-4">
            <div class="col-lg-6 col-md-12" data-aos="fade-up" data-aos-delay="100">
              <div class="team-card compact">
                <div class="member-summary">
                  <h5>Tahun Bergabung</h5>
                  <div class="mt-3">
                    <canvas id="chart-umkm-year" height="260"></canvas>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-lg-6 col-md-12" data-aos="fade-up" data-aos-delay="200">
              <div class="team-card compact">
                <div class="member-summary">
                  <h5>Cluster UMKM</h5>
                  <div class="mt-3">
                    <canvas id="chart-umkm-cluster" height="260"></canvas>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- <div class="achievements-banner" data-aos="zoom-in" data-aos-delay="700">
          <div class="row text-center">
            <div class="col-lg-3 col-sm-6">
              <div class="achievement-item">
                <i class="bi bi-award"></i>
                <h3>15+</h3>
                <p>Industry Awards</p>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="achievement-item">
                <i class="bi bi-shield-check"></i>
                <h3>Zero</h3>
                <p>Safety Incidents</p>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="achievement-item">
                <i class="bi bi-clock-history"></i>
                <h3>18</h3>
                <p>Years Experience</p>
              </div>
            </div>
            <div class="col-lg-3 col-sm-6">
              <div class="achievement-item">
                <i class="bi bi-people"></i>
                <h3>350+</h3>
                <p>Satisfied Clients</p>
              </div>
            </div>
          </div>
        </div> --}}

      </div>

    </section><!-- /Certifications Section -->

    <!-- Testimonials Section -->
    {{-- <section id="testimonials" class="testimonials section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="testimonials-slider swiper init-swiper">
          <script type="application/json" class="swiper-config">
            {
              "loop": true,
              "speed": 600,
              "autoplay": {
                "delay": 5000
              },
              "slidesPerView": 1,
              "spaceBetween": 30,
              "pagination": {
                "el": ".swiper-pagination",
                "type": "bullets",
                "clickable": true
              },
              "navigation": {
                "nextEl": ".swiper-button-next",
                "prevEl": ".swiper-button-prev"
              }
            }
          </script>
          <div class="swiper-wrapper">

            <div class="swiper-slide">
              <div class="testimonial-slide" data-aos="fade-up" data-aos-delay="200">
                <div class="testimonial-header">
                  <div class="stars-rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </div>
                  <div class="quote-icon">
                    <i class="bi bi-quote"></i>
                  </div>
                </div>
                <div class="testimonial-body">
                  <p>"Binus Malang membantu saya memberikan pengarahan serta pendampingan dalam pengembangan bisnis saya. Kini bisnis saya berjalan dengan lancar."</p>
                </div>
                <div class="testimonial-footer">
                  <div class="author-info">
                    <img src="{{ asset('uploads/comdev/testimonial/mandato.jpg') }}" alt="Rika Hernawati" class="author-avatar">
                    <div class="author-details">
                      <h4>Rika Hernawati</h4>
                      <span class="role">Owner</span>
                      <span class="company">Mandato</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="swiper-slide">
              <div class="testimonial-slide" data-aos="fade-up" data-aos-delay="300">
                <div class="testimonial-header">
                  <div class="stars-rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </div>
                  <div class="quote-icon">
                    <i class="bi bi-quote"></i>
                  </div>
                </div>
                <div class="testimonial-body">
                  <p>"Pelatihan serta bimbingan yang diberikan oleh Binus Malang melalui Pak Pandu sangat bermanfaat bagi pemasaran usaha saya."</p>
                </div>
                <div class="testimonial-footer">
                  <div class="author-info">
                    <img src="{{ asset('uploads/comdev/testimonial/raissa.webp') }}" alt="Mira Ayu Candra Palupi" class="author-avatar">
                    <div class="author-details">
                      <h4>Mira Ayu Candra Palupi</h4>
                      <span class="role">Owner</span>
                      <span class="company">Raissa Catering and Cookies</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="swiper-slide">
              <div class="testimonial-slide" data-aos="fade-up" data-aos-delay="400">
                <div class="testimonial-header">
                  <div class="stars-rating">
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </div>
                  <div class="quote-icon">
                    <i class="bi bi-quote"></i>
                  </div>
                </div>
                <div class="testimonial-body">
                  <p>"Tidak hanya pelatihan, Binus Malang juga memberikan ruang untuk memasarkan dagangan kepada masyarakat melalui event - event rutin. Terima kasih Binus Malang"</p>
                </div>
                <div class="testimonial-footer">
                  <div class="author-info">
                    <img src="{{ asset('uploads/comdev/testimonial/superheru.jpg') }}" alt="Heru Nurwahyudin" class="author-avatar">
                    <div class="author-details">
                      <h4>Heru Nurwahyudin</h4>
                      <span class="role">Owner</span>
                      <span class="company">Superheru</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <div class="swiper-navigation-wrapper">
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
            <div class="swiper-button-next"></div>
          </div>

        </div>

      </div>

    </section> --}}
    <!-- /Testimonials Section -->

    <!-- Call To Action Section -->
    {{-- <section id="call-to-action" class="call-to-action section light-background">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-5 align-items-center">

          <div class="col-lg-6">
            <div class="cta-hero-content" data-aos="fade-right" data-aos-delay="200">
              <div class="badge-wrapper">
                <span class="cta-badge">
                  <i class="bi bi-shield-check"></i>
                  Licensed &amp; Bonded Since 2008
                </span>
              </div>

              <h2>Transform Your Space with Expert Construction Services</h2>
              <p>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae. Mauris viverra veniam sit amet lacus cursus venenatis. Donec auctor blandit quam, ac sollicitudin eros convallis vel.</p>

              <div class="feature-highlights">
                <div class="highlight-item">
                  <i class="bi bi-check-circle-fill"></i>
                  <span>Free project consultation and detailed estimates</span>
                </div>
                <div class="highlight-item">
                  <i class="bi bi-check-circle-fill"></i>
                  <span>Comprehensive insurance coverage for all projects</span>
                </div>
                <div class="highlight-item">
                  <i class="bi bi-check-circle-fill"></i>
                  <span>24/7 emergency response and support services</span>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="cta-form-section" data-aos="fade-left" data-aos-delay="300">
              <div class="form-container">
                <div class="form-header">
                  <h3>Request Your Free Quote</h3>
                  <p>Get started with your next construction project today</p>
                </div>

                <form action="forms/get-a-quote.php" method="post" class="php-email-form">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <div class="form-group">
                        <input type="text" name="name" class="form-control" placeholder="Your Name" required="">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Your Email" required="">
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="form-group">
                        <input type="tel" name="phone" class="form-control" placeholder="Phone Number" required="">
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="form-group">
                        <select name="type" class="form-control" required="">
                          <option value="">Select Project Type</option>
                          <option value="residential">Residential Construction</option>
                          <option value="commercial">Commercial Building</option>
                          <option value="renovation">Renovation &amp; Remodeling</option>
                          <option value="industrial">Industrial Projects</option>
                          <option value="other">Other</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="form-group">
                        <textarea name="message" class="form-control" rows="4" placeholder="Project Details" required=""></textarea>
                      </div>
                    </div>
                  </div>

                  <div class="loading">Loading</div>
                  <div class="error-message"></div>
                  <div class="sent-message">Your quote request has been sent. Thank you!</div>

                  <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                      <i class="bi bi-send"></i>
                      Send Quote Request
                    </button>

                    <div class="contact-alternative">
                      <span>Or call us directly:</span>
                      <a href="tel:+15558921567" class="phone-link">
                        <i class="bi bi-telephone-fill"></i>
                        +1 (555) 892-1567
                      </a>
                    </div>
                  </div>
                </form>
              </div>

              <div class="trust-indicators" data-aos="fade-up" data-aos-delay="400">
                <div class="row g-3">
                  <div class="col-4">
                    <div class="trust-item">
                      <div class="trust-icon">
                        <i class="bi bi-clock"></i>
                      </div>
                      <div class="trust-content">
                        <span class="trust-number">24h</span>
                        <span class="trust-label">Response Time</span>
                      </div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="trust-item">
                      <div class="trust-icon">
                        <i class="bi bi-star-fill"></i>
                      </div>
                      <div class="trust-content">
                        <span class="trust-number">4.9</span>
                        <span class="trust-label">Customer Rating</span>
                      </div>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="trust-item">
                      <div class="trust-icon">
                        <i class="bi bi-hammer"></i>
                      </div>
                      <div class="trust-content">
                        <span class="trust-number">350+</span>
                        <span class="trust-label">Projects Done</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>

    </section><!-- /Call To Action Section --> --}}

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
  <script src="{{ asset('assets/backoffice/js/custom/campur/chart.js') }}"></script>

  <!-- Main JS File -->
  <script src="{{ asset('landing/assets/js/main.js') }}"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const buildChart = (canvasId, labels, data, type, options = {}) => {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
          return;
        }

        new Chart(canvas, {
          type,
          data: {
            labels,
            datasets: [{
              data
            }]
          },
          options: Object.assign({
            responsive: true,
            maintainAspectRatio: false
          }, options)
        });
      };

      buildChart(
        'chart-prodi',
        @json($prodiLabels),
        @json($prodiCounts),
        'doughnut',
        {
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      );

      buildChart(
        'chart-jja',
        @json($jjaLabels),
        @json($jjaData),
        'bar',
        {
          indexAxis: 'y',
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            x: {
              beginAtZero: true
            }
          }
        }
      );

      buildChart(
        'chart-pendidikan',
        @json($pendidikanLabels),
        @json($pendidikanData),
        'bar',
        {
          indexAxis: 'y',
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            x: {
              beginAtZero: true
            }
          }
        }
      );

      buildChart(
        'chart-umkm-year',
        @json($umkmYearLabels),
        @json($umkmYearData),
        'bar',
        {
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      );

      buildChart(
        'chart-umkm-cluster',
        @json($umkmClusterLabels),
        @json($umkmClusterData),
        'doughnut',
        {
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      );
    });
  </script>

</body>

</html>

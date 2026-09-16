<header id="header" class="header sticky-top">

    <div class="topbar d-flex align-items-center dark-background">
      <div class="container d-flex justify-content-center justify-content-md-between">
        <div class="contact-info d-flex align-items-center">
          <i class="bi bi-envelope d-flex align-items-center"><a href="mailto:rcdc.mlg@binus.edu">rcdc.mlg@binus.edu</a></i>
          <i class="bi bi-phone d-flex align-items-center ms-4"><span>+62 (341) 3036969</span></i>
        </div>
        <div class="social-links d-none d-md-flex align-items-center">
          {{-- <a href="#" class="twitter"><i class="bi bi-twitter-x"></i></a>
          <a href="#" class="facebook"><i class="bi bi-facebook"></i></a> --}}
          <a href="https://www.instagram.com/rcdc_binusmalang/" target="_blank" class="instagram"><i class="bi bi-instagram"></i></a>
          {{-- <a href="#" class="linkedin"><i class="bi bi-linkedin"></i></a> --}}
        </div>
      </div>
    </div><!-- End Top Bar -->

    <div class="branding d-flex align-items-cente">

      <div class="container position-relative d-flex align-items-center justify-content-between">
        <a href="{{ url('/') }}" class="logo d-flex align-items-center">
          <!-- Uncomment the line below if you also wish to use an image logo -->
          <img src="{{ asset('Logo-Formal-ALRC-FIX.png') }}" alt="ALRC BINUS Malang — Home" class="logo-formal-alrc">
          {{-- <h1 class="sitename">Constructo</h1> --}}
        </a>

        <nav id="navmenu" class="navmenu">
          <ul>
            <li><a href="{{ url('/') }}" @if (Request::is('/')) class="active" @endif>Home</a></li>
            {{-- <li><a href="about.html">About</a></li>
            <li><a href="services.html">Services</a></li>
            <li><a href="projects.html">Projects</a></li> --}}
            <li><a href="{{ route('lecturers') }}" @if (Request::is('lecturers')) class="active" @endif>Lecturers</a></li>
            <li><a href="{{ route('publication-dashboard') }}" @if (Request::routeIs('publication-dashboard')) class="active" aria-current="page" @endif>Dashboard KPI</a></li>
            <li><a href="{{ route('research-gallery.index') }}" @if (Request::routeIs('research-gallery.*')) class="active" aria-current="page" @endif>Research Gallery</a></li>
            <li><a href="{{ route('outletpublikasi.public') }}" @if (Request::routeIs('outletpublikasi.public')) class="active" @endif>Outlet Publikasi</a></li>
            {{-- <li class="dropdown"><a href="#"><span>More Pages</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="service-details.html">Service Details</a></li>
                <li><a href="project-details.html">Project Details</a></li>
                <li><a href="quote.html">Quote Form</a></li>
                <li><a href="terms.html">Terms</a></li>
                <li><a href="privacy.html">Privacy</a></li>
                <li><a href="404.html">404</a></li>
              </ul>
            </li> --}}
            {{-- <li class="dropdown"><a href="#"><span>Dropdown</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
              <ul>
                <li><a href="#">Dropdown 1</a></li>
                <li class="dropdown"><a href="#"><span>Deep Dropdown</span> <i class="bi bi-chevron-down toggle-dropdown"></i></a>
                  <ul>
                    <li><a href="#">Deep Dropdown 1</a></li>
                    <li><a href="#">Deep Dropdown 2</a></li>
                    <li><a href="#">Deep Dropdown 3</a></li>
                    <li><a href="#">Deep Dropdown 4</a></li>
                    <li><a href="#">Deep Dropdown 5</a></li>
                  </ul>
                </li>
                <li><a href="#">Dropdown 2</a></li>
                <li><a href="#">Dropdown 3</a></li>
                <li><a href="#">Dropdown 4</a></li>
              </ul>
            </li> --}}
            {{-- <li><a href="contact.html">Contact</a></li> --}}
          </ul>
          <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
        </nav>

      </div>

    </div>

  </header>

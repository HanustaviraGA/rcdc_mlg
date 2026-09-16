<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Portal Catur Dharma') · BINUS Malang</title>
    <meta name="description" content="@yield('description', 'Jelajahi capaian publikasi, penelitian, dan kolaborasi BINUS Malang melalui Portal Catur Dharma.')">
    <link rel="icon" href="{{ asset('assets/backoffice/media/logos/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&family=Lato:wght@400;700&family=Ubuntu:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="{{ asset('landing/assets/vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/vendor/aos/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/vendor/swiper/swiper-bundle.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/assets/vendor/glightbox/css/glightbox.min.css') }}">
    {{-- Constructo: the same template and shared components used by Home. --}}
    <link rel="stylesheet" href="{{ asset('landing/assets/css/main.css') }}">
    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/public-portal.css') }}">
</head>
<body class="public-portal">
    <a class="portal-skip" href="#main-content">Lewati ke konten</a>
    @include('landing.components.header')
    <main id="main-content" class="main @yield('page_class')" tabindex="-1">
        @yield('content')
    </main>
    @include('landing.components.footer')
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center" aria-label="Kembali ke atas"><i class="bi bi-arrow-up-short" aria-hidden="true"></i></a>
    <script src="{{ asset('landing/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('landing/assets/vendor/aos/aos.js') }}"></script>
    <script src="{{ asset('landing/assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
    <script src="{{ asset('landing/assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('landing/assets/js/main.js') }}"></script>
    @stack('scripts')
</body>
</html>

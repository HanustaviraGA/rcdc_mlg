<!DOCTYPE html>
<!--
Author: Keenthemes
Product Name: Metronic - Bootstrap 5 HTML, VueJS, React, Angular & Laravel Admin Dashboard Theme
Purchase: https://1.envato.market/EA4JP
Website: http://www.keenthemes.com
Contact: support@keenthemes.com
Follow: www.twitter.com/keenthemes
Dribbble: www.dribbble.com/keenthemes
Like: www.facebook.com/keenthemes
License: For each use you must have a valid license purchased only from above link in order to legally use the theme for your project.
-->
<html lang="en">
	<!--begin::Head-->
	<head><base href="../">
		<title id="ttl">RCDC Binus@Malang</title>
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta charset="utf-8" />
		<link rel="shortcut icon" href="{{ asset('20230411_163530_0000-removebg-preview.png') }}" />
		<!--begin::Fonts-->
		<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
		<!--end::Fonts-->
		<!--begin::Global Stylesheets Bundle(used by all pages)-->
		<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
		<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/plugins/custom/jstree/jstree.bundle.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/plugins/custom/jquery-confirm/jquery-confirm.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
		<!--end::Global Stylesheets Bundle-->
        <link href="{{ asset('assets/plugins/custom/orgchart-master/css/jquery.orgchart.css') }}" rel="stylesheet" type="text/css" />
        <link rel="stylesheet" href="{{ asset('assets/css/custom/introjs.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('assets/css/custom/tempus-dominus.min.css') }}" crossorigin="anonymous">
        <link rel="stylesheet" href="{{ asset('assets/css/custom/leaflet.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom/magnific-popup.css') }}">
        <link href="{{ asset('assets/css/custom/tagify.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet" type="text/css" />
        <link rel='stylesheet' href='{{ asset('assets/plugins/custom/hls/plyr.css') }}'>
	</head>
	<!--end::Head-->
	<!--begin::Body-->
	<body id="kt_body" style="background-image: url({{ asset('assets/media/patterns/header-bg-rcdc2.png') }})" class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled aside-enabled">
		<!--begin::Main-->
		<!--begin::Root-->
		<div class="d-flex flex-column flex-root">
			<!--begin::Page-->
			<div class="page d-flex flex-row flex-column-fluid">
				<!--begin::Wrapper-->
				<div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">

                    <!--begin::Header-->
					<div id="kt_header" class="header align-items-stretch" data-kt-sticky="true" data-kt-sticky-name="header" data-kt-sticky-offset="{default: '200px', lg: '300px'}">
						<!--begin::Container-->
						<div class="container-xxl d-flex align-items-center">
							<!--begin::Heaeder menu toggle-->
							<div class="d-flex topbar align-items-center d-lg-none ms-n2 me-3" title="Show aside menu">
								<div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px" id="kt_header_menu_mobile_toggle">
									<!--begin::Svg Icon | path: icons/duotune/abstract/abs015.svg-->
									<span class="svg-icon svg-icon-2x">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
											<path d="M21 7H3C2.4 7 2 6.6 2 6V4C2 3.4 2.4 3 3 3H21C21.6 3 22 3.4 22 4V6C22 6.6 21.6 7 21 7Z" fill="black" />
											<path opacity="0.3" d="M21 14H3C2.4 14 2 13.6 2 13V11C2 10.4 2.4 10 3 10H21C21.6 10 22 10.4 22 11V13C22 13.6 21.6 14 21 14ZM22 20V18C22 17.4 21.6 17 21 17H3C2.4 17 2 17.4 2 18V20C2 20.6 2.4 21 3 21H21C21.6 21 22 20.6 22 20Z" fill="black" />
										</svg>
									</span>
									<!--end::Svg Icon-->
								</div>
							</div>
							<!--end::Heaeder menu toggle-->
							<!--begin::Header Logo-->
							<div class="header-logo me-5 me-md-10 flex-grow-1 flex-lg-grow-0">
								<a href="{{ url('/') }}">
									{{-- <img alt="Logo" src="assets/media/logos/logo-rcdc.png" class="logo-default h-75px" />
									<img alt="Logo" src="assets/media/logos/logo-rcdc.png" class="logo-sticky h-75px" /> --}}
									<img alt="Logo" src="{{ asset('20230411_163530_0000-removebg-preview.png') }}" class="logo-default h-75px" />
									<img alt="Logo" src="{{ asset('20230411_163530_0000-removebg-preview.png') }}" class="logo-sticky h-75px" />
								</a>
							</div>
							<!--end::Header Logo-->
							<!--begin::Wrapper-->
							<div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1">
								<!--begin::Navbar-->
								<div class="d-flex align-items-stretch" id="kt_header_nav">
									<!--begin::Menu wrapper-->
									<div class="header-menu align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_header_menu_mobile_toggle" data-kt-swapper="true" data-kt-swapper-mode="prepend" data-kt-swapper-parent="{default: '#kt_body', lg: '#kt_header_nav'}">
										<!--begin::Menu-->
										<div class="menu menu-lg-rounded menu-column menu-lg-row menu-state-bg menu-title-gray-700 menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-400 fw-bold my-5 my-lg-0 align-items-stretch" id="#kt_header_menu" data-kt-menu="true">

                                           {{-- Sengaja dikosongkan --}}

										</div>
										<!--end::Menu-->
									</div>
									<!--end::Menu wrapper-->
								</div>
								<!--end::Navbar-->
								<!--begin::Topbar-->
								<div class="d-flex align-items-stretch flex-shrink-0">
									<!--begin::Toolbar wrapper-->
									<div class="topbar d-flex align-items-stretch flex-shrink-0">

										<!--begin::User-->
										<div class="d-flex align-items-center me-n3 ms-1 ms-lg-3" id="kt_header_user_menu_toggle">
											<!--begin::Menu wrapper-->
											<div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px" data-kt-menu-trigger="click" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
												<img class="h-30px w-30px rounded" src="{{ url('assets/media/avatars/blank.png') }}" alt="" />
											</div>
											<!--begin::Menu-->
											<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-primary fw-bold py-4 fs-6 w-275px" data-kt-menu="true">
												<!--begin::Menu item-->
												<div class="menu-item px-3">
													<div class="menu-content d-flex align-items-center px-3">
														<!--begin::Username-->
														<div class="d-flex flex-column">
															<div class="fw-bolder d-flex align-items-center fs-5">
                                                                Navigasi
                                                            </div>
														</div>
														<!--end::Username-->
													</div>
												</div>
												<!--end::Menu item-->
												<div class="separator my-2"></div>
												<!--begin::Menu item-->
												<div class="menu-item px-5">
													<a href="https://sites.google.com/view/rcdcmalang/home?authuser=0" class="menu-link px-5">Google Sites</a>
												</div>
												<div class="menu-item px-5">
													<a href="" class="menu-link px-5">Linktree RCDC</a>
												</div>
												<div class="menu-item px-5">
													<a href="" class="menu-link px-5">Linktree DOQA</a>
												</div>
												<div class="menu-item px-5">
													<a href="" class="menu-link px-5">Landing</a>
												</div>
												<!--end::Menu item-->
											</div>
											<!--end::Menu-->
											<!--end::Menu wrapper-->
										</div>
										<!--end::User -->

										<!--begin::Aside mobile toggle-->
										<div class="d-flex align-items-center d-lg-none ms-4" title="Show header menu">
											<div class="btn btn-icon btn-active-light-primary w-30px h-30px w-md-40px h-md-40px" id="kt_aside_mobile_toggle">
												<!--begin::Svg Icon | path: icons/duotune/text/txt001.svg-->
												<span class="svg-icon svg-icon-1">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
														<path d="M13 11H3C2.4 11 2 10.6 2 10V9C2 8.4 2.4 8 3 8H13C13.6 8 14 8.4 14 9V10C14 10.6 13.6 11 13 11ZM22 5V4C22 3.4 21.6 3 21 3H3C2.4 3 2 3.4 2 4V5C2 5.6 2.4 6 3 6H21C21.6 6 22 5.6 22 5Z" fill="black" />
														<path opacity="0.3" d="M21 16H3C2.4 16 2 15.6 2 15V14C2 13.4 2.4 13 3 13H21C21.6 13 22 13.4 22 14V15C22 15.6 21.6 16 21 16ZM14 20V19C14 18.4 13.6 18 13 18H3C2.4 18 2 18.4 2 19V20C2 20.6 2.4 21 3 21H13C13.6 21 14 20.6 14 20Z" fill="black" />
													</svg>
												</span>
												<!--end::Svg Icon-->
											</div>
										</div>
										<!--end::Aside mobile toggle-->
									</div>
									<!--end::Toolbar wrapper-->
								</div>
								<!--end::Topbar-->
							</div>
							<!--end::Wrapper-->
						</div>
						<!--end::Container-->
					</div>
					<!--end::Header-->

					<!--begin::Toolbar-->
					<div class="toolbar py-5 py-lg-15" id="kt_toolbar">
						<!--begin::Container-->
						<div id="kt_toolbar_container" class="container-xxl d-flex flex-stack flex-wrap">
							<!--begin::Page title-->
							<div class="page-title d-flex flex-column me-3">
								<!--begin::Title-->
								<h1 class="d-flex text-white fw-bolder my-1 fs-3" id="ttl-header" style="font-size: 25px !important;">Dashboard</h1>
								<!--end::Title-->
							</div>
							<!--end::Page title-->

						</div>
						<!--end::Container-->
					</div>
					<!--end::Toolbar-->

					<!--begin::Container-->
					<div id="kt_content_container" class="d-flex flex-column-fluid align-items-start container-xxl">
						<!--begin::Aside-->
						<div id="kt_aside" class="aside card" data-kt-drawer="true" data-kt-drawer-name="aside" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="{default:'200px', '300px': '250px'}" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_aside_mobile_toggle" data-kt-sticky="true" data-kt-sticky-name="aside-sticky" data-kt-sticky-offset="{default: false, lg: '200px'}" data-kt-sticky-width="{lg: '265px'}" data-kt-sticky-left="auto" data-kt-sticky-top="95px" data-kt-sticky-animation="false" data-kt-sticky-zindex="95">
							<!--begin::Aside menu-->
							<div class="aside-menu flex-column-fluid">
								<!--begin::Aside Menu-->
								<div class="hover-scroll-overlay-y my-5 my-lg-6" id="kt_aside_menu_wrapper" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-dependencies="#kt_header, #kt_aside_footer, #kt_footer" data-kt-scroll-wrappers="#kt_aside, #kt_aside_menu" data-kt-scroll-offset="0px">
									<!--begin::Menu-->
									<div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500" id="#kt_aside_menu" data-kt-menu="true">

                                        {!! $sidebar !!}

									</div>
									<!--end::Menu-->
								</div>
							</div>
							<!--end::Aside menu-->
						</div>
						<!--end::Aside-->

						<!--begin::Post-->
						<div class="content flex-row-fluid" id="kt_content">
							<!--begin::Card-->
							<div class="card">
								<!--begin::Card body-->
								<div class="card-body p-0">

									<!--begin::Wrapper-->
									<div class="card-px" id="kt_post">
										<!--begin::Title-->
										<h2 class="fs-2x fw-bolder mb-10 mt-10">Selamat datang !</h2>
										<!--end::Title-->
										<!--begin::Description-->
										<p class="text-gray-400 fs-4 fw-bold mb-10">Memuat aplikasi, mohon tunggu...</p>
										<!--end::Description-->
									</div>
									<!--end::Wrapper-->

								</div>
								<!--end::Card body-->
							</div>
							<!--end::Card-->
						</div>
						<!--end::Post-->

					</div>
					<!--end::Container-->

					<!--begin::Footer-->
					<div class="footer py-4 d-flex flex-lg-column" id="kt_footer">
						<!--begin::Container-->
						<div class="container-xxl d-flex flex-column flex-md-row align-items-center justify-content-between">
							<!--begin::Copyright-->
							<div class="text-dark order-2 order-md-1">
								<span class="text-muted fw-bold me-1">{{ date('Y') }} ©</span>
								<a href="javascript:void(0)" class="text-gray-800 text-hover-primary">Research and Community Development Center (RCDC)</a>
							</div>
							<!--end::Copyright-->
							<!--begin::Menu-->
							<ul class="menu menu-gray-600 menu-hover-primary fw-bold order-1">
								<li class="menu-item">
									<a href="https://sites.google.com/view/rcdcmalang/home/kontak-kami" target="_blank" class="menu-link px-2">Support</a>
								</li>
							</ul>
							<!--end::Menu-->
						</div>
						<!--end::Container-->
					</div>
					<!--end::Footer-->

				</div>
				<!--end::Wrapper-->
			</div>
			<!--end::Page-->
		</div>
		<!--end::Root-->

		<!--begin::Scrolltop-->
		<div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
			<!--begin::Svg Icon | path: icons/duotune/arrows/arr066.svg-->
			<span class="svg-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
					<rect opacity="0.5" x="13" y="6" width="13" height="2" rx="1" transform="rotate(90 13 6)" fill="black" />
					<path d="M12.5657 8.56569L16.75 12.75C17.1642 13.1642 17.8358 13.1642 18.25 12.75C18.6642 12.3358 18.6642 11.6642 18.25 11.25L12.7071 5.70711C12.3166 5.31658 11.6834 5.31658 11.2929 5.70711L5.75 11.25C5.33579 11.6642 5.33579 12.3358 5.75 12.75C6.16421 13.1642 6.83579 13.1642 7.25 12.75L11.4343 8.56569C11.7467 8.25327 12.2533 8.25327 12.5657 8.56569Z" fill="black" />
				</svg>
			</span>
			<!--end::Svg Icon-->
		</div>
		<!--end::Scrolltop-->

		<!--end::Main-->
		<script>var hostUrl = "{{ asset('assets/') }}";</script>
		<!--begin::Javascript-->
		<!--begin::Global Javascript Bundle(used by all pages)-->
		<script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
		<script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
        <script src="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js') }}"></script>
        {{-- <script src="{{ asset('assets/js/custom/apps/calendar/calendar.js') }}"></script> --}}
		<!--end::Global Javascript Bundle-->
		<!--begin::Page Custom Javascript(used by this page)-->
		<script src="{{ asset('assets/js/custom/widgets.js') }}"></script>
		<script src="{{ asset('assets/js/custom/apps/chat/chat.js') }}"></script>
		<script src="{{ asset('assets/js/custom/modals/create-app.js') }}"></script>
		<script src="{{ asset('assets/js/custom/modals/upgrade-plan.js') }}"></script>
        {{-- Block UI --}}
        <script src="{{ asset('assets/plugins/custom/blockui/jquery.blockUI.js') }}"></script>
        {{-- jsTree --}}
        <script src="{{ asset('assets/plugins/custom/jstree/jstree.bundle.js') }}"></script>
        {{-- jQuery Confirm --}}
        <script src="{{ asset('assets/plugins/custom/jquery-confirm/jquery-confirm.js') }}"></script>
        {{-- Datatables --}}
        <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
        {{-- Various --}}
        <script src="{{ asset('assets/plugins/custom/introjs/intro.min.js') }}"></script>
        <script src="{{ asset('assets/js/custom/campur/index-amcharts.js') }}"></script>
        <script src="{{ asset('assets/js/custom/campur/xy.js') }}"></script>
        <script src="{{ asset('assets/js/custom/campur/percent.js') }}"></script>
        <script src="{{ asset('assets/js/custom/campur/Animated.js') }}"></script>
        <script src="{{ asset('assets/js/custom/campur/id_ID.js') }}"></script>
        <script src="{{ asset('assets/js/custom/campur/chart.js') }}"></script>
        <script type="text/javascript" src="{{ asset('assets/js/custom/campur/qr-code-styling.js') }}"></script>
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <!-- Popperjs -->
        <script src="{{ asset('assets/js/custom/campur/popper.min.js') }}" crossorigin="anonymous"></script>
        <!-- Tempus Dominus JavaScript -->
        <script src="{{ asset('assets/js/custom/campur/tempus-dominus.min.js') }}" crossorigin="anonymous"></script>
        <script type="text/javascript" src="{{ asset('assets/js/custom/campur/jquery.magnific-popup.js') }}"></script>
        <script src="{{ asset('assets/plugins/custom/orgchart-master/js/jquery.orgchart.js') }}"></script>
	    <script src="{{ asset('assets/plugins/custom/orgchart-master/js/json-digger.js') }}"></script>
        <script src="{{ asset('assets/js/custom/campur/tagify.js') }}"></script>
        <script src="{{ asset('assets/js/custom/campur/tagify.polyfills.min.js') }}"></script>
        <script src="{{ asset('assets/js/custom/campur/ckeditor.js') }}"></script>
        {{-- Navigo --}}
        <script src="{{ asset('assets/js/custom/campur/navigo.min.js') }}"></script>
        {{-- Pusher --}}
        <script src="{{ asset('assets/plugins/custom/pusher/pusher.min.js') }}"></script>
        {{-- Emoji --}}
        <script src="{{ asset('assets/plugins/custom/emoji/vanillaEmojiPicker.js') }}"></script>
        {{-- HLS --}}
        <script src="{{ asset('assets/plugins/custom/hls/hls.min.js') }}"></script>
        <script src="{{ asset('assets/plugins/custom/hls/plyr.min.js') }}"></script>
        {{-- Form Repeater --}}
        <script src="{{ asset('assets/plugins/custom/formrepeater/formrepeater.bundle.js') }}"></script>
		<!--begin::Page Custom Javascript(used by this page)-->
		{{-- <script src="{{ asset('assets/js/custom/apps/customers/list/export.js') }}"></script>
		<script src="{{ asset('assets/js/custom/apps/customers/list/list.js') }}"></script>
		<script src="{{ asset('assets/js/custom/apps/customers/add.js') }}"></script>
		<script src="{{ asset('assets/js/custom/widgets.js') }}"></script>
		<script src="{{ asset('assets/js/custom/apps/chat/chat.js') }}"></script>
		<script src="{{ asset('assets/js/custom/modals/create-app.js') }}"></script>
		<script src="{{ asset('assets/js/custom/modals/upgrade-plan.js') }}"></script> --}}
		{{-- Pie Chart --}}
		<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
		<!--end::Page Custom Javascript-->
        @include('dashboard.javascript')
		<!--end::Javascript-->
	</body>
	<!--end::Body-->
</html>

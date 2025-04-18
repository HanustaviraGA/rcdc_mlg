<!DOCTYPE html>
<html lang="en">
   <head>
      <!-- basic -->
      <meta charset="utf-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <!-- mobile metas -->
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="viewport" content="initial-scale=1, maximum-scale=1">
      <!-- site metas -->
      <title>RCDC Binus Malang</title>
      <meta name="keywords" content="">
      <meta name="description" content="">
      <meta name="author" content="">
      <!-- site icon -->
      <link rel="icon" href="{{asset('20230411_163530_0000-removebg-preview.png')}}" type="image/png" />
      <!-- bootstrap css -->
      <link rel="stylesheet" href="{{asset('css/bootstrap.min.css')}}" />
      <!-- site css -->
      <link rel="stylesheet" href="{{asset('style.css')}}" />
      <!-- responsive css -->
      <link rel="stylesheet" href="{{asset('css/responsive.css')}}" />
      <!-- color css -->
      <link rel="stylesheet" href="{{asset('css/colors.css')}}" />
      <!-- select bootstrap -->
      <link rel="stylesheet" href="{{asset('css/bootstrap-select.css')}}" />
      <!-- scrollbar css -->
      <link rel="stylesheet" href="{{asset('css/perfect-scrollbar.css')}}" />
      <!-- custom css -->
      <link rel="stylesheet" href="{{asset('css/custom.css')}}" />
      <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
      <![endif]-->
   </head>
   <body class="dashboard dashboard_1">
      <div class="full_container">
         <div class="inner_container">
            <!-- Sidebar  -->
            @include('sidebar')
            <!-- end sidebar -->
            <!-- right content -->
            <div id="content">
               <!-- topbar -->
               @include('topbar')
               <!-- end topbar -->
               <!-- dashboard inner -->
               @yield('konten')
               <!-- end dashboard inner -->
               <!-- footer -->
               <div class="container-fluid">
                  <div class="footer d-flex align-items-start">
                     <!-- Image -->
                     <img src="{{ asset('20230411_163530_0000-removebg-preview.png') }}" alt="Fostering & Empowering" style="width: 150px; height: auto; margin-top: 40px; margin-left:20px;">
                     <img src="{{asset('sticker-3.gif')}}" alt="Fostering & Empowering" style="width: 150px; height: auto; margin-top: 40px; margin-right: 20px;">
                     <!-- Text block -->
                     <p style="text-align: left; margin: 0;">
                        Email : <a href="mailto:rcdc.mlg@binus.edu">rcdc.mlg@binus.edu</a><br>
                        <b>Research and Community Development Center (RCDC)</b><br>
                        BINUS University, Malang Campus<br>
                        Araya Mansion, Kav 8-22, Araya 65154<br>
                        Ext 7552
                     </p>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- jQuery -->
      <script src="{{asset('js/jquery.min.js')}}"></script>
      <script src="{{asset('js/popper.min.js')}}"></script>
      <script src="{{asset('js/bootstrap.min.js')}}"></script>
      <!-- wow animation -->
      <script src="{{asset('js/animate.js')}}"></script>
      <!-- select country -->
      <script src="{{asset('js/bootstrap-select.js')}}"></script>
      <!-- owl carousel -->
      <script src="{{asset('js/owl.carousel.js')}}"></script> 
      <!-- chart js -->
      <script src="{{asset('js/Chart.min.js')}}"></script>
      <script src="{{asset('js/Chart.bundle.min.js')}}"></script>
      <script src="{{asset('js/utils.js')}}"></script>
      <script src="{{asset('js/analyser.js')}}"></script>
      <!-- nice scrollbar -->
      <script src="{{asset('js/perfect-scrollbar.min.js')}}"></script>
      <script>
         var ps = new PerfectScrollbar('#sidebar');
      </script>
      <!-- custom js -->
      <script src="{{asset('js/chart_custom_style1.js')}}"></script>
      <script src="{{asset('js/custom.js')}}"></script>
   </body>
</html>
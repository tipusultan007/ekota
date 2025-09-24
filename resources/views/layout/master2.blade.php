<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>@yield('title', 'পদ্মা শ্রমজীবী সমবায় সমিতি লিমিটেড | সমিতি ব্যবস্থাপনা সফটওয়্যার বাংলাদেশ')</title>
<meta name="description" content="@yield('meta_description', 'পদ্মা শ্রমজীবী সমবায় সমিতি লিমিটেড - আধুনিক সমিতি ব্যবস্থাপনা সফটওয়্যার বাংলাদেশে। Savings, Loan, FDR, Microfinance ও Accounting সিস্টেম এক প্ল্যাটফর্মে। Laravel দিয়ে নির্মিত।')">
<meta name="author" content="পদ্মা শ্রমজীবী সমবায় সমিতি লিমিটেড">
<meta name="keywords" content="@yield('meta_keywords', 'পদ্মা শ্রমজীবী সমবায় সমিতি লিমিটেড, সমিতি সফটওয়্যার বাংলাদেশ, Samiti Management Software, microfinance software, cooperative software, loan management, savings management, FDR management, accounting system, laravel software, সমিতি, সমবায়, ক্ষুদ্রঋণ সফটওয়্যার')">


  <!-- color-modes:js -->
  @vite(['resources/js/pages/color-modes.js'])
  <script>
    (function() {
      const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
      document.documentElement.setAttribute('data-bs-theme', theme);
    })();
  </script>

 <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- End fonts -->

  <!-- CSRF Token -->
  <meta name="_token" content="{{ csrf_token() }}">
  
  <link rel="shortcut icon" href="{{ asset('/favicon.ico') }}">

  <!-- Splash Screen -->
  <link href="{{ asset('splash-screen.css') }}" rel="stylesheet" />

  <!-- plugin css -->
  <link href="{{ asset('build/plugins/perfect-scrollbar/perfect-scrollbar.css') }}" rel="stylesheet" />

  @stack('plugin-styles')

  <!-- CSS for LTR layout-->
  @vite(['resources/sass/app.scss', 'resources/css/custom.css'])

  <!-- CSS for RTL layout-->
  <!-- @vite(['resources/rtl-css/app-rtl.css', 'resources/rtl-css/custom-rtl.css']) -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">

  @stack('style')
</head>
<body data-base-url="{{url('/')}}">

  <script>
    // Create splash screen container
    var splash = document.createElement("div");
    splash.innerHTML = `
      <div class="splash-screen">
        <div class="logo"></div>
        <div class="spinner"></div>
      </div>`;
    
    // Insert splash screen as the first child of the body
    document.body.insertBefore(splash, document.body.firstChild);

    // Add 'loaded' class to body once DOM is fully loaded
    document.addEventListener("DOMContentLoaded", function () {
      document.body.classList.add("loaded");
    });
  </script>

  <div class="main-wrapper" id="app">
    <div class="page-wrapper full-page">
      <div class="page-content container-xxl d-flex align-items-center justify-content-center">
        @yield('content')
      </div>
    </div>
  </div>

    <!-- base js -->
    @vite(['resources/js/app.js'])
    <script src="{{ asset('build/plugins/lucide/lucide.min.js') }}"></script>
    <!-- end base js -->

    <!-- plugin js -->
    @stack('plugin-scripts')
    <!-- end plugin js -->

    <!-- common js -->
    @vite(['resources/js/pages/template.js'])
    <!-- end common js -->

    @stack('custom-scripts')
    <script src="{{ asset('build/plugins/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
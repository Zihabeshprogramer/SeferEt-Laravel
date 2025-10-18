<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Login') - {{ config('app.name', 'SeferEt') }}</title>
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <!-- Additional CSS -->
    @stack('styles')
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <!-- Logo -->
        <div class="login-logo">
            <a href="{{ url('/') }}">
                <i class="fas fa-mosque text-primary mr-2"></i>
                <b>Sefer</b>Et
            </a>
        </div>
        
        <!-- Login Card -->
        <div class="card">
            <div class="card-body login-card-body">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    
    <!-- Additional JavaScript -->
    @stack('scripts')
    
    <!-- Page-specific JavaScript -->
    @yield('scripts')
</body>
</html>

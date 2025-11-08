<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'B2B Portal') - {{ config('app.name', 'SeferEt') }}</title>
    
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <style>
        .auth-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-image: url('data:image/svg+xml,<svg width="180" height="180" viewBox="0 0 180 180" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="golden-islamic" x="0" y="0" width="180" height="180" patternUnits="userSpaceOnUse"><g fill="none" stroke="%23DAA520" opacity="0.25"><circle cx="90" cy="90" r="50" stroke-width="1.5" fill="none"/><circle cx="90" cy="90" r="35" stroke-width="1.2" fill="none"/><circle cx="90" cy="90" r="22" stroke-width="1.5" fill="none"/><circle cx="90" cy="90" r="12" stroke-width="1" fill="none"/><path d="M90,35 L110,55 L90,75 L70,55 Z M90,75 L110,95 L90,115 L70,95 Z M35,90 L55,70 L75,90 L55,110 Z M145,90 L125,70 L145,35 L165,55 Z M145,90 L165,110 L145,145 L125,125 Z" stroke-width="1.2" opacity="0.3"/><circle cx="90" cy="40" r="3" fill="%23DAA520" opacity="0.2"/><circle cx="140" cy="90" r="3" fill="%23DAA520" opacity="0.2"/><circle cx="90" cy="140" r="3" fill="%23DAA520" opacity="0.2"/><circle cx="40" cy="90" r="3" fill="%23DAA520" opacity="0.2"/><circle cx="65" cy="65" r="2" fill="%23DAA520" opacity="0.25"/><circle cx="115" cy="65" r="2" fill="%23DAA520" opacity="0.25"/><circle cx="65" cy="115" r="2" fill="%23DAA520" opacity="0.25"/><circle cx="115" cy="115" r="2" fill="%23DAA520" opacity="0.25"/><path d="M70,70 L110,70 M90,50 L90,130 M75,75 L105,105 M105,75 L75,105" stroke-width="0.8" opacity="0.15"/></g></pattern></defs><rect width="100%" height="100%" fill="url(%23golden-islamic)"/></svg>');
            background-size: 180px 180px;
            z-index: 1;
            pointer-events: none;
            opacity: 0.9;
        }
        .auth-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 1;
        }
        .auth-background-left {
            position: absolute;
            top: 0;
            left: 0;
            width: 60%;
            height: 100%;
            background: #f8f9fa;
            z-index: 1;
        }
        .auth-background-right {
            position: absolute;
            top: 0;
            right: 0;
            width: 40%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            clip-path: polygon(25% 0%, 100% 0%, 100% 100%, 25% 100%, 0% 50%);
            z-index: 2;
        }
        .auth-background-right::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.1) 100%);
            z-index: 1;
        }
        .floating-form-container {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            padding: 2rem 0;
        }
        .floating-form {
            max-width: 500px;
            margin: 0 auto;
            margin-left: 10%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .floating-form-wide {
            max-width: 600px;
        }
        .auth-image-col::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(0,0,0,0.4) 0%, rgba(0,0,0,0.2) 100%);
            z-index: 2;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .progress-step {
            position: relative;
            flex: 1;
            text-align: center;
        }
        .progress-step.active .step-number {
            background-color: #007bff;
            color: white;
        }
        .progress-step.completed .step-number {
            background-color: #28a745;
            color: white;
        }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: #6c757d;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .progress-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15px;
            left: 50%;
            width: 100%;
            height: 2px;
            background-color: #e9ecef;
            z-index: -1;
        }
        .progress-step.completed:not(:last-child)::after {
            background-color: #28a745;
        }
        .bg-opacity-25 {
            background-color: rgba(255, 255, 255, 0.25) !important;
        }
        .auth-image-col .text-white {
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .mosque-icon {
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(5px);
            box-shadow: 0 8px 32px rgba(0, 123, 255, 0.1);
        }
        @media (max-width: 767.98px) {
            .auth-background {
                display: none !important;
            }
            .auth-pattern {
                display: none !important;
            }
            .floating-form-container {
                min-height: auto;
                padding: 1rem;
            }
            .floating-form {
                margin-left: 0;
                background: white;
                backdrop-filter: none;
                box-shadow: none;
                border: none;
                border-radius: 0;
                padding: 1rem;
            }
        }
        @media (min-width: 768px) {
            .auth-pattern {
                animation: subtleFloat 20s ease-in-out infinite;
            }
        }
        @media (min-width: 768px) {
            .auth-pattern {
                animation: subtleFloat 20s ease-in-out infinite;
            }
        }
        @keyframes subtleFloat {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-2px); }
        }
    </style>
    
    <!-- Additional CSS -->
    @stack('styles')
</head>
<body class="bg-light">
    <!-- Fixed Background Layers -->
    <div class="auth-background d-none d-md-block">
        <!-- Left Side Background -->
        <div class="auth-background-left"></div>
        
        <!-- Right Side Background with Image -->
        <div class="auth-background-right" 
             style="background-image: url('@yield('auth-image', 'https://via.placeholder.com/400x600/667eea/ffffff?text=Partner+Portal')');">
            <div class="d-flex align-items-center justify-content-center h-100 position-relative" style="z-index: 2;">
                <div class="text-center text-white p-4">
                    <div class="mb-4">
                        <i class="@yield('auth-icon', 'fas fa-handshake') fa-4x mb-3" style="opacity: 0.9;"></i>
                    </div>
                    <h3 class="mb-3 font-weight-bold">@yield('auth-title', 'Welcome to SeferEt')</h3>
                    <p class="lead mb-4" style="opacity: 0.9;">@yield('auth-subtitle', 'Your trusted B2B partner portal for seamless travel business management')</p>
                    <div class="mt-4">
                        <div class="d-inline-flex align-items-center bg-white bg-opacity-25 rounded-pill px-3 py-2">
                            <i class="fas fa-users mr-2"></i>
                            <span class="small font-weight-semibold">@yield('auth-message', 'Join thousands of partners worldwide')</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Islamic Pattern Overlay -->
    <div class="auth-pattern d-none d-md-block"></div>
    
    <!-- Floating Form Container -->
    <div class="floating-form-container">
        <div class="floating-form @yield('form-class', '')">
            <!-- Logo -->
            <div class="text-center mb-4">
                <a href="{{ url('/') }}" class="text-decoration-none">
                    <h2 class="text-primary mb-0">
                        <img src="{{ asset('images/logo/seferet-logo-notext-colored.png') }}" alt="SeferEt" style="height: 65px; width: 105px; margin-right: 8px;">
                         <span class="font-weight-bold">Sefer</span>Et
                    </h2>
                </a>
                <p class="text-muted mt-2">Partner Portal</p>
            </div>

            <!-- Main Content -->
            @yield('content')
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

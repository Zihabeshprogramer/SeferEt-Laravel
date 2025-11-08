<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Customer Portal') - {{ config('app.name', 'SeferEt') }}</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            /* Customer Brand Colors - Blue Theme */
            --primary-color: #1E40AF;
            --primary-rgb: 30, 64, 175;
            --primary-light: #3B82F6;
            --primary-dark: #1E3A8A;
            
            /* Secondary Colors */
            --secondary-color: #F59E0B;
            --secondary-rgb: 245, 158, 11;
            --accent-color: #06B6D4;
            
            /* Surface Colors */
            --surface-color: #FFFFFF;
            --surface-variant-color: #F8FAFC;
            --background-color: #F1F5F9;
            --border-color: #E2E8F0;
            
            /* Text Colors */
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #64748B;
            --text-on-primary: #FFFFFF;
            
            /* Shadows */
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .auth-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-image: url('data:image/svg+xml,<svg width="180" height="180" viewBox="0 0 180 180" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="customer-pattern" x="0" y="0" width="180" height="180" patternUnits="userSpaceOnUse"><g fill="none" stroke="%231E40AF" opacity="0.15"><circle cx="90" cy="90" r="50" stroke-width="1.5"/><circle cx="90" cy="90" r="35" stroke-width="1.2"/><circle cx="90" cy="90" r="22" stroke-width="1.5"/><path d="M90,35 L110,55 L90,75 L70,55 Z M90,75 L110,95 L90,115 L70,95 Z" stroke-width="1.2" opacity="0.3"/><circle cx="90" cy="40" r="3" fill="%231E40AF" opacity="0.2"/><circle cx="140" cy="90" r="3" fill="%231E40AF" opacity="0.2"/><circle cx="90" cy="140" r="3" fill="%231E40AF" opacity="0.2"/></g></pattern></defs><rect width="100%" height="100%" fill="url(%23customer-pattern)"/></svg>');
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
            background: var(--background-color);
            z-index: 1;
        }
        
        .auth-background-right {
            position: absolute;
            top: 0;
            right: 0;
            width: 40%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
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
            background: linear-gradient(135deg, rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.05) 100%);
            z-index: 1;
        }
        
        .floating-form-container {
            position: relative;
            z-index: 10;
            min-height: 100vh;
            padding: 2rem 0;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }
        
        .floating-form {
            max-width: 500px;
            width: 100%;
            margin-left: 10%;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(30, 64, 175, 0.15);
            padding: 2.5rem;
            border: 1px solid rgba(30, 64, 175, 0.1);
        }
        
        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: var(--surface-variant-color);
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
            background-color: white;
            outline: none;
        }
        
        .form-control.is-invalid {
            border-color: #EF4444;
            background-color: #FEF2F2;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            display: block;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 0.875rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.3);
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(var(--primary-rgb), 0.4);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-block {
            width: 100%;
        }
        
        .icheck-primary {
            display: flex;
            align-items: center;
        }
        
        .icheck-primary input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 0.5rem;
            cursor: pointer;
            accent-color: var(--primary-color);
        }
        
        .icheck-primary label {
            margin-bottom: 0;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-sm);
        }
        
        .alert-danger {
            background-color: #FEF2F2;
            color: #991B1B;
        }
        
        .alert-success {
            background-color: #F0FDF4;
            color: #166534;
        }
        
        .alert ul {
            margin-bottom: 0;
            padding-left: 1.25rem;
        }
        
        .invalid-feedback {
            color: #EF4444;
            font-size: 0.875rem;
            margin-top: 0.375rem;
            display: block;
        }
        
        .text-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .text-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border-color);
        }
        
        .divider span {
            padding: 0 1rem;
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        
        .btn-group-sm .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-outline-info {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-info:hover {
            background: var(--secondary-color);
            color: white;
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            color: white;
        }
        
        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.2s ease;
        }
        
        .password-toggle:hover {
            color: var(--text-primary);
        }
        
        .password-wrapper {
            position: relative;
        }
        
        @media (max-width: 767.98px) {
            .auth-background,
            .auth-pattern {
                display: none !important;
            }
            
            .floating-form-container {
                padding: 1rem;
                min-height: auto;
            }
            
            .floating-form {
                margin-left: 0;
                padding: 1.5rem;
                box-shadow: none;
                border: none;
                border-radius: 0;
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
        
        .fade-in {
            animation: fadeIn 0.4s ease-in;
        }
        
        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(10px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Fixed Background Layers -->
    <div class="auth-background d-none d-md-block">
        <div class="auth-background-left"></div>
        <div class="auth-background-right" 
             style="background-image: url('@yield('auth-image', route('get.media', ['login-rt.jpg']))');"></div>
    </div>

    <!-- Pattern Overlay -->
    <div class="auth-pattern d-none d-md-block"></div>
    
    <!-- Floating Form Container -->
    <div class="floating-form-container">
        <div class="floating-form fade-in">
            <!-- Logo -->
            <div class="text-center mb-4">
                <a href="{{ url('/') }}" class="text-decoration-none">
                    <img src="{{ asset('images/logo/seferet-logo-colored-sidetext.png') }}" 
                         alt="SeferEt" 
                         style="height: 70px; width: auto; margin-bottom: 0.5rem;">
                </a>
                <p class="text-muted mb-0" style="font-size: 0.9rem;">@yield('subtitle', 'Customer Portal')</p>
            </div>

            <!-- Main Content -->
            @yield('content')
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
    @yield('scripts')
</body>
</html>

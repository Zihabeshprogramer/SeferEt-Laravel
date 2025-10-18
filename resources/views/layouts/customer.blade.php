<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>
        @hasSection('title')
            @yield('title') - SeferEt
        @else
            SeferEt - Your Trusted Partner for Umrah Journeys
        @endif
    </title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Customer Styles -->
    <style>
        /* SeferEt Flutter App Design System */
        :root {
            /* Primary Brand Colors - Blue Theme */
            --primary-color: #1E40AF;
            --primary-rgb: 30, 64, 175;
            --primary-light: #3B82F6;
            --primary-dark: #1E3A8A;
            
            /* Secondary Colors */
            --secondary-color: #F59E0B;
            --secondary-rgb: 245, 158, 11;
            --accent-color: #06B6D4;
            --accent-rgb: 6, 182, 212;
            
            /* Status Colors */
            --success-color: #10B981;
            --success-rgb: 16, 185, 129;
            --error-color: #EF4444;
            --error-rgb: 239, 68, 68;
            --warning-color: #F59E0B;
            --warning-rgb: 245, 158, 11;
            --info-color: #06B6D4;
            --info-rgb: 6, 182, 212;
            
            /* Surface Colors - Modern Flutter Style */
            --surface-color: #FFFFFF;
            --surface-variant-color: #F8FAFC;
            --background-color: #F1F5F9;
            --background-secondary: #E2E8F0;
            --border-color: #E2E8F0;
            --border-light: #F1F5F9;
            --card-shadow: rgba(15, 23, 42, 0.08);
            
            /* Text Colors - Flutter Material Design */
            --text-primary: #0F172A;
            --text-secondary: #475569;
            --text-muted: #64748B;
            --text-light: #94A3B8;
            --text-on-primary: #FFFFFF;
            --disabled-text-color: #CBD5E1;
            --disabled-bg-color: #F1F5F9;
            
            /* Typography */
            --font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            --font-arabic: 'Amiri', serif;
            
            /* Spacing */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 3rem;
            
            /* Border Radius */
            --border-radius-sm: 0.375rem;
            --border-radius-md: 0.5rem;
            --border-radius-lg: 0.75rem;
            --border-radius-xl: 1rem;
            
            /* Shadows */
            --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Global Styles - Flutter App Style */
        body {
            font-family: var(--font-primary);
            background-color: var(--background-color);
            color: var(--text-primary);
            line-height: 1.6;
            font-size: 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding-top: 0; /* Remove any top padding since navbar is overlay */
        }
        
        /* Main content padding for non-homepage pages */
        .main-content {
            padding-top: 80px; /* Account for fixed navbar height */
        }
        
        /* Remove top padding for homepage since hero should extend to top */
        .home-page .main-content {
            padding-top: 0;
        }
        
        /* Default Bootstrap container-fluid override */
        .container-fluid {
            padding-left: 15px;
            padding-right: 15px;
            max-width: none;
        }
        
        /* Consistent container alignment system */
        .main-content .container-fluid,
        .hero-section .container-fluid,
        .stats-section .container-fluid,
        .featured-packages-section .container-fluid,
        .destinations-section .container-fluid,
        .offers-section .container-fluid,
        .why-choose-us-section .container-fluid,
        .cta-section .container-fluid,
        .testimonials-section .container-fluid {
            padding-left: 5rem;
            padding-right: 5rem;
        }
        
        @media (max-width: 1200px) {
            .main-content .container-fluid,
            .hero-section .container-fluid,
            .stats-section .container-fluid,
            .featured-packages-section .container-fluid,
            .destinations-section .container-fluid,
            .offers-section .container-fluid,
            .why-choose-us-section .container-fluid,
            .cta-section .container-fluid,
            .testimonials-section .container-fluid {
                padding-left: 3rem;
                padding-right: 3rem;
            }
        }
        
        @media (max-width: 768px) {
            .main-content .container-fluid,
            .hero-section .container-fluid,
            .stats-section .container-fluid,
            .featured-packages-section .container-fluid,
            .destinations-section .container-fluid,
            .offers-section .container-fluid,
            .why-choose-us-section .container-fluid,
            .cta-section .container-fluid,
            .testimonials-section .container-fluid {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
        
        /* Bootstrap 5 Overrides - Flutter App Design */
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--text-on-primary);
            box-shadow: 0 2px 4px rgba(var(--primary-rgb), 0.2);
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(var(--primary-rgb), 0.3);
        }
        
        .btn-secondary {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: var(--text-primary);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--text-on-primary);
        }
        
        /* Text Colors */
        .text-primary { color: var(--primary-color) !important; }
        .text-secondary { color: var(--text-secondary) !important; }
        .text-muted { color: var(--text-muted) !important; }
        .text-success { color: var(--success-color) !important; }
        .text-warning { color: var(--warning-color) !important; }
        .text-danger { color: var(--error-color) !important; }
        
        /* Background Colors */
        .bg-primary { background-color: var(--primary-color) !important; }
        .bg-secondary { background-color: var(--secondary-color) !important; }
        .bg-success { background-color: var(--success-color) !important; }
        .bg-warning { background-color: var(--warning-color) !important; }
        .bg-danger { background-color: var(--error-color) !important; }
        
        /* Card Styles */
        .card {
            border: none;
            box-shadow: 0 2px 8px var(--card-shadow);
            border-radius: 12px;
        }
        
        /* Animation Classes */
        .animate-fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Home Page Specific Styles */
        .hero-section {
            position: relative;
            min-height: 100vh; /* Full viewport height */
            display: flex;
            align-items: center;
            padding-top: 0; /* Remove padding since navbar is overlay */
        }
        
        .hero-background {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            background-image: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 300" fill="none"><path d="M0,100 C150,200 350,0 500,100 C650,200 850,0 1000,100 L1000,00 L0,0" fill="%23ffffff" fill-opacity="0.1"/></svg>');
            background-size: cover;
            background-position: bottom;
            width: 100%;
            min-height: 100vh; /* Full viewport height */
        }
        
        .search-card {
            max-width: 900px;
        }
        
        .nav-pills .nav-link {
            border-radius: 25px;
            margin: 0 0.25rem;
            transition: all 0.3s ease;
        }
        
        .nav-pills .nav-link.active {
            background: var(--primary-color);
            color: white;
        }
        
        .package-image {
            position: relative;
            overflow: hidden;
        }
        
        .package-image img {
            height: 200px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .package-card:hover .package-image img {
            transform: scale(1.05);
        }
        
        .package-badges {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        
        .destination-image {
            position: relative;
            overflow: hidden;
            height: 250px;
        }
        
        .destination-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .destination-card:hover .destination-image img {
            transform: scale(1.1);
        }
        
        .destination-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            padding: 1.5rem 1rem 1rem;
        }
        
        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)) !important;
        }
        
        .bg-gradient-success {
            background: linear-gradient(135deg, var(--success-color), #059669) !important;
        }
        
        .offer-card {
            border: none;
            overflow: hidden;
        }
        
        .badge-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        
        .discount-text {
            font-size: 1.5rem;
            font-weight: bold;
        }
        
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        
        .section-subtitle {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .min-vh-75 {
            min-height: 75vh;
        }
        
        .min-vh-100 {
            min-height: 100vh;
        }
        
        /* Navbar Styles with Scroll Behavior - Overlay Position */
        .seferet-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: transparent;
            backdrop-filter: none;
            border-bottom: none;
            transition: all 0.4s ease;
            z-index: 1050;
            box-shadow: none;
        }
        
        .seferet-navbar.transparent {
            background: transparent;
            backdrop-filter: none;
            border-bottom: none;
            box-shadow: none;
        }
        
        .seferet-navbar.scrolled {
            background: var(--background-color);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        
        .seferet-navbar .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color);
            transition: all 0.4s ease;
            text-shadow: none;
        }
        
        .seferet-navbar.transparent .navbar-brand {
            color: white;
            text-shadow: 0 2px 6px rgba(0,0,0,0.6);
        }
        
        .seferet-navbar.scrolled .navbar-brand {
            color: var(--primary-color);
            text-shadow: none;
        }
        
        .seferet-navbar .nav-link {
            color: var(--text-secondary);
            font-weight: 500;
            padding: 0.5rem 1rem;
            margin: 0 0.25rem;
            border-radius: 6px;
            transition: all 0.4s ease;
            display: flex;
            align-items: center;
            text-shadow: none;
        }
        
        .seferet-navbar .nav-link i {
            margin-right: 0.5rem;
            font-size: 0.9rem;
        }
        
        .seferet-navbar.transparent .nav-link {
            color: white;
            text-shadow: 0 1px 4px rgba(0,0,0,0.6);
        }
        
        .seferet-navbar.scrolled .nav-link {
            color: var(--text-secondary);
            text-shadow: none;
        }
        
        .seferet-navbar .nav-link:hover,
        .seferet-navbar .nav-link.active {
            background: var(--primary-color);
            color: white;
            text-shadow: none;
        }
        
        .seferet-navbar.transparent .nav-link:hover,
        .seferet-navbar.transparent .nav-link.active {
            background: rgba(255,255,255,0.25);
            color: white;
            text-shadow: none;
            backdrop-filter: blur(10px);
        }
        
        .seferet-navbar.scrolled .nav-link:hover,
        .seferet-navbar.scrolled .nav-link.active {
            background: var(--primary-color);
            color: white;
            text-shadow: none;
        }
        
        .seferet-navbar .navbar-toggler {
            border: none;
            color: var(--primary-color);
            transition: all 0.4s ease;
        }
        
        .seferet-navbar.transparent .navbar-toggler {
            color: white;
            text-shadow: 0 1px 3px rgba(0,0,0,0.6);
        }
        
        .seferet-navbar.scrolled .navbar-toggler {
            color: var(--primary-color);
            text-shadow: none;
        }
        
        .seferet-navbar .dropdown-menu {
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(15px);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            margin-top: 0.5rem;
        }
        
        .seferet-navbar.transparent .dropdown-menu {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        /* Navbar button styling for transparent and scrolled states */
        .seferet-navbar .btn {
            transition: all 0.4s ease;
        }
        
        .seferet-navbar.transparent .btn-secondary {
            background: rgba(255,255,255,0.2);
            border-color: rgba(255,255,255,0.3);
            color: white;
            backdrop-filter: blur(10px);
        }
        
        .seferet-navbar.transparent .btn-secondary:hover {
            background: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.4);
            color: white;
            transform: translateY(-1px);
        }
        
        .profile-avatar .avatar-placeholder {
            width: 35px;
            height: 35px;
            background: var(--surface-variant-color);
            color: var(--text-secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            transition: all 0.4s ease;
        }
        
        .seferet-navbar.transparent .profile-avatar .avatar-placeholder {
            background: rgba(255,255,255,0.25);
            color: white;
            backdrop-filter: blur(10px);
        }
        
        .seferet-navbar.scrolled .profile-avatar .avatar-placeholder {
            background: var(--surface-variant-color);
            color: var(--text-secondary);
        }
        
        /* Navbar container alignment - match main content exactly */
        .seferet-navbar .container-fluid {
            padding-left: 5rem !important;
            padding-right: 5rem !important;
            max-width: none !important;
        }
        
        @media (max-width: 1200px) {
            .seferet-navbar .container-fluid {
                padding-left: 3rem !important;
                padding-right: 3rem !important;
            }
        }
        
        @media (max-width: 768px) {
            .seferet-navbar .container-fluid {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }
        }
        
        /* Additional alignment fixes */
        .seferet-navbar .navbar-nav {
            align-items: center;
        }
        
        .seferet-navbar .navbar-nav .nav-item {
            margin: 0 0.125rem;
        }
        
        /* Ensure dropdown menus align correctly */
        .seferet-navbar .dropdown-menu {
            margin-top: 0.5rem;
        }
        
        /* Debug mode - uncomment to see container boundaries */
        /*
        .seferet-navbar .container-fluid {
            background: rgba(255, 0, 0, 0.1) !important;
        }
        .main-content .container-fluid {
            background: rgba(0, 255, 0, 0.1) !important;
        }
        */
    </style>
    
    <!-- Additional CSS -->
    @stack('styles')
    @yield('css')
</head>
<body class="seferet-customer {{ request()->is('/') ? 'home-page' : '' }}">
    
    <!-- Navigation -->
    @include('components.customer.navbar')
    
    <!-- Main Content -->
    <main class="main-content">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show m-3 animate-fade-in" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show m-3 animate-fade-in" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" aria-label="Close"></button>
            </div>
        @endif
        
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show m-3 animate-fade-in" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show m-3 animate-fade-in" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <!-- Page Content -->
        @yield('content')
    </main>
    
    <!-- Footer -->
    @include('components.customer.footer')
    
    <!-- Toast Container for Notifications -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;"></div>
    
    <!-- Bootstrap 5 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Customer JavaScript -->
    <script>
        // Initialize Bootstrap components
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Initialize toasts
            const toastElList = [].slice.call(document.querySelectorAll('.toast'));
            toastElList.map(function(toastEl) {
                return new bootstrap.Toast(toastEl);
            });
            
            // Initialize modals
            const modalElList = [].slice.call(document.querySelectorAll('.modal'));
            modalElList.map(function(modalEl) {
                return new bootstrap.Modal(modalEl);
            });
            
            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.classList.contains('show')) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);
            });
            
            // Navbar scroll behavior with fully transparent top state
            const navbar = document.querySelector('.seferet-navbar');
            let ticking = false;
            
            const applyNavbarState = () => {
                const scrollY = window.scrollY;
                const atTop = scrollY <= 20; // Reduced threshold for quicker transition
                const currentPath = window.location.pathname;
                const onHome = currentPath === '/' || 
                              currentPath === '/home' || 
                              currentPath === '' ||
                              currentPath.match(/^\/$/);
                
                // Clear all state classes first
                navbar.classList.remove('transparent', 'scrolled');
                
                if (onHome) {
                    if (atTop) {
                        navbar.classList.add('transparent');
                    } else {
                        navbar.classList.add('scrolled');
                    }
                } else {
                    // Always scrolled state on non-homepage
                    navbar.classList.add('scrolled');
                }
                
                ticking = false;
            };
            
            // Throttled scroll handler for better performance
            const handleScroll = () => {
                if (!ticking) {
                    requestAnimationFrame(applyNavbarState);
                    ticking = true;
                }
            };
            
            // Set initial state immediately
            const currentPath = window.location.pathname;
            const onHome = currentPath === '/' || 
                          currentPath === '/home' || 
                          currentPath === '' ||
                          currentPath.match(/^\/$/);
            
            if (onHome && window.scrollY <= 20) {
                navbar.classList.add('transparent');
            } else {
                navbar.classList.add('scrolled');
            }
            
            // Apply proper state after DOM is ready
            setTimeout(() => {
                applyNavbarState();
                // Add scroll listener
                window.addEventListener('scroll', handleScroll, { passive: true });
            }, 10);
            
            window.addEventListener('load', applyNavbarState);


        });
        
        // Utility functions
        window.SeferEt = {
            showToast: function(message, type = 'info') {
                const toastContainer = document.querySelector('.toast-container');
                if (!toastContainer) return;
                
                const toastId = 'toast-' + Date.now();
                const iconClass = {
                    'success': 'fas fa-check-circle',
                    'error': 'fas fa-exclamation-triangle',
                    'warning': 'fas fa-exclamation-circle',
                    'info': 'fas fa-info-circle'
                }[type] || 'fas fa-info-circle';
                
                const toastHTML = `
                    <div class="toast" id="${toastId}" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="toast-header">
                            <i class="${iconClass} me-2 text-${type}"></i>
                            <strong class="me-auto">SeferEt</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                        <div class="toast-body">${message}</div>
                    </div>
                `;
                
                toastContainer.insertAdjacentHTML('beforeend', toastHTML);
                const toastEl = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
                
                // Remove toast element after hiding
                toastEl.addEventListener('hidden.bs.toast', function() {
                    toastEl.remove();
                });
            },
            
            debounce: function(func, wait, immediate) {
                let timeout;
                return function executedFunction(...args) {
                    const later = function() {
                        timeout = null;
                        if (!immediate) func(...args);
                    };
                    const callNow = immediate && !timeout;
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                    if (callNow) func(...args);
                };
            },
            
            formatCurrency: function(amount) {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD'
                }).format(amount);
            }
        };
    </script>
    
    <!-- Additional JavaScript -->
    @stack('scripts')
    @yield('js')
    
</body>
</html>

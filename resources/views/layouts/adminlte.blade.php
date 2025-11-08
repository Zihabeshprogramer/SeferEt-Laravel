<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Service Request Notification Meta Tags -->
    <meta name="user-id" content="{{ auth()->id() }}">
    <meta name="user-role" content="{{ auth()->user()->role }}">
    @if(isset($package) && $package)
    <meta name="package-id" content="{{ $package->id }}">
    @endif
    
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'SeferEt') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
    <!-- OverlayScrollbars CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/styles/overlayscrollbars.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- SeferEt Custom CSS -->
    <style>
        :root {
            --seferet-primary: #2E8B57;
            --seferet-secondary: #FFD700;
            --seferet-accent: #87CEEB;
        }
        
        .main-sidebar {
            background: linear-gradient(180deg, var(--seferet-primary) 0%, #267348 100%) !important;
        }
        
        .btn-primary {
            background-color: var(--seferet-primary) !important;
            border-color: var(--seferet-primary) !important;
        }
        
        .btn-primary:hover {
            background-color: #267348 !important;
            border-color: #267348 !important;
        }
        
        .small-box.bg-primary {
            background: linear-gradient(45deg, var(--seferet-primary), #3ea76a) !important;
        }
        
        .nav-sidebar .nav-link.active {
            background-color: var(--seferet-accent) !important;
            color: var(--seferet-primary) !important;
        }
    </style>
    
    <!-- Additional CSS -->
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="@yield('home-url', '/')" class="nav-link">Home</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Messages Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <i class="far fa-comments"></i>
                        <span class="badge badge-danger navbar-badge">3</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <a href="#" class="dropdown-item">
                            <div class="media">
                                <div class="media-body">
                                    <h3 class="dropdown-item-title">
                                        Brad Diesel
                                        <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                                    </h3>
                                    <p class="text-sm">Call me whenever you can...</p>
                                    <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
                    </div>
                </li>
                
                <!-- Notifications Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-warning navbar-badge">15</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">15 Notifications</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-envelope mr-2"></i> 4 new messages
                            <span class="float-right text-muted text-sm">3 mins</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                    </div>
                </li>
                
                <!-- User Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                        <i class="far fa-user"></i>
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">{{ Auth::user()->name }}</span>
                        <div class="dropdown-divider"></div>
                        <a href="@yield('profile-url', '#')" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-cog mr-2"></i> Settings
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="@yield('logout-url', route('admin.logout'))" class="dropdown-item p-0">
                            @csrf
                            <button type="submit" class="btn btn-link dropdown-item text-left w-100">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="@yield('home-url', '/')" class="brand-link">
                <i class="fas fa-mosque brand-image img-circle elevation-3" style="opacity: .8; margin-left: 8px; margin-right: 8px;"></i>
                <span class="brand-text font-weight-light">SeferEt</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <i class="fas fa-user-circle text-white" style="font-size: 2.1rem;"></i>
                    </div>
                    <div class="info">
                        <a href="@yield('profile-url', '#')" class="d-block text-white">{{ Auth::user()->name }}</a>
                        <small class="text-white-50">{{ ucfirst(Auth::user()->role) }}</small>
                    </div>
                </div>

                <!-- SidebarSearch Form -->
                <div class="form-inline">
                    <div class="input-group" data-widget="sidebar-search">
                        <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-sidebar">
                                <i class="fas fa-search fa-fw"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        @yield('sidebar-menu')
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('page-title', 'Dashboard')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                @yield('breadcrumb')
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle mr-2"></i>
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <strong>Copyright &copy; {{ date('Y') }} <a href="{{ url('/') }}">SeferEt</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0.0
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE JS -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
    <!-- Select2 JS - Using RC version that works better -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>
    <!-- OverlayScrollbars JS -->
    <script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.3.0/browser/overlayscrollbars.browser.es6.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.5/dist/sweetalert2.all.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <!-- Laravel Echo and Broadcasting -->
    <script>
        // Pusher configuration
        window.PUSHER_APP_KEY = '{{ config("broadcasting.connections.pusher.key") }}';
        window.PUSHER_APP_CLUSTER = '{{ config("broadcasting.connections.pusher.options.cluster") }}';
        window.PUSHER_HOST = '{{ config("broadcasting.connections.pusher.options.host") }}';
        window.PUSHER_PORT = {{ config('broadcasting.connections.pusher.options.port', 80) }};
        window.PUSHER_WSS_PORT = {{ config('broadcasting.connections.pusher.options.encrypted', 443) }};
        window.PUSHER_SCHEME = '{{ config("broadcasting.connections.pusher.options.scheme", "https") }}';
    </script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://unpkg.com/laravel-echo@1.15.0/dist/echo.iife.js"></script>
    <script src="{{ asset('js/echo-init.js') }}"></script>
    
    <!-- Service Request Management -->
    <script src="{{ asset('js/service-request-helpers.js') }}"></script>
    <script src="{{ asset('js/service-request-manager.js') }}"></script>
    <script src="{{ asset('js/service-request-notifications.js') }}"></script>
    
    <!-- SeferEt Custom JavaScript -->
    <script>
        // Global Select2 initialization function
        window.initializeSelect2 = function(selector = '.select2') {
            // Check if Select2 is loaded
            if (typeof $.fn.select2 === 'undefined') {
                console.error('Select2 is not loaded');
                return;
            }
            
            $(selector).each(function() {
                // Skip if already initialized
                if ($(this).hasClass('select2-hidden-accessible')) {
                    return;
                }
                
                $(this).select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: $(this).data('placeholder') || $(this).attr('placeholder') || 'Select an option',
                    allowClear: $(this).data('allow-clear') !== false
                });
            });
        };
        
        // Wait for all scripts to load before initializing
        $(document).ready(function() {
            // Delay initialization slightly to ensure Select2 is fully loaded
            setTimeout(function() {
                console.log('Initializing Select2 globally...');
                console.log('Select2 available:', typeof $.fn.select2 !== 'undefined');
                
                // Initialize Select2 for existing elements
                window.initializeSelect2();
            }, 200); // 200ms delay to ensure Select2 is loaded
            
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Configure Toastr
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };
            
            // Initialize Charts
            const revenueChartCanvas = document.getElementById('revenueChart');
            if (revenueChartCanvas) {
                new Chart(revenueChartCanvas, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Revenue',
                            data: [12000, 19000, 15000, 25000, 22000, 30000],
                            backgroundColor: 'rgba(46, 139, 87, 0.1)',
                            borderColor: '#2E8B57',
                            borderWidth: 2,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
            
            const bookingsChartCanvas = document.getElementById('bookingsChart');
            if (bookingsChartCanvas) {
                new Chart(bookingsChartCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Confirmed', 'Pending', 'Cancelled'],
                        datasets: [{
                            data: [55, 30, 15],
                            backgroundColor: ['#2E8B57', '#FFD700', '#FF6B6B']
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
            

        });
    </script>
    
    <!-- Additional JavaScript -->
    @stack('scripts')
    
    <!-- Page-specific JavaScript -->
    @yield('scripts')
</body>
</html>

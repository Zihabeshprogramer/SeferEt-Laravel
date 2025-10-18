@extends('layouts.adminlte')

@section('home-url', route('admin.dashboard'))
@section('profile-url', route('admin.profile', ['id' => Auth::id()]))
@section('logout-url', route('admin.logout'))

@section('sidebar-menu')
    <!-- Dashboard -->
    <li class="nav-item">
        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>

    <!-- Section: User Management -->
    <li class="nav-header">ADMIN USER MANAGEMENT</li>
    
    <!-- Admin User Moderation -->
    <li class="nav-item">
        <a href="{{ route('admin.users.moderation') }}" class="nav-link {{ request()->routeIs('admin.users.moderation') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-shield"></i>
            <p>Admin Users</p>
        </a>
    </li>
    
    <!-- Create Admin User (Permission Based) -->
    @if(auth()->user()->getPermissionsViaRoles()->where('name', 'create admin users')->isNotEmpty())
    <li class="nav-item">
        <a href="{{ route('admin.users.create-admin') }}" class="nav-link {{ request()->routeIs('admin.users.create-admin') ? 'active' : '' }}">
            <i class="nav-icon fas fa-user-plus"></i>
            <p>Create Admin User</p>
        </a>
    </li>
    @endif
    

    <!-- Section: Business Management -->
    <li class="nav-header">BUSINESS MANAGEMENT</li>
    
    <!-- Partner Management (Comprehensive) -->
    <li class="nav-item {{ request()->routeIs('admin.partners*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->routeIs('admin.partners*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-handshake"></i>
            <p>
                Partner Management
                <i class="fas fa-angle-left right"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('admin.partners.management') }}" class="nav-link {{ request()->routeIs('admin.partners.management') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>All Partners</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.partners.business-overview') }}" class="nav-link {{ request()->routeIs('admin.partners.business-overview') ? 'active' : '' }}">
                    <i class="far fa-chart-bar nav-icon"></i>
                    <p>Business Overview</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.partners.management') }}?approval_status=pending" class="nav-link">
                    <i class="far fa-clock nav-icon"></i>
                    <p>Pending Approval</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.partners.hotel-services') }}" class="nav-link {{ request()->routeIs('admin.partners.hotel-services*') ? 'active' : '' }}">
                    <i class="fas fa-hotel nav-icon"></i>
                    <p>Hotel Services Review</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.partners.export') }}" class="nav-link">
                    <i class="far fa-file-excel nav-icon"></i>
                    <p>Export Data</p>
                </a>
            </li>
        </ul>
    </li>


    <!-- Package Management -->
    <li class="nav-item">
        <a href="{{ route('admin.packages') }}" class="nav-link {{ request()->routeIs('admin.packages*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-box"></i>
            <p>Packages</p>
        </a>
    </li>

    <!-- Booking Management -->
    <li class="nav-item">
        <a href="{{ route('admin.bookings') }}" class="nav-link {{ request()->routeIs('admin.bookings*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-calendar-check"></i>
            <p>Bookings</p>
        </a>
    </li>

    <!-- Section: Analytics & Reports -->
    <li class="nav-header">ANALYTICS & REPORTS</li>
    
    <!-- Analytics Dashboard -->
    <li class="nav-item">
        <a href="{{ route('admin.analytics') }}" class="nav-link {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-chart-line"></i>
            <p>Analytics</p>
        </a>
    </li>

    <!-- Reports Submenu -->
    <li class="nav-item {{ request()->routeIs('admin.reports*') ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>
                Reports
                <i class="fas fa-angle-left right"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Revenue Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Booking Report</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="far fa-circle nav-icon"></i>
                    <p>User Report</p>
                </a>
            </li>
        </ul>
    </li>

    <!-- Section: System -->
    <li class="nav-header">SYSTEM</li>
    
    <!-- Settings -->
    <li class="nav-item">
        <a href="{{ route('admin.settings') }}" class="nav-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-cog"></i>
            <p>Settings</p>
        </a>
    </li>

    <!-- System Tools -->
    <li class="nav-item">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-tools"></i>
            <p>System Tools</p>
        </a>
    </li>
    
    <!-- System Logs -->
    <li class="nav-item">
        <a href="#" class="nav-link">
            <i class="nav-icon fas fa-file-medical-alt"></i>
            <p>System Logs</p>
        </a>
    </li>
@endsection

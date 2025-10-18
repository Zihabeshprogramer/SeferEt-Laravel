{{-- Customer Navigation Bar - Flutter Design Match --}}
<nav class="navbar navbar-expand-lg seferet-navbar">
    <div class="container-fluid">
        {{-- Brand/Logo --}}
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="fas fa-mosque"></i>
            <span>SeferEt</span>
        </a>
        
        {{-- Mobile Toggle Button --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#customerNavbar" aria-controls="customerNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        
        {{-- Navigation Menu --}}
        <div class="collapse navbar-collapse" id="customerNavbar">
            {{-- Left Navigation --}}
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ route('home') }}">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('packages*') ? 'active' : '' }}" href="{{ route('packages') }}">
                        <i class="fas fa-box"></i>
                        <span>Packages</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('explore*') ? 'active' : '' }}" href="{{ route('explore') }}">
                        <i class="fas fa-compass"></i>
                        <span>Explore</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('about*') ? 'active' : '' }}" href="{{ route('about') }}">
                        <i class="fas fa-info-circle"></i>
                        <span>About</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('contact*') ? 'active' : '' }}" href="{{ route('contact') }}">
                        <i class="fas fa-envelope"></i>
                        <span>Contact</span>
                    </a>
                </li>
            </ul>
            
            {{-- Right Navigation --}}
            <ul class="navbar-nav ms-auto">
                @guest
                    {{-- Login Button --}}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('customer.login') }}">
                            <i class="fas fa-sign-in-alt"></i>
                            <span>Login</span>
                        </a>
                    </li>
                    {{-- Join Now Button --}}
                    <li class="nav-item">
                        <a class="btn btn-secondary ms-2" href="{{ route('customer.register') }}">
                            <i class="fas fa-user-plus"></i>
                            <span>Join Now</span>
                        </a>
                    </li>
                @else
                    {{-- Notifications --}}
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="#">
                            <i class="fas fa-bell"></i>
                        </a>
                    </li>
                    
                    {{-- Favorites --}}
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-heart"></i>
                        </a>
                    </li>
                    
                    {{-- Profile Dropdown --}}
                    <li class="nav-item dropdown profile-dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{-- Profile Avatar --}}
                            <div class="profile-avatar me-2">
                                <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            {{-- Dashboard --}}
                            <li>
                                <a class="dropdown-item" href="{{ route('customer.dashboard') }}">
                                    <i class="fas fa-tachometer-alt"></i>
                                    <span>My Dashboard</span>
                                </a>
                            </li>
                            
                            {{-- My Bookings --}}
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-calendar-check"></i>
                                    <span>My Bookings</span>
                                </a>
                            </li>
                            
                            {{-- Profile Settings --}}
                            <li>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user-edit"></i>
                                    <span>Profile Settings</span>
                                </a>
                            </li>
                            
                            <li><hr class="dropdown-divider"></li>
                            
                            {{-- Logout --}}
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('customer.logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <span>Sign Out</span>
                                </a>
                            </li>
                        </ul>
                        
                        {{-- Hidden Logout Form --}}
                        <form id="logout-form" action="{{ route('customer.logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                @endguest
                
                {{-- Business Access Dropdown --}}
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="businessDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-briefcase"></i>
                        <span class="d-none d-lg-inline">Business</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">B2B Partners</li>
                        <li>
                            <a class="dropdown-item" href="{{ route('b2b.login') }}">
                                <i class="fas fa-handshake"></i>
                                <span>B2B Login</span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('b2b.register') }}">
                                <i class="fas fa-building"></i>
                                <span>Become Partner</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="dropdown-header">Administration</li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.login') }}">
                                <i class="fas fa-shield-alt"></i>
                                <span>Admin Access</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

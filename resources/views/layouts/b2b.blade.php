@extends('layouts.adminlte')

@section('home-url', route('b2b.dashboard'))
@section('profile-url', route('b2b.profile'))
@section('logout-url', route('b2b.logout'))

@section('page-title')
    @yield('content_header')
@endsection

@section('css')
    @stack('css')
    @yield('css')
@endsection

@section('scripts')
    @stack('js')
    @yield('js')
@endsection

@section('sidebar-menu')
    <!-- Dashboard -->
    <li class="nav-item">
        <a href="{{ route('b2b.dashboard') }}" class="nav-link {{ request()->routeIs('b2b.dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>

    {{-- TRAVEL AGENT MENU --}}
    @role('travel_agent')
        <!-- Travel Agent Dashboard -->
        <li class="nav-item">
            <a href="{{ route('b2b.travel-agent.dashboard') }}" class="nav-link {{ request()->routeIs('b2b.travel-agent.dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-plane"></i>
                <p>Agent Dashboard</p>
            </a>
        </li>

        <!-- Package Management -->
        <li class="nav-item {{ request()->routeIs('b2b.travel-agent.packages*') || request()->routeIs('b2b.travel-agent.drafts*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('b2b.travel-agent.packages*') || request()->routeIs('b2b.travel-agent.drafts*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-box"></i>
                <p>
                    Package Management
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('b2b.travel-agent.packages.index') }}" class="nav-link {{ request()->routeIs('b2b.travel-agent.packages.index') ? 'active' : '' }}">
                        <i class="far fa-list-alt nav-icon"></i>
                        <p>All Packages</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('b2b.travel-agent.packages.create') }}" class="nav-link {{ request()->routeIs('b2b.travel-agent.packages.create') ? 'active' : '' }}">
                        <i class="far fa-plus-square nav-icon"></i>
                        <p>Create Package</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('b2b.travel-agent.drafts') }}" class="nav-link {{ request()->routeIs('b2b.travel-agent.drafts*') ? 'active' : '' }}">
                        <i class="far fa-edit nav-icon"></i>
                        <p>Draft Packages</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Flight Management -->
        <li class="nav-item {{ request()->routeIs('b2b.travel-agent.flights*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('b2b.travel-agent.flights*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-plane"></i>
                <p>
                    Flight Management
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('b2b.travel-agent.flights.index') }}" class="nav-link {{ request()->routeIs('b2b.travel-agent.flights.index') ? 'active' : '' }}">
                        <i class="fas fa-list nav-icon"></i>
                        <p>All Flights</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('b2b.travel-agent.flights.create') }}" class="nav-link {{ request()->routeIs('b2b.travel-agent.flights.create') ? 'active' : '' }}">
                        <i class="far fa-plus-square nav-icon"></i>
                        <p>Add Flight</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Service Requests -->
        <li class="nav-item">
            <a href="{{ route('b2b.travel-agent.requests') }}" class="nav-link {{ request()->routeIs('b2b.travel-agent.requests*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-paper-plane"></i>
                <p>Service Requests</p>
                @php
                    try {
                        // Count service requests created by this travel agent
                        $pendingServiceRequests = \App\Models\ServiceRequest::where('agent_id', auth()->id())
                            ->where('status', \App\Models\ServiceRequest::STATUS_PENDING)
                            ->count();
                    } catch (\Exception $e) {
                        $pendingServiceRequests = 0;
                    }
                @endphp
                @if($pendingServiceRequests > 0)
                    <span class="badge badge-info right">{{ $pendingServiceRequests }}</span>
                @endif
            </a>
        </li>

        <!-- Bookings Management -->
        <li class="nav-item {{ request()->routeIs('b2b.travel-agent.bookings*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('b2b.travel-agent.bookings*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-calendar-check"></i>
                <p>
                    Booking Management
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('b2b.travel-agent.bookings.all') }}" class="nav-link {{ request()->routeIs('b2b.travel-agent.bookings.all') ? 'active' : '' }}">
                        <i class="fas fa-bookmark nav-icon"></i>
                        <p>All Bookings</p>
                        @php
                            try {
                                // Count total active bookings across all types
                                $totalActiveBookings = 0;
                                
                                // Package bookings
                                $packageBookings = \App\Models\PackageBooking::where('agent_id', auth()->id())
                                    ->whereNotIn('status', ['cancelled', 'completed'])
                                    ->count();
                                
                                // Hotel bookings (via service requests)
                                $hotelBookings = \App\Models\HotelBooking::whereHas('serviceRequest', function($q) {
                                    $q->where('agent_id', auth()->id());
                                })->whereNotIn('status', ['cancelled', 'completed'])->count();
                                
                                // Flight bookings (via service requests)
                                $flightBookings = \App\Models\FlightBooking::whereHas('serviceRequest', function($q) {
                                    $q->where('agent_id', auth()->id());
                                })->whereNotIn('status', ['cancelled', 'completed'])->count();
                                
                                // Transport bookings (via service requests)
                                $transportBookings = \App\Models\TransportBooking::whereHas('serviceRequest', function($q) {
                                    $q->where('agent_id', auth()->id());
                                })->whereNotIn('status', ['cancelled', 'completed'])->count();
                                
                                $totalActiveBookings = $packageBookings + $hotelBookings + $flightBookings + $transportBookings;
                            } catch (\Exception $e) {
                                $totalActiveBookings = 0;
                            }
                        @endphp
                        @if($totalActiveBookings > 0)
                            <span class="badge badge-success right">{{ $totalActiveBookings }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('b2b.travel-agent.bookings') }}" class="nav-link {{ request()->routeIs('b2b.travel-agent.bookings') && !request()->routeIs('b2b.travel-agent.bookings.all') ? 'active' : '' }}">
                        <i class="far fa-list-alt nav-icon"></i>
                        <p>Package Bookings</p>
                        @php
                            try {
                                $pendingPackageBookings = \App\Models\PackageBooking::where('agent_id', auth()->id())
                                    ->where('status', 'pending')
                                    ->count();
                            } catch (\Exception $e) {
                                $pendingPackageBookings = 0;
                            }
                        @endphp
                        @if($pendingPackageBookings > 0)
                            <span class="badge badge-warning right">{{ $pendingPackageBookings }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </li>

        <!-- Customers -->
        <li class="nav-item">
            <a href="{{ route('b2b.travel-agent.customers') }}" class="nav-link {{ request()->routeIs('b2b.travel-agent.customers') ? 'active' : '' }}">
                <i class="nav-icon fas fa-users"></i>
                <p>Customers</p>
            </a>
        </li>

        <!-- Commissions -->
        <li class="nav-item">
            <a href="{{ route('b2b.travel-agent.commissions') }}" class="nav-link {{ request()->routeIs('b2b.travel-agent.commissions') ? 'active' : '' }}">
                <i class="nav-icon fas fa-percentage"></i>
                <p>Commissions</p>
            </a>
        </li>

        <!-- Reports -->
        <li class="nav-item">
            <a href="{{ route('b2b.travel-agent.reports') }}" class="nav-link {{ request()->routeIs('b2b.travel-agent.reports') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-line"></i>
                <p>Reports</p>
            </a>
        </li>
    @endrole

    {{-- HOTEL PROVIDER MENU --}}
    @role('hotel_provider')
        <!-- My Hotels -->
        <li class="nav-item {{ request()->routeIs('b2b.hotel-provider.hotels*') ? 'menu-open' : '' }}{{ request()->routeIs('b2b.hotel-provider.rooms*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('b2b.hotel-provider.hotels*') ? 'active' : '' }}{{ request()->routeIs('b2b.hotel-provider.rooms*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-hotel"></i>
                <p>
                    My Hotels
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('b2b.hotel-provider.hotels.index') }}" class="nav-link {{ request()->routeIs('b2b.hotel-provider.hotels.index') ? 'active' : '' }}">
                        <i class="far fa-building nav-icon"></i>
                        <p>List of Hotels</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('b2b.hotel-provider.hotels.create') }}" class="nav-link {{ request()->routeIs('b2b.hotel-provider.hotels.create') ? 'active' : '' }}">
                        <i class="far fa-plus-square nav-icon"></i>
                        <p>Add New Hotel</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('b2b.hotel-provider.rooms.index') }}" class="nav-link {{ request()->routeIs('b2b.hotel-provider.rooms.*') ? 'active' : '' }}">
                        <i class="fas fa-list-check nav-icon"></i>
                        <p>Room Management</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Requests -->
        <li class="nav-item">
            <a href="{{ route('b2b.hotel-provider.requests') }}" class="nav-link {{ request()->routeIs('b2b.hotel-provider.requests*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-inbox"></i>
                <p>Requests</p>
                @php
                    try {
                        $pendingRequests = \App\Models\ServiceRequest::where('provider_id', auth()->id())
                            ->where('status', \App\Models\ServiceRequest::STATUS_PENDING)
                            ->count();
                    } catch (\Exception $e) {
                        $pendingRequests = 0;
                    }
                @endphp
                @if($pendingRequests > 0)
                    <span class="badge badge-warning right">{{ $pendingRequests }}</span>
                @endif
            </a>
        </li>

        <!-- Bookings -->
        <li class="nav-item">
            <a href="{{ route('b2b.hotel-provider.bookings.index') }}" class="nav-link {{ request()->routeIs('b2b.hotel-provider.bookings*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-calendar-check"></i>
                <p>Bookings</p>
            </a>
        </li>

        <!-- Rates -->
        <li class="nav-item">
            <a href="{{ route('b2b.hotel-provider.rates') }}" class="nav-link {{ request()->routeIs('b2b.hotel-provider.rates') ? 'active' : '' }}">
                <i class="nav-icon fas fa-dollar-sign"></i>
                <p>Rates & Pricing</p>
            </a>
        </li>

        <!-- Availability -->
        <li class="nav-item">
            <a href="{{ route('b2b.hotel-provider.availability') }}" class="nav-link {{ request()->routeIs('b2b.hotel-provider.availability') ? 'active' : '' }}">
                <i class="nav-icon fas fa-calendar-alt"></i>
                <p>Availability</p>
            </a>
        </li>

        <!-- Reports -->
        <li class="nav-item">
            <a href="{{ route('b2b.hotel-provider.reports') }}" class="nav-link {{ request()->routeIs('b2b.hotel-provider.reports') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-bar"></i>
                <p>Reports</p>
            </a>
        </li>
    @endrole

    {{-- TRANSPORT PROVIDER MENU --}}
    @role('transport_provider')
        <!-- Dashboard -->
        <li class="nav-item">
            <a href="{{ route('b2b.transport-provider.dashboard') }}" class="nav-link {{ request()->routeIs('b2b.transport-provider.dashboard') ? 'active' : '' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>Provider Dashboard</p>
            </a>
        </li>

        <!-- Services Management -->
        <li class="nav-item {{ request()->routeIs('b2b.transport-provider.services*') || request()->routeIs('b2b.transport-provider.create') || request()->routeIs('b2b.transport-provider.show') || request()->routeIs('b2b.transport-provider.edit') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('b2b.transport-provider.services*') || request()->routeIs('b2b.transport-provider.create') || request()->routeIs('b2b.transport-provider.show') || request()->routeIs('b2b.transport-provider.edit') ? 'active' : '' }}">
                <i class="nav-icon fas fa-bus"></i>
                <p>
                    Services
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('b2b.transport-provider.services.index') }}" class="nav-link {{ request()->routeIs('b2b.transport-provider.services.index') ? 'active' : '' }}">
                        <i class="fas fa-list nav-icon"></i>
                        <p>All Services</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('b2b.transport-provider.create') }}" class="nav-link {{ request()->routeIs('b2b.transport-provider.create') ? 'active' : '' }}">
                        <i class="far fa-plus-square nav-icon"></i>
                        <p>Add New Service</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Rates & Pricing -->
        <li class="nav-item {{ request()->routeIs('b2b.transport-provider.rates*') || request()->routeIs('b2b.transport-provider.pricing-rules*') || request()->routeIs('b2b.transport-provider.transport-rates*') || request()->routeIs('b2b.transport-provider.transport-pricing-rules*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('b2b.transport-provider.rates*') || request()->routeIs('b2b.transport-provider.pricing-rules*') || request()->routeIs('b2b.transport-provider.transport-rates*') || request()->routeIs('b2b.transport-provider.transport-pricing-rules*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-dollar-sign"></i>
                <p>
                    Rates & Pricing
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('b2b.transport-provider.rates') }}" class="nav-link {{ request()->routeIs('b2b.transport-provider.rates') || request()->routeIs('b2b.transport-provider.transport-rates*') ? 'active' : '' }}">
                        <i class="fas fa-tags nav-icon"></i>
                        <p>Rate Management</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('b2b.transport-provider.pricing-rules.index') }}" class="nav-link {{ request()->routeIs('b2b.transport-provider.pricing-rules*') || request()->routeIs('b2b.transport-provider.transport-pricing-rules*') ? 'active' : '' }}">
                        <i class="fas fa-magic nav-icon"></i>
                        <p>Pricing Rules</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Fleet Management -->
        <li class="nav-item {{ request()->routeIs('b2b.transport-provider.fleet*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('b2b.transport-provider.fleet*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-truck"></i>
                <p>
                    Fleet Management
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('b2b.transport-provider.fleet.vehicles') }}" class="nav-link {{ request()->routeIs('b2b.transport-provider.fleet.vehicles') ? 'active' : '' }}">
                        <i class="fas fa-car nav-icon"></i>
                        <p>Vehicles</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('b2b.transport-provider.fleet.drivers') }}" class="nav-link {{ request()->routeIs('b2b.transport-provider.fleet.drivers') ? 'active' : '' }}">
                        <i class="fas fa-id-card nav-icon"></i>
                        <p>Drivers</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('b2b.transport-provider.fleet.maintenance') }}" class="nav-link {{ request()->routeIs('b2b.transport-provider.fleet.maintenance') ? 'active' : '' }}">
                        <i class="fas fa-wrench nav-icon"></i>
                        <p>Maintenance</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Requests -->
        <li class="nav-item">
            <a href="{{ route('b2b.transport-provider.requests') }}" class="nav-link {{ request()->routeIs('b2b.transport-provider.requests*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-inbox"></i>
                <p>Requests</p>
                @php
                    try {
                        $pendingRequests = \App\Models\ServiceRequest::where('provider_id', auth()->id())
                            ->where('status', \App\Models\ServiceRequest::STATUS_PENDING)
                            ->count();
                    } catch (\Exception $e) {
                        $pendingRequests = 0;
                    }
                @endphp
                @if($pendingRequests > 0)
                    <span class="badge badge-warning right">{{ $pendingRequests }}</span>
                @endif
            </a>
        </li>

        <!-- Operations -->
        <li class="nav-item {{ request()->routeIs('b2b.transport-provider.operations*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ request()->routeIs('b2b.transport-provider.operations*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-clipboard-list"></i>
                <p>
                    Operations
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                <li class="nav-item">
                    <a href="{{ route('b2b.transport-provider.operations.bookings') }}" class="nav-link {{ request()->routeIs('b2b.transport-provider.operations.bookings') || request()->routeIs('b2b.transport-provider.bookings') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check nav-icon"></i>
                        <p>Bookings</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('b2b.transport-provider.operations.routes') }}" class="nav-link {{ request()->routeIs('b2b.transport-provider.operations.routes') ? 'active' : '' }}">
                        <i class="fas fa-route nav-icon"></i>
                        <p>Route Planning</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Reports -->
        <li class="nav-item">
            <a href="{{ route('b2b.transport-provider.reports.index') }}" class="nav-link {{ request()->routeIs('b2b.transport-provider.reports*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-chart-pie"></i>
                <p>Reports & Analytics</p>
            </a>
        </li>
    @endrole

    {{-- COMMON SECTIONS FOR ALL PARTNERS --}}
    <!-- Notifications -->
    <li class="nav-item">
        <a href="{{ route('b2b.notifications') }}" class="nav-link {{ request()->routeIs('b2b.notifications*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-bell"></i>
            <p>
                Notifications
                @if(isset($unreadNotifications) && $unreadNotifications > 0)
                    <span class="badge badge-danger right">{{ $unreadNotifications }}</span>
                @endif
            </p>
        </a>
    </li>

    <!-- Customer Support -->
    <li class="nav-item">
        <a href="{{ route('b2b.help') }}" class="nav-link {{ request()->routeIs('b2b.help*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-headset"></i>
            <p>Customer Support</p>
        </a>
    </li>

    <!-- Divider -->
    <li class="nav-header">ACCOUNT</li>

    <!-- Profile -->
    <li class="nav-item">
        @role('transport_provider')
            <a href="{{ route('b2b.transport-provider.profile.index') }}" class="nav-link {{ request()->routeIs('b2b.transport-provider.profile*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-user"></i>
                <p>My Profile</p>
            </a>
        @else
            <a href="{{ route('b2b.profile') }}" class="nav-link {{ request()->routeIs('b2b.profile*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-user"></i>
                <p>My Profile</p>
            </a>
        @endrole
    </li>

    <!-- Settings -->
    <li class="nav-item">
        <a href="{{ route('b2b.settings') }}" class="nav-link {{ request()->routeIs('b2b.settings*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-cog"></i>
            <p>Settings</p>
        </a>
    </li>

    <!-- Help -->
    <li class="nav-item">
        <a href="{{ route('b2b.help') }}" class="nav-link {{ request()->routeIs('b2b.help*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-question-circle"></i>
            <p>Help & Support</p>
        </a>
    </li>
@endsection

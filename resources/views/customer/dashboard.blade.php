@extends('layouts.customer')

@section('title', 'My Dashboard')

@section('content')
    <!-- Hero Welcome Section -->
    <div class="customer-hero-section mb-4">
        <div class="container-fluid">
            <x-customer.card variant="elevated" elevation="lg" padding="lg" class="hero-welcome-card">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="welcome-content">
                            <h1 class="hero-title mb-2">
                                <i class="fas fa-star me-2 text-secondary"></i>
                                Assalamu Alaikum, {{ $customer->name }}!
                            </h1>
                            <p class="hero-subtitle mb-3">
                                Welcome to your spiritual journey dashboard. Plan your next Umrah with ease.
                            </p>
                            <div class="user-details">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-envelope me-2 text-primary"></i>
                                    <span>{{ $customer->email }}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock me-2 text-success"></i>
                                    <small>Last visit: {{ $customer->last_login_at ? $customer->last_login_at->format('M d, Y \a\t H:i') : 'Welcome to SeferEt!' }}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="hero-avatar">
                            @if($customer->avatar)
                                <img src="{{ $customer->avatar }}" alt="{{ $customer->name }}" class="user-avatar">
                            @else
                                <div class="avatar-placeholder">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                            @endif
                            <div class="mt-3">
                                <x-customer.button href="{{ route('customer.profile') }}" variant="outline-primary" size="sm">
                                    <i class="fas fa-edit me-1"></i> Edit Profile
                                </x-customer.button>
                            </div>
                        </div>
                    </div>
                </div>
            </x-customer.card>
        </div>
    </div>

    <!-- Journey Stats -->
    <div class="stats-section mb-4">
        <div class="container-fluid">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" hover="true" clickable="true" href="{{ route('customer.bookings') }}" class="stat-card stat-primary">
                        <div class="stat-content">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-info">
                                <h3 class="stat-number">{{ $stats['totalBookings'] }}</h3>
                                <p class="stat-label">Total Bookings</p>
                                <small class="stat-link">
                                    <i class="fas fa-arrow-right me-1"></i>View Details
                                </small>
                            </div>
                        </div>
                    </x-customer.card>
                </div>

                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" hover="true" clickable="true" href="{{ route('customer.bookings') }}" class="stat-card stat-success">
                        <div class="stat-content">
                            <div class="stat-icon">
                                <i class="fas fa-plane-departure"></i>
                            </div>
                            <div class="stat-info">
                                <h3 class="stat-number">{{ $stats['upcomingTrips'] }}</h3>
                                <p class="stat-label">Upcoming Trips</p>
                                <small class="stat-link">
                                    <i class="fas fa-arrow-right me-1"></i>View Details
                                </small>
                            </div>
                        </div>
                    </x-customer.card>
                </div>

                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" hover="true" clickable="true" href="{{ route('customer.bookings') }}" class="stat-card stat-warning">
                        <div class="stat-content">
                            <div class="stat-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-info">
                                <h3 class="stat-number">{{ $stats['completedTrips'] }}</h3>
                                <p class="stat-label">Completed Trips</p>
                                <small class="stat-link">
                                    <i class="fas fa-arrow-right me-1"></i>View Details
                                </small>
                            </div>
                        </div>
                    </x-customer.card>
                </div>

                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" hover="true" clickable="true" href="{{ route('customer.bookings') }}" class="stat-card stat-secondary">
                        <div class="stat-content">
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-info">
                                <h3 class="stat-number">${{ number_format($stats['totalSpent']) }}</h3>
                                <p class="stat-label">Total Investment</p>
                                <small class="stat-link">
                                    <i class="fas fa-arrow-right me-1"></i>View Details
                                </small>
                            </div>
                        </div>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Content -->
    <div class="main-content-section">
        <div class="container-fluid">
            <div class="row g-4">
                <!-- Featured Umrah Packages -->
                <div class="col-xl-8 col-lg-7">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="packages-section">
                        <x-slot name="header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1">
                                        <i class="fas fa-mosque me-2 text-primary"></i>
                                        Featured Umrah Packages
                                    </h3>
                                    <p class="text-muted mb-0">Discover spiritual journeys crafted just for you</p>
                                </div>
                                <x-customer.button href="{{ route('packages') }}" variant="outline-primary" size="sm">
                                    <i class="fas fa-eye me-1"></i> View All
                                </x-customer.button>
                            </div>
                        </x-slot>

                        @if(count($availablePackages) > 0)
                            <div class="packages-grid">
                                @foreach($availablePackages as $package)
                                <div class="package-item mb-4">
                                    <x-customer.card variant="outlined" elevation="sm" hover="true" class="package-card">
                                        <div class="package-content">
                                            <div class="package-header mb-3">
                                                <h5 class="package-title">{{ $package['name'] }}</h5>
                                                <div class="package-badges">
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-calendar me-1"></i>{{ $package['duration'] }} Days
                                                    </span>
                                                    @if(isset($package['featured']) && $package['featured'])
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-star me-1"></i>Featured
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <p class="package-description">{{ Str::limit($package['description'], 120) }}</p>
                                            
                                            <div class="package-price-section mb-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="price-info">
                                                        <span class="price-label">Starting from</span>
                                                        <div class="price-amount">
                                                            <h4 class="text-primary mb-0">${{ number_format($package['price']) }}</h4>
                                                            <small class="text-muted">per person</small>
                                                        </div>
                                                    </div>
                                                    @if(isset($package['rating']))
                                                        <div class="package-rating">
                                                            <div class="stars mb-1">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    <i class="fas fa-star {{ $i <= $package['rating'] ? 'text-warning' : 'text-muted' }}"></i>
                                                                @endfor
                                                            </div>
                                                            <small class="text-muted">{{ $package['rating'] }}/5</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="package-actions">
                                                <x-customer.button variant="primary" size="sm" fullWidth="true" class="mb-2">
                                                    <i class="fas fa-shopping-cart me-1"></i>Book This Journey
                                                </x-customer.button>
                                                <div class="d-flex gap-2">
                                                    <x-customer.button variant="outline-secondary" size="sm" class="flex-fill">
                                                        <i class="fas fa-info-circle me-1"></i>Details
                                                    </x-customer.button>
                                                    <x-customer.button variant="outline-secondary" size="sm" class="flex-fill">
                                                        <i class="fas fa-heart me-1"></i>Save
                                                    </x-customer.button>
                                                </div>
                                            </div>
                                        </div>
                                    </x-customer.card>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state text-center py-5">
                                <div class="empty-icon mb-3">
                                    <i class="fas fa-mosque fa-4x text-muted opacity-50"></i>
                                </div>
                                <h5 class="text-muted mb-2">No Packages Available</h5>
                                <p class="text-muted mb-4">Our partners are preparing amazing Umrah packages for you.<br>Check back soon for spiritual journey opportunities.</p>
                                <x-customer.button href="{{ route('packages') }}" variant="primary">
                                    <i class="fas fa-search me-2"></i>Explore Packages
                                </x-customer.button>
                            </div>
                        @endif
                    </x-customer.card>
                </div>

                <!-- Quick Actions & Profile Sidebar -->
                <div class="col-xl-4 col-lg-5">
                    <div class="sidebar-content">
                        <!-- Quick Actions -->
                        <x-customer.card variant="elevated" elevation="md" padding="lg" class="quick-actions-card mb-4">
                            <x-slot name="header">
                                <h4 class="mb-0">
                                    <i class="fas fa-bolt me-2 text-secondary"></i>
                                    Quick Actions
                                </h4>
                            </x-slot>

                            <div class="actions-grid">
                                <x-customer.button href="{{ route('packages') }}" variant="success" size="md" fullWidth="true" class="mb-3" elevation="true">
                                    <i class="fas fa-search me-2"></i>Browse Packages
                                </x-customer.button>
                                
                                <x-customer.button href="{{ route('customer.bookings') }}" variant="primary" size="md" fullWidth="true" class="mb-3" elevation="true">
                                    <i class="fas fa-calendar-alt me-2"></i>My Bookings
                                </x-customer.button>
                                
                                <x-customer.button href="{{ route('customer.profile') }}" variant="warning" size="md" fullWidth="true" class="mb-3" elevation="true">
                                    <i class="fas fa-user-edit me-2"></i>Update Profile
                                </x-customer.button>
                                
                                <x-customer.button href="#" variant="outline-secondary" size="md" fullWidth="true" elevation="true">
                                    <i class="fas fa-headset me-2"></i>Contact Support
                                </x-customer.button>
                            </div>
                        </x-customer.card>

                        <!-- Profile Summary -->
                        <x-customer.card variant="elevated" elevation="md" padding="lg" class="profile-summary-card">
                            <x-slot name="header">
                                <h4 class="mb-0">
                                    <i class="fas fa-user-circle me-2 text-primary"></i>
                                    Profile Overview
                                </h4>
                            </x-slot>

                            <div class="profile-info">
                                <div class="profile-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="profile-icon success me-3">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="profile-details flex-grow-1">
                                            <span class="profile-label">Full Name</span>
                                            <div class="profile-value">{{ $customer->name }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="profile-icon primary me-3">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                        <div class="profile-details flex-grow-1">
                                            <span class="profile-label">Phone Number</span>
                                            <div class="profile-value">{{ $customer->phone ?: 'Not provided' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="profile-icon warning me-3">
                                            <i class="fas fa-globe"></i>
                                        </div>
                                        <div class="profile-details flex-grow-1">
                                            <span class="profile-label">Nationality</span>
                                            <div class="profile-value">{{ $customer->nationality ?: 'Not specified' }}</div>
                                        </div>
                                    </div>
                                </div>

                                @if($customer->has_umrah_experience)
                                <div class="profile-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="profile-icon success me-3">
                                            <i class="fas fa-mosque"></i>
                                        </div>
                                        <div class="profile-details flex-grow-1">
                                            <span class="profile-label">Umrah Experience</span>
                                            <div class="profile-value">{{ $customer->completed_umrah_count ?: 0 }} previous journeys</div>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <div class="profile-actions mt-4">
                                    <x-customer.button href="{{ route('customer.profile') }}" variant="outline-primary" size="sm" fullWidth="true">
                                        <i class="fas fa-edit me-1"></i> Complete Profile
                                    </x-customer.button>
                                </div>
                            </div>
                        </x-customer.card>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    @if(count($recentBookings) > 0)
    <div class="recent-bookings-section mt-4">
        <div class="container-fluid">
            <x-customer.card variant="elevated" elevation="md" padding="lg" class="bookings-history">
                <x-slot name="header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">
                            <i class="fas fa-history me-2 text-primary"></i>
                            Recent Journey History
                        </h3>
                        <x-customer.button href="{{ route('customer.bookings') }}" variant="outline-primary" size="sm">
                            <i class="fas fa-list me-1"></i> View All
                        </x-customer.button>
                    </div>
                </x-slot>

                <div class="bookings-list">
                    @foreach($recentBookings as $booking)
                    <div class="booking-item">
                        <x-customer.card variant="outlined" elevation="none" class="booking-card mb-3">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="booking-package">
                                        <h6 class="mb-1">{{ $booking['package_name'] }}</h6>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>{{ $booking['booking_date'] }}
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="booking-travel-date">
                                        <span class="label">Travel Date</span>
                                        <div class="date-value">{{ $booking['travel_date'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="booking-status">
                                        <span class="badge bg-{{ $booking['status_color'] }}">
                                            {{ $booking['status'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="booking-amount">
                                        <span class="label">Amount</span>
                                        <div class="amount-value text-success fw-bold">${{ number_format($booking['amount']) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <x-customer.button variant="outline-primary" size="sm">
                                        <i class="fas fa-eye"></i>
                                    </x-customer.button>
                                </div>
                            </div>
                        </x-customer.card>
                    </div>
                    @endforeach
                </div>
            </x-customer.card>
        </div>
    </div>
    @endif
@endsection

@push('styles')
<style>
/* Custom Dashboard Styles - Flutter Design Match */
.customer-hero-section {
    background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.05) 0%, rgba(var(--secondary-rgb), 0.05) 100%);
    padding: var(--spacing-lg) 0;
    margin: -var(--spacing-md) -15px var(--spacing-lg);
    border-radius: 0 0 var(--border-radius-lg) var(--border-radius-lg);
}

.hero-welcome-card {
    background: linear-gradient(135deg, var(--primary-color) 0%, #1f5d3e 100%);
    color: var(--text-title-color);
    border: none;
}

.hero-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-title-color);
}

.hero-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    color: var(--text-title-color);
}

.user-details {
    opacity: 0.8;
}

.hero-avatar .user-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.avatar-placeholder {
    font-size: 4rem;
    color: rgba(255, 255, 255, 0.6);
}

/* Stats Cards */
.stat-card {
    transition: all 0.3s ease;
}

.stat-content {
    display: flex;
    align-items: center;
    padding: var(--spacing-md);
}

.stat-icon {
    font-size: 2.5rem;
    width: 60px;
    text-align: center;
    margin-right: var(--spacing-md);
}

.stat-primary .stat-icon { color: var(--primary-color); }
.stat-success .stat-icon { color: var(--success-color); }
.stat-warning .stat-icon { color: var(--warning-color); }
.stat-secondary .stat-icon { color: var(--secondary-color); }

.stat-number {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    color: var(--text-color);
}

.stat-label {
    font-size: 0.9rem;
    color: var(--text-secondary-color);
    margin: 0;
}

.stat-link {
    color: var(--primary-color);
    font-weight: 500;
}

/* Package Cards */
.package-card {
    height: 100%;
    transition: all 0.3s ease;
}

.package-title {
    color: var(--text-color);
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.package-badges .badge {
    font-size: 0.75rem;
    margin-right: 0.5rem;
}

.package-description {
    color: var(--text-secondary-color);
    line-height: 1.5;
}

.price-label {
    font-size: 0.8rem;
    color: var(--text-secondary-color);
    text-transform: uppercase;
    font-weight: 600;
}

.stars .fa-star {
    font-size: 0.8rem;
}

/* Profile Items */
.profile-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.profile-icon.success { background: rgba(var(--success-rgb), 0.1); color: var(--success-color); }
.profile-icon.primary { background: rgba(var(--primary-rgb), 0.1); color: var(--primary-color); }
.profile-icon.warning { background: rgba(var(--warning-rgb), 0.1); color: var(--warning-color); }

.profile-label {
    font-size: 0.8rem;
    color: var(--text-secondary-color);
    text-transform: uppercase;
    font-weight: 600;
    letter-spacing: 0.5px;
}

.profile-value {
    font-size: 1rem;
    color: var(--text-color);
    font-weight: 500;
}

/* Recent Bookings */
.booking-card {
    transition: all 0.2s ease;
}

.booking-card:hover {
    transform: translateX(4px);
    box-shadow: var(--shadow-md);
}

.booking-item .label {
    font-size: 0.75rem;
    color: var(--text-secondary-color);
    text-transform: uppercase;
    font-weight: 600;
}

.date-value, .amount-value {
    font-weight: 600;
    color: var(--text-color);
}

/* Empty State */
.empty-state {
    background: var(--surface-variant-color);
    border-radius: var(--border-radius-lg);
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title {
        font-size: 1.5rem;
    }
    
    .stat-content {
        flex-direction: column;
        text-align: center;
    }
    
    .stat-icon {
        margin-right: 0;
        margin-bottom: var(--spacing-sm);
    }
    
    .booking-card .row {
        text-align: center;
    }
    
    .booking-card .col-md-2 {
        margin-bottom: var(--spacing-sm);
    }
}

@media (max-width: 576px) {
    .customer-hero-section {
        margin: -var(--spacing-sm) -15px var(--spacing-md);
        padding: var(--spacing-md) 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize any dashboard-specific functionality
    
    // Animate stats on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
    
    // Add click tracking for quick actions
    document.querySelectorAll('.actions-grid .seferet-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Add ripple effect or analytics tracking here
            console.log('Quick action clicked:', this.textContent.trim());
        });
    });
});
</script>
@endpush

@extends('layouts.customer')

@section('title', 'About SeferEt - Your Trusted Umrah Partner')

@section('content')
    <!-- About Header -->
    <div class="about-header bg-primary text-white py-5">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-3">
                        <i class="fas fa-info-circle me-3"></i>
                        About SeferEt
                    </h1>
                    <p class="lead opacity-90 mb-0">Your trusted partner in spiritual journeys, dedicated to making your Umrah experience meaningful and hassle-free</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-mosque fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Story Section -->
    <div class="our-story py-5">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <x-customer.card variant="elevated" elevation="md" padding="lg">
                        <h2 class="section-title mb-4">Our Story</h2>
                        <p class="lead text-muted mb-4">Founded in 2016, SeferEt was born from a simple yet profound vision: to make the sacred journey of Umrah accessible, meaningful, and transformative for every Muslim.</p>
                        
                        <p class="mb-4">Our founders, having experienced the challenges of organizing spiritual journeys firsthand, recognized the need for a platform that bridges the gap between pilgrims and quality service providers. What started as a small initiative has grown into a trusted network connecting thousands of pilgrims with carefully vetted partners across the globe.</p>
                        
                        <p class="mb-4">Today, SeferEt stands as more than just a booking platform. We are facilitators of spiritual transformation, custodians of sacred traditions, and companions on your journey toward spiritual fulfillment.</p>
                        
                        <div class="story-highlights">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="highlight-item">
                                        <i class="fas fa-calendar text-primary me-2"></i>
                                        <span><strong>Founded:</strong> 2016</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="highlight-item">
                                        <i class="fas fa-users text-success me-2"></i>
                                        <span><strong>Pilgrims Served:</strong> 50,000+</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="highlight-item">
                                        <i class="fas fa-globe text-info me-2"></i>
                                        <span><strong>Countries:</strong> 85+</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="highlight-item">
                                        <i class="fas fa-handshake text-warning me-2"></i>
                                        <span><strong>Partners:</strong> 200+</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </x-customer.card>
                </div>
                <div class="col-lg-6">
                    <div class="story-image">
                        <img src="https://images.unsplash.com/photo-1591604129939-f1efa4d9f7fa?w=600&h=400&fit=crop" 
                             alt="Kaaba at Masjid al-Haram" 
                             class="img-fluid rounded shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mission & Vision -->
    <div class="mission-vision py-5 bg-light">
        <div class="container-fluid">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Mission & Vision</h2>
                <p class="section-subtitle text-muted">Guiding principles that drive everything we do</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-6">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100 text-center">
                        <div class="mission-icon mb-4">
                            <div class="icon-circle bg-primary text-white mx-auto">
                                <i class="fas fa-bullseye fa-2x"></i>
                            </div>
                        </div>
                        <h3 class="h4 text-primary mb-3">Our Mission</h3>
                        <p class="text-muted mb-0">To democratize access to spiritual journeys by connecting pilgrims with trustworthy service providers, ensuring every Umrah experience is safe, meaningful, and transformative. We strive to remove barriers and complexities, making the sacred journey accessible to all Muslims regardless of their background or experience level.</p>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-6">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100 text-center">
                        <div class="vision-icon mb-4">
                            <div class="icon-circle bg-success text-white mx-auto">
                                <i class="fas fa-eye fa-2x"></i>
                            </div>
                        </div>
                        <h3 class="h4 text-success mb-3">Our Vision</h3>
                        <p class="text-muted mb-0">To become the world's most trusted platform for Islamic spiritual journeys, fostering a global community where every Muslim can embark on their spiritual path with confidence, dignity, and peace of mind. We envision a future where technology serves faith, bringing the Ummah closer together.</p>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Values -->
    <div class="our-values py-5">
        <div class="container-fluid">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Core Values</h2>
                <p class="section-subtitle text-muted">The Islamic principles that guide our every action</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100 text-center value-card">
                        <div class="value-icon mb-3">
                            <i class="fas fa-shield-alt fa-3x text-primary"></i>
                        </div>
                        <h5 class="value-title mb-3">Amanah (Trust)</h5>
                        <p class="text-muted small">We hold ourselves to the highest standards of trustworthiness, treating every pilgrim's journey as a sacred responsibility.</p>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100 text-center value-card">
                        <div class="value-icon mb-3">
                            <i class="fas fa-balance-scale fa-3x text-success"></i>
                        </div>
                        <h5 class="value-title mb-3">Adl (Justice)</h5>
                        <p class="text-muted small">We ensure fair treatment and transparent practices for all our customers and partners, maintaining equity in all our dealings.</p>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100 text-center value-card">
                        <div class="value-icon mb-3">
                            <i class="fas fa-heart fa-3x text-danger"></i>
                        </div>
                        <h5 class="value-title mb-3">Rahmah (Compassion)</h5>
                        <p class="text-muted small">We approach every interaction with empathy and kindness, understanding the deep spiritual significance of each journey.</p>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-3 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100 text-center value-card">
                        <div class="value-icon mb-3">
                            <i class="fas fa-star fa-3x text-warning"></i>
                        </div>
                        <h5 class="value-title mb-3">Ihsan (Excellence)</h5>
                        <p class="text-muted small">We strive for excellence in every aspect of our service, continuously improving to better serve the Ummah.</p>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>

    <!-- Why Choose Us -->
    <div class="why-choose-us py-5 bg-light">
        <div class="container-fluid">
            <div class="text-center mb-5">
                <h2 class="section-title">Why Choose SeferEt?</h2>
                <p class="section-subtitle text-muted">What makes us different in serving your spiritual journey</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-certificate fa-2x text-primary"></i>
                        </div>
                        <h5 class="feature-title mb-3">Verified Partners</h5>
                        <p class="text-muted">All our partners undergo rigorous verification processes to ensure they meet our high standards for service quality, Islamic compliance, and customer satisfaction.</p>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-4">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-clock fa-2x text-success"></i>
                        </div>
                        <h5 class="feature-title mb-3">24/7 Support</h5>
                        <p class="text-muted">Our dedicated support team is available around the clock to assist you before, during, and after your journey, ensuring peace of mind throughout your pilgrimage.</p>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-4">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-dollar-sign fa-2x text-warning"></i>
                        </div>
                        <h5 class="feature-title mb-3">Best Value Promise</h5>
                        <p class="text-muted">We guarantee competitive pricing without compromising on quality. Our transparent pricing model ensures you get the best value for your spiritual investment.</p>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-4">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-mobile-alt fa-2x text-info"></i>
                        </div>
                        <h5 class="feature-title mb-3">Easy Booking</h5>
                        <p class="text-muted">Our user-friendly platform makes booking your Umrah journey simple and intuitive. Compare options, read reviews, and book with confidence in just a few clicks.</p>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-4">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-book-open fa-2x text-primary"></i>
                        </div>
                        <h5 class="feature-title mb-3">Islamic Guidance</h5>
                        <p class="text-muted">Beyond booking, we provide comprehensive Islamic guidance and educational resources to help you prepare spiritually and practically for your sacred journey.</p>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-4">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100">
                        <div class="feature-icon mb-3">
                            <i class="fas fa-users fa-2x text-success"></i>
                        </div>
                        <h5 class="feature-title mb-3">Community Focus</h5>
                        <p class="text-muted">We foster a sense of community among pilgrims, connecting you with fellow travelers and creating opportunities for shared spiritual experiences.</p>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>

    <!-- Team Section -->
    <div class="our-team py-5">
        <div class="container-fluid">
            <div class="text-center mb-5">
                <h2 class="section-title">Our Leadership Team</h2>
                <p class="section-subtitle text-muted">Dedicated professionals committed to serving the Ummah</p>
            </div>
            
            <div class="row g-4 justify-content-center">
                <div class="col-lg-4 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100 text-center team-card">
                        <div class="team-photo mb-3">
                            <div class="avatar-placeholder mx-auto">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        </div>
                        <h5 class="team-name mb-1">Ahmed Al-Rashid</h5>
                        <p class="team-role text-primary mb-3">Founder & CEO</p>
                        <p class="text-muted small">A seasoned entrepreneur with over 15 years in the travel industry, Ahmed's vision drives SeferEt's mission to transform spiritual travel.</p>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100 text-center team-card">
                        <div class="team-photo mb-3">
                            <div class="avatar-placeholder mx-auto">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        </div>
                        <h5 class="team-name mb-1">Fatima Hassan</h5>
                        <p class="team-role text-success mb-3">Head of Operations</p>
                        <p class="text-muted small">With expertise in logistics and customer service, Fatima ensures every aspect of your journey is meticulously planned and executed.</p>
                    </x-customer.card>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100 text-center team-card">
                        <div class="team-photo mb-3">
                            <div class="avatar-placeholder mx-auto">
                                <i class="fas fa-user fa-3x text-muted"></i>
                            </div>
                        </div>
                        <h5 class="team-name mb-1">Dr. Omar Yusuf</h5>
                        <p class="team-role text-info mb-3">Islamic Scholar & Advisor</p>
                        <p class="text-muted small">Our resident Islamic scholar provides spiritual guidance and ensures all our services align with authentic Islamic teachings and practices.</p>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="cta-section py-5 bg-gradient-primary text-white">
        <div class="container-fluid">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h2 class="h1 mb-4">Ready to Begin Your Spiritual Journey?</h2>
                    <p class="lead mb-4">Join thousands of pilgrims who have trusted SeferEt to make their Umrah dreams a reality. Let us help you create memories that will last a lifetime.</p>
                    
                    <div class="cta-buttons">
                        <x-customer.button href="{{ route('packages') }}" variant="light" size="lg" class="me-3 mb-2">
                            <i class="fas fa-box me-2"></i>Browse Packages
                        </x-customer.button>
                        <x-customer.button href="{{ route('contact') }}" variant="outline-light" size="lg" class="mb-2">
                            <i class="fas fa-envelope me-2"></i>Contact Us
                        </x-customer.button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.about-header {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.9), rgba(30, 58, 138, 0.9)), url('https://images.unsplash.com/photo-1549308509-7e78b5f4b8a9?w=1920&h=600&fit=crop') center/cover;
    background-attachment: fixed;
    position: relative;
}

.about-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 300" fill="none"><path d="M0,100 C150,200 350,0 500,100 C650,200 850,0 1000,100 L1000,00 L0,0" fill="%23ffffff" fill-opacity="0.05"/></svg>') bottom/cover;
    pointer-events: none;
}

.section-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.section-subtitle {
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto 2rem;
}

.story-image img {
    transition: transform 0.3s ease;
}

.story-image img:hover {
    transform: scale(1.02);
}

.highlight-item {
    display: flex;
    align-items: center;
    padding: 0.5rem 0;
}

.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.value-card {
    transition: all 0.3s ease;
}

.value-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.15) !important;
}

.value-icon i {
    transition: transform 0.3s ease;
}

.value-card:hover .value-icon i {
    transform: scale(1.1);
}

.feature-icon i {
    transition: color 0.3s ease;
}

.team-card {
    transition: all 0.3s ease;
}

.team-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
}

.avatar-placeholder {
    width: 100px;
    height: 100px;
    background: var(--surface-variant-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.team-role {
    font-weight: 600;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)) !important;
}

.cta-buttons .btn {
    min-width: 150px;
}

/* Animation for scroll reveal */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-on-scroll {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease;
}

.animate-on-scroll.animated {
    opacity: 1;
    transform: translateY(0);
}

@media (max-width: 768px) {
    .about-header {
        background-attachment: scroll;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .section-subtitle {
        font-size: 1rem;
    }
    
    .cta-buttons .btn {
        min-width: auto;
        margin-bottom: 0.5rem;
    }
    
    .icon-circle {
        width: 60px;
        height: 60px;
    }
    
    .avatar-placeholder {
        width: 80px;
        height: 80px;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, observerOptions);
    
    // Add animation class to cards and observe them
    document.querySelectorAll('.card, .highlight-item').forEach(element => {
        element.classList.add('animate-on-scroll');
        observer.observe(element);
    });
    
    // Staggered animation for value cards
    document.querySelectorAll('.value-card').forEach((card, index) => {
        card.style.transitionDelay = `${index * 0.1}s`;
    });
    
    // Team cards staggered animation
    document.querySelectorAll('.team-card').forEach((card, index) => {
        card.style.transitionDelay = `${index * 0.15}s`;
    });
    
    // Smooth scroll for internal links
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>
@endpush

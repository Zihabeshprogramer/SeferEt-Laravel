@extends('layouts.customer')

@section('title', 'Explore Sacred Destinations - SeferEt')

@section('content')
    <!-- Explore Header -->
    <div class="explore-header bg-primary text-white py-5">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-3">
                        <i class="fas fa-compass me-3"></i>
                        Explore Sacred Destinations
                    </h1>
                    <p class="lead opacity-90 mb-0">Discover the spiritual beauty and rich heritage of the Holy Cities and surrounding regions</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-map-marked-alt fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation -->
    <div class="quick-nav py-4 bg-light">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="nav nav-pills justify-content-center" role="tablist">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#destinations" type="button" role="tab">
                            <i class="fas fa-mosque me-2"></i>Destinations
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#experiences" type="button" role="tab">
                            <i class="fas fa-star me-2"></i>Experiences
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#culture" type="button" role="tab">
                            <i class="fas fa-book-open me-2"></i>Culture & History
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#practical" type="button" role="tab">
                            <i class="fas fa-info-circle me-2"></i>Travel Tips
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    <div class="explore-content py-5">
        <div class="container-fluid">
            <div class="tab-content">
                <!-- Destinations Tab -->
                <div class="tab-pane fade show active" id="destinations" role="tabpanel">
                    <div class="text-center mb-5">
                        <h2 class="section-title">Sacred Destinations</h2>
                        <p class="section-subtitle text-muted">Explore the most significant places in Islamic heritage</p>
                    </div>
                    
                    <div class="row g-4">
                        @foreach($destinations as $destination)
                        <div class="col-lg-6">
                            <x-customer.card variant="elevated" elevation="md" padding="none" class="destination-card h-100">
                                <div class="destination-image">
                                    <img src="{{ $destination['image'] }}" alt="{{ $destination['name'] }}" class="img-fluid">
                                    <div class="destination-overlay">
                                        <div class="destination-info text-white">
                                            <h4 class="destination-name mb-2">{{ $destination['name'] }}</h4>
                                            <p class="destination-description mb-3">{{ $destination['description'] }}</p>
                                            <div class="destination-meta">
                                                <span class="badge bg-light text-dark me-2">
                                                    <i class="fas fa-calendar me-1"></i>{{ $destination['best_time'] }}
                                                </span>
                                                <span class="badge bg-light text-dark">
                                                    <i class="fas fa-map-marker-alt me-1"></i>{{ count($destination['attractions']) }} attractions
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="destination-details p-4">
                                    <h6 class="mb-3">
                                        <i class="fas fa-star text-warning me-2"></i>
                                        Top Attractions
                                    </h6>
                                    <div class="attractions-list">
                                        @foreach(array_slice($destination['attractions'], 0, 3) as $attraction)
                                        <span class="attraction-tag">
                                            <i class="fas fa-mosque text-primary me-1"></i>{{ $attraction }}
                                        </span>
                                        @endforeach
                                        @if(count($destination['attractions']) > 3)
                                        <span class="text-muted small">+{{ count($destination['attractions']) - 3 }} more</span>
                                        @endif
                                    </div>
                                    <div class="destination-actions mt-4">
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <x-customer.button variant="primary" size="sm" fullWidth="true">
                                                    <i class="fas fa-eye me-1"></i>Explore
                                                </x-customer.button>
                                            </div>
                                            <div class="col-6">
                                                <x-customer.button variant="outline-primary" size="sm" fullWidth="true">
                                                    <i class="fas fa-box me-1"></i>Packages
                                                </x-customer.button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </x-customer.card>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Experiences Tab -->
                <div class="tab-pane fade" id="experiences" role="tabpanel">
                    <div class="text-center mb-5">
                        <h2 class="section-title">Spiritual Experiences</h2>
                        <p class="section-subtitle text-muted">Curated experiences to deepen your spiritual journey</p>
                    </div>
                    
                    <div class="row g-4">
                        @foreach($experiences as $experience)
                        <div class="col-lg-4">
                            <x-customer.card variant="elevated" elevation="md" padding="none" class="experience-card h-100">
                                <div class="experience-image">
                                    <img src="{{ $experience['image'] }}" alt="{{ $experience['title'] }}" class="img-fluid">
                                    <div class="experience-badges">
                                        <span class="badge bg-success">
                                            <i class="fas fa-star me-1"></i>{{ $experience['rating'] }}
                                        </span>
                                    </div>
                                </div>
                                <div class="experience-details p-4">
                                    <h5 class="experience-title mb-2">{{ $experience['title'] }}</h5>
                                    <p class="experience-description text-muted mb-3">{{ $experience['description'] }}</p>
                                    
                                    <div class="experience-meta mb-3">
                                        <div class="row g-2 text-center">
                                            <div class="col-6">
                                                <div class="meta-item">
                                                    <i class="fas fa-clock text-primary"></i>
                                                    <small class="d-block text-muted">{{ $experience['duration'] }}</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="meta-item">
                                                    <i class="fas fa-dollar-sign text-success"></i>
                                                    <small class="d-block text-muted">From ${{ $experience['price'] }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="experience-actions">
                                        <x-customer.button variant="primary" size="md" fullWidth="true">
                                            <i class="fas fa-plus me-2"></i>Add to Trip
                                        </x-customer.button>
                                    </div>
                                </div>
                            </x-customer.card>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Culture & History Tab -->
                <div class="tab-pane fade" id="culture" role="tabpanel">
                    <div class="text-center mb-5">
                        <h2 class="section-title">Culture & Islamic Heritage</h2>
                        <p class="section-subtitle text-muted">Learn about the rich Islamic history and traditions</p>
                    </div>
                    
                    <div class="row g-4">
                        <!-- Islamic History Timeline -->
                        <div class="col-lg-6">
                            <x-customer.card variant="elevated" elevation="md" padding="lg">
                                <h4 class="mb-4">
                                    <i class="fas fa-scroll text-primary me-2"></i>
                                    Islamic History Timeline
                                </h4>
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-primary"></div>
                                        <div class="timeline-content">
                                            <h6>The Revelation</h6>
                                            <small class="text-muted">610 CE</small>
                                            <p class="small">Prophet Muhammad (PBUH) receives the first revelation in the cave of Hira</p>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6>The Hijra</h6>
                                            <small class="text-muted">622 CE</small>
                                            <p class="small">Migration from Makkah to Madinah marks the beginning of the Islamic calendar</p>
                                        </div>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-warning"></div>
                                        <div class="timeline-content">
                                            <h6>Conquest of Makkah</h6>
                                            <small class="text-muted">630 CE</small>
                                            <p class="small">Peaceful return to Makkah and cleansing of the Kaaba</p>
                                        </div>
                                    </div>
                                </div>
                            </x-customer.card>
                        </div>

                        <!-- Islamic Traditions -->
                        <div class="col-lg-6">
                            <x-customer.card variant="elevated" elevation="md" padding="lg">
                                <h4 class="mb-4">
                                    <i class="fas fa-praying-hands text-primary me-2"></i>
                                    Islamic Traditions
                                </h4>
                                <div class="traditions-list">
                                    <div class="tradition-item mb-3">
                                        <h6><i class="fas fa-mosque text-success me-2"></i>The Five Pillars</h6>
                                        <p class="small text-muted mb-0">Shahada, Salah, Zakat, Sawm, and Hajj form the foundation of Islamic practice</p>
                                    </div>
                                    <div class="tradition-item mb-3">
                                        <h6><i class="fas fa-book text-info me-2"></i>Quran & Sunnah</h6>
                                        <p class="small text-muted mb-0">The holy book and traditions of Prophet Muhammad (PBUH) guide Muslim life</p>
                                    </div>
                                    <div class="tradition-item mb-3">
                                        <h6><i class="fas fa-hands-helping text-warning me-2"></i>Community (Ummah)</h6>
                                        <p class="small text-muted mb-0">The global Muslim community united by shared faith and values</p>
                                    </div>
                                    <div class="tradition-item">
                                        <h6><i class="fas fa-heart text-danger me-2"></i>Compassion & Justice</h6>
                                        <p class="small text-muted mb-0">Core Islamic values emphasizing mercy, justice, and care for others</p>
                                    </div>
                                </div>
                            </x-customer.card>
                        </div>

                        <!-- Sacred Architecture -->
                        <div class="col-12">
                            <x-customer.card variant="elevated" elevation="md" padding="lg">
                                <h4 class="mb-4">
                                    <i class="fas fa-building text-primary me-2"></i>
                                    Sacred Islamic Architecture
                                </h4>
                                <div class="row g-4">
                                    <div class="col-md-4 text-center">
                                        <div class="architecture-item">
                                            <i class="fas fa-kaaba fa-3x text-primary mb-3"></i>
                                            <h6>The Kaaba</h6>
                                            <p class="small text-muted">The cubic structure at the center of the Grand Mosque, towards which Muslims pray</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <div class="architecture-item">
                                            <i class="fas fa-mosque fa-3x text-success mb-3"></i>
                                            <h6>Masjid an-Nabawi</h6>
                                            <p class="small text-muted">The Prophet's Mosque in Madinah, built by Prophet Muhammad (PBUH) himself</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <div class="architecture-item">
                                            <i class="fas fa-star-and-crescent fa-3x text-warning mb-3"></i>
                                            <h6>Minarets & Domes</h6>
                                            <p class="small text-muted">Architectural elements that define Islamic mosque design and call to prayer</p>
                                        </div>
                                    </div>
                                </div>
                            </x-customer.card>
                        </div>
                    </div>
                </div>

                <!-- Travel Tips Tab -->
                <div class="tab-pane fade" id="practical" role="tabpanel">
                    <div class="text-center mb-5">
                        <h2 class="section-title">Essential Travel Tips</h2>
                        <p class="section-subtitle text-muted">Practical information for your spiritual journey</p>
                    </div>
                    
                    <div class="row g-4">
                        <!-- Visa & Documentation -->
                        <div class="col-lg-4">
                            <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100">
                                <h5 class="mb-4">
                                    <i class="fas fa-passport text-primary me-2"></i>
                                    Visa & Documentation
                                </h5>
                                <div class="tips-list">
                                    <div class="tip-item mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span class="small">Valid passport with 6+ months validity</span>
                                    </div>
                                    <div class="tip-item mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span class="small">Umrah visa obtained through authorized agents</span>
                                    </div>
                                    <div class="tip-item mb-3">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span class="small">Vaccination certificates (if required)</span>
                                    </div>
                                    <div class="tip-item">
                                        <i class="fas fa-check-circle text-success me-2"></i>
                                        <span class="small">Travel insurance recommended</span>
                                    </div>
                                </div>
                            </x-customer.card>
                        </div>

                        <!-- What to Pack -->
                        <div class="col-lg-4">
                            <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100">
                                <h5 class="mb-4">
                                    <i class="fas fa-suitcase text-primary me-2"></i>
                                    What to Pack
                                </h5>
                                <div class="tips-list">
                                    <div class="tip-item mb-3">
                                        <i class="fas fa-tshirt text-info me-2"></i>
                                        <span class="small">Modest, loose-fitting clothing</span>
                                    </div>
                                    <div class="tip-item mb-3">
                                        <i class="fas fa-shoe-prints text-warning me-2"></i>
                                        <span class="small">Comfortable walking shoes</span>
                                    </div>
                                    <div class="tip-item mb-3">
                                        <i class="fas fa-sun text-warning me-2"></i>
                                        <span class="small">Sun protection (hat, sunscreen)</span>
                                    </div>
                                    <div class="tip-item">
                                        <i class="fas fa-book text-primary me-2"></i>
                                        <span class="small">Prayer book and Quran</span>
                                    </div>
                                </div>
                            </x-customer.card>
                        </div>

                        <!-- Health & Safety -->
                        <div class="col-lg-4">
                            <x-customer.card variant="elevated" elevation="md" padding="lg" class="h-100">
                                <h5 class="mb-4">
                                    <i class="fas fa-heart text-primary me-2"></i>
                                    Health & Safety
                                </h5>
                                <div class="tips-list">
                                    <div class="tip-item mb-3">
                                        <i class="fas fa-tint text-primary me-2"></i>
                                        <span class="small">Stay hydrated, especially during summer</span>
                                    </div>
                                    <div class="tip-item mb-3">
                                        <i class="fas fa-pills text-success me-2"></i>
                                        <span class="small">Bring necessary medications</span>
                                    </div>
                                    <div class="tip-item mb-3">
                                        <i class="fas fa-walking text-warning me-2"></i>
                                        <span class="small">Pace yourself during walking tours</span>
                                    </div>
                                    <div class="tip-item">
                                        <i class="fas fa-mobile-alt text-info me-2"></i>
                                        <span class="small">Keep emergency contacts handy</span>
                                    </div>
                                </div>
                            </x-customer.card>
                        </div>

                        <!-- Cultural Etiquette -->
                        <div class="col-12">
                            <x-customer.card variant="elevated" elevation="md" padding="lg">
                                <h5 class="mb-4">
                                    <i class="fas fa-handshake text-primary me-2"></i>
                                    Cultural Etiquette & Customs
                                </h5>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <h6 class="text-success">
                                            <i class="fas fa-check me-2"></i>Do
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><i class="fas fa-dot-circle text-success me-2"></i>Dress modestly and respectfully</li>
                                            <li class="mb-2"><i class="fas fa-dot-circle text-success me-2"></i>Remove shoes when entering mosques</li>
                                            <li class="mb-2"><i class="fas fa-dot-circle text-success me-2"></i>Be patient and respectful with crowds</li>
                                            <li class="mb-2"><i class="fas fa-dot-circle text-success me-2"></i>Learn basic Arabic greetings</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-danger">
                                            <i class="fas fa-times me-2"></i>Don't
                                        </h6>
                                        <ul class="list-unstyled">
                                            <li class="mb-2"><i class="fas fa-times-circle text-danger me-2"></i>Take photos of people without permission</li>
                                            <li class="mb-2"><i class="fas fa-times-circle text-danger me-2"></i>Use left hand for eating or greeting</li>
                                            <li class="mb-2"><i class="fas fa-times-circle text-danger me-2"></i>Point feet towards the Qibla</li>
                                            <li class="mb-2"><i class="fas fa-times-circle text-danger me-2"></i>Be loud or disruptive during prayers</li>
                                        </ul>
                                    </div>
                                </div>
                            </x-customer.card>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.explore-header {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.9), rgba(30, 58, 138, 0.9)), url('https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=1920&h=600&fit=crop') center/cover;
    background-attachment: fixed;
    position: relative;
}

.explore-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 300" fill="none"><path d="M0,100 C150,200 350,0 500,100 C650,200 850,0 1000,100 L1000,00 L0,0" fill="%23ffffff" fill-opacity="0.05"/></svg>') bottom/cover;
    pointer-events: none;
}

.destination-card, .experience-card {
    transition: all 0.3s ease;
}

.destination-card:hover, .experience-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 25px rgba(0,0,0,0.15) !important;
}

.destination-image, .experience-image {
    position: relative;
    height: 300px;
    overflow: hidden;
}

.destination-image img, .experience-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.destination-card:hover .destination-image img,
.experience-card:hover .experience-image img {
    transform: scale(1.1);
}

.destination-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    padding: 3rem 1.5rem 1.5rem;
}

.experience-badges {
    position: absolute;
    top: 1rem;
    right: 1rem;
}

.attraction-tag {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: var(--surface-variant-color);
    border-radius: 20px;
    font-size: 0.8rem;
    margin-right: 0.5rem;
    margin-bottom: 0.5rem;
    color: var(--text-secondary);
}

.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border-color);
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.timeline-content {
    background: var(--surface-variant-color);
    padding: 1rem;
    border-radius: 8px;
}

.architecture-item, .tradition-item {
    transition: transform 0.2s ease;
}

.architecture-item:hover, .tradition-item:hover {
    transform: translateY(-2px);
}

.tips-list .tip-item {
    display: flex;
    align-items: flex-start;
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

@media (max-width: 768px) {
    .explore-header {
        background-attachment: scroll;
    }
    
    .destination-image, .experience-image {
        height: 200px;
    }
    
    .timeline {
        padding-left: 1rem;
    }
    
    .timeline-marker {
        left: -1rem;
        width: 1rem;
        height: 1rem;
    }
    
    .destination-overlay {
        padding: 2rem 1rem 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tabs
    const tabTriggerList = [].slice.call(document.querySelectorAll('.nav-pills button'));
    tabTriggerList.map(function(tabTriggerEl) {
        return new bootstrap.Tab(tabTriggerEl);
    });
    
    // Smooth scroll animations
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
    
    // Observe cards for animation
    document.querySelectorAll('.destination-card, .experience-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease';
        observer.observe(card);
    });
    
    // Timeline animation
    document.querySelectorAll('.timeline-item').forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-30px)';
        item.style.transition = `all 0.6s ease ${index * 0.1}s`;
        observer.observe(item);
    });
});
</script>
@endpush

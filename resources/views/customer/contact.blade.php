@extends('layouts.customer')

@section('title', 'Contact Us - SeferEt')

@section('content')
    <!-- Contact Header -->
    <div class="contact-header bg-primary text-white py-5">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-3">
                        <i class="fas fa-envelope me-3"></i>
                        Contact Us
                    </h1>
                    <p class="lead opacity-90 mb-0">We're here to help you plan your perfect spiritual journey. Get in touch with our experienced team.</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-headset fa-4x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Content -->
    <div class="contact-content py-5">
        <div class="container-fluid">
            <div class="row g-5">
                <!-- Contact Form -->
                <div class="col-lg-8">
                    <x-customer.card variant="elevated" elevation="md" padding="lg">
                        <h2 class="section-title mb-4">
                            <i class="fas fa-paper-plane text-primary me-2"></i>
                            Send Us a Message
                        </h2>
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Please correct the errors below.
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('contact.submit') }}" method="POST" class="contact-form">
                            @csrf
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Full Name *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user text-primary"></i></span>
                                        <input type="text" 
                                               name="name" 
                                               class="form-control @error('name') is-invalid @enderror" 
                                               placeholder="Enter your full name" 
                                               value="{{ old('name') }}" 
                                               required>
                                    </div>
                                    @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Address *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope text-primary"></i></span>
                                        <input type="email" 
                                               name="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               placeholder="Enter your email address" 
                                               value="{{ old('email') }}" 
                                               required>
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-phone text-primary"></i></span>
                                        <input type="tel" 
                                               name="phone" 
                                               class="form-control @error('phone') is-invalid @enderror" 
                                               placeholder="Enter your phone number" 
                                               value="{{ old('phone') }}">
                                    </div>
                                    @error('phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Inquiry Type *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-tag text-primary"></i></span>
                                        <select name="subject" class="form-select @error('subject') is-invalid @enderror" required>
                                            <option value="">Select inquiry type</option>
                                            <option value="General Inquiry" {{ old('subject') == 'General Inquiry' ? 'selected' : '' }}>General Inquiry</option>
                                            <option value="Package Information" {{ old('subject') == 'Package Information' ? 'selected' : '' }}>Package Information</option>
                                            <option value="Booking Support" {{ old('subject') == 'Booking Support' ? 'selected' : '' }}>Booking Support</option>
                                            <option value="Payment Issues" {{ old('subject') == 'Payment Issues' ? 'selected' : '' }}>Payment Issues</option>
                                            <option value="Travel Documentation" {{ old('subject') == 'Travel Documentation' ? 'selected' : '' }}>Travel Documentation</option>
                                            <option value="Special Requirements" {{ old('subject') == 'Special Requirements' ? 'selected' : '' }}>Special Requirements</option>
                                            <option value="Partnership Inquiry" {{ old('subject') == 'Partnership Inquiry' ? 'selected' : '' }}>Partnership Inquiry</option>
                                            <option value="Complaint" {{ old('subject') == 'Complaint' ? 'selected' : '' }}>Complaint</option>
                                            <option value="Other" {{ old('subject') == 'Other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>
                                    @error('subject')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Message *</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-comment-alt text-primary"></i></span>
                                        <textarea name="message" 
                                                  rows="6" 
                                                  class="form-control @error('message') is-invalid @enderror" 
                                                  placeholder="Please describe your inquiry in detail..." 
                                                  required>{{ old('message') }}</textarea>
                                    </div>
                                    @error('message')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="newsletter" id="newsletter" {{ old('newsletter') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="newsletter">
                                            I would like to receive updates about special offers and new packages
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-12">
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <x-customer.button type="reset" variant="outline-secondary" size="lg" class="me-md-2">
                                            <i class="fas fa-undo me-2"></i>Clear Form
                                        </x-customer.button>
                                        <x-customer.button type="submit" variant="primary" size="lg">
                                            <i class="fas fa-paper-plane me-2"></i>Send Message
                                        </x-customer.button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </x-customer.card>
                </div>

                <!-- Contact Information -->
                <div class="col-lg-4">
                    <!-- Quick Contact -->
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                        <h4 class="mb-4">
                            <i class="fas fa-phone text-primary me-2"></i>
                            Quick Contact
                        </h4>
                        
                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-phone-alt text-success"></i>
                                </div>
                                <div class="contact-details">
                                    <h6 class="mb-1">Phone Support</h6>
                                    <p class="text-muted mb-1">+1 (555) 123-4567</p>
                                    <small class="text-muted">Available 24/7</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-envelope text-primary"></i>
                                </div>
                                <div class="contact-details">
                                    <h6 class="mb-1">Email Support</h6>
                                    <p class="text-muted mb-1">support@seferet.com</p>
                                    <small class="text-muted">Response within 24 hours</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-comments text-info"></i>
                                </div>
                                <div class="contact-details">
                                    <h6 class="mb-1">Live Chat</h6>
                                    <p class="text-muted mb-1">Available on website</p>
                                    <small class="text-muted">Instant support</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="contact-item">
                            <div class="d-flex align-items-start">
                                <div class="contact-icon me-3">
                                    <i class="fas fa-headset text-warning"></i>
                                </div>
                                <div class="contact-details">
                                    <h6 class="mb-1">Emergency Support</h6>
                                    <p class="text-muted mb-1">+1 (555) 999-0000</p>
                                    <small class="text-muted">For travelers abroad</small>
                                </div>
                            </div>
                        </div>
                    </x-customer.card>

                    <!-- Office Information -->
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                        <h4 class="mb-4">
                            <i class="fas fa-building text-primary me-2"></i>
                            Office Locations
                        </h4>
                        
                        <div class="office-item mb-4">
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                Headquarters - Dubai
                            </h6>
                            <address class="text-muted mb-3">
                                SeferEt Building<br>
                                Sheikh Zayed Road<br>
                                Dubai, United Arab Emirates<br>
                                P.O. Box 12345
                            </address>
                            <div class="office-hours">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Sun-Thu: 9:00 AM - 6:00 PM
                                </small>
                            </div>
                        </div>
                        
                        <div class="office-item">
                            <h6 class="text-success mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                Branch Office - London
                            </h6>
                            <address class="text-muted mb-3">
                                Islamic Centre Building<br>
                                Regent's Park<br>
                                London NW1 4LB<br>
                                United Kingdom
                            </address>
                            <div class="office-hours">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Mon-Fri: 9:00 AM - 5:00 PM
                                </small>
                            </div>
                        </div>
                    </x-customer.card>

                    <!-- Social Media -->
                    <x-customer.card variant="elevated" elevation="md" padding="lg" class="mb-4">
                        <h4 class="mb-4">
                            <i class="fas fa-share-alt text-primary me-2"></i>
                            Follow Us
                        </h4>
                        
                        <div class="social-links">
                            <a href="#" class="social-link facebook mb-3 d-flex align-items-center">
                                <div class="social-icon me-3">
                                    <i class="fab fa-facebook-f"></i>
                                </div>
                                <div class="social-details">
                                    <h6 class="mb-1">Facebook</h6>
                                    <small class="text-muted">@SeferEtOfficial</small>
                                </div>
                            </a>
                            
                            <a href="#" class="social-link twitter mb-3 d-flex align-items-center">
                                <div class="social-icon me-3">
                                    <i class="fab fa-twitter"></i>
                                </div>
                                <div class="social-details">
                                    <h6 class="mb-1">Twitter</h6>
                                    <small class="text-muted">@SeferEt</small>
                                </div>
                            </a>
                            
                            <a href="#" class="social-link instagram mb-3 d-flex align-items-center">
                                <div class="social-icon me-3">
                                    <i class="fab fa-instagram"></i>
                                </div>
                                <div class="social-details">
                                    <h6 class="mb-1">Instagram</h6>
                                    <small class="text-muted">@seferet.official</small>
                                </div>
                            </a>
                            
                            <a href="#" class="social-link youtube mb-3 d-flex align-items-center">
                                <div class="social-icon me-3">
                                    <i class="fab fa-youtube"></i>
                                </div>
                                <div class="social-details">
                                    <h6 class="mb-1">YouTube</h6>
                                    <small class="text-muted">SeferEt Travel</small>
                                </div>
                            </a>
                            
                            <a href="#" class="social-link whatsapp d-flex align-items-center">
                                <div class="social-icon me-3">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <div class="social-details">
                                    <h6 class="mb-1">WhatsApp</h6>
                                    <small class="text-muted">+971 50 123 4567</small>
                                </div>
                            </a>
                        </div>
                    </x-customer.card>

                    <!-- FAQ Link -->
                    <x-customer.card variant="elevated" elevation="md" padding="lg">
                        <h4 class="mb-3">
                            <i class="fas fa-question-circle text-primary me-2"></i>
                            Need Quick Answers?
                        </h4>
                        <p class="text-muted mb-3">Visit our comprehensive FAQ section for immediate answers to common questions about bookings, travel, and our services.</p>
                        
                        <div class="d-grid">
                            <x-customer.button href="#" variant="outline-primary" size="md">
                                <i class="fas fa-book-open me-2"></i>View FAQ
                            </x-customer.button>
                        </div>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>

    <!-- Map Section -->
    <div class="map-section py-5 bg-light">
        <div class="container-fluid">
            <div class="text-center mb-5">
                <h2 class="section-title">Find Us</h2>
                <p class="section-subtitle text-muted">Visit our offices or locate us on the map</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <x-customer.card variant="elevated" elevation="md" padding="none">
                        <div class="map-placeholder">
                            <div class="map-content d-flex align-items-center justify-content-center">
                                <div class="text-center text-muted">
                                    <i class="fas fa-map fa-4x mb-3"></i>
                                    <h5>Interactive Map</h5>
                                    <p>Google Maps integration would be implemented here<br>showing our office locations worldwide</p>
                                </div>
                            </div>
                        </div>
                    </x-customer.card>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.contact-header {
    background: linear-gradient(135deg, rgba(30, 64, 175, 0.9), rgba(30, 58, 138, 0.9)), url('https://images.unsplash.com/photo-1542816417-0983c9c9ad53?w=1920&h=600&fit=crop') center/cover;
    background-attachment: fixed;
    position: relative;
}

.contact-header::before {
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
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.section-subtitle {
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
}

.contact-form .input-group-text {
    background: var(--surface-variant-color);
    border-right: none;
}

.contact-form .form-control {
    border-left: none;
    transition: all 0.3s ease;
}

.contact-form .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(var(--primary-rgb), 0.25);
}

.contact-form .form-control:focus + .input-group-text,
.contact-form .form-control:focus ~ .input-group-text {
    border-color: var(--primary-color);
}

.contact-item {
    padding: 1rem 0;
    border-bottom: 1px solid var(--border-color);
}

.contact-item:last-child {
    border-bottom: none;
}

.contact-icon {
    width: 40px;
    height: 40px;
    background: var(--surface-variant-color);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.office-item {
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 1rem;
}

.office-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.social-links .social-link {
    text-decoration: none;
    color: var(--text-primary);
    padding: 0.5rem;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.social-links .social-link:hover {
    background: var(--surface-variant-color);
    transform: translateX(5px);
}

.social-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.facebook .social-icon {
    background: #1877F2;
}

.twitter .social-icon {
    background: #1DA1F2;
}

.instagram .social-icon {
    background: linear-gradient(45deg, #F56040, #E1306C, #C13584);
}

.youtube .social-icon {
    background: #FF0000;
}

.whatsapp .social-icon {
    background: #25D366;
}

.map-placeholder {
    height: 400px;
    background: var(--surface-variant-color);
    border: 2px dashed var(--border-color);
    border-radius: 12px;
}

.map-content {
    height: 100%;
    width: 100%;
}

/* Form validation styles */
.is-invalid {
    border-color: var(--error-color) !important;
}

.invalid-feedback {
    display: block !important;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: var(--error-color);
}

/* Loading state for form submission */
.contact-form.loading {
    opacity: 0.7;
    pointer-events: none;
}

.contact-form.loading button[type="submit"] {
    opacity: 0.5;
}

@media (max-width: 768px) {
    .contact-header {
        background-attachment: scroll;
    }
    
    .section-title {
        font-size: 1.75rem;
    }
    
    .contact-form .d-md-flex {
        flex-direction: column !important;
    }
    
    .contact-form .me-md-2 {
        margin-right: 0 !important;
        margin-bottom: 0.5rem;
    }
    
    .map-placeholder {
        height: 250px;
    }
}

/* Animation classes */
.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission handling
    const contactForm = document.querySelector('.contact-form');
    const submitButton = contactForm.querySelector('button[type="submit"]');
    const originalSubmitText = submitButton.innerHTML;
    
    contactForm.addEventListener('submit', function(e) {
        // Add loading state
        contactForm.classList.add('loading');
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
        submitButton.disabled = true;
    });
    
    // Reset form button
    const resetButton = contactForm.querySelector('button[type="reset"]');
    resetButton.addEventListener('click', function() {
        // Clear any validation errors
        const invalidElements = contactForm.querySelectorAll('.is-invalid');
        invalidElements.forEach(element => {
            element.classList.remove('is-invalid');
        });
        
        const errorMessages = contactForm.querySelectorAll('.invalid-feedback');
        errorMessages.forEach(message => {
            message.remove();
        });
    });
    
    // Form validation
    const inputs = contactForm.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('blur', validateField);
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField.call(this);
            }
        });
    });
    
    function validateField() {
        const field = this;
        const value = field.value.trim();
        let isValid = true;
        let message = '';
        
        // Remove existing validation
        field.classList.remove('is-invalid');
        const existingError = field.parentNode.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
        
        // Required field validation
        if (field.required && !value) {
            isValid = false;
            message = 'This field is required.';
        }
        
        // Email validation
        if (field.type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                message = 'Please enter a valid email address.';
            }
        }
        
        // Phone validation
        if (field.type === 'tel' && value) {
            const phoneRegex = /^[\+]?[\d\s\-\(\)]+$/;
            if (!phoneRegex.test(value) || value.length < 10) {
                isValid = false;
                message = 'Please enter a valid phone number.';
            }
        }
        
        // Message length validation
        if (field.name === 'message' && value && value.length < 10) {
            isValid = false;
            message = 'Message must be at least 10 characters long.';
        }
        
        if (!isValid) {
            field.classList.add('is-invalid');
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            field.parentNode.parentNode.appendChild(errorDiv);
        }
        
        return isValid;
    }
    
    // Social media links
    const socialLinks = document.querySelectorAll('.social-link');
    socialLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            // In a real application, these would link to actual social media profiles
            console.log('Social media link clicked:', this.querySelector('.social-details h6').textContent);
        });
    });
    
    // Smooth scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);
    
    // Observe cards for animation
    document.querySelectorAll('.card').forEach(card => {
        observer.observe(card);
    });
});
</script>
@endpush

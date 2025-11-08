{{-- Customer Footer - Flutter Design Match --}}
<footer class="seferet-footer bg-primary text-title py-5 mt-5">
    <div class="container">
        <div class="row g-4">
            {{-- Company Info --}}
            <div class="col-lg-4 col-md-6">
                <div class="footer-section">
                    <h5 class="footer-title d-flex align-items-center mb-3">
                        <img src="{{ asset('images/logo/seferet-logo-white-sidetext.png') }}" alt="SeferEt" style="height: 85px; width: 230px; margin-right: 8px;">                    </h5>
                    <p class="footer-description mb-3">
                        Your trusted partner for Umrah journeys. We provide comprehensive travel packages and services to make your spiritual journey memorable and comfortable.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link me-3">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link me-3">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-link me-3">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Quick Links --}}
            <div class="col-lg-2 col-md-6">
                <div class="footer-section">
                    <h6 class="footer-title mb-3">Quick Links</h6>
                    <ul class="footer-links">
                        <li><a href="{{ route('home') }}">Home</a></li>
                        <li><a href="#">Packages</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
            </div>

            {{-- Services --}}
            <div class="col-lg-2 col-md-6">
                <div class="footer-section">
                    <h6 class="footer-title mb-3">Services</h6>
                    <ul class="footer-links">
                        <li><a href="#">Umrah Packages</a></li>
                        <li><a href="#">Flight Booking</a></li>
                        <li><a href="#">Hotel Booking</a></li>
                        <li><a href="#">Transportation</a></li>
                        <li><a href="#">Visa Services</a></li>
                        <li><a href="#">Travel Insurance</a></li>
                    </ul>
                </div>
            </div>

            {{-- Support --}}
            <div class="col-lg-2 col-md-6">
                <div class="footer-section">
                    <h6 class="footer-title mb-3">Support</h6>
                    <ul class="footer-links">
                        <li><a href="#">Help Center</a></li>
                        <li><a href="#">Customer Support</a></li>
                        <li><a href="#">Terms & Conditions</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Refund Policy</a></li>
                        <li><a href="#">Sitemap</a></li>
                    </ul>
                </div>
            </div>

            {{-- Contact Info --}}
            <div class="col-lg-2 col-md-6">
                <div class="footer-section">
                    <h6 class="footer-title mb-3">Contact Info</h6>
                    <div class="contact-info">
                        <div class="contact-item mb-2">
                            <i class="fas fa-phone me-2"></i>
                            <span>+1 (555) 123-4567</span>
                        </div>
                        <div class="contact-item mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            <span>info@seferet.com</span>
                        </div>
                        <div class="contact-item mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <span>123 Travel Street<br>New York, NY 10001</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock me-2"></i>
                            <span>24/7 Support</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="footer-divider my-4">

        {{-- Bottom Footer --}}
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="footer-copyright mb-0">
                    &copy; {{ date('Y') }} SeferEt. All rights reserved.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="footer-badges">
                    <span class="badge bg-secondary me-2">
                        <i class="fas fa-shield-alt me-1"></i>
                        Secure Booking
                    </span>
                    <span class="badge bg-success me-2">
                        <i class="fas fa-check me-1"></i>
                        Verified Partner
                    </span>
                    <span class="badge bg-warning">
                        <i class="fas fa-star me-1"></i>
                        4.9/5 Rating
                    </span>
                </div>
            </div>
        </div>
    </div>
</footer>

{{-- Additional Footer Styles --}}
<style>
.seferet-footer {
    background: linear-gradient(135deg, var(--primary-color) 0%, #1f5d3e 100%);
}

.footer-title {
    font-weight: 600;
    color: var(--text-title-color);
}

.footer-description {
    color: rgba(255, 255, 255, 0.8);
    line-height: 1.6;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 0.5rem;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: color 0.2s ease;
    font-size: 0.9rem;
}

.footer-links a:hover {
    color: var(--secondary-color);
}

.social-links .social-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: var(--text-title-color);
    text-decoration: none;
    transition: all 0.2s ease;
}

.social-links .social-link:hover {
    background: var(--secondary-color);
    color: var(--text-color);
    transform: translateY(-2px);
}

.contact-info .contact-item {
    display: flex;
    align-items: flex-start;
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
}

.contact-info .contact-item i {
    margin-top: 2px;
    color: var(--secondary-color);
}

.footer-divider {
    border-color: rgba(255, 255, 255, 0.2);
}

.footer-copyright {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
}

.footer-badges .badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
}

@media (max-width: 768px) {
    .footer-badges {
        margin-top: 1rem;
        text-align: center !important;
    }
    
    .social-links {
        text-align: center;
        margin-top: 1rem;
    }
}
</style>

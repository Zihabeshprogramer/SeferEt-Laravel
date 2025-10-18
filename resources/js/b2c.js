/**
 * B2C Customer Website JavaScript
 * 
 * This file handles the Inertia.js setup and customer-specific functionality
 * for the SeferEt B2C website.
 */
import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
// Create Inertia app
createInertiaApp({
    title: (title) => `${title} - SeferEt`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
    progress: {
        color: '#2E8B57',
    },
})
// SeferEt B2C Custom JavaScript
class SeferEtB2C {
    constructor() {
        this.init();
    }
    init() {
        this.setupScrollEffects();
        this.setupPackageFilters();
        this.setupContactForm();
        this.setupBookingFlow();
    }
    /**
     * Setup scroll effects for animations
     */
    setupScrollEffects() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                }
            });
        }, observerOptions);
        // Observe all elements with fade-in-on-scroll class
        document.querySelectorAll('.fade-in-on-scroll').forEach(el => {
            observer.observe(el);
        });
    }
    /**
     * Setup package filtering functionality
     */
    setupPackageFilters() {
        const filterButtons = document.querySelectorAll('.package-filter-btn');
        const packageCards = document.querySelectorAll('.package-card');
        filterButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                // Remove active class from all buttons
                filterButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                button.classList.add('active');
                const filter = button.dataset.filter;
                // Filter packages
                packageCards.forEach(card => {
                    if (filter === 'all' || card.dataset.category === filter) {
                        card.style.display = 'block';
                        card.classList.add('slide-in-up');
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });
    }
    /**
     * Setup contact form functionality
     */
    setupContactForm() {
        const contactForm = document.getElementById('contactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const submitBtn = contactForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
                // Simulate form submission (replace with actual implementation)
                setTimeout(() => {
                    this.showNotification('Message sent successfully!', 'success');
                    contactForm.reset();
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }, 2000);
            });
        }
    }
    /**
     * Setup booking flow functionality
     */
    setupBookingFlow() {
        // Package booking buttons
        document.querySelectorAll('.book-package-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const packageId = btn.dataset.packageId;
                const packageName = btn.dataset.packageName;
                // Show booking modal or redirect to booking page
                this.showBookingModal(packageId, packageName);
            });
        });
    }
    /**
     * Show booking modal
     */
    showBookingModal(packageId, packageName) {
        // Create modal HTML
        const modalHtml = `
            <div class="modal fade" id="bookingModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fas fa-calendar-plus mr-2"></i>
                                Book ${packageName}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="bookingForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Departure Date</label>
                                            <input type="date" class="form-control" name="departure_date" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Number of Travelers</label>
                                            <select class="form-control" name="travelers" required>
                                                <option value="">Select...</option>
                                                <option value="1">1 Person</option>
                                                <option value="2">2 People</option>
                                                <option value="3">3 People</option>
                                                <option value="4">4 People</option>
                                                <option value="5+">5+ People</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Special Requirements</label>
                                    <textarea class="form-control" name="requirements" rows="3" placeholder="Any special requirements or notes..."></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="confirmBooking">
                                <i class="fas fa-check mr-2"></i>
                                Confirm Booking
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        // Remove existing modal if any
        const existingModal = document.getElementById('bookingModal');
        if (existingModal) {
            existingModal.remove();
        }
        // Add modal to DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
        modal.show();
        // Handle booking confirmation
        document.getElementById('confirmBooking').addEventListener('click', () => {
            this.processBooking(packageId);
        });
    }
    /**
     * Process booking request
     */
    processBooking(packageId) {
        const form = document.getElementById('bookingForm');
        const formData = new FormData(form);
        // Add package ID
        formData.append('package_id', packageId);
        // Show loading state
        const confirmBtn = document.getElementById('confirmBooking');
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Processing...';
        // Simulate booking process (replace with actual API call)
        setTimeout(() => {
            this.showNotification('Booking request submitted successfully!', 'success');
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('bookingModal'));
            modal.hide();
            // Redirect to customer dashboard or booking confirmation
            // window.location.href = '/dashboard';
        }, 2000);
    }
    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        const notificationHtml = `
            <div class="alert alert-${type} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} mr-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', notificationHtml);
        // Auto-remove after 5 seconds
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
    /**
     * Format currency
     */
    formatCurrency(amount, currency = 'USD') {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency
        }).format(amount);
    }
    /**
     * Smooth scroll to element
     */
    scrollToElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
}
// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.SeferEtB2C = new SeferEtB2C();
});
// Export for module usage
export default SeferEtB2C;
